<?php
/**
 * Update User API
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

// Get form data
$staff_id = trim($_POST['staff_id'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'staff';

// Validate inputs
if (empty($staff_id) || empty($full_name) || empty($email)) {
    header('Location: ../../../pages/admin/users.php?error=empty');
    exit();
}

if (!isValidEmail($email)) {
    header('Location: ../../../pages/admin/users.php?error=invalid_email');
    exit();
}

try {
    $db = getDB();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT staff_id FROM staff WHERE staff_id = ?");
    $stmt->execute([$staff_id]);
    if (!$stmt->fetch()) {
        header('Location: ../../../pages/admin/users.php?error=not_found');
        exit();
    }
    
    // Check email uniqueness (excluding current user)
    $stmt = $db->prepare("SELECT staff_id FROM staff WHERE email = ? AND staff_id != ?");
    $stmt->execute([$email, $staff_id]);
    if ($stmt->fetch()) {
        header('Location: ../../../pages/admin/users.php?error=exists');
        exit();
    }
    
    // Prepare update query
    $sql = "UPDATE staff SET full_name = ?, email = ?, phone = ?, role = ? WHERE staff_id = ?";
    $params = [$full_name, $email, $phone, $role, $staff_id];
    
    // Create separate query if password is provided
    if (!empty($password)) {
        if (!isValidPassword($password)) {
            header('Location: ../../../pages/admin/users.php?error=weak_password');
            exit();
        }
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE staff SET full_name = ?, email = ?, phone = ?, role = ?, password = ? WHERE staff_id = ?";
        $params = [$full_name, $email, $phone, $role, $hashed_password, $staff_id];
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    require_once __DIR__ . '/../../../includes/activity_logger.php';
    logActivity('update_user', 'staff', $staff_id, "Updated user $full_name");
    
    header('Location: ../../../pages/admin/users.php?success=updated');
    exit();
    
} catch (PDOException $e) {
    error_log("Update user error: " . $e->getMessage());
    header('Location: ../../../pages/admin/users.php?error=server');
    exit();
}
?>
