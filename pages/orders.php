<?php
/**
 * Orders Management Page
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

// Get filter parameters
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

try {
    $db = getDB();
    
    // Build query
    $where = ["1=1"];
    $params = [];
    
    if ($search) {
        $where[] = "o.customer_supplier_name LIKE ?";
        $params[] = "%$search%";
    }
    
    if ($type) {
        $where[] = "o.order_type = ?";
        $params[] = $type;
    }
    
    if ($date_from) {
        $where[] = "o.order_date >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $where[] = "o.order_date <= ?";
        $params[] = $date_to;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Get orders with item count
    $sql = "SELECT o.*, s.full_name as staff_name,
                   COUNT(oi.order_item_id) as item_count,
                   GROUP_CONCAT(p.product_name SEPARATOR ', ') as product_names
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.product_id
            LEFT JOIN staff s ON o.staff_id = s.staff_id
            WHERE $whereClause
            GROUP BY o.order_id
            ORDER BY o.created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    // Get products for dropdown (only non-deleted)
    $stmt = $db->query("SELECT product_id, product_name, quantity, price FROM products WHERE is_deleted = 0 ORDER BY product_name");
    $products = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Orders page error: " . $e->getMessage());
    $orders = [];
    $products = [];
}
?>

<!-- Page Header -->
<div class="d-flex justify-between align-center mb-4" style="flex-wrap: wrap; gap: var(--spacing-md);">
    <div>
        <h1 style="font-size: var(--font-size-3xl); font-weight: 800; color: var(--white);">
            <i class="fas fa-file-invoice" style="color: var(--primary-light);"></i>
            Orders Management
        </h1>
        <p style="color: var(--gray-300); margin-top: var(--spacing-sm);">
            Create and track sales and purchase orders
        </p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-success" onclick="openOrderModal('Sale')">
            <i class="fas fa-shopping-cart"></i>
            Sale Order
        </button>
        <button class="btn btn-primary" onclick="openOrderModal('Purchase')">
            <i class="fas fa-truck"></i>
            Purchase Order
        </button>
    </div>
</div>

<!-- Messages -->
<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>
            <?php
            switch ($success) {
                case 'created': echo 'Order created successfully'; break;
                case 'deleted': echo 'Order deleted and inventory updated'; break;
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
                case 'insufficient': echo 'Requested quantity exceeds available stock'; break;
                case 'not_found': echo 'Order not found'; break;
                case 'empty': echo 'Please fill in all required fields'; break;
                default: echo 'An error occurred, please try again';
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
                    placeholder="Search by customer or supplier..."
                    value="<?php echo sanitize($search); ?>"
                >
                <i class="fas fa-search"></i>
            </div>
            
            <div class="filter-group">
                <select name="type" class="form-control" style="min-width: 150px;">
                    <option value="">All Types</option>
                    <option value="Sale" <?php echo $type === 'Sale' ? 'selected' : ''; ?>>Sale</option>
                    <option value="Purchase" <?php echo $type === 'Purchase' ? 'selected' : ''; ?>>Purchase</option>
                </select>
                
                <input 
                    type="date" 
                    name="date_from" 
                    class="form-control" 
                    style="min-width: 150px;"
                    value="<?php echo $date_from; ?>"
                    placeholder="From date"
                >
                
                <input 
                    type="date" 
                    name="date_to" 
                    class="form-control" 
                    style="min-width: 150px;"
                    value="<?php echo $date_to; ?>"
                    placeholder="To date"
                >
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>
                
                <a href="orders.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Orders List
            <span class="badge badge-primary"><?php echo count($orders); ?></span>
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fas fa-file-invoice"></i>
                <h3>No Orders</h3>
                <p>Start by creating sales or purchase orders</p>
                <div class="d-flex gap-2 justify-center mt-2">
                    <button class="btn btn-success" onclick="openOrderModal('Sale')">
                        <i class="fas fa-shopping-cart"></i>
                        Sale Order
                    </button>
                    <button class="btn btn-primary" onclick="openOrderModal('Purchase')">
                        <i class="fas fa-truck"></i>
                        Purchase Order
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Items</th>
                            <th>Customer/Supplier</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Staff</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['order_id']; ?></td>
                                <td>
                                    <?php if ($order['order_type'] === 'Sale'): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-arrow-up"></i>
                                            Sale
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-primary">
                                            <i class="fas fa-arrow-down"></i>
                                            Purchase
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?php echo $order['item_count']; ?> item(s)</div>
                                    <small class="text-muted"><?php echo sanitize(substr($order['product_names'], 0, 50)); ?><?php echo strlen($order['product_names']) > 50 ? '...' : ''; ?></small>
                                </td>
                                <td><?php echo sanitize($order['customer_supplier_name']); ?></td>
                                <td>
                                    <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                                </td>
                                <td><?php echo date('Y/m/d', strtotime($order['order_date'])); ?></td>
                                <td><?php echo sanitize($order['staff_name'] ?? 'Unknown'); ?></td>
                                <td>
                                    <button
                                        class="btn btn-danger btn-icon btn-sm"
                                        onclick="confirmDelete(<?php echo $order['order_id']; ?>, '<?php echo $order['order_type'] === 'Sale' ? 'Sale' : 'Purchase'; ?>', '<?php echo sanitize($order['product_names']); ?>')"
                                        title="Delete"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Order Modal -->
<div class="modal-overlay" id="orderModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="orderModalTitle">Create Order</h3>
            <button class="modal-close" onclick="closeOrderModal()">&times;</button>
        </div>
        <form id="orderForm" action="../api/orders/create.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="order_type" id="orderType">
            
            <div class="modal-body">
                <!-- Order Details (Global) -->
                <div class="row" style="display: flex; gap: var(--spacing-md); margin-bottom: var(--spacing-lg);">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label for="orderDate" class="form-label required">Date</label>
                        <input
                            type="date"
                            id="orderDate"
                            name="order_date"
                            class="form-control"
                            value="<?php echo date('Y-m-d'); ?>"
                            required
                        >
                    </div>
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label for="customerSupplier" class="form-label required">Customer/Supplier</label>
                        <input
                            type="text"
                            id="customerSupplier"
                            name="customer_supplier_name"
                            class="form-control"
                            placeholder="Enter customer or supplier name"
                            required
                        >
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--gray-200); margin-bottom: var(--spacing-md);">

                <!-- Items Container -->
                <div id="orderItems">
                    <!-- Dynamic Rows will be added here -->
                </div>

                <button type="button" class="btn btn-secondary btn-sm mb-4" onclick="addProductRow()">
                    <i class="fas fa-plus"></i> Add Another Product
                </button>
            </div>
            
            <div class="modal-footer" style="justify-content: space-between; align-items: center;">
                <div class="total-display text-success" style="font-size: 1.1em; font-weight: bold;">
                    Total: $<span id="totalAmount">0.00</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" onclick="closeOrderModal()">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submitOrderBtn">
                        <i class="fas fa-save"></i>
                        <span>Create Order</span>
                    </button>
                </div>
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
                Confirm Delete Order
            </h3>
            <button class="modal-close" onclick="closeDeleteModal()" style="color: white;">&times;</button>
        </div>
        <div class="modal-body text-center">
            <p style="font-size: var(--font-size-lg);">
                Are you sure you want to delete this <span id="deleteOrderType"></span> order?
            </p>
            <p style="font-weight: 700; color: var(--primary);" id="deleteProductName"></p>
            <div class="alert alert-warning" style="margin-top: var(--spacing-md);">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Inventory will be updated automatically</span>
            </div>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <form action="../api/orders/delete.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="order_id" id="deleteOrderId">
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
let currentOrderType = 'Sale';
let itemCount = 0;

// Store product options for dynamic rows
const productOptions = `
    <option value="">Select Product</option>
    <?php foreach ($products as $product): ?>
        <option 
            value="<?php echo $product['product_id']; ?>"
            data-quantity="<?php echo $product['quantity']; ?>"
            data-price="<?php echo $product['price']; ?>"
        >
            <?php echo sanitize($product['product_name']); ?> 
            (Qty: <?php echo $product['quantity']; ?> | $<?php echo $product['price']; ?>)
        </option>
    <?php endforeach; ?>
`;

function openOrderModal(type) {
    currentOrderType = type;
    document.getElementById('orderType').value = type;
    
    if (type === 'Sale') {
        document.getElementById('orderModalTitle').innerHTML = '<i class="fas fa-shopping-cart" style="color: var(--success);"></i> Create Sale Order';
        document.getElementById('submitOrderBtn').className = 'btn btn-success';
    } else {
        document.getElementById('orderModalTitle').innerHTML = '<i class="fas fa-truck" style="color: var(--primary);"></i> Create Purchase Order';
        document.getElementById('submitOrderBtn').className = 'btn btn-primary';
    }
    
    // Reset form
    document.getElementById('orderForm').reset();
    document.getElementById('orderDate').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('customerSupplier').value = '';
    document.getElementById('totalAmount').textContent = '0.00';
    
    // Clear Items and Add First Row
    document.getElementById('orderItems').innerHTML = '';
    addProductRow();
    
    document.getElementById('orderModal').classList.add('active');
}

function addProductRow() {
    itemCount++;
    const rowId = 'item-' + itemCount;
    const nameLabel = currentOrderType === 'Sale' ? 'Customer Name' : 'Supplier Name';
    const namePlaceholder = currentOrderType === 'Sale' ? 'Enter customer' : 'Enter supplier';
    
    const div = document.createElement('div');
    div.className = 'order-row card mb-3';
    div.id = rowId;
    div.style.padding = 'var(--spacing-md)';
    div.style.border = '1px solid var(--gray-200)';
    div.style.position = 'relative';
    
    const html = `
        <button type="button" class="btn-text text-danger" onclick="removeRow('${rowId}')" style="position: absolute; right: 10px; top: 10px; background: none; border: none; cursor: pointer; z-index: 5;">
            <i class="fas fa-times"></i>
        </button>
        <div class="row" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start;">
            <div style="flex: 2; min-width: 200px;">
                <label class="form-label required" style="font-size: 0.85rem;">Product</label>
                <select name="product_id[]" class="form-control product-select" required onchange="updateRowInfo('${rowId}')">
                    ${productOptions}
                </select>
                <div class="form-text stock-info text-primary" style="font-size: 0.8rem; height: 20px;"></div>
            </div>
            <div style="flex: 1; min-width: 80px;">
                <label class="form-label required" style="font-size: 0.85rem;">Quantity</label>
                <input type="number" name="quantity[]" class="form-control qty-input" min="1" required oninput="calculateTotal()">
            </div>
        </div>
    `;
    
    div.innerHTML = html;
    document.getElementById('orderItems').appendChild(div);
}

function removeRow(rowId) {
    const rows = document.getElementById('orderItems').children;
    if (rows.length > 1) {
        document.getElementById(rowId).remove();
        calculateTotal();
    } else {
        alert("At least one product is required.");
    }
}

function updateRowInfo(rowId) {
    const row = document.getElementById(rowId);
    const select = row.querySelector('.product-select');
    const option = select.options[select.selectedIndex];
    const stockInfo = row.querySelector('.stock-info');
    const qtyInput = row.querySelector('.qty-input');
    
    if (option.value) {
        const qty = parseInt(option.dataset.quantity);
        const price = parseFloat(option.dataset.price);
        
        stockInfo.textContent = `Avail: ${qty} | Unit: $${price}`;
        
        if (currentOrderType === 'Sale') {
            qtyInput.max = qty;
        } else {
            qtyInput.removeAttribute('max');
        }
    } else {
        stockInfo.textContent = '';
        qtyInput.removeAttribute('max');
    }
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    let valid = true;

    document.querySelectorAll('.order-row').forEach(row => {
        const select = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');

        const option = select.options[select.selectedIndex];

        if (option && option.value && qtyInput.value) {
            const price = parseFloat(option.dataset.price);
            const qty = parseInt(qtyInput.value);
            const max = parseInt(option.dataset.quantity);

            total += price * qty;

            // Validate Stock for Sale
            if (currentOrderType === 'Sale' && qty > max) {
                valid = false;
                qtyInput.classList.add('error');
            } else {
                qtyInput.classList.remove('error');
            }
        }
    });

    // Check if customer/supplier is filled
    const customerSupplier = document.getElementById('customerSupplier').value.trim();
    if (!customerSupplier) {
        valid = false;
    }

    document.getElementById('totalAmount').textContent = total.toFixed(2);
    document.getElementById('submitOrderBtn').disabled = !valid;
}

function closeOrderModal() {
    document.getElementById('orderModal').classList.remove('active');
}

function confirmDelete(id, type, productName) {
    document.getElementById('deleteOrderId').value = id;
    document.getElementById('deleteOrderType').textContent = type;
    document.getElementById('deleteProductName').textContent = productName;
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
}

// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});

// Add event listener for customer/supplier input
document.getElementById('customerSupplier').addEventListener('input', calculateTotal);

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(modal => {
            modal.classList.remove('active');
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
