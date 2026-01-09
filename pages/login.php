<?php
/**
 * Login Page
 */
session_start();

// Redirect if already logged in
if (isset($_SESSION['staff_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Get error message if any
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login - Inventory and Order Management System">
    <title>Login - Quick Mart</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-store"></i>
                </div>
                <h1>Quick Mart</h1>
                <p>Inventory & Order Management System</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>
                            <?php
                            switch ($error) {
                                case 'invalid':
                                    echo 'Invalid email or password';
                                    break;
                                case 'empty':
                                    echo 'Please fill in all required fields';
                                    break;
                                case 'session':
                                    echo 'Session expired, please login again';
                                    break;
                                default:
                                    echo 'An error occurred, please try again';
                            }
                            ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>
                            <?php
                            switch ($success) {
                                case 'registered':
                                    echo 'Account created successfully, you can now login';
                                    break;
                                case 'pending_approval':
                                    echo 'Registration successful! Please wait for Admin approval.';
                                    break;
                                case 'logout':
                                    echo 'Logged out successfully';
                                    break;
                                default:
                                    echo 'Operation completed successfully';
                            }
                            ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <form action="../api/auth/login.php" method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="email" class="form-label required">Email Address</label>
                        <div class="input-group">
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control" 
                                placeholder="Enter your email"
                                required
                                autocomplete="email"
                            >
                            <i class="fas fa-envelope input-group-icon"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label required">Password</label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control" 
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                            >
                            <i class="fas fa-lock input-group-icon"></i>
                            <i class="fas fa-eye password-toggle" id="togglePasswordIcon" onclick="togglePassword('password', 'togglePasswordIcon')"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </button>
                </form>
            </div>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Create Account</a></p>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    </script>
</body>
</html>
