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