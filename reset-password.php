<?php
// reset-password.php
require_once 'includes/functions.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($new_password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $result = resetPassword($token, $new_password);
        
        if ($result['success']) {
            $success = $result['message'];
            $token = ''; // Clear token after successful reset
        } else {
            $error = $result['message'];
        }
    }
}

// If no token provided, redirect
if (empty($token) && !$success) {
    header('Location: forgot-password.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Yalla Al Mandi</title>
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
            <a href="index.php" class="logo">Yalla <span>Al Mandi</span></a>
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
            <h2 class="auth-title display-3">Reset Password</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <div style="margin-top: 10px;">
                        <a href="login.php" class="btn btn-primary">Login Now</a>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="reset_password" value="1">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <label class="form-label">New Password *</label>
                        <input type="password" 
                               name="new_password" 
                               class="form-control" 
                               required 
                               placeholder="Enter new password">
                        <div class="form-hint">
                            Must be at least 8 characters with uppercase, lowercase, and number
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password *</label>
                        <input type="password" 
                               name="confirm_password" 
                               class="form-control" 
                               required 
                               placeholder="Confirm new password">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Reset Password
                    </button>
                </form>
                <div class="form-footer" style="margin-top: 20px;">
                    <a href="login.php">Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>