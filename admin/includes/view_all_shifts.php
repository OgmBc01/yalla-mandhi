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