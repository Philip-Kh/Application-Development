<?php
/**
 * Registration Page
 */
session_start();

// Redirect if already logged in
if (isset($_SESSION['staff_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Get error message if any
$error = $_GET['error'] ?? '';
$field = $_GET['field'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Register New Staff - Inventory and Order Management System">
    <title>Register - Quick Mart</title>
    
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
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1>Create Account</h1>
                <p>Enter your details to register</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>
                            <?php
                            switch ($error) {
                                case 'staff_exists':
                                    echo 'Staff ID already exists';
                                    break;
                                case 'email_exists':
                                    echo 'Email already registered';
                                    break;
                                case 'invalid_email':
                                    echo 'Invalid email format';
                                    break;
                                case 'weak_password':
                                    echo 'Password must be at least 8 characters';
                                    break;
                                case 'password_mismatch':
                                    echo 'Passwords do not match';
                                    break;
                                case 'empty':
                                    echo 'Please fill in all required fields';
                                    break;
                                default:
                                    echo 'An error occurred, please try again';
                            }
                            ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <form action="../api/auth/register.php" method="POST" id="registerForm">
                    
                    <div class="form-group">
                        <label for="full_name" class="form-label required">Full Name</label>
                        <div class="input-group">
                            <input 
                                type="text" 
                                id="full_name" 
                                name="full_name" 
                                class="form-control" 
                                placeholder="Enter full name"
                                required
                                autocomplete="name"
                            >
                            <i class="fas fa-user input-group-icon"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label required">Email Address</label>
                        <div class="input-group">
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control <?php echo $field === 'email' ? 'error' : ''; ?>" 
                                placeholder="example@email.com"
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
                                class="form-control <?php echo $field === 'password' ? 'error' : ''; ?>"
                                placeholder="At least 8 characters"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <i class="fas fa-lock input-group-icon"></i>
                            <i class="fas fa-eye password-toggle" id="togglePasswordIcon" onclick="togglePassword('password', 'togglePasswordIcon')"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label required">Confirm Password</label>
                        <div class="input-group">
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-control"
                                placeholder="Re-enter password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <i class="fas fa-lock input-group-icon"></i>
                            <i class="fas fa-eye password-toggle" id="toggleConfirmPasswordIcon" onclick="togglePassword('confirm_password', 'toggleConfirmPasswordIcon')"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-block btn-lg">
                        <i class="fas fa-user-plus"></i>
                        <span>Create Account</span>
                    </button>
                </form>
            </div>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }
            
            // Strict password policy regex
            // Min 8 chars, 1 uppercase, 1 lowercase, 1 special char
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/;
            
            if (!passwordRegex.test(password)) {
                e.preventDefault();
                alert('Password must contain:\n- At least 8 characters\n- At least one uppercase letter (A-Z)\n- At least one lowercase letter (a-z)\n- At least one special character (!@#$% etc.)');
                return;
            }
        });
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
