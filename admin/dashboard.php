<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/header.php';
require_once 'includes/nav.php';
require_once 'includes/sidebar.php';
require_once 'includes/functions.php';

$current_user = getCurrentUser();

// Ensure $connection is set
if (!isset($connection) || !$connection) {
    if (function_exists('getDBConnection')) {
        $connection = getDBConnection();
    } else {
        require_once dirname(__DIR__, 1) . '/includes/database.php';
        $connection = getDBConnection();
    }
}

// Get dashboard statistics
$stats = getDashboardStats($connection);

// Get recent reservations
$recent_reservations = getRecentReservations($connection, 5);

// Get recent orders
$recent_orders = getRecentOrders($connection, 5);

// Get popular menu items
$popular_items = getPopularMenuItems($connection, 5);

// Get today's reservations by hour
$hourly_reservations = getTodayReservationsByHour($connection);

// Get monthly revenue
$monthly_revenue = getMonthlyRevenue($connection, date('Y'));
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="h4">Dashboard Overview</h2>
                <p class="text-muted">Welcome back, <strong><?php echo htmlspecialchars($current_user['username']); ?></strong>! Here's what's happening today at Yalla Al Mandi.</p>
            </div>
        </div>
        
        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Today's Revenue
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    AED <?php echo number_format($stats['today_revenue'], 2); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-currency-exchange fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Reservations
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $stats['total_reservations']; ?>
                                </div>
                                <div class="mt-2">
                                    <small class="text-danger">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo $stats['pending_reservations']; ?> pending
                                    </small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-check fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Orders
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $stats['total_orders']; ?>
                                </div>
                                <div class="mt-2">
                                    <small class="text-warning">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo $stats['pending_orders']; ?> pending
                                    </small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-cart fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Total Customers
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $stats['total_users']; ?>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-people me-1"></i>
                                        Registered users
                                    </small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts and Tables Row -->
        <div class="row">
            <!-- Revenue Chart -->
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Monthly Revenue (<?php echo date('Y'); ?>)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Reservations by Hour -->
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Today's Reservations by Hour</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-4">
                            <canvas id="hourlyReservationsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Row -->
        <div class="row">
            <!-- Recent Reservations -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Reservations</h6>
                           <a class="btn btn-sm btn-primary" href="includes/view_all_reservations.php">
                               View All
                           </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Date & Time</th>
                                        <th>Guests</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_reservations)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No recent reservations</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_reservations as $reservation): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($reservation['customer_name']); ?></td>
                                                <td>
                                                    <?php echo date('M d', strtotime($reservation['reservation_date'])); ?><br>
                                                    <small><?php echo date('h:i A', strtotime($reservation['reservation_time'])); ?></small>
                                                </td>
                                                <td><?php echo $reservation['number_of_guests']; ?></td>
                                                <td>
                                                    <span class="badge badge-<?php 
                                                        switch($reservation['status']) {
                                                            case 'confirmed': echo 'success'; break;
                                                            case 'pending': echo 'warning'; break;
                                                            case 'cancelled': echo 'danger'; break;
                                                            default: echo 'secondary';
                                                        }
                                                    ?>">
                                                        <?php echo ucfirst($reservation['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                        <a class="btn btn-sm btn-primary" href="orders.php">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_orders)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No recent orders</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars($order['order_number']); ?></td>
                                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                <td>AED <?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php 
                                                        switch($order['status']) {
                                                            case 'delivered': echo 'success'; break;
                                                            case 'preparing': echo 'info'; break;
                                                            case 'pending': echo 'warning'; break;
                                                            case 'cancelled': echo 'danger'; break;
                                                            default: echo 'secondary';
                                                        }
                                                    ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Items Row -->
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Popular Menu Items</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if (empty($popular_items)): ?>
                                <div class="col-12 text-center text-muted">No popular items data available</div>
                            <?php else: ?>
                                <?php foreach ($popular_items as $item): ?>
                                    <div class="col-md-2 col-sm-4 col-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <div class="text-primary mb-2">
                                                    <i class="bi bi-egg-fried fa-2x"></i>
                                                </div>
                                                <h6 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <p class="card-text small text-muted"><?php echo htmlspecialchars($item['category']); ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-primary">AED <?php echo $item['price']; ?></span>
                                                    <span class="badge bg-success"><?php echo $item['total_quantity']; ?> sold</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a class="btn btn-primary w-100 h-100 py-3" href="reservations.php?source=add_reservation">
                                    <i class="bi bi-plus-circle me-2"></i> Add Reservation
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a class="btn btn-success w-100 h-100 py-3" href="orders.php?source=add_order">
                                    <i class="bi bi-cart-plus me-2"></i> Add Order
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a class="btn btn-info w-100 h-100 py-3" href="menu_items.php?source=add_item">
                                    <i class="bi bi-plus-circle me-2"></i> Add Menu Item
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a class="btn btn-warning w-100 h-100 py-3" href="reports.php">
                                    <i class="bi bi-graph-up me-2"></i> View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
// Expose PHP data for dashboard charts to JS
echo '<script>';
echo 'window.dashboardMonthlyRevenue = ' . json_encode(array_values($monthly_revenue)) . ';';
echo 'window.dashboardHourlyReservations = ' . json_encode(array_values($hourly_reservations)) . ';';
echo '</script>';
include 'includes/footer.php';
?>

