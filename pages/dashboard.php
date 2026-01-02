<?php
/**
 * Dashboard Page
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

try {
    $db = getDB();
    
    // Get total products count
    $stmt = $db->query("SELECT COUNT(*) as count FROM products WHERE is_deleted = 0");
    $totalProducts = $stmt->fetch()['count'];
    
    // Get low stock products count
    $stmt = $db->query("SELECT COUNT(*) as count FROM products WHERE is_deleted = 0 AND status = 'Low Stock'");
    $lowStockCount = $stmt->fetch()['count'];
    
    // Get total inventory value
    $stmt = $db->query("SELECT SUM(quantity * price) as total FROM products WHERE is_deleted = 0");
    $inventoryValue = $stmt->fetch()['total'] ?? 0;
    
    // Get today's orders count
    $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = CURDATE()");
    $todayOrders = $stmt->fetch()['count'];
    
    // Get products by category for chart
    $stmt = $db->query("SELECT category, COUNT(*) as count FROM products WHERE is_deleted = 0 GROUP BY category");
    $categoriesData = $stmt->fetchAll();
    
    // Get low stock products list
    $stmt = $db->query("SELECT product_id, product_name, quantity, min_stock_level FROM products WHERE is_deleted = 0 AND status = 'Low Stock' ORDER BY quantity ASC LIMIT 10");
    $lowStockProducts = $stmt->fetchAll();
    
    // Get top selling products (based on sale orders)
    // Get top selling products (based on sale orders)
    $stmt = $db->query("
        SELECT p.product_name, SUM(o.quantity) as total_sold 
        FROM orders o 
        JOIN products p ON o.product_id = p.product_id 
        WHERE o.order_type = 'Sale' 
        GROUP BY o.product_id 
        ORDER BY total_sold DESC 
        LIMIT 5
    ");
    $topSelling = $stmt->fetchAll();

    // Get Total Sales Value (Admin Only)
    $totalSalesValue = 0;
    if (isAdmin()) {
        $stmt = $db->query("SELECT SUM(total_amount) as total FROM orders WHERE order_type = 'Sale'");
        $totalSalesValue = $stmt->fetch()['total'] ?? 0;
    }
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $totalProducts = 0;
    $lowStockCount = 0;
    $inventoryValue = 0;
    $todayOrders = 0;
    $categoriesData = [];
    $lowStockProducts = [];
    $topSelling = [];
}
?>

<!-- Page Header -->
<div class="d-flex justify-between align-center mb-4">
    <div>
        <h1 style="font-size: var(--font-size-3xl); font-weight: 800; color: var(--white);">
            <i class="fas fa-chart-pie" style="color: var(--primary-light);"></i>
            Dashboard
        </h1>
        <p style="color: var(--gray-300); margin-top: var(--spacing-sm);">
            Welcome <?php echo sanitize(getCurrentStaffName()); ?>, here's your inventory overview
        </p>
    </div>
    <p style="color: var(--gray-400);">
        <i class="fas fa-calendar"></i>
        <?php echo date('Y/m/d'); ?>
    </p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-icon primary">
            <i class="fas fa-boxes-stacked"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($totalProducts); ?></h3>
            <p>Total Products</p>
        </div>
    </div>
    
    <div class="stat-card warning">
        <div class="stat-icon warning">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($lowStockCount); ?></h3>
            <p>Low Stock</p>
        </div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-icon success">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($inventoryValue, 2); ?></h3>
            <p>Inventory Value ($)</p>
        </div>
    </div>
    
    <div class="stat-card danger">
        <div class="stat-icon danger">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($todayOrders); ?></h3>
            <p>Today's Orders</p>
        </div>
    </div>

    <?php if (isAdmin()): ?>
    <div class="stat-card success">
        <div class="stat-icon success">
            <i class="fas fa-hand-holding-dollar"></i>
        </div>
        <div class="stat-info">
            <h3>$<?php echo number_format($totalSalesValue, 2); ?></h3>
            <p>Total Sales Value</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Charts and Alerts Row -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: var(--spacing-lg);">
    
    <!-- Category Distribution Chart -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-pie"></i>
                Products by Category
            </h3>
        </div>
        <div class="card-body">
            <?php if (empty($categoriesData)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No Products</h3>
                    <p>Add products to see distribution</p>
                </div>
            <?php else: ?>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Low Stock Alerts -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i>
                Low Stock Alerts
            </h3>
            <?php if ($lowStockCount > 0): ?>
                <span class="badge badge-warning"><?php echo $lowStockCount; ?> products</span>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($lowStockProducts)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <h3>Excellent!</h3>
                    <p>All products are at good stock levels</p>
                </div>
            <?php else: ?>
                <ul class="alert-list">
                    <?php foreach ($lowStockProducts as $product): ?>
                        <li class="alert-list-item">
                            <div>
                                <span class="product-name"><?php echo sanitize($product['product_name']); ?></span>
                                <br>
                                <small class="text-muted">Min Level: <?php echo $product['min_stock_level']; ?></small>
                            </div>
                            <span class="product-qty">
                                <i class="fas fa-box"></i>
                                <?php echo $product['quantity']; ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php if ($lowStockCount > 0): ?>
            <div class="card-footer">
                <a href="products.php?status=Low Stock" class="btn btn-warning btn-sm">
                    <i class="fas fa-eye"></i>
                    View All
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Top Selling Products -->
<?php if (!empty($topSelling)): ?>
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-fire" style="color: var(--danger);"></i>
            Top Selling Products
        </h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topSelling as $index => $product): ?>
                        <tr>
                            <td>
                                <?php if ($index === 0): ?>
                                    <i class="fas fa-trophy" style="color: gold;"></i>
                                <?php elseif ($index === 1): ?>
                                    <i class="fas fa-medal" style="color: silver;"></i>
                                <?php elseif ($index === 2): ?>
                                    <i class="fas fa-award" style="color: #cd7f32;"></i>
                                <?php else: ?>
                                    <?php echo $index + 1; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo sanitize($product['product_name']); ?></td>
                            <td>
                                <span class="badge badge-success">
                                    <?php echo number_format($product['total_sold']); ?> units
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Category Chart Script -->
<?php if (!empty($categoriesData)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    const colors = [
        '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', '#f97316',
        '#eab308', '#22c55e', '#14b8a6', '#06b6d4', '#3b82f6'
    ];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($categoriesData, 'category')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($categoriesData, 'count')); ?>,
                backgroundColor: colors.slice(0, <?php echo count($categoriesData); ?>),
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            family: 'Inter'
                        },
                        padding: 20
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true
            }
        }
    });
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
