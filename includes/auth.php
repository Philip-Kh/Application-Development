<?php
/**
 * Authentication Middleware
 * Enhanced with admin support and login limiting
 */

session_start();

define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes in seconds

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    if (!isset($_SESSION['staff_id'])) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            // Session expired
            logout();
            return false;
        }
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Require authentication - redirect to login if not logged in
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require admin role - redirect if not admin
 */
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header('Location: dashboard.php?error=unauthorized');
        exit();
    }
}

/**
 * Logout user
 */
function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Get current staff ID
 */
function getCurrentStaffId() {
    return $_SESSION['staff_id'] ?? null;
}

/**
 * Get current staff name
 */
function getCurrentStaffName() {
    return $_SESSION['full_name'] ?? null;
}

/**
 * Get current staff role
 */
function getCurrentRole() {
    return $_SESSION['role'] ?? 'staff';
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize output to prevent XSS
 */
function sanitize($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password strength
 * Min 8 chars, 1 uppercase, 1 lowercase, 1 special char
 */
function isValidPassword($password) {
    // Regex explanation:
    // (?=.*[a-z])      : At least one lowercase
    // (?=.*[A-Z])      : At least one uppercase
    // (?=.*[\W_])      : At least one special char (non-alphanumeric or underscore)
    // .{8,}            : Minimum 8 chars total
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $password);
}

/**
 * Check if account is locked
 */
function isAccountLocked($lockedUntil) {
    if (!$lockedUntil) {
        return false;
    }
    return strtotime($lockedUntil) > time();
}

/**
 * Get remaining lockout time in minutes
 */
function getLockoutRemaining($lockedUntil) {
    if (!$lockedUntil) {
        return 0;
    }
    $remaining = strtotime($lockedUntil) - time();
    return max(0, ceil($remaining / 60));
}

/**
 * Increment login attempts
 */
function incrementLoginAttempts($db, $staffId) {
    $stmt = $db->prepare("
        UPDATE staff 
        SET login_attempts = login_attempts + 1,
            locked_until = CASE 
                WHEN login_attempts + 1 >= ? THEN DATE_ADD(NOW(), INTERVAL ? SECOND)
                ELSE locked_until 
            END
        WHERE staff_id = ?
    ");
    $stmt->execute([MAX_LOGIN_ATTEMPTS, LOCKOUT_DURATION, $staffId]);
}

/**
 * Reset login attempts
 */
function resetLoginAttempts($db, $staffId) {
    $stmt = $db->prepare("UPDATE staff SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE staff_id = ?");
    $stmt->execute([$staffId]);
}

/**
 * Get system setting
 */
function getSetting($key, $default = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}
/**
 * Generate next Staff ID
 * Format: STF-XXXX (e.g. STF-0001)
 */
function generateStaffId($db) {
    // specific prefix
    $prefix = 'STF-';
    
    // Find the last ID with this prefix
    $stmt = $db->prepare("SELECT staff_id FROM staff WHERE staff_id LIKE ? ORDER BY LENGTH(staff_id) DESC, staff_id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $lastId = $stmt->fetchColumn();
    
    if ($lastId) {
        // Extract number
        $number = (int) substr($lastId, strlen($prefix));
        $nextNumber = $number + 1;
    } else {
        // Start from 1 if no existing IDs
        $nextNumber = 1;
    }
    
    // Pad with zeros to 4 digits
    return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}
?>
