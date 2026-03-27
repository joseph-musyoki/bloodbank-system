<?php
require __DIR__ . '/app/core/Database.php';

$db = Database::getInstance();

try {
    // Add units_fulfilled column
    $db->exec('ALTER TABLE requests ADD COLUMN units_fulfilled INT DEFAULT 0 AFTER units_requested');

    // Add staff_id column
    $db->exec('ALTER TABLE requests ADD COLUMN staff_id INT NULL AFTER fulfilled_by');

    // Add foreign key constraint
    $db->exec('ALTER TABLE requests ADD CONSTRAINT fk_requests_staff_id FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE SET NULL');

    echo "Database schema updated successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>