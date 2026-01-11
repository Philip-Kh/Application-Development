<?php
/**
 * Toggle User Status API
 * Activate/Deactivate user
 */
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../../pages/admin/users.php?error=invalid');
    exit();
}

// Validate CSRF
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../../../pages/admin/users.php?error=csrf');
    exit();
}

$staff_id = $_POST['staff_id'] ?? '';
$action = $_POST['action'] ?? '';

if (empty($staff_id) || !in_array($action, ['activate', 'deactivate'])) {
    header('Location: ../../../pages/admin/users.php?error=invalid');
    exit();
}

// Cannot deactivate self
if ($staff_id === getCurrentStaffId() && $action === 'deactivate') {
    header('Location: ../../../pages/admin/users.php?error=self_deactivate');
    exit();
}

try {
    $db = getDB();
    
    $is_active = ($action === 'activate') ? 1 : 0;
    
    $stmt = $db->prepare("UPDATE staff SET is_active = ? WHERE staff_id = ?");
    $stmt->execute([$is_active, $staff_id]);
    
    require_once __DIR__ . '/../../../includes/activity_logger.php';
    logActivity('user_status_change', 'staff', $staff_id, ucfirst($action) . "d user account");
    
    header('Location: ../../../pages/admin/users.php?success=' . $action . 'd');
    exit();
    
} catch (PDOException $e) {
    error_log("Toggle user error: " . $e->getMessage());
    header('Location: ../../../pages/admin/users.php?error=server');
    exit();
}
?>
