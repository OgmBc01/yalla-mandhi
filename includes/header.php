<?php
include 'database.php';
include 'functions.php'
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yalla Al Mandhi | Authentic Yemani Mandhi Restaurant</title>
    <meta name="description" content="Experience authentic Yemani Mandhi in a family-friendly atmosphere. Traditional flavors with modern presentation.">
    
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

    <title>Yalla Al Mandhi | Authentic Yemani Mandhi Restaurant</title>
    <meta name="description" content="Experience authentic Yemani Mandhi in a family-friendly atmosphere. Traditional flavors with modern presentation.">

    <!-- Open Graph -->
    <meta property="og:title" content="Yalla Al Mandhi | Authentic Yemani Mandhi Restaurant">
    <meta property="og:description" content="Experience authentic Yemani Mandhi in a family-friendly atmosphere. Traditional flavors with modern presentation.">
    <meta property="og:type" content="yallaalmandhi">
    <meta property="og:url" content="https://yallaalmandhi.com/">
    <meta property="og:image" content="https://yallaalmandhi.com/resources/img/mandhi.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Yalla Al Mandhi | Authentic Yemani Mandhi Restaurant">
    <meta name="twitter:description" content="Experience authentic Yemani Mandhi in a family-friendly atmosphere. Traditional flavors with modern presentation.">
    <meta name="twitter:image" content="https://yallaalmandhi.com/resources/img/mandhi.jpg">
</head>
<body>
    <!-- ===== NAVIGATION ===== -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="index.html" class="logo">Yalla <span>Al Mandhi</span></a>
            
            <div class="nav-links">
                <a href="index.html" class="nav-link active">Home</a>
                
                <div class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle">About Us</a>
                    <div class="dropdown-menu">
                        <a href="about.html" class="dropdown-item">About Yalla Al Mandhi</a>
                        <a href="gallery.html" class="dropdown-item">Gallery</a>
                        <a href="offers.html" class="dropdown-item">Offers & Promotions</a>
                        <a href="testimonials.html" class="dropdown-item">Testimonials</a>
                    </div>
                </div>
                
                <a href="menu.html" class="nav-link">Menu</a>
                <a href="branches.html" class="nav-link">Branches</a>
                <a href="contact.html" class="nav-link">Contact / Reservations</a>
            </div>
            
            <div class="mobile-toggle" id="mobileToggle">
                ☰
            </div>
        </div>
    </nav>
