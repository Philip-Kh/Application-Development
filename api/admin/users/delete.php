<?php
/**
 * Delete User API
 * Admin only
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
$current_staff_id = getCurrentStaffId();

if (empty($staff_id)) {
    header('Location: ../../../pages/admin/users.php?error=missing_id');
    exit();
}

// Prevent self-deletion
if ($staff_id === $current_staff_id) {
    header('Location: ../../../pages/admin/users.php?error=self_delete');
    exit();
}

try {
    $db = getDB();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT full_name, role FROM staff WHERE staff_id = ?");
    $stmt->execute([$staff_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: ../../../pages/admin/users.php?error=not_found');
        exit();
    }
    
    // Check if trying to delete another admin (optional safeguard, but usually allowed)
    // For now, allow deleting other admins if current user is admin
    
    // Delete user
    // Note: Foreign keys should be set to ON DELETE SET NULL as per schema verification
    $stmt = $db->prepare("DELETE FROM staff WHERE staff_id = ?");
    $stmt->execute([$staff_id]);
    
    require_once __DIR__ . '/../../../includes/activity_logger.php';
    logActivity('delete_user', 'staff', $staff_id, "Deleted user {$user['full_name']} ({$user['role']})");
    
    header('Location: ../../../pages/admin/users.php?success=deleted');
    exit();
    
} catch (PDOException $e) {
    error_log("Delete user error: " . $e->getMessage());
    header('Location: ../../../pages/admin/users.php?error=server');
    exit();
}
?>
