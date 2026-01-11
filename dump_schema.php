<?php
require_once __DIR__ . '/config/database.php';
$db = getDB();
$stmt = $db->query("SHOW CREATE TABLE products");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($row, true) . "</pre>";
?>
