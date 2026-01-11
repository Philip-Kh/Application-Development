<?php
/**
 * Migration script to update orders table to support multiple products per order
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = getDB();
    $db->beginTransaction();

    // Create order_items table
    $db->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            order_item_id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(10, 2) NOT NULL,
            total_price DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Insert existing order data into order_items
    $db->exec("
        INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
        SELECT order_id, product_id, quantity, (total_amount / quantity), total_amount
        FROM orders
    ");

    // Alter orders table: drop product_id and quantity
    $db->exec("ALTER TABLE orders DROP COLUMN product_id");
    $db->exec("ALTER TABLE orders DROP COLUMN quantity");

    // Add indexes
    $db->exec("CREATE INDEX idx_order_items_order ON order_items(order_id)");
    $db->exec("CREATE INDEX idx_order_items_product ON order_items(product_id)");

    $db->commit();
    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>