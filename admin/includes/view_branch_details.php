<?php
// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin'])) {
    header("Location: login.php");
    exit();
}

// Get branch ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: branches.php");
    exit();
}

$branch_id = (int)$_GET['id'];

// Fetch branch data
$branch_query = "SELECT * FROM branches WHERE id = ?";
$branch_stmt = $connection->prepare($branch_query);
$branch_stmt->bind_param("i", $branch_id);
$branch_stmt->execute();
$branch_result = $branch_stmt->get_result();

if ($branch_result->num_rows === 0) {
    header("Location: branches.php");
    exit();
}

$branch = $branch_result->fetch_assoc();
$branch_stmt->close();

// Get branch statistics
$stats_query = "SELECT 
    COUNT(DISTINCT u.id) as total_customers,
    COUNT(DISTINCT o.id) as total_orders,
    COALESCE(SUM(o.total_amount), 0) as total_revenue
    FROM users u
    LEFT JOIN orders o ON u.id = o.customer_id
    WHERE u.preferred_branch = ?";
$stats_stmt = $connection->prepare($stats_query);
$stats_stmt->bind_param("i", $branch_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stats_stmt->close();
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Branch Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="branches.php">Branches</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($branch['name']); ?></li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                <a href="branches.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <a href="branches.php?source=edit_branch&id=<?php echo $branch_id; ?>" 
                   class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <button type="button" class="btn btn-danger" 
                        onclick="branchShowDeleteConfirm(
                            <?php echo $branch_id; ?>,
                            '<?php echo htmlspecialchars(addslashes($branch['name']), ENT_QUOTES); ?>'
                        )">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
        </div>

        <!-- Branch Profile Card -->
        <div class="row mb-4">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 120px; height: 120px;">
                            <i class="bi bi-building display-3 text-muted"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($branch['name']); ?></h3>
                        
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <?php if ($branch['is_active']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Active
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> Inactive
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-start">
                            <h6><i class="bi bi-geo-alt me-2"></i> Address</h6>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($branch['address'])); ?></p>
                            
                            <?php if ($branch['phone']): ?>
                                <h6><i class="bi bi-telephone me-2"></i> Phone</h6>
                                <p><?php echo htmlspecialchars($branch['phone']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($branch['email']): ?>
                                <h6><i class="bi bi-envelope me-2"></i> Email</h6>
                                <p><?php echo htmlspecialchars($branch['email']); ?></p>
                            <?php endif; ?>
                            
                            <p><strong>Created:</strong> <?php echo date('F d, Y', strtotime($branch['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Customers</h6>
                                        <h2 class="mb-0"><?php echo $stats['total_customers'] ?? 0; ?></h2>
                                    </div>
                                    <i class="bi bi-people display-4 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Orders</h6>
                                        <h2 class="mb-0"><?php echo $stats['total_orders'] ?? 0; ?></h2>
                                    </div>
                                    <i class="bi bi-cart display-4 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Revenue</h6>
                                        <h2 class="mb-0">$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h2>
                                    </div>
                                    <i class="bi bi-currency-dollar display-4 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Avg/Order</h6>
                                        <h2 class="mb-0">
                                            <?php
                                            $avg_order = ($stats['total_orders'] > 0) 
                                                ? $stats['total_revenue'] / $stats['total_orders'] 
                                                : 0;
                                            echo '$' . number_format($avg_order, 2);
                                            ?>
                                        </h2>
                                    </div>
                                    <i class="bi bi-graph-up display-4 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Opening Hours -->
                <?php if ($branch['opening_hours']): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Opening Hours</h5>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;"><?php echo htmlspecialchars($branch['opening_hours']); ?></pre>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Quick Actions -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <a href="customers.php?branch=<?php echo $branch_id; ?>" 
                                   class="btn btn-outline-primary w-100">
                                    <i class="bi bi-people me-1"></i> View Customers
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="orders.php?branch=<?php echo $branch_id; ?>" 
                                   class="btn btn-outline-success w-100">
                                    <i class="bi bi-cart me-1"></i> View Orders
                                </a>
                            </div>
                            <div class="col-md-4">
                                <?php if ($branch['is_active']): ?>
                                    <button class="btn btn-outline-secondary w-100" 
                                            onclick="toggleBranchStatus(<?php echo $branch_id; ?>, 0)">
                                        <i class="bi bi-toggle-on me-1"></i> Deactivate
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-success w-100" 
                                            onclick="toggleBranchStatus(<?php echo $branch_id; ?>, 1)">
                                        <i class="bi bi-toggle-off me-1"></i> Activate
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>