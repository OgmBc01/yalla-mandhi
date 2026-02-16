Recommended: Option 2 - Tier-Based Loyalty System
This is the most practical and provides good value. Here's what I'll build:

Features:
Tier Levels: Bronze, Silver, Gold, Platinum

Benefits per tier: Discount percentages, free items, priority service

Points System: Earn points on purchases

Reward Redemption: Convert points to discounts

Customer Dashboard: View tier, points, rewards

Admin Management: Configure tiers, assign rewards

Database Schema Additions
sql
-- Loyalty tiers table
CREATE TABLE loyalty_tiers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    min_points INT NOT NULL,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    benefits TEXT,
    color VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Loyalty rewards/redemption
CREATE TABLE loyalty_rewards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    points_required INT NOT NULL,
    reward_type ENUM('discount', 'free_item', 'free_shipping', 'cashback') DEFAULT 'discount',
    reward_value DECIMAL(10,2),
    is_active BOOLEAN DEFAULT TRUE,
    expiry_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Points transactions
CREATE TABLE loyalty_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    points INT NOT NULL,
    transaction_type ENUM('earned', 'redeemed', 'expired', 'adjusted') DEFAULT 'earned',
    reference_type ENUM('purchase', 'referral', 'birthday', 'manual', 'redemption') DEFAULT 'purchase',
    reference_id INT NULL,
    description TEXT,
    balance_after INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reward redemptions
CREATE TABLE reward_redemptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    reward_id INT NOT NULL,
    points_used INT NOT NULL,
    status ENUM('pending', 'approved', 'used', 'expired', 'cancelled') DEFAULT 'pending',
    redemption_code VARCHAR(50) UNIQUE,
    expires_at DATE NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reward_id) REFERENCES loyalty_rewards(id)
);

-- Referral tracking
CREATE TABLE referrals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    referrer_id INT NOT NULL,
    referred_id INT NOT NULL UNIQUE,
    status ENUM('pending', 'signed_up', 'first_purchase', 'completed') DEFAULT 'pending',
    referrer_points INT DEFAULT 0,
    referred_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referrer_id) REFERENCES users(id),
    FOREIGN KEY (referred_id) REFERENCES users(id)
);
Implementation Plan
Phase 1: Basic Setup
Loyalty tiers configuration

Points earning system

Simple customer loyalty dashboard

Phase 2: Rewards & Redemption
Create rewards catalog

Points redemption system

Reward management

Phase 3: Advanced Features
Referral program

Automated rewards

Analytics & reporting

Which part would you like me to start with?
Choose one to begin:

Admin Panel - Configure loyalty tiers and settings

Customer Dashboard - View points and tier status

Points Earning System - Add points on purchases

Rewards Catalog - Create redeemable rewards

Referral Program - Track and reward referrals



I want to implement a complete ordering module for our restaurant management system. The code arrangement should follow our current structure and coding standards, ensuring seamless integration with our existing systems. 
main page structure sample;

This module will allow customers to place orders online (from order form/page in public facing pages), and it will integrate with our existing inventory and kitchen management systems.

The module should allow users to place orders for pickup or delivery, and it should provide real-time updates on order status. Additionally, the module should support various payment methods, including credit cards, and cash on delivery.

The system should also allow admin staff in restaurant to punch orders directly into the system for in-person customers. The ordering module should be user-friendly and efficient, ensuring a smooth experience for both customers and staff.

Here’s a high-level overview of the features and components I plan to implement for the ordering module:

Features:
1. Order punching page (admin interface for staff to enter orders):
    - In this page, there shud be a noticeable button to be clicked to punch a new order. first the staff will select from a pop-up modal whether the order
     is for dine-in, pickup or delivery. Then they will enter the customer details (name, contact information, and address if it's a 
     delivery) and table number if dine-in. Also note that delivery can be our restaurant delivery, i.e., for customers who ordered through
     our website, whatsapp, or phone call; or delivery can also be orders received from online vendors like Noon Food, Keeta, Deliveroo or Smile.
    The system shud be able to auto-fill customer details if the customer is already in the database based on their contact information (through auto complete feature).
    - After that the Order punching page interface shud be structured to contain different parts. First on the left part shud be the categories bar where all 
    the food categories from our categories table will be shown. Then upon clicking a category, all the food items from our (menu_items) 
    database table shud be displayed. The categories menu bar shud stay open while the menu item in that category are on display right 
    next to it. 
    - Whenever a category is clicked, the menu items for that category shud be displayed and the staff can click on any of the food items to add it to the order.
    Upon clicking the food item, a pop up shud appear to enter the quantity and any special instructions for that item. All the  
    The order items and details shud be displayed on the right side of the page, where staff can review the order before submitting it.
    - Once the order is punched/submitted, it shud be saved in the orders table with all the relevant details , and the inventory should be updated 
    accordingly. The order status shud be set to "pending" and the kitchen staff shud receive a notification for the new order.
    - For all punched orders, the system shud allow staff to update the order status (e.g., from pending to in preparation, ready for pickup, 
    out for delivery, etc.) and the customers shud receive real-time updates on their order status through the customer dashboard or 
    notifications. Also at the top of the order punching page, there shud be a search bar to search for any existing orders by customer name, 
    contact information, or order ID. This will allow staff to quickly access and manage existing orders.
    - For all punched orders I need them to be shown and pills in the order punching page with different colors based on their status (e.g., 
    pending orders in yellow, in preparation in blue, ready for pickup in green, out for delivery in orange, etc.). This will allow staff to 
    easily identify and manage orders based on their status. The pills shud be clickable to open the order details and update the status or 
    make any necessary changes to the order. The pills shud also contain the basic information of the order such as customer name, order ID, 
    and order type (dine-in, pickup, delivery) for quick reference.
    - All orders placed by customers through the online ordering page (public facing) shud also be displayed in the order punching page for 
    staff to manage and update their status (these kinds of orders shud have some special noticeable indicator). This will ensure that all orders, whether placed in-person or online, are centralized in one 
    interface for efficient management by the staff. And when any user with Admin previledge logs in to the system, they shud be able to 
    see a dashboard with all the orders and their statuses, and they shud be able to click on any order to view its details and update its 
    status as needed. This will allow admin staff to have an overview of all orders and manage them effectively.
    - I also want to keep track of which admin staff punched each order, update any status or close the order, so that we can have a record 
    of who is responsible for each order and track any issues or discrepancies that may arise. This information shud be stored in the orders 
    table and displayed in the order details for reference.
    - I also want to keep track of the order history for each customer, so that staff can easily access past orders and their details when 
    needed. This will help in providing better customer service and understanding customer preferences.
    - The order punching page shud also be able to save all opened orders (once an item is unched in any specific order, without having to manually save) and maintain the record and details even if the staff logs out or 
    refreshes the page. This will ensure that no order details are lost and staff can continue managing orders seamlessly without any disruption. 
    
