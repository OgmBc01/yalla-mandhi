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
                    <!-- Quick Navigation Shortcuts -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-3 justify-content-start align-items-center">
                                <a href="orders.php?source=add_order" class="btn btn-outline-success btn-lg d-flex align-items-center gap-2" title="New Order">
                                    <i class="bi bi-plus-circle"></i> New Order
                                </a>
                                <a href="orders.php?source=order_list" class="btn btn-outline-info btn-lg d-flex align-items-center gap-2" title="All Orders">
                                    <i class="bi bi-card-checklist"></i> All Orders
                                </a>
                                <a href="orders.php?source=online_orders" class="btn btn-outline-warning btn-lg d-flex align-items-center gap-2" title="Online Orders">
                                    <i class="bi bi-globe"></i> Online Orders
                                </a>
                                <a href="orders.php?source=kitchen_display" class="btn btn-outline-secondary btn-lg d-flex align-items-center gap-2" title="Kitchen Display">
                                    <i class="bi bi-tv"></i> Kitchen Display
                                </a>
                                <a href="menu_items.php" class="btn btn-outline-warning btn-lg d-flex align-items-center gap-2" title="View Menu Items">
                                    <i class="bi bi-card-checklist"></i> Menu Items
                                </a>
                                <a href="categories.php" class="btn btn-outline-danger btn-lg d-flex align-items-center gap-2" title="Categories">
                                    <i class="bi bi-tags"></i> Categories
                                </a>
                                <a href="customers.php?source=add_customer" class="btn btn-outline-primary btn-lg d-flex align-items-center gap-2" title="Add Customer">
                                    <i class="bi bi-person-plus"></i> Add Customer
                                </a>
                                <a href="customers.php" class="btn btn-outline-success btn-lg d-flex align-items-center gap-2" title="View Customers">
                                    <i class="bi bi-person-lines-fill"></i> Customers
                                </a>
                                <a href="branches.php" class="btn btn-outline-warning btn-lg d-flex align-items-center gap-2" title="View Branches">
                                    <i class="bi bi-card-checklist"></i> Branches
                                </a>
                                <a href="employees.php?source=add_employee" class="btn btn-outline-info btn-lg d-flex align-items-center gap-2" title="Add Employee">
                                    <i class="bi bi-person-plus"></i> Add Employee
                                </a>
                                <a href="employees.php" class="btn btn-outline-warning btn-lg d-flex align-items-center gap-2" title="View Employees">
                                    <i class="bi bi-person-lines-fill"></i> Employees
                                </a>
                                <a href="shifts.php?source=add_shift" class="btn btn-outline-danger btn-lg d-flex align-items-center gap-2" title="Add Shift">
                                    <i class="bi bi-plus-circle"></i> Add Shift
                                </a>
                                <a href="shifts.php" class="btn btn-outline-primary btn-lg d-flex align-items-center gap-2" title="View Shifts">
                                    <i class="bi bi-card-checklist"></i> Shifts
                                </a>
                                <a href="shifts.php?source=attendance" class="btn btn-outline-success btn-lg d-flex align-items-center gap-2" title="Attendance">
                                    <i class="bi bi-calendar-check"></i> Attendance
                                </a>
                                <a href="reports.php" class="btn btn-outline-info btn-lg d-flex align-items-center gap-2" title="Reports">
                                    <i class="bi bi-graph-up"></i> Reports
                                </a>
                                <a href="settings.php" class="btn btn-outline-warning btn-lg d-flex align-items-center gap-2" title="Settings">
                                    <i class="bi bi-gear"></i> Settings
                                </a>
                            </div>
                        </div>
                    </div>
        </div>
        
        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Today's Revenue</h6>
                                <h2 class="mb-0">AED <?php echo number_format($stats['today_revenue'], 2); ?></h2>
                                <div class="mt-2">
                                    <small>Yesterday: AED <?php echo number_format($stats['yesterday_revenue'] ?? 0, 2); ?></small><br>
                                    <small>Last 7 days: AED <?php echo number_format($stats['week_revenue'] ?? 0, 2); ?></small><br>
                                    <small>Last 30 days: AED <?php echo number_format($stats['month_revenue'] ?? 0, 2); ?></small>
                                </div>
                            </div>
                            <i class="bi bi-currency-exchange display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Reservations</h6>
                                <h2 class="mb-0"><?php echo $stats['total_reservations']; ?></h2>
                                <div class="mt-2">
                                    <small>Yesterday: <?php echo $stats['yesterday_reservations'] ?? 0; ?></small><br>
                                    <small>Last 7 days: <?php echo $stats['week_reservations'] ?? 0; ?></small><br>
                                    <small>Last 30 days: <?php echo $stats['month_reservations'] ?? 0; ?></small>
                                    <br><small class="text-danger"><i class="bi bi-clock me-1"></i><?php echo $stats['pending_reservations']; ?> pending</small>
                                </div>
                            </div>
                            <i class="bi bi-calendar-check display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Orders</h6>
                                <h2 class="mb-0"><?php echo $stats['total_orders']; ?></h2>
                                <div class="mt-2">
                                    <small>Yesterday: <?php echo $stats['yesterday_orders'] ?? 0; ?></small><br>
                                    <small>Last 7 days: <?php echo $stats['week_orders'] ?? 0; ?></small><br>
                                    <small>Last 30 days: <?php echo $stats['month_orders'] ?? 0; ?></small>
                                    <br><small class="text-warning"><i class="bi bi-clock me-1"></i><?php echo $stats['pending_orders']; ?> pending</small>
                                </div>
                            </div>
                            <i class="bi bi-cart-check display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Customers</h6>
                                <h2 class="mb-0"><?php echo $stats['total_users']; ?></h2>
                                <div class="mt-2">
                                    <small>Yesterday: <?php echo $stats['yesterday_users'] ?? 0; ?></small><br>
                                    <small>Last 7 days: <?php echo $stats['week_users'] ?? 0; ?></small><br>
                                    <small>Last 30 days: <?php echo $stats['month_users'] ?? 0; ?></small>
                                    <br><small class="text-muted"><i class="bi bi-people me-1"></i>Registered users</small>
                                </div>
                            </div>
                            <i class="bi bi-people display-4 opacity-50"></i>
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

            <!-- Today's Revenue Breakdown -->
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Today's Revenue Breakdown</h6>
                    </div>
                    <div class="card-body">
                        <div class="revenue-breakdown-box text-center">
                            <?php
                            // Fetch today's payment method breakdown directly from database
                            $today_payment_query = "SELECT 
                                COALESCE(payment_method, 'unknown') as payment_method,
                                SUM(total_amount) as total
                                FROM orders 
                                WHERE DATE(created_at) = CURDATE() 
                                AND payment_status = 'paid'
                                GROUP BY payment_method";
                            $payment_result = $connection->query($today_payment_query);
                            $payment_breakdown = [];
                            $payment_icons = [
                                'cash' => 'bi-cash-coin text-success',
                                'card' => 'bi-credit-card-2-front text-primary',
                                'online' => 'bi-wifi text-info',
                                'unknown' => 'bi-question-circle text-secondary'
                            ];
                            $payment_labels = [
                                'cash' => 'Cash',
                                'card' => 'Card',
                                'online' => 'Online',
                                'unknown' => 'Other'
                            ];
                            $total_today = 0;
                            while ($row = $payment_result->fetch_assoc()) {
                                $method = $row['payment_method'] ?: 'unknown';
                                $payment_breakdown[$method] = $row['total'];
                                $total_today += $row['total'];
                            }
                            // If no payments today, show zeros for all methods
                            if (empty($payment_breakdown)) {
                                $payment_breakdown = [
                                    'cash' => 0,
                                    'card' => 0,
                                    'online' => 0
                                ];
                            }
                            ?>
                            <h4 class="mb-3">Total Collected: <span class="fw-bold text-success">AED <?php echo number_format($total_today, 2); ?></span></h4>
                            <div class="d-flex flex-column gap-2 align-items-center">
                                <?php
                                foreach (['cash', 'card', 'online'] as $method):
                                    $amount = $payment_breakdown[$method] ?? 0;
                                    $percentage = $total_today > 0 ? round(($amount / $total_today) * 100, 1) : 0;
                                ?>
                                <div class="revenue-method-row bg-light rounded p-3 w-100 mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi <?php echo $payment_icons[$method]; ?> me-2"></i>
                                            <span class="fw-bold"><?php echo $payment_labels[$method]; ?>:</span>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-<?php echo $method == 'cash' ? 'success' : ($method == 'card' ? 'primary' : 'info'); ?>">
                                                AED <?php echo number_format($amount, 2); ?>
                                            </span>
                                            <span class="badge bg-secondary ms-2"><?php echo $percentage; ?>%</span>
                                        </div>
                                    </div>
                                    <?php if ($total_today > 0): ?>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-<?php echo $method == 'cash' ? 'success' : ($method == 'card' ? 'primary' : 'info'); ?>" 
                                             style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                                <!-- Summary -->
                                <div class="mt-3 pt-2 border-top w-100">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Total Transactions:</span>
                                        <span class="fw-bold"><?php echo array_sum($payment_breakdown) > 0 ? count(array_filter($payment_breakdown)) : 0; ?></span>
                                    </div>
                                </div>
                            </div>
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
                                                    <?php 
                                                        // Use 'order_status' from DB, fallback to 'closed' if missing
                                                        $status = isset($order['order_status']) && $order['order_status'] !== null ? $order['order_status'] : 'closed';
                                                        switch($status) {
                                                            case 'delivered': $badge = 'success'; break;
                                                            case 'preparing': $badge = 'info'; break;
                                                            case 'pending': $badge = 'warning'; break;
                                                            case 'cancelled': $badge = 'danger'; break;
                                                            case 'closed': $badge = 'secondary'; break;
                                                            default: $badge = 'secondary';
                                                        }
                                                    ?>
                                                    <span class="badge badge-<?php echo $badge; ?>" style="color:#fff !important;">
                                                        <?php echo ucfirst($status); ?>
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
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                                        <div class="popular-item-card">
                                            <div class="popular-item-icon">
                                                <i class="bi bi-egg-fried"></i>
                                            </div>
                                            <div class="popular-item-title">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </div>
                                            <div class="popular-item-category">
                                                <?php echo htmlspecialchars($item['category']); ?>
                                            </div>
                                            <div class="popular-item-badges">
                                                <span class="popular-item-badge-price">AED <?php echo number_format($item['price'], 2); ?></span>
                                                <span class="popular-item-badge-sold"><?php echo $item['total_quantity']; ?> sold</span>
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

