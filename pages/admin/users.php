<?php
/**
 * Admin Users Management Page
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

// Get filter parameters
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

// Messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

try {
    $db = getDB();
    
    // Build query
    $where = ["1=1"];
    $params = [];
    
    if ($search) {
        $where[] = "(staff_id LIKE ? OR full_name LIKE ? OR email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($role) {
        $where[] = "role = ?";
        $params[] = $role;
    }
    
    if ($status !== '') {
        $where[] = "is_active = ?";
        $params[] = $status;
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "SELECT * FROM staff WHERE $whereClause ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Users page error: " . $e->getMessage());
    $users = [];
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-between align-center mb-4" style="flex-wrap: wrap; gap: var(--spacing-md);">
    <div>
        <h1 style="font-size: var(--font-size-3xl); font-weight: 800; color: var(--white);">
            <i class="fas fa-users" style="color: var(--primary-light);"></i>
            User Management
        </h1>
        <p style="color: var(--gray-300); margin-top: var(--spacing-sm);">
            Manage staff accounts and permissions
        </p>
    </div>
    <button class="btn btn-success" onclick="openAddModal()">
        <i class="fas fa-user-plus"></i>
        Add New User
    </button>
</div>

<!-- Messages -->
<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>
            <?php
            switch ($success) {
                case 'created': echo 'User created successfully'; break;
                case 'updated': echo 'User updated successfully'; break;
                case 'activated': echo 'User activated successfully'; break;
                case 'deactivated': echo 'User deactivated successfully'; break;
                case 'deleted': echo 'User deleted permanently'; break;
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
                case 'exists': echo 'Staff ID or email already exists'; break;
                case 'not_found': echo 'User not found'; break;
                case 'self_deactivate': echo 'You cannot deactivate yourself'; break;
                case 'self_delete': echo 'You cannot delete yourself'; break;
                default: echo 'An error occurred';
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
                <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?php echo sanitize($search); ?>">
                <i class="fas fa-search"></i>
            </div>
            
            <div class="filter-group">
                <select name="role" class="form-control" style="min-width: 150px;">
                    <option value="">All Roles</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="staff" <?php echo $role === 'staff' ? 'selected' : ''; ?>>Staff</option>
                </select>
                
                <select name="status" class="form-control" style="min-width: 150px;">
                    <option value="">All Status</option>
                    <option value="1" <?php echo $status === '1' ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo $status === '0' ? 'selected' : ''; ?>>Inactive</option>
                </select>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>
                
                <a href="users.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Users List
            <span class="badge badge-primary"><?php echo count($users); ?></span>
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?php echo sanitize($user['staff_id']); ?></strong></td>
                            <td><?php echo sanitize($user['full_name']); ?></td>
                            <td><?php echo sanitize($user['email']); ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge badge-primary">
                                        <i class="fas fa-shield-halved"></i> Admin
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-info">
                                        <i class="fas fa-user"></i> Staff
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Active
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-ban"></i> Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                if ($user['last_login']) {
                                    echo date('Y/m/d H:i', strtotime($user['last_login']));
                                } else {
                                    echo '<span class="text-muted">Never</span>';
                                }
                                ?>
                            </td>
                            <td>
                                    <div class="d-flex gap-1" style="gap: 5px;">
                                        <button class="btn btn-primary btn-icon btn-sm" 
                                                onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <?php if ($user['staff_id'] !== getCurrentStaffId()): ?>
                                            <?php if ($user['is_active']): ?>
                                                <form action="../../api/admin/users/toggle.php" method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                    <input type="hidden" name="staff_id" value="<?php echo $user['staff_id']; ?>">
                                                    <input type="hidden" name="action" value="deactivate">
                                                    <button type="submit" class="btn btn-warning btn-icon btn-sm" title="Deactivate">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form action="../../api/admin/users/toggle.php" method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                    <input type="hidden" name="staff_id" value="<?php echo $user['staff_id']; ?>">
                                                    <input type="hidden" name="action" value="activate">
                                                    <button type="submit" class="btn btn-success btn-icon btn-sm" title="Activate">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form action="../../api/admin/users/delete.php" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to permanently delete this user? This action cannot be undone.');">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="staff_id" value="<?php echo $user['staff_id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-icon btn-sm" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-user-plus"></i>
                Add New User
            </h3>
            <button class="modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <form action="../../api/admin/users/create.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="modal-body">

                
                <div class="form-group">
                    <label for="full_name" class="form-label required">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Enter full name" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="email@example.com" required>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" placeholder="Phone number">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label required">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                        <i class="fas fa-lock input-group-icon"></i>
                        <i class="fas fa-eye password-toggle" id="toggleAdminPass" onclick="togglePassword('password', 'toggleAdminPass')"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-control">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i>
                    Create User
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeAddModal()">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-user-edit"></i>
                Edit User
            </h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form action="../../api/admin/users/update.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="staff_id" id="edit_staff_id">
            
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Staff ID</label>
                    <input type="text" id="edit_staff_id_display" class="form-control" disabled>
                </div>
                
                <div class="form-group">
                    <label for="edit_full_name" class="form-label required">Full Name</label>
                    <input type="text" id="edit_full_name" name="full_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email" class="form-label required">Email</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_phone" class="form-label">Phone</label>
                    <input type="text" id="edit_phone" name="phone" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="edit_password" class="form-label">New Password</label>
                    <div class="input-group">
                        <input type="password" id="edit_password" name="password" class="form-control" placeholder="Leave blank to keep current" minlength="8">
                        <i class="fas fa-lock input-group-icon"></i>
                        <i class="fas fa-eye password-toggle" id="toggleEditPass" onclick="togglePassword('edit_password', 'toggleEditPass')"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_role" class="form-label">Role</label>
                    <select id="edit_role" name="role" class="form-control">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update User
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').classList.add('active');
}

function closeAddModal() {
    document.getElementById('addModal').classList.remove('active');
}

function openEditModal(user) {
    document.getElementById('edit_staff_id').value = user.staff_id;
    document.getElementById('edit_staff_id_display').value = user.staff_id;
    document.getElementById('edit_full_name').value = user.full_name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_phone').value = user.phone || '';
    document.getElementById('edit_role').value = user.role;
    document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

// Password Validation Helper
function validatePassword(password) {
    // Min 8 chars, 1 uppercase, 1 lowercase, 1 special char
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/;
    return regex.test(password);
}

const passwordErrorMsg = 'Password must contain:\n- At least 8 characters\n- At least one uppercase letter (A-Z)\n- At least one lowercase letter (a-z)\n- At least one special character (!@#$% etc.)';

// Add User Form Validation
const addForm = document.querySelector('#addModal form');
if (addForm) {
    addForm.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        if (!validatePassword(password)) {
            e.preventDefault();
            alert(passwordErrorMsg);
        }
    });
}

// Edit User Form Validation
const editForm = document.querySelector('#editModal form');
if (editForm) {
    editForm.addEventListener('submit', function(e) {
        const password = document.getElementById('edit_password').value;
        // Only validate if password field is not empty (password change is optional)
        if (password && !validatePassword(password)) {
            e.preventDefault();
            alert(passwordErrorMsg);
        }
    });
}

// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(modal => {
            modal.classList.remove('active');
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