2. Online ordering page (customer interface for placing orders):
    - This page will be accessible from the restaurant's website and will allow customers to place orders for pickup or delivery (you need to create that). 
    The interface will be user-friendly and mobile-responsive, ensuring a smooth experience for customers on any device.
    - Customers will be able to browse the menu, select items, customize their orders (e.g., add special instructions), and proceed to checkout.
    - The checkout process will include options for payment methods (credit card, cash on delivery) and will require customers to enter 
    their contact information and delivery address if applicable.
    - Once the order is placed, customers will receive real-time updates on their order status through the customer dashboard or notifications.
    - Customers will also be able to view their order history and track the status of their current orders through the customer dashboard. 
    This will enhance the customer experience and provide transparency throughout the ordering process.

3. Closing Orders:
    - Once an order is completed (e.g., delivered or picked up), staff will be able to mark the order as closed in the system. This will 
    update the order status and allow for accurate record-keeping and reporting. Closed orders will also be archived for future reference 
    and analysis.
    - The staff shud be able to close each order by completing/indicating the payment method of the order, whether it's cash on delivery, credit card 
    payment, or debit (for online vendors like Noon Food, Keeta Deliveroo and Smile). For cash on delivery, the staff will confirm the 
    payment and mark the order as closed by cash. For credit card payments, the system will automatically process the payment and update the 
    order status to paid (and allow the admin to close it-in order punching pages) once the payment is confirmed as successful. This means there shud be an indicatore in the online order details
    that will show if order is paid, or if it will be paid on delibery. This will ensure that all orders are properly closed and accounted for in the system.

4. Reporting and Analytics:
    - The staff shud be able to generate reports on closed orders, including details such as order items, total amount, customer information, payment method and order history.
    - The system will also allow for generating reports on order history, sales, and customer preferences based on the data collected from 
    the orders. This will help in making informed business decisions and improving overall operations.


First of all, I want to accept all the things you recommended in step 8️⃣ SYSTEM IMPROVEMENTS (Professional Recommendations)



Secondly I want to also make it possible for the system to prepare a sales invoice receipt that can be sent to the kitchen printer or printed at the payment counter (for restaurant record or on customer request) via the small printer machine like (Terminal Machine) with below details;



- Printer Model: IRP-200D / POS-80C
- Paper Width 800mm
- Receipt: 72mm x 297mm
- Connection with system: USB



The sales receipt shud contain all necessary details like order number, invoice number, Date and time of order punching, delivery/location details (for delivery), but online vendors delivery like noon and the others must not have delivery details since the delivery persons must have received it from their own system. The receipt shud also contain the list of ordered items with their quantities and prices, total amount, payment method, and any special instructions for the kitchen. This will ensure that the kitchen receives all the necessary information to prepare the order accurately and efficiently, and it will also provide a record of the transaction for both the restaurant and the customer. 

When punching a new order and if the customer is new (not existing in our database) then add the customer in our users table and always set their role as customer.

When sending an order to kitchen via the printed receipt, the financial details like total amount and payment method shud not be included in the receipt sent to the kitchen, but they shud be included in the receipt printed at the payment counter. This will ensure that the kitchen receives only the relevant information needed for order preparation, while the payment counter has a complete record of the transaction for financial and record-keeping purposes. Both the counter receipt and kitchen receipt shud have the restaurant name on top (Yalla Al Mandi) but the kitchen receipt shud also contain the section "For Kitchen Use Only" to clearly indicate that it's meant for the kitchen staff and not for the customer. This will help in avoiding any confusion and ensure that the right information is provided to the right parties.