<style>
/* Responsive dashboard stat-cards */
@media (max-width: 1200px) {
    .main-content .row.mb-4 > .col-md-3 {
        flex: 0 0 50%;
        max-width: 50%;
        margin-bottom: 1rem;
    }
}
@media (max-width: 992px) {
    .main-content .row.mb-4 > .col-md-3 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 1rem;
    }
}
@media (max-width: 768px) {
    .main-content .row.mb-4 > .col-md-3 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 1rem;
    }
    .main-content .row.mb-4 {
        flex-direction: column;
    }
    .popular-item-card {
        min-height: 180px;
        padding: 1rem 0.5rem;
    }
}
@media (max-width: 576px) {
    .main-content .row.mb-4 > .col-md-3 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 1rem;
    }
    .popular-item-card {
        min-height: 140px;
        padding: 0.7rem 0.3rem;
        font-size: 0.95em;
    }
    .popular-item-title {
        font-size: 1em;
    }
    .popular-item-icon {
        font-size: 1.5em;
    }
}
/* Responsive grid for popular menu items */
.popular-items-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}
.popular-items-row > [class*='col-'] {
    flex: 1 1 160px;
    min-width: 140px;
    max-width: 220px;
}
/* Dashboard Theme Improvements */
.card-header, .card-header.py-3 {
    background: #f5f1e8 !important;
    color: #c41e3a !important;
    border-bottom: 2px solid #f39c12;
    font-weight: 600;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(196,30,58,0.08);
}
.card {
    border-radius: 16px !important;
    box-shadow: 0 4px 24px rgba(196,30,58,0.08), 0 1.5px 4px rgba(243,156,18,0.08);
    border: none !important;
    overflow: hidden;
}
.card-footer {
    background: #f5f1e8;
    border-top: 1px solid #f39c12;
    border-radius: 0 0 16px 16px;
    font-weight: 500;
}
/* Remove stat-card and card-body background so card color comes from .bg-primary, .bg-success, etc. */
/* Badge improvements for visibility */
.badge {
    border-radius: 8px !important;
    font-size: 0.95em;
    padding: 0.45em 1.1em;
    font-weight: 600;
    letter-spacing: 0.03em;
    color: #fff !important;
}
.badge-secondary {
    background: #b2b2b2 !important;
    color: #fff !important;
}
.badge-success {
    background: #27ae60 !important;
    color: #fff !important;
}
.badge-info {
    background: #3498db !important;
    color: #fff !important;
}
.badge-warning {
    background: #f39c12 !important;
    color: #fff !important;
}
.badge-danger {
    background: #c41e3a !important;
    color: #fff !important;
}
.table-hover tbody tr:hover {
    background: #fffbe6 !important;
}
.btn-primary, .btn-success, .btn-info, .btn-warning {
    border: none;
    border-radius: 10px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(196,30,58,0.08);
    transition: background 0.2s, box-shadow 0.2s;
}
.btn-primary {
    background: linear-gradient(135deg, #c41e3a 0%, #f39c12 100%) !important;
}
.btn-success {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%) !important;
}
.btn-info {
    background: linear-gradient(135deg, #3498db 0%, #6dd5fa 100%) !important;
}
.btn-warning {
    background: linear-gradient(135deg, #f39c12 0%, #f7b733 100%) !important;
    color: #fff !important;
}
.card-title, .h4, .h5, .h6 {
    color: #c41e3a !important;
    font-weight: 700;
    letter-spacing: 0.5px;
}
.text-muted {
    color: #8a8635 !important;
}
.table th {
    background: #f5f1e8 !important;
    color: #c41e3a !important;
    font-weight: 700;
    border: none;
}
.table td {
    border: none;
}
.table thead tr {
    border-bottom: 2px solid #f39c12 !important;
}
.table-responsive {
    border-radius: 12px;
    overflow: hidden;
}
.chart-area, .chart-pie {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(196,30,58,0.04);
    padding: 1rem;
}
/* Quick Actions */
.card .btn {
    margin-bottom: 0.5rem;
}
.card .btn i {
    font-size: 1.2em;
    margin-right: 0.5em;
}
/* Popular Menu Items Card Improvements */
.popular-item-card {
    background: #fffbe6;
    border: 1.5px solid #c41e3a;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(196,30,58,0.06);
    padding: 1.2rem 0.7rem 1rem 0.7rem;
    margin-bottom: 1.2rem;
    transition: box-shadow 0.2s;
    min-height: 210px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
}
.popular-item-card:hover {
    box-shadow: 0 8px 32px rgba(196,30,58,0.13);
    transform: translateY(-2px) scale(1.01);
}
.popular-item-icon {
    color: #c41e3a;
    font-size: 2.2em;
    margin-bottom: 0.5em;
}
.popular-item-title {
    font-size: 1.1em;
    font-weight: 700;
    color: #c41e3a;
    margin-bottom: 0.2em;
    text-align: center;
}
.popular-item-category {
    font-size: 0.95em;
    color: #8a8635;
    margin-bottom: 0.5em;
    text-align: center;
}
.popular-item-badges {
    display: flex;
    gap: 0.5em;
    justify-content: center;
    margin-top: 0.5em;
}
.popular-item-badge-price {
    background: #f39c12;
    color: #fff;
    border-radius: 8px;
    padding: 0.3em 0.9em;
    font-weight: 600;
    font-size: 0.98em;
}
.popular-item-badge-sold {
    background: #27ae60;
    color: #fff;
    border-radius: 8px;
    padding: 0.3em 0.9em;
    font-weight: 600;
    font-size: 0.98em;
}
</style>

