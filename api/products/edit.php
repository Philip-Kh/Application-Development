<?php
/**
 * Edit Product API
 
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
$product_id = intval($_POST['product_id'] ?? 0);
$product_name = trim($_POST['product_name'] ?? '');
$category = trim($_POST['category'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$min_stock_level = intval($_POST['min_stock_level'] ?? 10);

// Validation
if (!$product_id || empty($product_name) || empty($category)) {
    header('Location: ../../pages/products.php?error=empty');
    exit();
}

if ($price < 0 || $min_stock_level < 0) {
    header('Location: ../../pages/products.php?error=invalid');
    exit();
}

try {
    $db = getDB();
    
    // Check if product exists
    $stmt = $db->prepare("SELECT quantity FROM products WHERE product_id = ? AND is_deleted = 0");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: ../../pages/products.php?error=not_found');
        exit();
    }
    
    // Check if product name already exists (for another product)
    $stmt = $db->prepare("SELECT product_id FROM products WHERE product_name = ? AND product_id != ? AND is_deleted = 0");
    $stmt->execute([$product_name, $product_id]);
    if ($stmt->fetch()) {
        header('Location: ../../pages/products.php?error=exists');
        exit();
    }
    
    // Determine status based on current quantity
    $status = ($product['quantity'] < $min_stock_level) ? 'Low Stock' : 'Normal';
    
    // Image Upload Handling
    $imageUpdateSql = "";
    $imageParams = [];
    
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
                $imageUpdateSql = ", image_path = ?";
                $imageParams[] = 'assets/uploads/products/' . $newFilename;
            }
        }
    }

    // Prepare Update Query
    $sql = "UPDATE products 
            SET product_name = ?, category = ?, price = ?, min_stock_level = ?, status = ?, updated_by = ?, updated_at = NOW() $imageUpdateSql
            WHERE product_id = ?";
            
    $params = [
        $product_name,
        $category,
        $price,
        $min_stock_level,
        $status,
        getCurrentStaffId()
    ];
    
    // Merge image params if exist
    if (!empty($imageParams)) {
        $params = array_merge($params, $imageParams);
    }
    
    // Add product_id at the end
    $params[] = $product_id;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    require_once __DIR__ . '/../../includes/activity_logger.php';
    logActivity('update_product', 'product', $product_id, "Updated product: $product_name (Category: $category, Price: $$price, Min Stock: $min_stock_level)");

    header('Location: ../../pages/products.php?success=updated');
    exit();
    
} catch (PDOException $e) {
    error_log("Edit product error: " . $e->getMessage());
    header('Location: ../../pages/products.php?error=server');
    exit();
}
?>
