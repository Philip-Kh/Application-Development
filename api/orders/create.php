<?php
/**
 * Create Order API

 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/orders.php?error=invalid');
    exit();
}

// Validate CSRF
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../../pages/orders.php?error=csrf');
    exit();
}

// Get form data
$order_type = $_POST['order_type'] ?? '';
$order_date = $_POST['order_date'] ?? date('Y-m-d');
$order_customer_supplier = trim($_POST['customer_supplier_name'] ?? '');

// Arrays
$product_ids = $_POST['product_id'] ?? [];
$quantities = $_POST['quantity'] ?? [];

// Validation - Basic
if (!in_array($order_type, ['Sale', 'Purchase'])) {
    header('Location: ../../pages/orders.php?error=invalid');
    exit();
}

if (empty($product_ids) || !is_array($product_ids) || empty($order_customer_supplier)) {
    header('Location: ../../pages/orders.php?error=empty');
    exit();
}

try {
    $db = getDB();
    $db->beginTransaction();

    $staff_id = getCurrentStaffId();

    // Check if arrays match
    if (count($product_ids) !== count($quantities)) {
        throw new Exception("Mismatch in products and quantities");
    }

    $total_order_amount = 0;
    $order_items = [];

    foreach ($product_ids as $index => $pid) {
        $product_id = intval($pid);
        $qty = intval($quantities[$index] ?? 0);

        if ($product_id <= 0 || $qty <= 0) {
            continue; // Skip invalid rows
        }

        // Get product info
        $stmt = $db->prepare("SELECT product_id, quantity, price, min_stock_level, product_name FROM products WHERE product_id = ? AND is_deleted = 0");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            $db->rollBack();
            header('Location: ../../pages/orders.php?error=not_found');
            exit();
        }

        // For sale orders, check if sufficient quantity
        if ($order_type === 'Sale' && $qty > $product['quantity']) {
            $db->rollBack();
            header('Location: ../../pages/orders.php?error=insufficient');
            exit();
        }

        // Calculate new quantity
        if ($order_type === 'Sale') {
            $new_quantity = $product['quantity'] - $qty;
        } else {
            $new_quantity = $product['quantity'] + $qty;
        }

        $item_total = $qty * $product['price'];
        $total_order_amount += $item_total;

        // Determine new status
        $new_status = ($new_quantity < $product['min_stock_level']) ? 'Low Stock' : 'Normal';

        // Update product
        $stmt = $db->prepare("UPDATE products SET quantity = ?, status = ?, updated_at = NOW() WHERE product_id = ?");
        $stmt->execute([$new_quantity, $new_status, $product_id]);

        // Store item data for later insertion
        $order_items[] = [
            'product_id' => $product_id,
            'quantity' => $qty,
            'unit_price' => $product['price'],
            'total_price' => $item_total,
            'product_name' => $product['product_name']
        ];
    }

    if (empty($order_items)) {
        header('Location: ../../pages/orders.php?error=empty');
        exit();
    }

    // Create order record
    $stmt = $db->prepare("
        INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $order_type,
        $order_customer_supplier,
        $total_order_amount,
        $order_date,
        $staff_id
    ]);

    $order_id = $db->lastInsertId();

    // Insert order items
    $stmt = $db->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($order_items as $item) {
        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['unit_price'],
            $item['total_price']
        ]);
    }

    require_once __DIR__ . '/../../includes/activity_logger.php';
    logActivity('create_order', 'orders', $order_id, "Created $order_type order for $order_customer_supplier with " . count($order_items) . " items (Total: $$total_order_amount)");

    $db->commit();
    header('Location: ../../pages/orders.php?success=created');
    exit();

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Create order error: " . $e->getMessage());
    header('Location: ../../pages/orders.php?error=server');
    exit();
}
?>
