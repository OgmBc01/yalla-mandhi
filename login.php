
<?php
include_once 'includes/functions.php';

$error = '';
$success = '';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username_email = $_POST['username_email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    $result = loginUser($username_email, $password);
    
    if ($result['success']) {
        // Set remember me cookie if requested
        if ($remember) {
            setcookie('remember_user', $result['user']['username'], time() + (30 * 24 * 60 * 60), '/');
        }
        
        // Redirect to home page or intended page
        $redirect = $_GET['redirect'] ?? 'index.php';
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Yalla Al Mandhi</title>
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

<body class="auth-page">
    <!-- ===== NAVIGATION ===== -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">Yalla <span>Al Mandhi</span></a>
            
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="login.php" class="nav-link active">Login</a>
                <a href="signup.php" class="nav-link">Sign Up</a>
            </div>
            
            <div class="mobile-toggle" id="mobileToggle">
                ☰
            </div>
        </div>
    </nav>

    <!-- ===== LOGIN FORM ===== -->
    <section class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <h2 class="logo" style="font-size: 2.5rem; margin: 0;">Yalla <span>Al Mandhi</span></h2>
            </div>
            
            <h2 class="auth-title display-3">Welcome Back</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form id="loginForm" method="POST" action="">
                <input type="hidden" name="login" value="1">
                
                <div class="form-group">
                    <label class="form-label">Username or Email *</label>
                    <input type="text" 
                           name="username_email" 
                           class="form-control" 
                           required 
                           placeholder="Enter your username or email">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" 
                           name="password" 
                           class="form-control" 
                           required 
                           placeholder="Enter your password">
                </div>
                
                <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    
                    <a href="forgot-password.php" style="color: var(--color-red); font-size: 0.9rem;">
                        Forgot Password?
                    </a>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; margin-top: 10px;">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
            </form>
            
            <div class="auth-divider">
                <span>or continue with</span>
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
                Don't have an account? <a href="signup.php">Sign up here</a>
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