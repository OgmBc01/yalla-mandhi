
<?php
define('ADMIN_ROLES', ['admin', 'super-admin']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure $connection is set
if (!isset($connection) || !$connection) {
    if (function_exists('getDBConnection')) {
        $connection = getDBConnection();
    } else {
        require_once dirname(__DIR__, 2) . '/includes/database.php';
        $connection = getDBConnection();
    }
}

/**
 * Enforce session timeout (30 minutes)
 */
function enforce_session_timeout() {
    $timeout = 1800;

    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
        session_unset();
        session_destroy();
        header('Location: ../index.php?error=session_timeout');
        exit();
    }

    $_SESSION['LAST_ACTIVITY'] = time();
}

/**
 * Check if logged in
 */
function isLoggedIn() {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
}

/**
 * Check if user has admin access
 */
function isAdminUser() {
    return isLoggedIn()
        && isset($_SESSION['role'])
        && in_array($_SESSION['role'], ADMIN_ROLES, true);
}

/**
 * Require admin access
 */
function requireAdmin() {
    if (!isAdminUser()) {
        header('Location: ../index.php?error=access_denied');
        exit();
    }
}

/**
 * Get current user info (from session)
 */
if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        if (!isLoggedIn()) {
            return null;
        }
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'full_name' => $_SESSION['full_name'] ?? null,
            'role' => $_SESSION['role'] ?? null
        ];
    }
}

// Get dashboard statistics
if (!function_exists('getDashboardStats')) {
    function getDashboardStats($connection) {
        $stats = [];
        
        // Total reservations
        $sql = "SELECT COUNT(*) as total FROM reservations";
        $result = $connection->query($sql);
        $stats['total_reservations'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Total orders
        $sql = "SELECT COUNT(*) as total FROM orders";
        $result = $connection->query($sql);
        $stats['total_orders'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Total users
        $sql = "SELECT COUNT(*) as total FROM users";
        $result = $connection->query($sql);
        $stats['total_users'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Total revenue (today)
        $today = date('Y-m-d');
        $sql = "SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = '$today' AND payment_status = 'paid'";
        $result = $connection->query($sql);
        $stats['today_revenue'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Pending reservations
        $sql = "SELECT COUNT(*) as total FROM reservations WHERE status = 'pending'";
        $result = $connection->query($sql);
        $stats['pending_reservations'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Pending orders
        $sql = "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'";
        $result = $connection->query($sql);
        $stats['pending_orders'] = $result->fetch_assoc()['total'] ?? 0;
        
        return $stats;
    }
}

// Get recent reservations
if (!function_exists('getRecentReservations')) {
    function getRecentReservations($connection, $limit = 5) {
        $sql = "SELECT r.*, b.name as branch_name 
                FROM reservations r 
                LEFT JOIN branches b ON r.branch_id = b.id 
                ORDER BY r.reservation_date DESC, r.reservation_time DESC 
                LIMIT $limit";
        $result = $connection->query($sql);
        
        $reservations = [];
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        return $reservations;
    }
}

// Get recent orders
if (!function_exists('getRecentOrders')) {
    function getRecentOrders($connection, $limit = 5) {
        $sql = "SELECT o.*, b.name as branch_name 
                FROM orders o 
                LEFT JOIN branches b ON o.branch_id = b.id 
                ORDER BY o.created_at DESC 
                LIMIT $limit";
        $result = $connection->query($sql);
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        return $orders;
    }
}

// Update reservation status
if (!function_exists('updateReservationStatus')) {
    function updateReservationStatus($connection, $reservation_id, $status) {
        $sql = "UPDATE reservations SET status = ? WHERE id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("si", $status, $reservation_id);
        return $stmt->execute();
    }
}

// Update order status
if (!function_exists('updateOrderStatus')) {
    function updateOrderStatus($connection, $order_id, $status) {
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("si", $status, $order_id);
        return $stmt->execute();
    }
}

// Get monthly revenue
if (!function_exists('getMonthlyRevenue')) {
    function getMonthlyRevenue($connection, $year = null) {
        if (!$year) $year = date('Y');
        
        $sql = "SELECT 
                    MONTH(created_at) as month,
                    SUM(total_amount) as revenue
                FROM orders 
                WHERE YEAR(created_at) = ? 
                    AND payment_status = 'paid'
                GROUP BY MONTH(created_at)
                ORDER BY month";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $revenue = array_fill(1, 12, 0);
        while ($row = $result->fetch_assoc()) {
            $revenue[$row['month']] = $row['revenue'];
        }
        
        return $revenue;
    }
}

// Get popular menu items
if (!function_exists('getPopularMenuItems')) {
    function getPopularMenuItems($connection, $limit = 5) {

        $limit = (int)$limit; // safety

        $sql = "
            SELECT
                mi.name,
                mc.name AS category,
                mi.price,
                COUNT(oi.id) AS order_count,
                SUM(oi.quantity) AS total_quantity
            FROM order_items oi
            INNER JOIN menu_items mi 
                ON oi.menu_item_id = mi.id
            INNER JOIN menu_categories mc 
                ON mi.category_id = mc.id
            GROUP BY mi.id, mi.name, mc.name, mi.price
            ORDER BY total_quantity DESC
            LIMIT $limit
        ";

        $result = $connection->query($sql);

        $items = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }

        return $items;
    }
}

// Get today's reservations count by hour
if (!function_exists('getTodayReservationsByHour')) {
    function getTodayReservationsByHour($connection) {
        $today = date('Y-m-d');
        $sql = "SELECT 
                    HOUR(reservation_time) as hour,
                    COUNT(*) as count
                FROM reservations 
                WHERE reservation_date = ?
                GROUP BY HOUR(reservation_time)
                ORDER BY hour";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservations = array_fill(0, 24, 0);
        while ($row = $result->fetch_assoc()) {
            $reservations[$row['hour']] = $row['count'];
        }
        
        return $reservations;
    }
}
?>