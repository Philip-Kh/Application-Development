<?php
/**
 * Products Management Page
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

// Get filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'product_name';
$order = $_GET['order'] ?? 'ASC';

// Messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

try {
    $db = getDB();
    
    // Build query
    $where = ["is_deleted = 0"];
    $params = [];
    
    if ($search) {
        $where[] = "product_name LIKE ?";
        $params[] = "%$search%";
    }
    
    if ($category) {
        $where[] = "category = ?";
        $params[] = $category;
    }
    
    if ($status) {
        $where[] = "status = ?";
        $params[] = $status;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Validate sort column
    $allowedSorts = ['product_name', 'category', 'quantity', 'price', 'status', 'created_at'];
    $sort = in_array($sort, $allowedSorts) ? $sort : 'product_name';
    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
    
    // Get products
    $sql = "SELECT p.*, s.full_name as created_by_name 
            FROM products p 
            LEFT JOIN staff s ON p.created_by = s.staff_id 
            WHERE $whereClause 
            ORDER BY $sort $order";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Get categories for filter
    $stmt = $db->query("SELECT DISTINCT category FROM products WHERE is_deleted = 0 ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    error_log("Products page error: " . $e->getMessage());
    $products = [];
    $categories = [];
}
?>

<!-- Page Header -->
<div class="d-flex justify-between align-center mb-4" style="flex-wrap: wrap; gap: var(--spacing-md);">
    <div>
        <h1 style="font-size: var(--font-size-3xl); font-weight: 800; color: var(--white);">
            <i class="fas fa-boxes-stacked" style="color: var(--primary-light);"></i>
            Products Management
        </h1>
        <p style="color: var(--gray-300); margin-top: var(--spacing-sm);">
            View and manage all products in inventory
        </p>
    </div>
    <button class="btn btn-success" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Add New Product
    </button>
</div>

<!-- Messages -->
<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>
            <?php
            switch ($success) {
                case 'added': echo 'Product added successfully'; break;
                case 'updated': echo 'Product updated successfully'; break;
                case 'deleted': echo 'Product deleted successfully'; break;
                default: echo 'Operation completed successfully';
            }
            ?>
        </span>
        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span>
            <?php
            switch ($error) {
                case 'exists': echo 'Product name already exists'; break;
                case 'has_orders': echo 'Cannot delete product with associated orders'; break;
                case 'not_found': echo 'Product not found'; break;
                case 'server': 
                    echo 'Server Error: ' . sanitize($_GET['details'] ?? 'Unknown error'); 
                    break;
                default: 
                    echo 'An error occurred. ' . sanitize($_GET['details'] ?? '');
            }
            ?>
        </span>
        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
<?php endif; ?>

<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="toolbar">
            <div class="search-box">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Search for product..."
                    value="<?php echo sanitize($search); ?>"
                >
                <i class="fas fa-search"></i>
            </div>
            
            <div class="filter-group">
                <select name="category" class="form-control" style="min-width: 150px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo sanitize($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo sanitize($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="status" class="form-control" style="min-width: 150px;">
                    <option value="">All Status</option>
                    <option value="Normal" <?php echo $status === 'Normal' ? 'selected' : ''; ?>>Normal</option>
                    <option value="Low Stock" <?php echo $status === 'Low Stock' ? 'selected' : ''; ?>>Low Stock</option>
                </select>
                
                <select name="sort" class="form-control" style="min-width: 150px;">
                    <option value="product_name" <?php echo $sort === 'product_name' ? 'selected' : ''; ?>>Name</option>
                    <option value="category" <?php echo $sort === 'category' ? 'selected' : ''; ?>>Category</option>
                    <option value="quantity" <?php echo $sort === 'quantity' ? 'selected' : ''; ?>>Quantity</option>
                    <option value="price" <?php echo $sort === 'price' ? 'selected' : ''; ?>>Price</option>
                    <option value="created_at" <?php echo $sort === 'created_at' ? 'selected' : ''; ?>>Date Added</option>
                </select>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>
                
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Products List
            <span class="badge badge-primary"><?php echo count($products); ?></span>
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No Products</h3>
                <p>Start by adding new products to inventory</p>
                <button class="btn btn-success mt-2" onclick="openAddModal()">
                    <i class="fas fa-plus"></i>
                    Add Product
                </button>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Min Level</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['product_id']; ?></td>
                                <td>
                                    <?php if (!empty($product['image_path'])): ?>
                                        <img src="../<?php echo sanitize($product['image_path']); ?>" alt="Img" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <div style="width: 40px; height: 40px; background: var(--gray-200); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: var(--gray-500);">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo sanitize($product['product_name']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo sanitize($product['category']); ?></span>
                                </td>
                                <td>
                                    <strong><?php echo number_format($product['quantity']); ?></strong>
                                </td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $product['min_stock_level']; ?></td>
                                <td>
                                    <?php if ($product['status'] === 'Low Stock'): ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Low Stock
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i>
                                            Normal
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button 
                                            class="btn btn-primary btn-icon btn-sm" 
                                            onclick="openEditModal(<?php echo htmlspecialchars(json_encode($product)); ?>)"
                                            title="Edit"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button 
                                            class="btn btn-danger btn-icon btn-sm" 
                                            onclick="confirmDelete(<?php echo $product['product_id']; ?>, '<?php echo sanitize($product['product_name']); ?>')"
                                            title="Delete"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal-overlay" id="productModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Add New Product</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="productForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="product_id" id="productId">
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="productName" class="form-label required">Product Name</label>
                    <input 
                        type="text" 
                        id="productName" 
                        name="product_name" 
                        class="form-control" 
                        placeholder="Enter product name"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="productCategory" class="form-label required">Category</label>
                    <input 
                        type="text" 
                        id="productCategory" 
                        name="category" 
                        class="form-control" 
                        placeholder="Enter category (e.g., Beverages, Food)"
                        required
                        list="categoryList"
                    >
                    <datalist id="categoryList">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo sanitize($cat); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="form-group">
                    <label for="productImage" class="form-label">Product Image</label>
                    <input 
                        type="file" 
                        id="productImage" 
                        name="product_image" 
                        class="form-control" 
                        accept="image/*"
                    >
                    <span class="form-text">Shown on customer dashboard (JPG, PNG)</span>
                </div>
                
                <div id="quantityGroup" class="form-group">
                    <label for="productQuantity" class="form-label required">Initial Quantity</label>
                    <input 
                        type="number" 
                        id="productQuantity" 
                        name="quantity" 
                        class="form-control" 
                        placeholder="0"
                        min="0"
                        required
                    >
                    <span class="form-text">Initial quantity for new product</span>
                </div>
                
                <div class="form-group">
                    <label for="productPrice" class="form-label required">Price ($)</label>
                    <input 
                        type="number" 
                        id="productPrice" 
                        name="price" 
                        class="form-control" 
                        placeholder="0.00"
                        min="0"
                        step="0.01"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="productMinStock" class="form-label">Minimum Stock Level</label>
                    <input 
                        type="number" 
                        id="productMinStock" 
                        name="min_stock_level" 
                        class="form-control" 
                        placeholder="10"
                        min="0"
                        value="10"
                    >
                    <span class="form-text">You'll be alerted when quantity falls below this level</span>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn btn-success" id="submitBtn">
                    <i class="fas fa-save"></i>
                    <span>Save</span>
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                    <span>Cancel</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header" style="background: var(--danger); color: white;">
            <h3 class="modal-title">
                <i class="fas fa-exclamation-triangle"></i>
                Confirm Delete
            </h3>
            <button class="modal-close" onclick="closeDeleteModal()" style="color: white;">&times;</button>
        </div>
        <div class="modal-body text-center">
            <p style="font-size: var(--font-size-lg);">
                Are you sure you want to delete:
            </p>
            <p style="font-weight: 700; font-size: var(--font-size-xl); color: var(--danger);" id="deleteProductName"></p>
            <p class="text-muted">This action cannot be undone</p>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <form action="../api/products/delete.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="product_id" id="deleteProductId">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Yes, Delete
                </button>
            </form>
            <button class="btn btn-secondary" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i>
                Cancel
            </button>
        </div>
    </div>
</div>

<script>
// Modal functions
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Product';
    document.getElementById('productForm').action = '../api/products/add.php';
    document.getElementById('productId').value = '';
    document.getElementById('productName').value = '';
    document.getElementById('productCategory').value = '';
    document.getElementById('productQuantity').value = '';
    document.getElementById('productPrice').value = '';
    document.getElementById('productMinStock').value = '10';
    document.getElementById('quantityGroup').style.display = 'block';
    document.getElementById('productModal').classList.add('active');
}

function openEditModal(product) {
    document.getElementById('modalTitle').textContent = 'Edit Product';
    document.getElementById('productForm').action = '../api/products/edit.php';
    document.getElementById('productId').value = product.product_id;
    document.getElementById('productName').value = product.product_name;
    document.getElementById('productCategory').value = product.category;
    document.getElementById('productQuantity').value = product.quantity;
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productMinStock').value = product.min_stock_level;
    // Hide quantity field for edit (can only be changed through orders)
    document.getElementById('quantityGroup').style.display = 'none';
    document.getElementById('productQuantity').removeAttribute('required');
    document.getElementById('productModal').classList.add('active');
}

function closeModal() {
    document.getElementById('productModal').classList.remove('active');
    document.getElementById('quantityGroup').style.display = 'block';
    document.getElementById('productQuantity').setAttribute('required', 'required');
}

function confirmDelete(id, name) {
    document.getElementById('deleteProductId').value = id;
    document.getElementById('deleteProductName').textContent = name;
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(modal => {
            modal.classList.remove('active');
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
