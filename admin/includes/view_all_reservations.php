<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
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

// Fetch all reservations with branch name
$sql = "SELECT r.*, b.name 
        FROM reservations r 
        LEFT JOIN branches b ON r.branch_id = b.id 
        ORDER BY r.reservation_date DESC, r.reservation_time DESC";
$result = $connection->query($sql);

// Check for errors
if (!$result) {
    die("Query failed: " . $connection->error);
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Reservation Management</h1>
            <div>
                <a href="reservation.php?source=add_reservation" class="btn btn-primary me-2">
                    <i class="bi bi-calendar-plus"></i> Add New Reservation
                </a>
                <a href="reservation.php?source=view_pending" class="btn btn-warning">
                    <i class="bi bi-clock-history"></i> View Pending
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
                                <h6 class="card-title">Total Reservations</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $count_query = "SELECT COUNT(*) as total FROM reservations";
                                    $count_result = $connection->query($count_query);
                                    echo $count_result ? $count_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-calendar-check display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Pending</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $pending_query = "SELECT COUNT(*) as total FROM reservations WHERE status = 'pending'";
                                    $pending_result = $connection->query($pending_query);
                                    echo $pending_result ? $pending_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-clock display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Confirmed</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $confirmed_query = "SELECT COUNT(*) as total FROM reservations WHERE status = 'confirmed'";
                                    $confirmed_result = $connection->query($confirmed_query);
                                    echo $confirmed_result ? $confirmed_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-check-circle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Cancelled</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $cancelled_query = "SELECT COUNT(*) as total FROM reservations WHERE status = 'cancelled'";
                                    $cancelled_result = $connection->query($cancelled_query);
                                    echo $cancelled_result ? $cancelled_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-x-circle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservations Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>All Reservations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="reservationsTable">
                        <thead>
                            <tr class="table-dark">
                                <th width="50">ID</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Date & Time</th>
                                <th>Guests</th>
                                <th>Branch</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($reservation = $result->fetch_assoc()): ?>
                                    <tr id="reservation-row-<?php echo $reservation['id']; ?>">
                                        <td class="fw-bold">#<?php echo $reservation['id']; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($reservation['customer_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($reservation['customer_email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($reservation['customer_phone']); ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></div>
                                            <small class="text-muted"><?php echo date('h:i A', strtotime($reservation['reservation_time'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                                <?php echo $reservation['number_of_guests']; ?> Guests
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($reservation['name'] ?? 'Main'); ?></td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            switch($reservation['status']) {
                                                case 'pending': $status_class = 'status-pending'; break;
                                                case 'confirmed': $status_class = 'status-confirmed'; break;
                                                case 'cancelled': $status_class = 'status-cancelled'; break;
                                                case 'completed': $status_class = 'status-completed'; break;
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <?php echo ucfirst($reservation['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($reservation['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary view-reservation-btn" 
                                                        onclick="viewReservation(<?php echo $reservation['id']; ?>)" 
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                              
                                                <a href='reservation.php?source=edit_reservation&id=<?php echo $reservation['id']; ?>'
                                                   class="btn btn-outline-warning"
                                                   title="Edit Reservation">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-outline-danger delete-reservation-btn" 
                                                        onclick="showDeleteConfirmation(<?php echo $reservation['id']; ?>, '<?php echo htmlspecialchars(addslashes($reservation['customer_name'] . ' - ' . date('M d, Y', strtotime($reservation['reservation_date'])))); ?>')"
                                                        title="Delete Reservation">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-calendar-x display-4 d-block mb-2"></i>
                                            <h5>No reservations found</h5>
                                            <p>Get started by creating your first reservation.</p>
                                            <a href="reservation.php?source=add_reservation" class="btn btn-primary mt-2">
                                                <i class="bi bi-calendar-plus"></i> Add Reservation
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


<!-- View Reservation Modal (Redesigned) -->
<div class="modal fade" id="viewReservationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content reservation-modal-theme">
            <div class="modal-header reservation-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-event display-6 text-theme"></i>
                    <h5 class="modal-title mb-0">Reservation Details</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body reservation-modal-body" id="reservationDetails">
                <!-- Reservation details will be loaded here via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-theme" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer reservation-modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editReservationBtn" class="btn btn-theme">Edit Reservation</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete reservation <strong id="deleteReservationInfo"></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="deleteReservation()">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="toastMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="errorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>