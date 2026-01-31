<?php
// forgot-password.php
require_once 'includes/functions.php';

$error = '';
$success = '';

// Handle forgot password request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password'])) {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } else {
        $result = forgotPassword($email);
        
        if ($result['success']) {
            $success = $result['message'];
            // In production, you would send an email with the reset link
            // For demo purposes, we'll show the token
            $reset_token = $result['reset_token'] ?? '';
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
    <title>Forgot Password - Yalla Al Mandhi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="resources/css/style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
    <body class="auth-page">
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">Yalla <span>Al Mandhi</span></a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="login.php" class="nav-link">Login</a>
                <a href="signup.php" class="nav-link">Sign Up</a>
            </div>
            <div class="mobile-toggle" id="mobileToggle">☰</div>
        </div>
    </nav>
    <section class="auth-container">
        <div class="auth-card">
            <h2 class="auth-title display-3">Forgot Password</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <?php if (isset($reset_token) && $reset_token): ?>
                        <div style="margin-top: 10px; font-size: 0.9rem;">
                            <strong>Demo Token:</strong> <?php echo htmlspecialchars($reset_token); ?><br>
                            <a href="reset-password.php?token=<?php echo urlencode($reset_token); ?>">
                                Click here to reset password
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="forgot_password" value="1">
                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" 
                           name="email" 
                           class="form-control" 
                           required 
                           placeholder="Enter your registered email">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Send Reset Instructions
                </button>
            </form>
            <div class="form-footer" style="margin-top: 20px;">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </section>
</body>
</html>