<?php
// profile.php
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user = getCurrentUser();
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $data = [
        'full_name' => $_POST['full_name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? ''
    ];
    
    $result = updateProfile($_SESSION['user_id'], $data);
    
    if ($result['success']) {
        $success = $result['message'];
        $user = getCurrentUser(); // Refresh user data
    } else {
        $error = $result['message'];
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        $result = changePassword($_SESSION['user_id'], $current_password, $new_password);
        
        if ($result['success']) {
            $success = $result['message'];
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
    <title>My Profile | Yalla Al Mandi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="resources/css/style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
    <body class="profile-page">
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">Yalla <span>Al Mandi</span></a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="reservations.php" class="nav-link">Reservations</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="logout.php" class="btn btn-primary" style="margin-left: 10px; padding: 8px 22px; font-weight: 600; border-radius: 6px; background: var(--color-red); color: #fff; border: none; transition: background 0.2s;">Logout</a>
            </div>
            <div class="mobile-toggle" id="mobileToggle">☰</div>
        </div>
    </nav>
    <section class="profile-section">
        <div class="profile-card">
            <div class="profile-avatar">
                <span class="profile-avatar-inner"><i class="bi bi-person-circle"></i></span>
            </div>
            <h2 class="profile-title">My Profile</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="POST" class="profile-form">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    <small>Username cannot be changed</small>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
            <form method="POST" class="profile-form">
                <input type="hidden" name="change_password" value="1">
                <h3>Change Password</h3>
                <div class="form-group">
                    <label>Current Password *</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password *</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password *</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-secondary">Change Password</button>
            </form>
        </div>
    </section>
</body>
</html>