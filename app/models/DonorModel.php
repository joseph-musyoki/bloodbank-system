<?php
class DonorModel
{
    private PDO $db;

    public function __construct() { $this->db = Database::getInstance(); }

    public function findByUserId(int $userId): array|false
    {
        $s = $this->db->prepare('SELECT d.*, u.name, u.email FROM donors d JOIN users u ON d.user_id=u.id WHERE d.user_id=?');
        $s->execute([$userId]);
        return $s->fetch();
    }

    public function findById(int $id): array|false
    {
        $s = $this->db->prepare('SELECT d.*, u.name, u.email FROM donors d JOIN users u ON d.user_id=u.id WHERE d.id=?');
        $s->execute([$id]);
        return $s->fetch();
    }

    public function all(int $limit = 100, int $offset = 0): array
    {
        $s = $this->db->prepare('SELECT d.*, u.name, u.email FROM donors d JOIN users u ON d.user_id=u.id ORDER BY d.created_at DESC LIMIT :l OFFSET :o');
        $s->bindValue(':l', $limit, PDO::PARAM_INT);
        $s->bindValue(':o', $offset, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }

    public function search(array $f): array
    {
        $where = ['1=1']; $params = [];
        if (!empty($f['blood_type']))  { $where[] = 'd.blood_type=:bt'; $params[':bt'] = $f['blood_type']; }
        if (!empty($f['county']))      { $where[] = 'd.county LIKE :co'; $params[':co'] = '%'.$f['county'].'%'; }
        if (isset($f['eligible']) && $f['eligible'] !== '') { $where[] = 'd.is_eligible=:el'; $params[':el'] = (int)$f['eligible']; }
        $sql = 'SELECT d.*, u.name, u.email FROM donors d JOIN users u ON d.user_id=u.id WHERE '.implode(' AND ',$where).' ORDER BY u.name';
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }

    public function createUser(array $data): int
    {
        $s = $this->db->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)');
        $s->execute([$data['name'], $data['email'], password_hash($data['password'], PASSWORD_BCRYPT, ['cost'=>12]), 'donor']);
        return (int)$this->db->lastInsertId();
    }

    public function create(array $data): int
    {
        $s = $this->db->prepare('INSERT INTO donors (user_id,national_id,phone,date_of_birth,gender,blood_type,weight_kg,county,town,medical_notes) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $s->execute([$data['user_id'],$data['national_id'],$data['phone'],$data['dob'],$data['gender'],$data['blood_type'],$data['weight_kg'],$data['county'],$data['town'],$data['medical_notes']??null]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $s = $this->db->prepare('UPDATE donors SET phone=?,weight_kg=?,county=?,town=?,medical_notes=?,updated_at=NOW() WHERE id=?');
        $s->execute([$data['phone'],$data['weight_kg'],$data['county'],$data['town'],$data['medical_notes']??null,$id]);
    }

    public function setDeferral(int $id, ?string $until, ?string $reason): void
    {
        $eligible = ($until === null) ? 1 : 0;
        $s = $this->db->prepare('UPDATE donors SET is_eligible=?,deferral_until=?,deferral_reason=? WHERE id=?');
        $s->execute([$eligible,$until,$reason,$id]);
    }

    public function getDonationHistory(int $donorId): array
    {
        $s = $this->db->prepare('SELECT don.*, GROUP_CONCAT(bu.unit_code) as unit_codes FROM donations don LEFT JOIN blood_units bu ON bu.donation_id=don.id WHERE don.donor_id=? GROUP BY don.id ORDER BY don.donation_date DESC');
        $s->execute([$donorId]);
        return $s->fetchAll();
    }

    public function getLastDonation(int $donorId): array|false
    {
        $s = $this->db->prepare('SELECT * FROM donations WHERE donor_id=? AND status="completed" ORDER BY donation_date DESC LIMIT 1');
        $s->execute([$donorId]);
        return $s->fetch();
    }

    public function countDonationsThisYear(int $donorId): int
    {
        $s = $this->db->prepare('SELECT COUNT(*) FROM donations WHERE donor_id=? AND YEAR(donation_date)=YEAR(CURDATE()) AND status="completed"');
        $s->execute([$donorId]);
        return (int)$s->fetchColumn();
    }

    public function emailExists(string $email, int $excludeUserId = 0): bool
    {
        $s = $this->db->prepare('SELECT id FROM users WHERE email=? AND id!=?');
        $s->execute([$email, $excludeUserId]);
        return (bool)$s->fetch();
    }
}