<?php
/**
 * Public Header Component
 * For Customer Storefront
 */
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Quick Mart - Your Favorite Local Store">
    <title>Quick Mart Store</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* Store specific styles override */
        body {
            background: var(--gray-100);
        }
        
        .hero-section {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 4rem 1rem;
            text-align: center;
            margin-top: 60px;
            border-radius: 0 0 50% 50% / 4rem;
        }
        
        .product-card {
            background: white;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: transform 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }
        
        .product-image {
            height: 200px;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: var(--gray-300);
        }
        
        .product-details {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .price-tag {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            margin-top: auto;
        }
        
        body {
            background-color: #f8fafc;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Public Navigation Bar -->
        <nav class="navbar">
            <a href="store.php" class="navbar-brand">
                <i class="fas fa-store"></i>
                <span>Quick Mart</span>
            </a>
            
            <div class="navbar-menu" style="display: flex; align-items: center; gap: 1rem;">
                <a href="about.php" class="navbar-link" style="color: white; font-weight: 500;">About Us</a>
                
                <a href="login.php" class="btn btn-outline btn-sm" style="color: white; border-color: rgba(255,255,255,0.5);">
                    <i class="fas fa-user-lock"></i>
                    Staff Login
                </a>
            </div>
        </nav>
