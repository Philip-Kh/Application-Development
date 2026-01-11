<?php
require_once 'config/database.php';
$db = getDB();

$tables = ['orders', 'order_items'];

foreach ($tables as $table) {
    try {
        $stmt = $db->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>$table</h3><pre>";
        foreach ($columns as $col) {
            echo $col['Field'] . ' ' . $col['Type'] . "\n";
        }
        echo "</pre>";
    } catch (Exception $e) {
        echo "<h3>$table</h3><p>Table does not exist</p>";
    }
}
?>