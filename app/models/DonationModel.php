<?php
class DonationModel
{
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function record(array $data): int
    {
        $this->db->beginTransaction();
        try {
            // Insert donation
            $s = $this->db->prepare(
                'INSERT INTO donations (donor_id,appointment_id,donation_date,volume_ml,hemoglobin,blood_pressure,pulse,donation_site,staff_id,status,rejection_reason)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)'
            );
            $s->execute([
                $data['donor_id'], $data['appointment_id']??null,
                $data['donation_date'], $data['volume_ml']??450,
                $data['hemoglobin']??null, $data['blood_pressure']??null,
                $data['pulse']??null, $data['donation_site'],
                $data['staff_id']??null,
                $data['status']??'completed',
                $data['rejection_reason']??null,
            ]);
            $donationId = (int)$this->db->lastInsertId();

            // If completed, auto-create blood unit
            if (($data['status']??'completed') === 'completed') {
                $donor = (new DonorModel())->findById($data['donor_id']);
                $expiry = $this->calculateExpiry($data['donation_date'], $data['component']??'whole_blood');
                $inv = new InventoryModel();
                $inv->addUnit([
                    'donation_id'    => $donationId,
                    'blood_type'     => $donor['blood_type'],
                    'component'      => $data['component'] ?? 'whole_blood',
                    'volume_ml'      => $data['volume_ml'] ?? 450,
                    'collected_date' => $data['donation_date'],
                    'expiry_date'    => $expiry,
                    'location'       => $data['location'] ?? 'Main Storage',
                ]);
                // Update appointment status
                if (!empty($data['appointment_id'])) {
                    $this->db->prepare("UPDATE appointments SET status='completed' WHERE id=?")->execute([$data['appointment_id']]);
                }
            }
            $this->db->commit();
            return $donationId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Shelf life by component */
    public function calculateExpiry(string $collectedDate, string $component): string
    {
        $shelfLife = [
            'whole_blood'    => 35,  // days
            'packed_cells'   => 42,
            'platelets'      => 5,
            'plasma'         => 365,
            'cryoprecipitate'=> 365,
        ];
        $days = $shelfLife[$component] ?? 35;
        return (new DateTime($collectedDate))->modify("+{$days} days")->format('Y-m-d');
    }

    public function recent(int $limit = 20): array
    {
        return $this->db->query(
            "SELECT don.*, u.name as donor_name, d.blood_type
             FROM donations don
             JOIN donors d ON d.id=don.donor_id
             JOIN users u  ON u.id=d.user_id
             ORDER BY don.donation_date DESC, don.created_at DESC
             LIMIT {$limit}"
        )->fetchAll();
    }

    public function monthlyStats(): array
    {
        return $this->db->query(
            "SELECT DATE_FORMAT(donation_date,'%Y-%m') as month,
                    COUNT(*) as total,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status='rejected'  THEN 1 ELSE 0 END) as rejected
             FROM donations
             WHERE donation_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month"
        )->fetchAll();
    }
}