<?php
/**
 * Delete Order API

 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

// Only accept POST requests - FAILED SCENARIO: If not POST, redirect with 'invalid' error
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/orders.php?error=invalid');
    exit();
}

// Validate CSRF - FAILED SCENARIO: Invalid/missing CSRF token redirects with 'csrf' error
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../../pages/orders.php?error=csrf');
    exit();
}

$order_id = intval($_POST['order_id'] ?? 0);

if (!$order_id) {
    header('Location: ../../pages/orders.php?error=invalid');
    exit();
}

try {
    $db = getDB();
    $db->beginTransaction();

    // Get order info
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        $db->rollBack();
        header('Location: ../../pages/orders.php?error=not_found'); // FAILED SCENARIO: Order not found, transaction rolled back
        exit();
    }

    // Get all order items
    $stmt = $db->prepare("SELECT oi.*, p.quantity as product_quantity, p.min_stock_level
                          FROM order_items oi
                          JOIN products p ON oi.product_id = p.product_id
                          WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();

    // Reverse the effect on inventory for each item
    foreach ($order_items as $item) {
        if ($order['order_type'] === 'Sale') {
            // Sale was deleted: return quantity to inventory
            $new_quantity = $item['product_quantity'] + $item['quantity'];
        } else {
            // Purchase was deleted: deduct quantity from inventory
            $new_quantity = $item['product_quantity'] - $item['quantity'];
            // Ensure quantity doesn't go negative
            if ($new_quantity < 0) {
                $new_quantity = 0;
            }
        }

        // Determine new status
        $new_status = ($new_quantity < $item['min_stock_level']) ? 'Low Stock' : 'Normal';

        // Update product quantity and status
        $stmt = $db->prepare("UPDATE products SET quantity = ?, status = ?, updated_at = NOW() WHERE product_id = ?");
        $stmt->execute([$new_quantity, $new_status, $item['product_id']]);
    }

    // Delete order items first (due to foreign key)
    $stmt = $db->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);

    // Delete order
    $stmt = $db->prepare("DELETE FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);

    require_once __DIR__ . '/../../includes/activity_logger.php';
    logActivity('delete_order', 'order', $order_id, "Deleted {$order['order_type']} order for {$order['customer_supplier_name']} (Total: $${order['total_amount']})");

    $db->commit();
    header('Location: ../../pages/orders.php?success=deleted');
    exit();

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack(); // FAILED SCENARIO: Any database error rolls back the transaction
    }
    error_log("Delete order error: " . $e->getMessage());
    header('Location: ../../pages/orders.php?error=server');
    exit();
}
?>
