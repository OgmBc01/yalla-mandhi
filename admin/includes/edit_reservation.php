<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$reservation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$customer_name = $customer_email = $customer_phone = '';
$reservation_date = $reservation_time = '';
$number_of_guests = '';
$branch_id = 1;
$special_requests = '';
$status = 'pending';
$message = '';
$message_type = '';

// Ensure $connection is set
if (!isset($connection) || !$connection) {
    if (function_exists('getDBConnection')) {
        $connection = getDBConnection();
    } else {
        require_once dirname(__DIR__, 2) . '/includes/database.php';
        $connection = getDBConnection();
    }
}

// Get branches for dropdown
$branches = [];
$branch_query = "SELECT id, name FROM branches ORDER BY name";
$branch_result = $connection->query($branch_query);
if ($branch_result) {
    while ($row = $branch_result->fetch_assoc()) {
        $branches[] = $row;
    }
}

// Fetch reservation data if editing existing reservation
if ($reservation_id > 0) {
    $sql = "SELECT * FROM reservations WHERE id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $reservation = $result->fetch_assoc();
        $customer_name = $reservation['customer_name'];
        $customer_email = $reservation['customer_email'];
        $customer_phone = $reservation['customer_phone'];
        $reservation_date = $reservation['reservation_date'];
        $reservation_time = $reservation['reservation_time'];
        $number_of_guests = $reservation['number_of_guests'];
        $branch_id = $reservation['branch_id'];
        $special_requests = $reservation['special_requests'];
        $status = $reservation['status'];
    } else {
        $message = "Reservation not found.";
        $message_type = "error";
        $reservation_id = 0;
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $reservation_id = intval($_POST['reservation_id']);
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    $reservation_date = trim($_POST['reservation_date']);
    $reservation_time = trim($_POST['reservation_time']);
    $number_of_guests = intval($_POST['number_of_guests']);
    $branch_id = intval($_POST['branch_id']);
    $special_requests = trim($_POST['special_requests'] ?? '');
    $status = trim($_POST['status']);

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
        // Update database
        $sql = "UPDATE reservations SET 
                customer_name = ?, 
                customer_email = ?, 
                customer_phone = ?, 
                reservation_date = ?, 
                reservation_time = ?, 
                number_of_guests = ?, 
                branch_id = ?, 
                special_requests = ?, 
                status = ? 
                WHERE id = ?";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("sssssiissi", $customer_name, $customer_email, $customer_phone,
                         $reservation_date, $reservation_time, $number_of_guests, 
                         $branch_id, $special_requests, $status, $reservation_id);

        if ($stmt->execute()) {
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
        } else {
            $message = "Failed to update reservation. Error: " . $connection->error;
            $message_type = "error";
            $stmt->close();
        }
    }
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><?php echo $reservation_id > 0 ? 'Edit Reservation' : 'Add Reservation'; ?></h1>
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
                            <input type="hidden" name="reservation_id" value="<?php echo $reservation_id; ?>">
                            
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
                                               value="<?php echo htmlspecialchars($reservation_date); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="reservation_time" class="form-label"><i class="bi bi-clock me-1"></i>Reservation Time *</label>
                                        <select id="reservation_time" name="reservation_time" class="form-select" required>
                                            <option value="">Select Time</option>
                                            <?php
                                            // Generate time slots from 10:00 AM to 10:00 PM
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
                                        <label for="status" class="form-label"><i class="bi bi-info-circle me-1"></i>Status *</label>
                                        <select id="status" name="status" class="form-select" required>
                                            <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="special_requests" class="form-label"><i class="bi bi-chat-text me-1"></i>Special Requests</label>
                                        <textarea id="special_requests" name="special_requests" class="form-control" 
                                                  rows="2"><?php echo htmlspecialchars($special_requests); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg me-2">
                                        <i class="bi bi-check-circle me-1"></i> <?php echo $reservation_id > 0 ? 'Update Reservation' : 'Create Reservation'; ?>
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
                <h4 class="my-3">Reservation Updated Successfully!</h4>
                <p>The reservation has been updated in the database.</p>
            </div>
            <div class="modal-footer">
                <a href="reservation.php" class="btn btn-secondary">View All Reservations</a>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Continue Editing</button>
            </div>
        </div>
    </div>
</div>