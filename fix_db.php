<?php
require_once __DIR__ . '/config/database.php';
$db = getDB();

try {
    echo "Attempting to add column...\n";
    $result = $db->exec("ALTER TABLE products ADD image_path VARCHAR(255) DEFAULT NULL");
    
    if ($result === false) {
        print_r($db->errorInfo());
    } else {
        echo "ALTER command executed. Result: " . var_export($result, true) . "\n";
    }

    echo "Verifying...\n";
    $stmt = $db->query("SHOW COLUMNS FROM products LIKE 'image_path'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($col) {
        echo "VERIFIED: Column exists.\n";
    } else {
        echo "FAILED: Column still missing.\n";
    }

} catch (PDOException $e) {
    echo "Exception: " . $e->getMessage();
}
?>
