<?php
class InventoryModel
{
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    /** Live stock count grouped by blood type + component */
    public function getLiveStock(): array
    {
        return $this->db->query(
            "SELECT blood_type, component,
                    COUNT(*) as total_units,
                    SUM(CASE WHEN status='available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN status='reserved'  THEN 1 ELSE 0 END) as reserved,
                    MIN(expiry_date) as nearest_expiry,
                    SUM(CASE WHEN status='available' AND expiry_date <= DATE_ADD(CURDATE(),INTERVAL 7 DAY) THEN 1 ELSE 0 END) as expiring_soon
             FROM blood_units
             WHERE status IN ('available','reserved')
             GROUP BY blood_type, component
             ORDER BY blood_type, component"
        )->fetchAll();
    }

    /** Units expiring within N days */
    public function getNearExpiry(int $days = 7): array
    {
        $s = $this->db->prepare(
            "SELECT bu.*, u.name as donor_name
             FROM blood_units bu
             JOIN donations d  ON d.id = bu.donation_id
             JOIN donors don   ON don.id = d.donor_id
             JOIN users u      ON u.id  = don.user_id
             WHERE bu.status='available'
               AND bu.expiry_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
             ORDER BY bu.expiry_date ASC"
        );
        $s->execute([':days' => $days]);
        return $s->fetchAll();
    }

    /** Available units by blood type + component, prioritising soonest-expiry (FIFO) */
    public function getAvailable(string $bloodType, string $component, int $limit = 50): array
    {
        $s = $this->db->prepare(
            "SELECT * FROM blood_units
             WHERE blood_type=? AND component=? AND status='available'
             ORDER BY expiry_date ASC
             LIMIT ?"
        );
        $s->execute([$bloodType, $component, $limit]);
        return $s->fetchAll();
    }

    /** Compatible units for a request */
    public function getCompatibleUnits(string $recipientType, string $component, int $needed): array
    {
        $compatible = BloodCompatibility::getCompatibleDonors($recipientType, $component);
        if (empty($compatible)) return [];
        $placeholders = implode(',', array_fill(0, count($compatible), '?'));
        $s = $this->db->prepare(
            "SELECT * FROM blood_units
             WHERE blood_type IN ({$placeholders})
               AND component=?
               AND status='available'
             ORDER BY expiry_date ASC
             LIMIT ?"
        );
        $s->execute([...$compatible, $component, $needed]);
        return $s->fetchAll();
    }

    /** Thresholds */
    public function getThresholds(): array
    {
        $rows = $this->db->query('SELECT * FROM inventory')->fetchAll();
        $map  = [];
        foreach ($rows as $r) $map[$r['blood_type'].':'.$r['component']] = $r;
        return $map;
    }

    /** Generate alerts based on current stock vs thresholds */
    public function generateAlerts(): array
    {
        $stock     = $this->getLiveStock();
        $threshold = $this->getThresholds();
        $alerts    = [];

        foreach ($stock as $row) {
            $key = $row['blood_type'] . ':' . $row['component'];
            $min = $threshold[$key]['min_units']      ?? 10;
            $crit= $threshold[$key]['critical_units']  ?? 5;
            $avail = (int)$row['available'];

            if ($avail <= $crit) {
                $alerts[] = ['type' => 'critical', 'blood_type' => $row['blood_type'], 'component' => $row['component'],
                             'units' => $avail, 'message' => "CRITICAL: Only {$avail} unit(s) of {$row['blood_type']} {$row['component']} available."];
            } elseif ($avail <= $min) {
                $alerts[] = ['type' => 'low', 'blood_type' => $row['blood_type'], 'component' => $row['component'],
                             'units' => $avail, 'message' => "LOW STOCK: {$avail} unit(s) of {$row['blood_type']} {$row['component']} remaining."];
            }
            if ((int)$row['expiring_soon'] > 0) {
                $alerts[] = ['type' => 'expiry', 'blood_type' => $row['blood_type'], 'component' => $row['component'],
                             'units' => $row['expiring_soon'], 'message' => "{$row['expiring_soon']} unit(s) of {$row['blood_type']} expire within 7 days."];
            }
        }
        return $alerts;
    }

    public function addUnit(array $data): int
    {
        $code = 'BU-' . date('Y') . '-' . str_pad((string)rand(1,99999), 5, '0', STR_PAD_LEFT);
        $s = $this->db->prepare('INSERT INTO blood_units (unit_code,donation_id,blood_type,component,volume_ml,collected_date,expiry_date,location) VALUES (?,?,?,?,?,?,?,?)');
        $s->execute([$code,$data['donation_id'],$data['blood_type'],$data['component'],$data['volume_ml'],$data['collected_date'],$data['expiry_date'],$data['location']??'Main Storage']);
        return (int)$this->db->lastInsertId();
    }

    public function markIssued(int $unitId): void
    {
        $s = $this->db->prepare('UPDATE blood_units SET status="issued", updated_at=NOW() WHERE id=?');
        $s->execute([$unitId]);
    }

    public function markExpired(): int
    {
        $s = $this->db->prepare("UPDATE blood_units SET status='expired' WHERE status='available' AND expiry_date < CURDATE()");
        $s->execute();
        return $s->rowCount();
    }

    public function findByCode(string $code): array|false
    {
        $s = $this->db->prepare('SELECT * FROM blood_units WHERE unit_code=?');
        $s->execute([$code]);
        return $s->fetch();
    }

    public function summaryStats(): array
    {
        return $this->db->query(
            "SELECT
               (SELECT COUNT(*) FROM blood_units WHERE status='available') as available_units,
               (SELECT COUNT(*) FROM blood_units WHERE status='available' AND expiry_date <= DATE_ADD(CURDATE(),INTERVAL 7 DAY)) as expiring_7d,
               (SELECT COUNT(*) FROM blood_units WHERE status='expired') as expired_units,
               (SELECT COUNT(*) FROM requests WHERE status='pending') as pending_requests,
               (SELECT COUNT(*) FROM donors WHERE is_eligible=1) as eligible_donors,
               (SELECT COUNT(*) FROM donations WHERE donation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as donations_30d"
        )->fetch();
    }
}