Then you need to add the inventory module as well to add stock and manage available ones. For each item unit price, total price, date of purchase, quantity shud all be captured.

Another class of inventory I want to maintain in the system is that of menu items that are prepared on daily bases, so I need you to update my menu items table to be able to record how many units of a particular menu item is available. The menu item shud be automatically decrementing whenever that item is pinched in an order. And closed (marked as unavailable) once the item count becomes zero

In sales modules, I want to include option of filtering and generating/viewing sales reports, which shud include filtering by dates, order type (delivery-in-person/vendor like noon food talabat, etc, takeaway, dine-in), filtering by payment method (cash, credit card-like on restaurant system payment gateway, debit-like vendors: noon food, talabat etc), and exporting report in various standard formats or directly printing them.

Note that I dont have customers table currently in DB, only have users table that is segregating users by role (customer, staff, and admin)

Now with the above amendments additions and explanation, update the structure, module wise (e.g., order management-aadd, edit, view, delete etc; inventory management, sales management), so that I can be able to copy and paste each modules description as a prompt into my code generating AI agent




Now I want to implement the Order Management module, then the Sales management and the, the Inventory Management Module.

I need them to follow the same structure and coding standards as my existing system, and to be seamlessly integrated with the current database and functionalities.

Here is an example of how my code structure looks like for for your reference:

- admin/
    orders.php
    inventory.php
    sales.php
    - includes/
        - database.php
        - functions.php
        - get_bulk_preview.php
    
        - add_order.php
        - edit_order.php
        - view_orders.php
        - delete_order.php
    
        - add_stock.php
        - edit_stock.php
        - view_inventory.php
        - delete_stock.php
    
        - view_sales_report.php
        - export_sales_report.php

For example the shifts management module is structured as follows:
admin/shifts.php;
<?php
session_start();
require_once "../includes/database.php";

// Check if user has permission
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager'])) {
    header("Location: login.php");
    exit();
}

include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";
?>

<div class="row">
    <div class="col-md-12">
      <?php
        if(isset($_GET['source'])) {
          $source = $_GET['source'];
        } else {
          $source = '';
        }

        switch($source) {
          case 'add_shift':
              include "includes/add_shift.php";
              break;
              
          case 'edit_shift':
              include "includes/edit_shift.php";
              break;
              
          case 'view_shift':
              include "includes/view_shift_details.php";
              break;
              
          case 'bulk_assign':
              include "includes/bulk_assign_shifts.php";
              break;
              
          case 'calendar_view':
              include "includes/shift_calendar.php";
              break;
              
          case 'attendance':
              include "includes/attendance.php";
              break;
              
          case 'mark_attendance':
              include "includes/mark_attendance.php";
              break;
              
          case 'view_attendance':
              include "includes/view_attendance_details.php";
              break;
              
          default:
              include "includes/view_all_shifts.php";
              break;
        }
      ?>
    </div>
  </div>  
</div>

</body></br>
</html>

<?php
include "includes/footer.php";
?>

admin/includes/add_reservation.ph;
<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$customer_name = $customer_email = $customer_phone = '';
$reservation_date = $reservation_time = '';
$number_of_guests = '';
$branch_id = 1;
$special_requests = '';
$message = '';
$message_type = '';


