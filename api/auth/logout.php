<?php
/**
 * Logout API Handler

 */
require_once __DIR__ . '/../../includes/auth.php';

// Only accept POST requests for security
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/login.php');
    exit();
}

// Validate CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!validateCSRFToken($csrf_token)) {
    header('Location: ../../pages/login.php?error=session');
    exit();
}

// Logout the user
logout();

// Redirect to Customer Storefront
header('Location: ../../pages/store.php');
exit();
?>
