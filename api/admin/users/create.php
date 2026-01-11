<?php
/**
 * Create User API
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
// $staff_id is auto-generated
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'staff';

// Validate inputs
if (empty($full_name) || empty($email) || empty($password)) {
    header('Location: ../../../pages/admin/users.php?error=empty');
    exit();
}

if (!isValidEmail($email)) {
    header('Location: ../../../pages/admin/users.php?error=invalid_email');
    exit();
}

if (!isValidPassword($password)) {
    header('Location: ../../../pages/admin/users.php?error=weak_password');
    exit();
}

try {
    $db = getDB();
    
    // Generate Staff ID
    $staff_id = generateStaffId($db);
    
    // Check if email exists
    $stmt = $db->prepare("SELECT staff_id FROM staff WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: ../../../pages/admin/users.php?error=exists');
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user
    $stmt = $db->prepare("
        INSERT INTO staff (staff_id, full_name, email, phone, password, role, is_active, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
    ");
    $stmt->execute([$staff_id, $full_name, $email, $phone, $hashed_password, $role]);
    
    require_once __DIR__ . '/../../../includes/activity_logger.php';
    logActivity('create_user', 'staff', $staff_id, "Created user $full_name ($role)");
    
    header('Location: ../../../pages/admin/users.php?success=created');
    exit();
    
} catch (PDOException $e) {
    error_log("Create user error: " . $e->getMessage());
    header('Location: ../../../pages/admin/users.php?error=server');
    exit();
}
?>
