<?php
/**
 * Header Component with Navigation
 * Updated with Admin menu
 */
require_once __DIR__ . '/auth.php';
requireAuth();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Inventory and Order Management System - Quick Mart">
    <title>Quick Mart - Inventory Management System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo $currentDir === 'admin' ? '../../assets/css/style.css' : '../assets/css/style.css'; ?>">
</head>
<body>
    <div class="app-container">
        <!-- Navigation Bar -->
        <nav class="navbar">
            <a href="<?php echo $currentDir === 'admin' ? '../dashboard.php' : 'dashboard.php'; ?>" class="navbar-brand">
                <i class="fas fa-store"></i>
                <span>Quick Mart</span>
            </a>
            
            <button class="menu-toggle" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="navbar-menu" id="navMenu">
                <li class="navbar-item">
                    <a href="<?php echo $currentDir === 'admin' ? '../dashboard.php' : 'dashboard.php'; ?>" class="navbar-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="<?php echo $currentDir === 'admin' ? '../products.php' : 'products.php'; ?>" class="navbar-link <?php echo $currentPage === 'products' ? 'active' : ''; ?>">
                        <i class="fas fa-boxes-stacked"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="<?php echo $currentDir === 'admin' ? '../orders.php' : 'orders.php'; ?>" class="navbar-link <?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
                        <i class="fas fa-file-invoice"></i>
                        <span>Orders</span>
                    </a>
                </li>
                
                <?php if (isAdmin()): ?>
                <!-- Admin Menu -->
                <li class="navbar-item dropdown">
                    <a href="#" class="navbar-link <?php echo $currentDir === 'admin' ? 'active' : ''; ?>" onclick="toggleDropdown(event)">
                        <i class="fas fa-shield-halved"></i>
                        <span>Admin</span>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem; margin-left: 4px;"></i>
                    </a>
                    <ul class="dropdown-menu" id="adminDropdown">
                        <li>
                            <a href="<?php echo $currentDir === 'admin' ? 'users.php' : 'admin/users.php'; ?>" class="dropdown-link <?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                                <i class="fas fa-users"></i>
                                <span>Users</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $currentDir === 'admin' ? 'activity.php' : 'admin/activity.php'; ?>" class="dropdown-link <?php echo $currentPage === 'activity' ? 'active' : ''; ?>">
                                <i class="fas fa-clock-rotate-left"></i>
                                <span>Activity Log</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $currentDir === 'admin' ? 'settings.php' : 'admin/settings.php'; ?>" class="dropdown-link <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            
            <div class="navbar-user">
                <div class="navbar-item dropdown">
                    <a href="#" class="navbar-link user-profile-link" onclick="toggleDropdown(event)" style="background: transparent; padding: 0;">
                        <div class="user-avatar">
                            <?php echo mb_substr(getCurrentStaffName() ?? 'S', 0, 1); ?>
                        </div>
                        <span style="color: var(--white);">
                            <?php echo sanitize(getCurrentStaffName() ?? 'Staff'); ?>
                            <?php if (isAdmin()): ?>
                                <span class="role-badge admin" style="margin-left: 5px;">Admin</span>
                            <?php endif; ?>
                            <i class="fas fa-chevron-down" style="font-size: 0.7rem; margin-left: 4px;"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu" style="right: 0; left: auto; width: 180px;">
                        <li>
                            <form action="<?php echo $currentDir === 'admin' ? '../../api/auth/logout.php' : '../api/auth/logout.php'; ?>" method="POST" id="logoutForm">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <a href="#" onclick="document.getElementById('logoutForm').submit(); return false;" class="dropdown-link" style="color: var(--danger);">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <!-- Main Content Area -->
        <main class="main-content">
            <div class="container">
