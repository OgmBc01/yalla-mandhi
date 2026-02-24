<?php
require_once 'includes/functions.php';

$error = '';
$success = '';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $userData = [
        'username' => $_POST['username'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'full_name' => $_POST['full_name'] ?? '',
        'phone' => $_POST['phone'] ?? ''
    ];
    
    // Validate passwords match
    if ($userData['password'] !== $userData['confirm_password']) {
        $error = 'Passwords do not match';
    } else {
        $result = registerUser($userData);
        
        if ($result['success']) {
            // Auto-login successful, redirect
            header('Location: index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Yalla Al Mandi</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    
    <link rel="stylesheet" href="resources/css/style.css">

</head>
<body class="auth-page signup-bg">
    <!-- ===== NAVIGATION ===== -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">Yalla <span>Al Mandi</span></a>
            
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="login.php" class="nav-link">Login</a>
                <a href="signup.php" class="nav-link active">Sign Up</a>
            </div>
            
            <div class="mobile-toggle" id="mobileToggle">
                ☰
            </div>
        </div>
    </nav>

    <!-- ===== SIGNUP FORM ===== -->
    <section class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <h2 class="logo" style="font-size: 2.5rem; margin: 0;">Yalla <span>Al Mandi</span></h2>
            </div>
            
            <h2 class="auth-title display-3">Create Account</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form id="signupForm" method="POST" action="">
                <input type="hidden" name="signup" value="1">
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" 
                               name="username" 
                               class="form-control" 
                               required 
                               placeholder="Choose a username">
                        <div class="form-hint" style="font-size: 0.8rem; color: #666; margin-top: 5px;">
                            Letters, numbers, and underscores only
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" 
                               name="email" 
                               class="form-control" 
                               required 
                               placeholder="Enter your email">
                    </div>
                </div>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" 
                               name="full_name" 
                               class="form-control" 
                               placeholder="Enter your full name">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" 
                               name="phone" 
                               class="form-control" 
                               placeholder="Enter your phone number">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="form-control" 
                           required 
                           placeholder="Create a strong password">
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText">
                            Password strength: Very weak
                        </div>
                    </div>
                    <div class="form-hint" style="font-size: 0.8rem; color: #666; margin-top: 5px;">
                        Must be at least 8 characters with uppercase, lowercase, and number
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" 
                           name="confirm_password" 
                           class="form-control" 
                           required 
                           placeholder="Confirm your password">
                </div>
                
                <div class="form-group" style="margin-top: 20px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="terms" required style="margin-top: 3px;">
                        <span>
                            I agree to the 
                            <a href="terms.php" style="color: var(--color-red);">Terms of Service</a> 
                            and 
                            <a href="privacy.php" style="color: var(--color-red);">Privacy Policy</a>
                        </span>
                    </label>
                </div>
                
                <div class="form-group" style="margin-top: 20px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="newsletter" checked style="margin-top: 3px;">
                        <span>
                            Subscribe to our newsletter for updates and special offers
                        </span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; margin-top: 20px;">
                    <i class="bi bi-person-plus"></i> Create Account
                </button>
            </form>
            
            <div class="auth-divider">
                <span>or sign up with</span>
            </div>
            
            <div style="display: flex; gap: 15px; margin-bottom: 25px;">
                <button type="button" class="btn btn-outline" style="flex: 1;">
                    <i class="bi bi-google"></i> Google
                </button>
                <button type="button" class="btn btn-outline" style="flex: 1;">
                    <i class="bi bi-facebook"></i> Facebook
                </button>
            </div>
            
            <div class="form-footer">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <?php
    include 'includes/footer.php';
    ?>

    <!-- ===== EXTERNAL SCRIPTS ===== -->
    <script src="script.js"></script>
    
</body>
</html>