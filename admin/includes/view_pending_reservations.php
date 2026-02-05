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

// Fetch pending reservations
$sql = "SELECT r.*, b.name 
        FROM reservations r 
        LEFT JOIN branches b ON r.branch_id = b.id 
        WHERE r.status = 'pending' 
        ORDER BY r.reservation_date ASC, r.reservation_time ASC";
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
            <h1 class="page-title">Pending Reservations</h1>
            <div>
                <a href="reservation.php" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Back to All Reservations
                </a>
                <a href="reservation.php?source=add_reservation" class="btn btn-primary">
                    <i class="bi bi-calendar-plus"></i> Add New
                </a>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Pending Today</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $today = date('Y-m-d');
                                    $today_query = "SELECT COUNT(*) as total FROM reservations 
                                                   WHERE status = 'pending' AND reservation_date = '$today'";
                                    $today_result = $connection->query($today_query);
                                    echo $today_result ? $today_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-calendar-day display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Pending This Week</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $week_start = date('Y-m-d', strtotime('monday this week'));
                                    $week_end = date('Y-m-d', strtotime('sunday this week'));
                                    $week_query = "SELECT COUNT(*) as total FROM reservations 
                                                  WHERE status = 'pending' 
                                                  AND reservation_date BETWEEN '$week_start' AND '$week_end'";
                                    $week_result = $connection->query($week_query);
                                    echo $week_result ? $week_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-calendar-week display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Overdue Actions</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $yesterday = date('Y-m-d', strtotime('-1 day'));
                                    $overdue_query = "SELECT COUNT(*) as total FROM reservations 
                                                     WHERE status = 'pending' AND reservation_date < '$yesterday'";
                                    $overdue_result = $connection->query($overdue_query);
                                    echo $overdue_result ? $overdue_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-exclamation-triangle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Reservations Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Reservations Awaiting Confirmation</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="pendingReservationsTable">
                        <thead>
                            <tr class="table-warning">
                                <th width="50">ID</th>
                                <th>Customer</th>
                                <th>Date & Time</th>
                                <th>Guests</th>
                                <th>Branch</th>
                                <th>Special Requests</th>
                                <th>Created</th>
                                <th width="180" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($reservation = $result->fetch_assoc()): 
                                    // Check if reservation is today or past
                                    $reservation_datetime = $reservation['reservation_date'] . ' ' . $reservation['reservation_time'];
                                    $is_today = $reservation['reservation_date'] == date('Y-m-d');
                                    $is_past = strtotime($reservation_datetime) < time();
                                    $row_class = $is_past ? 'table-danger' : ($is_today ? 'table-info' : '');
                                ?>
                                    <tr class="<?php echo $row_class; ?>" id="reservation-row-<?php echo $reservation['id']; ?>">
                                        <td class="fw-bold">#<?php echo $reservation['id']; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($reservation['customer_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($reservation['customer_email']); ?></small>
                                            <br>
                                            <small><?php echo htmlspecialchars($reservation['customer_phone']); ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                <?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?>
                                            </div>
                                            <div>
                                                <span class="badge <?php echo $is_today ? 'bg-info' : ($is_past ? 'bg-danger' : 'bg-secondary'); ?>">
                                                    <?php echo date('h:i A', strtotime($reservation['reservation_time'])); ?>
                                                </span>
                                                <?php if ($is_today): ?>
                                                    <span class="badge bg-warning text-dark ms-1">Today</span>
                                                <?php elseif ($is_past): ?>
                                                    <span class="badge bg-danger ms-1">Past</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                                <?php echo $reservation['number_of_guests']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($reservation['name'] ?? 'Main'); ?></td>
                                        <td>
                                            <?php if (!empty($reservation['special_requests'])): ?>
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        data-bs-toggle="popover" 
                                                        data-bs-title="Special Requests"
                                                        data-bs-content="<?php echo htmlspecialchars($reservation['special_requests']); ?>">
                                                    <i class="bi bi-chat-text"></i> View
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d', strtotime($reservation['created_at'])); ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-success confirm-reservation-btn" 
                                                        onclick="confirmReservation(<?php echo $reservation['id']; ?>)"
                                                        title="Confirm Reservation">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                              
                                                <a href='reservation.php?source=edit_reservation&id=<?php echo $reservation['id']; ?>'
                                                   class="btn btn-outline-warning"
                                                   title="Edit Reservation">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-danger cancel-reservation-btn" 
                                                        onclick="cancelReservation(<?php echo $reservation['id']; ?>)"
                                                        title="Cancel Reservation">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4"> <!-- Fixed: colspan="8" not 9 -->
                                        <div class="text-muted">
                                            <i class="bi bi-check-circle display-4 d-block mb-2"></i>
                                            <h5>No pending reservations</h5>
                                            <p>All reservations are confirmed or processed.</p>
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