// Get branches for dropdown
$branches = [];
$branch_query = "SELECT id, name FROM branches ORDER BY name";
$branch_result = $connection->query($branch_query);
if ($branch_result) {
    while ($row = $branch_result->fetch_assoc()) {
        $branches[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    $reservation_date = trim($_POST['reservation_date']);
    $reservation_time = trim($_POST['reservation_time']);
    $number_of_guests = intval($_POST['number_of_guests']);
    $branch_id = isset($_POST['branch_id']) ? intval($_POST['branch_id']) : 1;
    $special_requests = trim($_POST['special_requests'] ?? '');
    $status = 'pending';

    // Validate required fields
    if (empty($customer_name) || empty($customer_email) || empty($customer_phone) || 
        empty($reservation_date) || empty($reservation_time) || empty($number_of_guests)) {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";
    } elseif ($number_of_guests <= 0) {
        $message = "Number of guests must be greater than 0.";
        $message_type = "error";
    } else {
        // Check if date/time is in the future
        $reservation_datetime = $reservation_date . ' ' . $reservation_time;
        if (strtotime($reservation_datetime) <= time()) {
            $message = "Reservation date and time must be in the future.";
            $message_type = "error";
        } else {
            // Insert into database
            $sql = "INSERT INTO reservations (customer_name, customer_email, customer_phone, 
                    reservation_date, reservation_time, number_of_guests, branch_id, 
                    special_requests, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("sssssiiss", $customer_name, $customer_email, $customer_phone,
                             $reservation_date, $reservation_time, $number_of_guests, 
                             $branch_id, $special_requests, $status);

            if ($stmt->execute()) {
                $new_reservation_id = $stmt->insert_id;
                $stmt->close();
                
                // Show success modal
                echo "
                <script>
                    window.addEventListener('load', function() {
                        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();
                    });
                </script>
                ";
                
                // Clear form after successful submission
                $customer_name = $customer_email = $customer_phone = '';
                $reservation_date = $reservation_time = '';
                $number_of_guests = '';
                $special_requests = '';
            } else {
                $message = "Failed to add reservation. Error: " . $connection->error;
                $message_type = "error";
                $stmt->close();
            }
        }
    }
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Add New Reservation</h1>
            <a href="reservation.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Reservations
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Reservation Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <!-- Left Column - Customer Information -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="customer_name" class="form-label"><i class="bi bi-person me-1"></i>Customer Name *</label>
                                        <input type="text" id="customer_name" name="customer_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($customer_name); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="customer_email" class="form-label"><i class="bi bi-envelope me-1"></i>Email *</label>
                                        <input type="email" id="customer_email" name="customer_email" class="form-control" 
                                               value="<?php echo htmlspecialchars($customer_email); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="customer_phone" class="form-label"><i class="bi bi-phone me-1"></i>Phone *</label>
                                        <input type="tel" id="customer_phone" name="customer_phone" class="form-control" 
                                               value="<?php echo htmlspecialchars($customer_phone); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="number_of_guests" class="form-label"><i class="bi bi-people me-1"></i>Number of Guests *</label>
                                        <input type="number" id="number_of_guests" name="number_of_guests" class="form-control" 
                                               value="<?php echo htmlspecialchars($number_of_guests); ?>" min="1" max="50" required>
                                        <div class="form-text">Maximum 50 guests per reservation</div>
                                    </div>
                                </div>

                                <!-- Right Column - Reservation Details -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="branch_id" class="form-label"><i class="bi bi-shop me-1"></i>Branch *</label>
                                        <select id="branch_id" name="branch_id" class="form-select" required>
                                            <option value="">Select Branch</option>
                                            <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>" 
                                                <?php echo $branch_id == $branch['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($branch['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="reservation_date" class="form-label"><i class="bi bi-calendar-date me-1"></i>Reservation Date *</label>
                                        <input type="date" id="reservation_date" name="reservation_date" class="form-control" 
                                               value="<?php echo htmlspecialchars($reservation_date); ?>" 
                                               min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="reservation_time" class="form-label"><i class="bi bi-clock me-1"></i>Reservation Time *</label>
                                        <select id="reservation_time" name="reservation_time" class="form-select" required>
                                            <option value="">Select Time</option>
                                            <?php
                                            // Generate time slots from 10:00 AM to 10:00 PM in 30-minute intervals
                                            $start_time = strtotime('10:00');
                                            $end_time = strtotime('22:00');
                                            for ($time = $start_time; $time <= $end_time; $time += 1800) {
                                                $time_value = date('H:i', $time);
                                                $time_display = date('h:i A', $time);
                                                $selected = ($time_value == $reservation_time) ? 'selected' : '';
                                                echo "<option value=\"$time_value\" $selected>$time_display</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="special_requests" class="form-label"><i class="bi bi-chat-text me-1"></i>Special Requests</label>
                                        <textarea id="special_requests" name="special_requests" class="form-control" 
                                                  rows="3"><?php echo htmlspecialchars($special_requests); ?></textarea>
                                        <div class="form-text">Allergies, special occasions, seating preferences, etc.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg me-2">
                                        <i class="bi bi-check-circle me-1"></i> Create Reservation
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-x-circle me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <h4 class="my-3">Reservation Created Successfully!</h4>
                <p>The reservation has been added and is now pending confirmation.</p>
            </div>
            <div class="modal-footer">
                <a href="reservation.php" class="btn btn-secondary">View All Reservations</a>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Add Another Reservation</button>
            </div>
        </div>
    </div>
</div>

admin/includes/view_all_shifts.php;
<?php
// Get date filter
$filter_date = $_GET['date'] ?? date('Y-m-d');
$filter_employee = $_GET['employee'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Build query
$query = "SELECT s.*, u.full_name, u.employee_id as emp_code, u.position, u.department 
          FROM shifts s 
          JOIN users u ON s.employee_id = u.id 
          WHERE 1=1";
$params = [];
$types = "";

if (!empty($filter_date)) {
    $query .= " AND s.shift_date = ?";
    $params[] = $filter_date;
    $types .= "s";
}

if (!empty($filter_employee) && is_numeric($filter_employee)) {
    $query .= " AND s.employee_id = ?";
    $params[] = intval($filter_employee);
    $types .= "i";
}

if ($filter_status === 'active') {
    $query .= " AND s.is_active = 1";
} elseif ($filter_status === 'inactive') {
    $query .= " AND s.is_active = 0";
}

$query .= " ORDER BY s.shift_date DESC, s.start_time";

// Prepare and execute query
$stmt = $connection->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_shifts,
    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_shifts,
    COUNT(CASE WHEN shift_date = CURDATE() THEN 1 END) as today_shifts,
    COUNT(DISTINCT employee_id) as employees_scheduled
    FROM shifts";
$stats_result = $connection->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get all employees for filter
$employees_query = "SELECT id, full_name FROM users WHERE role IN ('employee', 'admin') AND is_active = 1 ORDER BY full_name";
$employees_result = $connection->query($employees_query);
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Shift Schedule Management</h1>
            <div>
                <a href="shifts.php?source=calendar_view" class="btn btn-info me-2">
                    <i class="bi bi-calendar-week"></i> Calendar View
                </a>
                <a href="shifts.php?source=add_shift" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> Add Shift
                </a>
                <a href="shifts.php?source=bulk_assign" class="btn btn-warning">
                    <i class="bi bi-people-fill"></i> Bulk Assign
                </a>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Shifts</h6>
                                <h2 class="mb-0"><?php echo $stats['total_shifts'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-clock-history display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Active</h6>
                                <h2 class="mb-0"><?php echo $stats['active_shifts'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-check-circle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Today's Shifts</h6>
                                <h2 class="mb-0"><?php echo $stats['today_shifts'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-calendar-day display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Employees Scheduled</h6>
                                <h2 class="mb-0"><?php echo $stats['employees_scheduled'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-people display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="source" value="view_all_shifts">
                    
                    <div class="col-md-3">
                        <label for="filter_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="filter_date" name="date" 
                               value="<?php echo htmlspecialchars($filter_date); ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filter_employee" class="form-label">Employee</label>
                        <select class="form-select" id="filter_employee" name="employee">
                            <option value="">All Employees</option>
                            <?php if ($employees_result && $employees_result->num_rows > 0): ?>
                                <?php while ($emp = $employees_result->fetch_assoc()): ?>
                                    <option value="<?php echo $emp['id']; ?>" 
                                            <?php echo $filter_employee == $emp['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($emp['full_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filter_status" class="form-label">Status</label>
                        <select class="form-select" id="filter_status" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $filter_status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                            <a href="shifts.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Shifts Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Shift Schedule</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="shiftsTable">
                        <thead>
                            <tr class="table-dark">
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Shift Type</th>
                                <th>Time</th>
                                <th>Duration</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th width="120" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($shift = $result->fetch_assoc()): ?>
                                    <?php
                                    // Calculate duration
                                    $start = new DateTime($shift['start_time']);
                                    $end = new DateTime($shift['end_time']);
                                    if ($end <= $start) {
                                        $end->modify('+1 day'); // For night shifts
                                    }
                                    $duration = $start->diff($end);
                                    $duration_str = $duration->format('%h hr %i min');
                                    
                                    // Shift type badge
                                    $type_badges = [
                                        'morning' => 'bg-info',
                                        'afternoon' => 'bg-warning',
                                        'evening' => 'bg-primary',
                                        'night' => 'bg-dark'
                                    ];
                                    $type_badge = $type_badges[$shift['shift_type']] ?? 'bg-secondary';
                                    ?>
                                    
                                    <tr id="shift-row-<?php echo $shift['id']; ?>">
                                        <td>
                                            <strong><?php echo date('D, M d, Y', strtotime($shift['shift_date'])); ?></strong>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($shift['full_name']); ?></div>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($shift['position']); ?> • 
                                                <?php echo htmlspecialchars($shift['department']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $type_badge; ?> text-uppercase">
                                                <?php echo htmlspecialchars($shift['shift_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="bi bi-clock me-1"></i>
                                            <?php echo date('g:i A', strtotime($shift['start_time'])); ?> - 
                                            <?php echo date('g:i A', strtotime($shift['end_time'])); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark"><?php echo $duration_str; ?></span>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($shift['location']); ?>
                                        </td>
                                        <td>
                                            <?php if ($shift['is_active']): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-x-circle"></i> Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="shifts.php?source=view_shift&id=<?php echo $shift['id']; ?>" 
                                                   class="btn btn-outline-info" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                              
                                                <a href="shifts.php?source=edit_shift&id=<?php echo $shift['id']; ?>"
                                                   class="btn btn-outline-warning" title="Edit Shift">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-outline-danger delete-shift-btn" 
                                                        onclick="showDeleteConfirm(<?php echo $shift['id']; ?>,
                                                        '<?php echo htmlspecialchars(addslashes($shift['full_name']), ENT_QUOTES); ?>',
                                                        '<?php echo date('M d, Y', strtotime($shift['shift_date'])); ?>')"
                                                        title="Delete Shift">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-clock display-4 d-block mb-2"></i>
                                            <h5>No shifts found</h5>
                                            <p><?php echo !empty($filter_date) ? "No shifts scheduled for " . date('F d, Y', strtotime($filter_date)) : "No shifts scheduled"; ?></p>
                                            <a href="shifts.php?source=add_shift" class="btn btn-primary mt-2">
                                                <i class="bi bi-plus-circle"></i> Add New Shift
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Shift Modal -->
<div class="modal fade" id="deleteShiftConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete Shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the shift for <strong id="deleteShiftEmployee"></strong> on <strong id="deleteShiftDate"></strong>?
                <div class="alert alert-danger mt-2">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteShiftBtn" 
                        onclick="deleteShift()">
                    Delete Shift
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="shiftSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="shiftToastMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="shiftErrorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="shiftErrorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
let deleteShiftId = null;
let deleteModalInstance = null;

function showDeleteConfirm(shiftId, employeeName, shiftDate) {
    deleteShiftId = shiftId;
    
    document.getElementById('deleteShiftEmployee').textContent = employeeName;
    document.getElementById('deleteShiftDate').textContent = shiftDate;
    
    if (!deleteModalInstance) {
        deleteModalInstance = new bootstrap.Modal(
            document.getElementById('deleteShiftConfirmModal')
        );
    }
    
    deleteModalInstance.show();
}

function deleteShift() {
    if (!deleteShiftId) {
        showError('No shift selected');
        return;
    }

    const btn = document.getElementById('confirmDeleteShiftBtn');
    const originalHTML = btn.innerHTML;

    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    btn.disabled = true;

    fetch('includes/delete_shift.php?id=' + deleteShiftId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => {
            if (!res.ok) {
                throw new Error('Network response was not ok');
            }
            return res.json();
        })
        .then(data => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;

            if (!data.success) {
                showError(data.message || 'Delete failed');
                return;
            }

            deleteModalInstance.hide();

            const row = document.getElementById('shift-row-' + deleteShiftId);
            if (row) {
                if ($.fn.DataTable.isDataTable('#shiftsTable')) {
                    $('#shiftsTable').DataTable().row(row).remove().draw();
                } else {
                    row.remove();
                }
            }

            showSuccess(data.message || 'Shift deleted successfully');
            deleteShiftId = null;
        })
        .catch((error) => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            console.error('Delete error:', error);
            showError('Server error while deleting: ' + error.message);
        });
}

function showSuccess(msg) {
    document.getElementById('shiftToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('shiftSuccessToast')).show();
}

function showError(msg) {
    document.getElementById('shiftErrorToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('shiftErrorToast')).show();
}

// Initialize DataTable
document.addEventListener('DOMContentLoaded', () => {
    if ($.fn.DataTable.isDataTable('#shiftsTable')) {
        $('#shiftsTable').DataTable().destroy();
    }

    $('#shiftsTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [7] }],
        responsive: true,
        language: {
            search: "Search shifts:",
            lengthMenu: "Show _MENU_ shifts per page"
        }
    });
});
</script>

admin/includes/edit_shifts.php;
<?php
// Get shift ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: shifts.php");
    exit();
}

$shift_id = (int)$_GET['id'];

// Fetch shift data
$stmt = $connection->prepare("SELECT s.*, u.full_name FROM shifts s JOIN users u ON s.employee_id = u.id WHERE s.id = ?");
$stmt->bind_param("i", $shift_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: shifts.php");
    exit();
}

$shift = $result->fetch_assoc();
$stmt->close();

// Get all active employees
$employees_query = "SELECT id, full_name, employee_id, department, position 
                   FROM users 
                   WHERE role IN ('employee', 'admin') AND is_active = 1 
                   ORDER BY full_name";
$employees_result = $connection->query($employees_query);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    // Validate inputs
    $employee_id = intval($_POST['employee_id'] ?? 0);
    $shift_date = trim($_POST['shift_date'] ?? '');
    $shift_type = trim($_POST['shift_type'] ?? 'morning');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $location = trim($_POST['location'] ?? 'Main Restaurant');
    $notes = trim($_POST['notes'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    if ($employee_id <= 0) {
        $errors[] = "Please select an employee";
    }
    
    if (empty($shift_date)) {
        $errors[] = "Shift date is required";
    }
    
    if (empty($start_time) || empty($end_time)) {
        $errors[] = "Start time and end time are required";
    }
    
    if (strtotime($end_time) <= strtotime($start_time)) {
        $errors[] = "End time must be after start time";
    }
    
    // Check if employee already has a shift on this date (excluding current shift)
    if (empty($errors)) {
        $check_shift = $connection->prepare(
            "SELECT id FROM shifts WHERE employee_id = ? AND shift_date = ? AND id != ?"
        );
        $check_shift->bind_param("isi", $employee_id, $shift_date, $shift_id);
        $check_shift->execute();
        $check_shift->store_result();
        
        if ($check_shift->num_rows > 0) {
            $errors[] = "This employee already has a shift assigned on this date";
        }
        $check_shift->close();
    }

    if (empty($errors)) {
        // Update shift
        $stmt = $connection->prepare(
            "UPDATE shifts SET 
                employee_id = ?, 
                shift_date = ?, 
                shift_type = ?, 
                start_time = ?, 
                end_time = ?,
                location = ?,
                notes = ?,
                is_active = ?,
                updated_at = NOW()
             WHERE id = ?"
        );
        
        $stmt->bind_param("issssssii", 
            $employee_id, $shift_date, $shift_type, $start_time, $end_time,
            $location, $notes, $is_active, $shift_id
        );
        
        if ($stmt->execute()) {
            // Update $shift array for form re-population
            $shift['employee_id'] = $employee_id;
            $shift['shift_date'] = $shift_date;
            $shift['shift_type'] = $shift_type;
            $shift['start_time'] = $start_time;
            $shift['end_time'] = $end_time;
            $shift['location'] = $location;
            $shift['notes'] = $notes;
            $shift['is_active'] = $is_active;
            
            // Show success modal
            $show_success_modal = true;
        } else {
            $errors[] = "Failed to update shift: " . $connection->error;
        }
        $stmt->close();
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Edit Shift</h1>
            <a href="shifts.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Shifts
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Edit Shift Information</h5>
            </div>
            <div class="card-body">

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (isset($show_success_modal) && $show_success_modal): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>Shift updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Employee *</label>
                                <select class="form-select" id="employee_id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    <?php if ($employees_result && $employees_result->num_rows > 0): ?>
                                        <?php while ($emp = $employees_result->fetch_assoc()): ?>
                                            <option value="<?php echo $emp['id']; ?>" 
                                                    <?php echo $shift['employee_id'] == $emp['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($emp['full_name']); ?>
                                                <?php if ($emp['employee_id']): ?>
                                                    (ID: <?php echo htmlspecialchars($emp['employee_id']); ?>)
                                                <?php endif; ?>
                                                - <?php echo htmlspecialchars($emp['position']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift_date" class="form-label">Shift Date *</label>
                                <input type="date" class="form-control" id="shift_date" name="shift_date" 
                                       value="<?php echo htmlspecialchars($shift['shift_date']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="shift_type" class="form-label">Shift Type *</label>
                                <select class="form-select" id="shift_type" name="shift_type" required>
                                    <option value="morning" <?php echo $shift['shift_type'] == 'morning' ? 'selected' : ''; ?>>Morning Shift (6 AM - 2 PM)</option>
                                    <option value="afternoon" <?php echo $shift['shift_type'] == 'afternoon' ? 'selected' : ''; ?>>Afternoon Shift (2 PM - 10 PM)</option>
                                    <option value="evening" <?php echo $shift['shift_type'] == 'evening' ? 'selected' : ''; ?>>Evening Shift (4 PM - 12 AM)</option>
                                    <option value="night" <?php echo $shift['shift_type'] == 'night' ? 'selected' : ''; ?>>Night Shift (10 PM - 6 AM)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time *</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" 
                                       value="<?php echo htmlspecialchars($shift['start_time']); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time *</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" 
                                       value="<?php echo htmlspecialchars($shift['end_time']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <select class="form-select" id="location" name="location">
                                    <option value="Main Restaurant" <?php echo $shift['location'] == 'Main Restaurant' ? 'selected' : ''; ?>>Main Restaurant</option>
                                    <option value="Takeaway Counter" <?php echo $shift['location'] == 'Takeaway Counter' ? 'selected' : ''; ?>>Takeaway Counter</option>
                                    <option value="Kitchen" <?php echo $shift['location'] == 'Kitchen' ? 'selected' : ''; ?>>Kitchen</option>
                                    <option value="Delivery" <?php echo $shift['location'] == 'Delivery' ? 'selected' : ''; ?>>Delivery</option>
                                    <option value="Cashier" <?php echo $shift['location'] == 'Cashier' ? 'selected' : ''; ?>>Cashier</option>
                                    <option value="Other" <?php echo $shift['location'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           <?php echo $shift['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active Shift
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes / Special Instructions</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($shift['notes'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="shifts.php?source=view_shift&id=<?php echo $shift_id; ?>" 
                               class="btn btn-outline-info">
                                <i class="bi bi-eye me-2"></i>View Details
                            </a>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="shifts.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Update Shift
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <h4 class="my-3">Shift Updated Successfully!</h4>
                <p>The shift information has been updated.</p>
            </div>
            <div class="modal-footer">
                <a href="shifts.php" class="btn btn-secondary">View All Shifts</a>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Continue Editing</button>
            </div>
        </div>
    </div>
</div>

<?php if (isset($show_success_modal) && $show_success_modal): ?>
<script>
    window.addEventListener('load', function() {
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    });
</script>
<?php endif; ?>

<script>
// Auto-update times based on shift type
document.getElementById('shift_type').addEventListener('change', function() {
    const shiftType = this.value;
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');
    
    switch(shiftType) {
        case 'morning':
            startTime.value = '06:00';
            endTime.value = '14:00';
            break;
        case 'afternoon':
            startTime.value = '14:00';
            endTime.value = '22:00';
            break;
        case 'evening':
            startTime.value = '16:00';
            endTime.value = '00:00';
            break;
        case 'night':
            startTime.value = '22:00';
            endTime.value = '06:00';
            break;
    }
});
</script>

api/json endpoints examples are;
inclueds/get_bulk_attendance_data.php;
<?php
session_start();
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate date
$date = $_GET['date'] ?? date('Y-m-d');

// Fetch shifts for the date
$stmt = $connection->prepare(
    "SELECT s.id, u.full_name, u.position, a.status as attendance_status
     FROM shifts s 
     JOIN users u ON s.employee_id = u.id 
     LEFT JOIN attendance a ON s.id = a.shift_id AND a.attendance_date = s.shift_date
     WHERE s.shift_date = ? AND s.is_active = 1
     ORDER BY u.full_name"
);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$shifts = [];
$current_time = date('Y-m-d\TH:i', round(time() / 300) * 300);

while ($row = $result->fetch_assoc()) {
    $shifts[] = [
        'id' => $row['id'],
        'full_name' => $row['full_name'],
        'position' => $row['position'],
        'attendance_status' => $row['attendance_status'] ?? 'absent',
        'current_time' => $current_time
    ];
}

$stmt->close();

echo json_encode([
    'success' => true,
    'shifts' => $shifts,
    'date' => $date,
    'count' => count($shifts)
]);
?>

includes/delete_shift.php;
<?php
session_start();
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate ID from POST or GET
$shift_id = null;
if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $shift_id = (int)$_POST['id'];
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $shift_id = (int)$_GET['id'];
}
if (!$shift_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid shift ID']);
    exit;
}

// Delete shift
$stmt = $connection->prepare("DELETE FROM shifts WHERE id = ?");
$stmt->bind_param("i", $shift_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Shift deleted successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete shift'
    ]);
}

$stmt->close();
?>

root css;
:root {
  /* Color Palette */
  --color-red: #c41e3a;
  --color-red-light: #e84c4c;
  --color-beige: #f5f1e8;
  --color-sand: #e6dfd3;
  --color-dark-brown: #2c2416;
  --color-soft-black: #1a1a1a;
  --color-olive: #8a8635;
  --color-copper: #b87333;
  --color-white: #ffffff;
  --color-light-gray: #f8f9fa;}

I need to follow this same structure and styling to implement the full order management, inventory management and sales management modules. Each module should have its own set of pages for listing, viewing, adding, editing, and deleting records, as well as unique API endpoints for handling AJAX requests. All IDs, classes, functions, and methods shud be unique and peculiar to the Module

The design should be consistent across all modules, using the same color palette, typography, and UI components to ensure a cohesive user experience. 

There will be need to make some adjustments to some of the existing DB tables, so I will give you the description of how they currently are, and abse on the new description and features of the modules, I need you to update my databases accordingly, ensuring that all necessary relationships and constraints are properly defined.

menu_items table:
DESCRIBE menu_items;
[ Edit inline ] [ Edit ] [ Create PHP code ]
Field
Type
Null
Key
Default
Extra
id
int
NO
PRI
NULL
auto_increment
name
varchar(100)
NO
NULL
description
text
YES
NULL
category_id
int
NO
NULL
price
decimal(10,2)
NO
NULL
image_url
varchar(255)
YES
NULL
is_available
tinyint(1)
YES
1
is_featured
tinyint(1)
YES
0
created_at
timestamp
NO
CURRENT_TIMESTAMP
DEFAULT_GENERATED
updated_at
timestamp
NO
CURRENT_TIMESTAMP
DEFAULT_GENERATED on update CURRENT_TIMESTAMP

menu_categories table:
Field
Type
Null
Key
Default
Extra
id
int
NO
PRI
NULL
auto_increment
name
varchar(50)
NO
UNI
NULL
description
text
YES
NULL
is_active
tinyint(1)
YES
1
sort_order
int
YES
0
created_at
timestamp
NO
CURRENT_TIMESTAMP
DEFAULT_GENERATED
updated_at
timestamp
NO
CURRENT_TIMESTAMP
DEFAULT_GENERATED on update CURRENT_TIMESTAMP

users table:
Field
Type
Null
Key
Default
Extra
id
int
NO
PRI
NULL
auto_increment
username
varchar(50)
NO
UNI
NULL
employee_id
varchar(50)
YES
NULL
email
varchar(100)
NO
UNI
NULL
password_hash
varchar(255)
NO
NULL
full_name
varchar(100)
YES
NULL
phone
varchar(20)
YES
NULL
role
enum('customer','admin','super-admin','employee')
YES
MUL
customer
is_active
tinyint(1)
YES
MUL
1
created_at
timestamp
NO
CURRENT_TIMESTAMP
DEFAULT_GENERATED
updated_at
timestamp
NO
CURRENT_TIMESTAMP
DEFAULT_GENERATED on update CURRENT_TIMESTAMP
last_login
timestamp
YES
NULL
reset_token
varchar(255)
YES
NULL
reset_token_expiry
timestamp
YES
NULL
address
text
YES
NULL
preferred_branch
int
YES
MUL
1
loyalty_points
int
YES
0
last_order_date
date
YES
NULL
position
varchar(100)
YES
NULL
department
varchar(100)
YES
NULL
salary
decimal(10,2)
YES
0.00
hire_date
date
YES
NULL

orders table:
Field
Type
Null
Key
Default
Extra
id
int
NO
PRI
NULL
auto_increment
order_number
varchar(20)
NO
UNI
NULL
customer_id
int
YES
MUL
NULL
customer_name
varchar(100)
NO
NULL
customer_email
varchar(100)
YES
NULL
customer_phone
varchar(20)
NO
NULL
customer_address
text
YES
NULL
order_type
enum('delivery','pickup','dine_in')
NO
NULL
branch_id
int
YES
MUL
1
total_amount
decimal(10,2)
NO
NULL
status
enum('pending','confirmed','preparing','ready','delivered','cancelled')
YES
pending
payment_method
enum('cash','card','online')
YES
cash
payment_status
enum('pending','paid','failed')
YES
pending
special_instructions
text
YES
NULL
created_at
timestamp
NO
CURRENT_TIMESTAMP
DEFAULT_GENERATED
updated_at
timestamp
NO
CURRENT_TIMESTAMP
DEFAULT_GENERATED on update CURRENT_TIMESTAMP

order_items table:

Field
Type
Null
Key
Default
Extra
id
int
NO
PRI
NULL
auto_increment
order_id
int
NO
MUL
NULL
menu_item_id
int
YES
MUL
NULL
menu_item_name
varchar(100)
NO
NULL
quantity
int
NO
NULL
unit_price
decimal(10,2)
NO
NULL
total_price
decimal(10,2)
NO
NULL
special_instructions
text
YES
NULL

After you study the code and DB tables, I will be giving you the workflow, description and features we need to implement in the above mentioned new modules
