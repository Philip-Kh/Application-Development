<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = getDB();
    $db->exec("ALTER TABLE products ADD COLUMN image_path VARCHAR(255) DEFAULT NULL AFTER category");
    echo "Migration successful: Added image_path column.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column already exists.\n";
    } else {
        echo "Migration failed: " . $e->getMessage() . "\n";
    }
}
?>
