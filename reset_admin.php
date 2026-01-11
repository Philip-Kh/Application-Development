<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("UPDATE staff SET password = ? WHERE staff_id = 'ADMIN001'");
    $stmt->execute([$hash]);
    
    echo "Admin password updated successfully to: admin123\n";
    echo "Hash: " . $hash . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