<script>
// Confirm Reservation
function confirmReservation(reservationId) {
    if (!reservationId) {
        showError('Invalid reservation ID');
        return;
    }
    
    if (!confirm('Are you sure you want to confirm this reservation?')) {
        return;
    }
    
    fetch('includes/update_reservation_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + reservationId + '&status=confirmed'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove row from table
            const row = document.getElementById('reservation-row-' + reservationId);
            if (row) {
                const table = $('#pendingReservationsTable').DataTable();
                if (table) {
                    table.row(row).remove().draw();
                } else {
                    row.style.opacity = '0';
                    row.style.transition = 'opacity 0.4s';
                    setTimeout(() => {
                        row.remove();
                    }, 400);
                }
            }
            showSuccess('Reservation confirmed successfully!');
        } else {
            showError(data.message || 'Failed to confirm reservation');
        }
    })
    .catch(error => {
        showError('Error confirming reservation: ' + error.message);
        console.error('Error:', error);
    });
}

// Cancel Reservation
function cancelReservation(reservationId) {
    if (!reservationId) {
        showError('Invalid reservation ID');
        return;
    }
    
    if (!confirm('Are you sure you want to cancel this reservation?')) {
        return;
    }
    
    fetch('includes/update_reservation_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + reservationId + '&status=cancelled'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove row from table
            const row = document.getElementById('reservation-row-' + reservationId);
            if (row) {
                const table = $('#pendingReservationsTable').DataTable();
                if (table) {
                    table.row(row).remove().draw();
                } else {
                    row.style.opacity = '0';
                    row.style.transition = 'opacity 0.4s';
                    setTimeout(() => {
                        row.remove();
                    }, 400);
                }
            }
            showSuccess('Reservation cancelled successfully!');
        } else {
            showError(data.message || 'Failed to cancel reservation');
        }
    })
    .catch(error => {
        showError('Error cancelling reservation: ' + error.message);
        console.error('Error:', error);
    });
}

// Show success message
function showSuccess(message) {
    document.getElementById('toastMessage').textContent = message;
    const toast = new bootstrap.Toast(document.getElementById('successToast'));
    toast.show();
    
    setTimeout(() => toast.hide(), 5000);
}

// Show error message
function showError(message) {
    document.getElementById('errorToastMessage').textContent = message;
    const toast = new bootstrap.Toast(document.getElementById('errorToast'));
    toast.show();
    
    setTimeout(() => toast.hide(), 5000);
}

// Initialize DataTable and popovers
document.addEventListener('DOMContentLoaded', function() {
    // Initialize popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    
    // Initialize DataTable
    if (typeof jQuery !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        // Destroy existing DataTable instance if it exists
        if ($.fn.DataTable.isDataTable('#pendingReservationsTable')) {
            $('#pendingReservationsTable').DataTable().destroy();
        }
        
        $('#pendingReservationsTable').DataTable({
            pageLength: 25,
            order: [[2, 'asc']], // Sort by date (column index 2)
            columnDefs: [
                { orderable: false, targets: [7] }, // Make Actions column non-orderable (8th column, index 7)
                { width: "50px", targets: [0] }, // ID column width
                { width: "180px", targets: [7] } // Actions column width (8th column, index 7)
            ],
            responsive: true,
            language: {
                search: "Search pending reservations:",
                lengthMenu: "Show _MENU_ reservations per page",
                info: "Showing _START_ to _END_ of _TOTAL_ pending reservations",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                zeroRecords: "No matching reservations found",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }
});
</script>