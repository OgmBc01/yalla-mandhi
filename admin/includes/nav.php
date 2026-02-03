<?php
// admin/includes/nav.php
?>
<!-- Top Navigation Bar -->
<nav class="navbar top-navbar fixed-top navbar-expand-lg">
    <div class="container-fluid">
        <!-- Brand/Logo -->
        <a class="navbar-brand d-flex align-items-center" href="../index.php">
            <div class="logo-icon me-2">
                <i class="bi bi-house-door" style="color: var(--color-red); font-size: 1.5rem;"></i>
            </div>
            <span class="brand-text">Yalla Al Mandi</span>
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminTopNav">
            <i class="bi bi-list"></i>
        </button>
        
        <!-- Right-aligned user profile -->
        <div class="collapse navbar-collapse justify-content-end" id="adminTopNav">
            <div class="user-profile">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar me-2">
                            <i class="bi bi-person-circle" style="font-size: 1.5rem; color: var(--color-red);"></i>
                        </div>
                        <div class="user-info d-none d-md-block">
                            <span class="user-name">
                                <?php echo htmlspecialchars($current_user['username'] ?? 'Admin'); ?>
                            </span>
                            <small class="user-role text-muted d-block">
                                <?php echo htmlspecialchars($_SESSION['role'] ?? 'Admin'); ?>
                            </small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownUser">
                        <li><a class="dropdown-item" href="../index.php"><i class="bi bi-house me-2"></i> Home</a></li>
                        <li><a class="dropdown-item" href="../profile.php"><i class="bi bi-person-circle me-2"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>