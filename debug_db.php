<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    $stmt = $db->query("DESCRIBE products");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in products table:\n";
    print_r($columns);
    
    if (in_array('image_path', $columns)) {
        echo "\nSUCCESS: image_path column exists.\n";
    } else {
        echo "\nFAILURE: image_path column MISSING.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
