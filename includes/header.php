<?php
include 'database.php';
include 'functions.php';
$currentUser = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yalla Al Mandi | Authentic Yemani Mandi Restaurant</title>
    <meta name="description" content="Experience authentic Yemani Mandi in a family-friendly atmosphere. Traditional flavors with modern presentation.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Our CSS -->
    <link rel="stylesheet" href="resources/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">

    <!-- PRODUCTION HEADER -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Yalla Al Mandi | Authentic Yemani Mandi Restaurant</title>
    <meta name="description" content="Experience authentic Yemani Mandi in a family-friendly atmosphere. Traditional flavors with modern presentation.">

    <!-- Open Graph -->
    <meta property="og:title" content="Yalla Al Mandi | Authentic Yemani Mandi Restaurant">
    <meta property="og:description" content="Experience authentic Yemani Mandi in a family-friendly atmosphere. Traditional flavors with modern presentation.">
    <meta property="og:type" content="yallaalmandi">
    <meta property="og:url" content="https://yallaalmandhi.com/">
    <meta property="og:image" content="https://yallaalmandhi.com/resources/img/mandhi.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Yalla Al Mandi | Authentic Yemani Mandi Restaurant">
    <meta name="twitter:description" content="Experience authentic Yemani Mandi in a family-friendly atmosphere. Traditional flavors with modern presentation.">
    <meta name="twitter:image" content="https://yallaalmandhi.com/resources/img/mandhi.jpg">
</head>
<body>
    <!-- ===== NAVIGATION ===== -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">Yalla <span>Al Mandi</span></a>
            
            <div class="nav-links">
                <a href="index.php" class="nav-link active">Home</a>
                <div class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle">About Us</a>
                    <div class="dropdown-menu">
                        <a href="about.php" class="dropdown-item">About Yalla Al Mandi</a>
                        <a href="gallery.php" class="dropdown-item">Gallery</a>
                        <a href="offers.php" class="dropdown-item">Offers & Promotions</a>
                        <a href="testimonials.php" class="dropdown-item">Testimonials</a>
                    </div>
                </div>
                <a href="menu.php" class="nav-link">Menu</a>
                <a href="branches.php" class="nav-link">Branches</a>
                <a href="contact.php" class="nav-link">Contact / Reservations</a>
                <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                    <div class="dropdown" style="margin-left: 18px;">
                        <a href="#" class="dropdown-toggle" style="display: flex; align-items: center; gap: 8px; background: var(--color-beige); border: 2px solid var(--color-red); border-radius: 24px; box-shadow: 0 2px 8px rgba(196,30,58,0.07); padding: 4px 18px 4px 6px; font-weight: 600; text-decoration: none;">
                            <span style="display: inline-flex; width: 32px; height: 32px; border-radius: 50%; background: #fff; align-items: center; justify-content: center; margin-right: 6px;">
                                <i class="bi bi-person-circle" style="font-size: 1.5rem; color: var(--color-red);"></i>
                            </span>
                            <?php echo htmlspecialchars($currentUser['username']); ?>
                        </a>
                        <div class="dropdown-menu">
                            <a href="profile.php" class="dropdown-item">
                                <i class="bi bi-person"></i> My Profile
                            </a>
                            <a href="reservations.php" class="dropdown-item">
                                <i class="bi bi-calendar-check"></i> My Reservations
                            </a>
                            <a href="orders.php" class="dropdown-item">
                                <i class="bi bi-bag"></i> My Orders
                            </a>
                            <?php if (function_exists('isAdmin') && isAdmin()): ?>
                                <div class="dropdown-divider"></div>
                                <a href="admin/dashboard.php" class="dropdown-item">
                                    <i class="bi bi-speedometer2"></i> Admin Dashboard
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary" style="margin-left: 18px; padding: 8px 22px; font-weight: 600; border-radius: 6px; background: var(--color-red); color: #fff; border: none; transition: background 0.2s;">Login</a>
                    <a href="signup.php" class="btn" style="margin-left: 10px; padding: 8px 22px; font-weight: 600; border-radius: 6px; background: var(--color-beige); color: var(--color-red); border: 2px solid var(--color-red); transition: background 0.2s;">Sign Up</a>
                <?php endif; ?>
            </div>
            
            <div class="mobile-toggle" id="mobileToggle">
                ☰
            </div>
        </div>
    </nav>
