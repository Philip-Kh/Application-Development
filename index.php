<?php
/**
 * Entry Point - Index
 */

// Include auth system
require_once __DIR__ . '/includes/auth.php';

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect to dashboard
    header('Location: pages/dashboard.php');
} else {
    // Redirect to Customer Storefront
    header('Location: pages/store.php');
}
exit();
?>
