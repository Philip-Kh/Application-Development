<?php
/**
 * Login API Handler
 * Enhanced with login limiting and activity logging
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/activity_logger.php';

session_start();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/login.php?error=invalid');
    exit();
}

// Get form data
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate inputs
if (empty($email) || empty($password)) {
    header('Location: ../../pages/login.php?error=empty');
    exit();
}

try {
    $db = getDB();
    
    // Get user by email
    $stmt = $db->prepare("SELECT staff_id, full_name, email, password, role, is_active, login_attempts, locked_until FROM staff WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Check if user exists
    if (!$user) {
        // Log generic failure (conceptually linked to this email attempt)
        error_log("Login failed: User not found for email $email");
        header('Location: ../../pages/login.php?error=invalid');
        exit();
    }
    
    $staff_id = $user['staff_id']; // Resolved staff_id for internal logic
    
    // Check if account is active
    if (!$user['is_active']) {
        logActivity('login_failed', 'staff', $staff_id, 'Account deactivated');
        header('Location: ../../pages/login.php?error=deactivated');
        exit();
    }
    
    // Check if account is locked
    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
        $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
        logActivity('login_failed', 'staff', $staff_id, 'Account locked');
        header('Location: ../../pages/login.php?error=locked&minutes=' . $remaining);
        exit();
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Increment login attempts
        $stmt = $db->prepare("
            UPDATE staff 
            SET login_attempts = login_attempts + 1,
                locked_until = CASE 
                    WHEN login_attempts + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                    ELSE locked_until 
                END
            WHERE staff_id = ?
        ");
        $stmt->execute([$staff_id]);
        
        logActivity('login_failed', 'staff', $staff_id, 'Wrong password');
        
        // Check if now locked
        $attempts = $user['login_attempts'] + 1;
        if ($attempts >= 5) {
            header('Location: ../../pages/login.php?error=locked&minutes=15');
        } else {
            $remaining = 5 - $attempts;
            header('Location: ../../pages/login.php?error=invalid&attempts=' . $remaining);
        }
        exit();
    }
    
    // Reset login attempts and update last login
    $stmt = $db->prepare("UPDATE staff SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE staff_id = ?");
    $stmt->execute([$staff_id]);
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Set session variables
    $_SESSION['staff_id'] = $user['staff_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    
    // Log successful login
    logActivity('login_success', 'staff', $staff_id, 'Login successful');
    
    // Redirect to dashboard
    header('Location: ../../pages/dashboard.php');
    exit();
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    header('Location: ../../pages/login.php?error=server');
    exit();
}
?>
