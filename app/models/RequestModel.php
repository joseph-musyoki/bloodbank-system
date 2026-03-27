<?php
class RequestModel
{
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function create(array $data): int
    {
        $s = $this->db->prepare(
            'INSERT INTO requests (hospital_id,patient_name,patient_age,blood_type,component,units_requested,urgency,clinical_notes,required_by)
             VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $s->execute([$data['hospital_id'],$data['patient_name'],$data['patient_age']??null,$data['blood_type'],$data['component'],$data['units_requested'],$data['urgency'],$data['clinical_notes']??null,$data['required_by']??null]);
        return (int)$this->db->lastInsertId();
    }

    public function all(int $limit = 100): array
    {
        return $this->db->query(
            "SELECT r.id, r.hospital_id, r.patient_name, r.patient_age, r.blood_type, r.component, r.units_requested, r.units_fulfilled, r.urgency, r.clinical_notes, r.required_by, r.status, r.created_at, u.name as hospital_name, h.county as hospital_county
             FROM requests r
             JOIN hospitals h ON h.id=r.hospital_id
             JOIN users u ON u.id=h.user_id
             ORDER BY FIELD(r.urgency,'emergency','urgent','routine'), r.created_at DESC
             LIMIT {$limit}"
        )->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $s = $this->db->prepare(
            'SELECT r.*, u.name as hospital_name, h.phone as hospital_phone
             FROM requests r JOIN hospitals h ON h.id=r.hospital_id JOIN users u ON u.id=h.user_id
             WHERE r.id=?'
        );
        $s->execute([$id]);
        return $s->fetch();
    }

    public function byHospital(int $hospitalId): array
    {
        $s = $this->db->prepare("SELECT * FROM requests WHERE hospital_id=? ORDER BY created_at DESC");
        $s->execute([$hospitalId]);
        return $s->fetchAll();
    }

    public function fulfill(int $requestId, array $unitIds, int $staffId): void
    {
        $this->db->beginTransaction();
        try {
            $inv = new InventoryModel();
            foreach ($unitIds as $uid) {
                $s = $this->db->prepare('INSERT IGNORE INTO request_units (request_id,unit_id) VALUES (?,?)');
                $s->execute([$requestId, $uid]);
                $inv->markIssued((int)$uid);
            }
            $fulfilled = count($unitIds);
            $s = $this->db->prepare(
                "UPDATE requests SET
                   units_fulfilled = units_fulfilled + ?,
                   staff_id = ?,
                   processed_at = NOW(),
                   status = CASE
                     WHEN units_fulfilled + ? >= units_requested THEN 'fulfilled'
                     ELSE 'partial'
                   END
                 WHERE id=?"
            );
            $s->execute([$fulfilled, $staffId, $fulfilled, $requestId]);
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function cancel(int $id): void
    {
        $s = $this->db->prepare("UPDATE requests SET status='cancelled' WHERE id=?");
        $s->execute([$id]);
    }

    public function pendingCount(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM requests WHERE status='pending'")->fetchColumn();
    }

    public function getIssuedUnits(int $requestId): array
    {
        $s = $this->db->prepare(
            'SELECT bu.*, ru.issued_at FROM request_units ru JOIN blood_units bu ON bu.id=ru.unit_id WHERE ru.request_id=?'
        );
        $s->execute([$requestId]);
        return $s->fetchAll();
    }
}