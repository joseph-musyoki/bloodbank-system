<?php
/**
 * seed_users.php — Run from terminal: php database/seed_users.php
 * Creates/updates demo accounts with password "password"
 * Safe to re-run (uses INSERT ... ON DUPLICATE KEY UPDATE)
 */

define('BASE_PATH', __DIR__);
require BASE_PATH . '/app/core/Database.php';

$db   = Database::getInstance();
$hash = password_hash('password', PASSWORD_BCRYPT, ['cost' => 10]);

$users = [
    ['Dr. Amina Odera',   'staff@bloodbank.ke',   'staff'],
    ['James Kamau',       'donor@test.ke',         'donor'],
    ['Kenyatta Hospital', 'hospital@test.ke',      'hospital'],
];

foreach ($users as [$name, $email, $role]) {
    $s = $db->prepare('
        INSERT INTO users (name, email, password_hash, role)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role)
    ');
    $s->execute([$name, $email, $hash, $role]);
    echo "✓ {$role}: {$email} → password: password\n";
}

// Ensure donor record exists for donor user
$s = $db->prepare('SELECT id FROM users WHERE email=?');
$s->execute(['donor@test.ke']);
$userId = (int)$s->fetchColumn();

if ($userId) {
    $s = $db->prepare('SELECT id FROM donors WHERE user_id=?');
    $s->execute([$userId]);
    if (!$s->fetch()) {
        $s = $db->prepare('
            INSERT INTO donors (user_id, national_id, phone, date_of_birth, gender, blood_type, weight_kg, county, town)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $s->execute([$userId, '12345678', '0712345678', '1990-05-20', 'male', 'O+', 72.5, 'Nairobi', 'Westlands']);
        echo "✓ Donor profile created for donor@test.ke\n";
    } else {
        echo "✓ Donor profile already exists\n";
    }
}

// Ensure hospital record exists
$s = $db->prepare('SELECT id FROM users WHERE email=?');
$s->execute(['hospital@test.ke']);
$hospUserId = (int)$s->fetchColumn();

if ($hospUserId) {
    $s = $db->prepare('SELECT id FROM hospitals WHERE user_id=?');
    $s->execute([$hospUserId]);
    if (!$s->fetch()) {
        $s = $db->prepare('
            INSERT INTO hospitals (user_id, registration, phone, county, address, type, level, is_verified)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $s->execute([$hospUserId, 'KEPH-001', '0722000000', 'Nairobi', 'Hospital Road, Nairobi', 'public', 1, 1]);
        echo "✓ Hospital profile created for hospital@test.ke\n";
    } else {
        echo "✓ Hospital profile already exists\n";
    }
}

echo "\nDone. Login at /login with any of the above emails.\n";