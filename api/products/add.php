<?php
/**
 * Add Product API

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

// Get form data
$product_name = trim($_POST['product_name'] ?? '');
$category = trim($_POST['category'] ?? '');
$quantity = intval($_POST['quantity'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$min_stock_level = intval($_POST['min_stock_level'] ?? 10);

// Validation
if (empty($product_name) || empty($category)) {
    header('Location: ../../pages/products.php?error=empty');
    exit();
}

if ($quantity < 0 || $price < 0 || $min_stock_level < 0) {
    header('Location: ../../pages/products.php?error=invalid');
    exit();
}

// Determine status
$status = ($quantity < $min_stock_level) ? 'Low Stock' : 'Normal';

try {
    $db = getDB();
    
    // Check if product name already exists
    $stmt = $db->prepare("SELECT product_id FROM products WHERE product_name = ? AND is_deleted = 0");
    $stmt->execute([$product_name]);
    if ($stmt->fetch()) {
        header('Location: ../../pages/products.php?error=exists');
        exit();
    }
    
    // Image Upload Handling
    $image_path = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['product_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $uploadDir = __DIR__ . '/../../assets/uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique name
            $newFilename = uniqid('prod_') . '.' . $ext;
            $destPath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $destPath)) {
                $image_path = 'assets/uploads/products/' . $newFilename;
            }
        }
    }

    // Insert product
    $stmt = $db->prepare("
        INSERT INTO products (product_name, category, image_path, quantity, price, min_stock_level, status, created_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $product_name,
        $category,
        $image_path,
        $quantity,
        $price,
        $min_stock_level,
        $status,
        getCurrentStaffId()
    ]);

    $product_id = $db->lastInsertId();

    require_once __DIR__ . '/../../includes/activity_logger.php';
    logActivity('create_product', 'product', $product_id, "Added product: $product_name (Category: $category, Qty: $quantity, Price: $$price)");

    header('Location: ../../pages/products.php?success=added');
    exit();
    
} catch (PDOException $e) {
    error_log("Add product error: " . $e->getMessage());
    header('Location: ../../pages/products.php?error=server&details=' . urlencode($e->getMessage()));
    exit();
}
?>
