<?php
/**
 * Delete Product API
 
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/products.php?error=invalid');
    exit();
}

// Validate CSRF
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../../pages/products.php?error=csrf');
    exit();
}

$product_id = intval($_POST['product_id'] ?? 0);

if (!$product_id) {
    header('Location: ../../pages/products.php?error=invalid');
    exit();
}

try {
    $db = getDB();
    
    // Check if product exists and get details
    $stmt = $db->prepare("SELECT product_id, product_name FROM products WHERE product_id = ? AND is_deleted = 0");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: ../../pages/products.php?error=not_found');
        exit();
    }

    // Delete order_items first to allow product deletion
    $stmt = $db->prepare("DELETE FROM order_items WHERE product_id = ?");
    $stmt->execute([$product_id]);

    // Hard delete the product
    $stmt = $db->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $delete_type = 'hard';

    require_once __DIR__ . '/../../includes/activity_logger.php';
    logActivity('delete_product', 'product', $product_id, "Deleted product: {$product['product_name']} ($delete_type delete)");

    header('Location: ../../pages/products.php?success=deleted');
    exit();
    
} catch (PDOException $e) {
    error_log("Delete product error: " . $e->getMessage());
    header('Location: ../../pages/products.php?error=server');
    exit();
}
?>
