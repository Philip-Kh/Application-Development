<?php
/**
 * Registration API Handler

 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/register.php?error=invalid');
    exit();
}

// Get form data
// $staff_id is now auto-generated
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate required fields
if (empty($full_name) || empty($email) || empty($password)) {
    header('Location: ../../pages/register.php?error=empty');
    exit();
}

// Validate email format
if (!isValidEmail($email)) {
    header('Location: ../../pages/register.php?error=invalid_email&field=email');
    exit();
}

// Validate password strength
if (!isValidPassword($password)) {
    header('Location: ../../pages/register.php?error=weak_password&field=password');
    exit();
}

// Check password confirmation
if ($password !== $confirm_password) {
    header('Location: ../../pages/register.php?error=password_mismatch');
    exit();
}

try {
    $db = getDB();
    
    // Generate Staff ID automatically
    $staff_id = generateStaffId($db);
    
    // Safety check: ensure unique (though logic handles sequence)
    $stmt = $db->prepare("SELECT staff_id FROM staff WHERE staff_id = ?");
    $stmt->execute([$staff_id]);
    if ($stmt->fetch()) {
        // Fallback: try one more time or fail gracefully
         $staff_id = generateStaffId($db);
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT staff_id FROM staff WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: ../../pages/register.php?error=email_exists&field=email');
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert new staff member with is_active = 0 (Pending Approval)
    $stmt = $db->prepare("INSERT INTO staff (staff_id, full_name, email, password, role, is_active, created_at) VALUES (?, ?, ?, ?, 'staff', 0, NOW())");
    $stmt->execute([$staff_id, $full_name, $email, $hashed_password]);
    
    // Redirect to login with success message
    header('Location: ../../pages/login.php?success=pending_approval');
    exit();
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    header('Location: ../../pages/register.php?error=server');
    exit();
}
?>
