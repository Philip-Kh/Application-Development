<?php
/**
 * Public Storefront / Customer Dashboard
 */
require_once __DIR__ . '/../config/database.php';

// Get filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

try {
    $db = getDB();
    
    // Build query
    $where = ["is_deleted = 0", "quantity > 0"]; // Only show in-stock items
    $params = [];
    
    if ($search) {
        $where[] = "product_name LIKE ?";
        $params[] = "%$search%";
    }
    
    if ($category) {
        $where[] = "category = ?";
        $params[] = $category;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Get products
    $sql = "SELECT * FROM products WHERE $whereClause ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Get distinct categories
    $stmt = $db->query("SELECT DISTINCT category FROM products WHERE is_deleted = 0 AND quantity > 0 ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $products = [];
    $categories = [];
}

require_once __DIR__ . '/../includes/public_header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem;">Welcome to Quick Mart</h1>
        <p style="font-size: 1.2rem; opacity: 0.9;">Browse our collection of quality products</p>
    </div>
</div>

<main class="main-content" style="margin-top: 0;">
    <div class="container">
        
        <!-- Search & Filter -->
        <div class="card mb-4" style="margin-top: -3rem; position: relative; z-index: 10;">
            <div class="card-body">
                <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 250px;">
                        <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div style="flex: 0 0 200px;">
                        <select name="category" class="form-control" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="store.php" class="btn btn-secondary">Clear</a>
                </form>
            </div>
        </div>
        
        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 4rem;">
                    <i class="fas fa-box-open" style="font-size: 4rem; color: var(--gray-300); margin-bottom: 1rem;"></i>
                    <h3>No products found</h3>
                    <p style="color: var(--gray-500);">Try adjusting your search or filters.</p>
                </div>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['image_path'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <!-- Placeholder image based on category -->
                                <?php if (stripos($product['category'], 'electron') !== false): ?>
                                    <i class="fas fa-laptop"></i>
                                <?php elseif (stripos($product['category'], 'food') !== false): ?>
                                    <i class="fas fa-utensils"></i>
                                <?php elseif (stripos($product['category'], 'cloth') !== false): ?>
                                    <i class="fas fa-tshirt"></i>
                                <?php else: ?>
                                    <i class="fas fa-shopping-bag"></i>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="product-details">
                            <div style="margin-bottom: 0.5rem;">
                                <span class="badge badge-info"><?php echo htmlspecialchars($product['category']); ?></span>
                                <?php if ($product['quantity'] < 10): ?>
                                    <span class="badge badge-warning">Only <?php echo $product['quantity']; ?> left!</span>
                                <?php endif; ?>
                            </div>
                            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--dark);">
                                <?php echo htmlspecialchars($product['product_name']); ?>
                            </h3>
                            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto;">
                                <div class="price-tag">
                                    <?php echo number_format($product['price'], 2); ?> <small style="font-size: 0.8rem; font-weight: 500;">SAR</small>
                                </div>
                                <button class="btn btn-primary btn-sm btn-icon" title="Add to Cart (Coming Soon)">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    </div>

</main>

<?php require_once __DIR__ . '/../includes/public_footer.php'; ?>
