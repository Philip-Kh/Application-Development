<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    
    // Check tables existence
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database: " . implode(", ", $tables) . "\n\n";
    
    // Check staff table columns
    $columns = $db->query("DESCRIBE staff")->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in staff table:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    echo "\n";
    
    // Check for ADMIN001 user
    $stmt = $db->prepare("SELECT staff_id, full_name, role, is_active, login_attempts, locked_until FROM staff WHERE staff_id = 'ADMIN001'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "Admin user (ADMIN001) found:\n";
        print_r($admin);
    } else {
        echo "Admin user (ADMIN001) NOT FOUND in database!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
