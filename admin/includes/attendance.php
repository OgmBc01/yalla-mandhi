<?php
// Get date filter (default to today)
$filter_date = $_GET['date'] ?? date('Y-m-d');
$filter_employee = $_GET['employee'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Build query for today's shifts
$query = "SELECT s.*, u.full_name, u.employee_id as emp_code, u.position, u.department,
          a.status as attendance_status, a.check_in_time, a.check_out_time,
          a.late_minutes, a.overtime_minutes, a.notes as attendance_notes
          FROM shifts s 
          JOIN users u ON s.employee_id = u.id 
          LEFT JOIN attendance a ON s.id = a.shift_id AND a.attendance_date = s.shift_date
          WHERE s.shift_date = ? AND s.is_active = 1";

$params = [$filter_date];
$types = "s";

if (!empty($filter_employee) && is_numeric($filter_employee)) {
    $query .= " AND s.employee_id = ?";
    $params[] = intval($filter_employee);
    $types .= "i";
}

$query .= " ORDER BY s.start_time, u.full_name";

// Prepare and execute query
$stmt = $connection->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get attendance statistics
$stats_query = "SELECT 
    COUNT(DISTINCT a.employee_id) as employees_present,
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_count,
    COUNT(CASE WHEN a.status = 'half_day' THEN 1 END) as half_day_count
    FROM attendance a 
    WHERE a.attendance_date = ?";
$stats_stmt = $connection->prepare($stats_query);
$stats_stmt->bind_param("s", $filter_date);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc() ?? [];
$stats_stmt->close();

// Get all active employees for filter
$employees_query = "SELECT id, full_name FROM users WHERE role IN ('employee', 'admin') AND is_active = 1 ORDER BY full_name";
$employees_result = $connection->query($employees_query);
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Attendance Management</h1>
            <div>
                <button type="button" class="btn btn-warning me-2" onclick="markBulkAttendance()">
                    <i class="bi bi-check-circle"></i> Mark Bulk Attendance
                </button>
                <a href="shifts.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Shifts
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
                                <h6 class="card-title">Total Employees</h6>
                                <h2 class="mb-0"><?php echo $result->num_rows ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-people display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Present</h6>
                                <h2 class="mb-0"><?php echo $stats['present_count'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-check-circle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Absent</h6>
                                <h2 class="mb-0"><?php echo $stats['absent_count'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-x-circle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Late</h6>
                                <h2 class="mb-0"><?php echo $stats['late_count'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-clock-history display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Attendance Rate</h6>
                                <h2 class="mb-0">
                                    <?php 
                                    $total = $result->num_rows;
                                    $present = $stats['present_count'] ?? 0;
                                    if ($total > 0) {
                                        echo round(($present / $total) * 100, 1) . '%';
                                    } else {
                                        echo '0%';
                                    }
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-graph-up display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Navigation and Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="source" value="attendance">
                    
                    <div class="col-md-3">
                        <label for="filter_date" class="form-label">Select Date</label>
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
                        <label for="filter_status" class="form-label">Attendance Status</label>
                        <select class="form-select" id="filter_status" name="status">
                            <option value="">All Status</option>
                            <option value="present" <?php echo $filter_status == 'present' ? 'selected' : ''; ?>>Present</option>
                            <option value="absent" <?php echo $filter_status == 'absent' ? 'selected' : ''; ?>>Absent</option>
                            <option value="late" <?php echo $filter_status == 'late' ? 'selected' : ''; ?>>Late</option>
                            <option value="half_day" <?php echo $filter_status == 'half_day' ? 'selected' : ''; ?>>Half Day</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                            <a href="shifts.php?source=attendance" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                            <div class="btn-group ms-2" role="group">
                                <a href="shifts.php?source=attendance&date=<?php echo date('Y-m-d', strtotime($filter_date . ' -1 day')); ?>" 
                                   class="btn btn-outline-info">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                                <a href="shifts.php?source=attendance&date=<?php echo date('Y-m-d', strtotime($filter_date . ' +1 day')); ?>" 
                                   class="btn btn-outline-info">
                                    Next <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-check me-2"></i>
                    Attendance for <?php echo date('l, F d, Y', strtotime($filter_date)); ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="attendanceTable">
                            <thead>
                                <tr class="table-dark">
                                    <th>Employee</th>
                                    <th>Shift Details</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Status</th>
                                    <th>Working Hours</th>
                                    <th width="180" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($shift = $result->fetch_assoc()): ?>
                                    <?php
                                    // Calculate working hours
                                    $working_hours = '--';
                                    $check_in_time = $shift['check_in_time'];
                                    $check_out_time = $shift['check_out_time'];
                                    
                                    if ($check_in_time && $check_out_time) {
                                        $start = new DateTime($check_in_time);
                                        $end = new DateTime($check_out_time);
                                        $diff = $start->diff($end);
                                        $working_hours = $diff->format('%hh %im');
                                    } elseif ($check_in_time) {
                                        $working_hours = 'Clock-in only';
                                    }
                                    
                                    // Status badge
                                    $status = $shift['attendance_status'] ?? 'absent';
                                    $status_badges = [
                                        'present' => 'bg-success',
                                        'absent' => 'bg-danger',
                                        'late' => 'bg-warning',
                                        'half_day' => 'bg-info',
                                        'leave' => 'bg-secondary',
                                        'holiday' => 'bg-purple'
                                    ];
                                    $status_badge = $status_badges[$status] ?? 'bg-secondary';
                                    $status_texts = [
                                        'present' => 'Present',
                                        'absent' => 'Absent',
                                        'late' => 'Late',
                                        'half_day' => 'Half Day',
                                        'leave' => 'On Leave',
                                        'holiday' => 'Holiday'
                                    ];
                                    $status_text = $status_texts[$status] ?? 'Absent';
                                    ?>
                                    
                                    <tr id="attendance-row-<?php echo $shift['id']; ?>" 
                                        data-status="<?php echo $status; ?>">
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($shift['full_name']); ?></div>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($shift['position']); ?> • 
                                                <?php echo htmlspecialchars($shift['department']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div>
                                                <i class="bi bi-clock me-1"></i>
                                                <?php echo date('g:i A', strtotime($shift['start_time'])); ?> - 
                                                <?php echo date('g:i A', strtotime($shift['end_time'])); ?>
                                            </div>
                                            <small class="text-muted"><?php echo htmlspecialchars($shift['location']); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($check_in_time): ?>
                                                <span class="text-success">
                                                    <?php echo date('g:i A', strtotime($check_in_time)); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">--</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($check_out_time): ?>
                                                <span class="text-danger">
                                                    <?php echo date('g:i A', strtotime($check_out_time)); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">--</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $status_badge; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                            <?php if ($shift['late_minutes'] > 0): ?>
                                                <div class="text-warning small">
                                                    <i class="bi bi-clock-history"></i> Late: <?php echo $shift['late_minutes']; ?> min
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($shift['overtime_minutes'] > 0): ?>
                                                <div class="text-info small">
                                                    <i class="bi bi-plus-circle"></i> OT: <?php echo $shift['overtime_minutes']; ?> min
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="fw-bold"><?php echo $working_hours; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if (!$check_in_time && $status == 'absent'): ?>
                                                    <button type="button" class="btn btn-outline-success mark-attendance-btn" 
                                                            onclick="markAttendance(<?php echo $shift['id']; ?>, 'present')"
                                                            title="Mark Present">
                                                        <i class="bi bi-check-circle"></i> Present
                                                    </button>
                                                <?php elseif ($check_in_time && !$check_out_time): ?>
                                                    <button type="button" class="btn btn-outline-danger mark-attendance-btn" 
                                                            onclick="markCheckOut(<?php echo $shift['id']; ?>)"
                                                            title="Check Out">
                                                        <i class="bi bi-box-arrow-right"></i> Check Out
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline-warning mark-attendance-btn" 
                                                            onclick="editAttendance(<?php echo $shift['id']; ?>)"
                                                            title="Edit Attendance">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <a href="shifts.php?source=view_attendance&shift_id=<?php echo $shift['id']; ?>&date=<?php echo $filter_date; ?>" 
                                                   class="btn btn-outline-info" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="text-muted">
                            <i class="bi bi-calendar-x display-4 d-block mb-2"></i>
                            <h5>No shifts scheduled for <?php echo date('F d, Y', strtotime($filter_date)); ?></h5>
                            <p>There are no active shifts scheduled for this date.</p>
                            <a href="shifts.php?source=add_shift" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-circle"></i> Schedule Shifts
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Mark Attendance Modal -->
<div class="modal fade" id="markAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Mark Attendance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="markAttendanceModalBody">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Attendance Modal -->
<div class="modal fade" id="bulkAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="bi bi-people-fill me-2"></i>Bulk Attendance Marking</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Select employees and mark their attendance in bulk for <strong><?php echo date('F d, Y', strtotime($filter_date)); ?></strong>:</p>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Employee</th>
                                <th>Status</th>
                                <th>Check In Time</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody id="bulkAttendanceBody">
                            <!-- Will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Note:</strong> Employees already marked present will be skipped.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="submitBulkAttendance()">
                    <i class="bi bi-save me-2"></i>Save Bulk Attendance
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="attendanceSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="attendanceToastMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="attendanceErrorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="attendanceErrorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
let currentShiftId = null;
let markAttendanceModal = null;
let bulkAttendanceModal = null;

function markAttendance(shiftId, status) {
    currentShiftId = shiftId;
    
    const modalBody = document.getElementById('markAttendanceModalBody');
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading...</p>
        </div>
    `;
    
    fetch('includes/get_attendance_form.php?shift_id=' + shiftId + '&status=' + status + '&date=<?php echo $filter_date; ?>')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message || 'Failed to load form'}
                    </div>
                `;
                return;
            }
            
            modalBody.innerHTML = `
                <form id="attendanceForm">
                    <input type="hidden" name="shift_id" value="${shiftId}">
                    <input type="hidden" name="attendance_date" value="<?php echo $filter_date; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" value="${escapeHtml(data.employee_name)}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Attendance Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="present" ${status === 'present' ? 'selected' : ''}>Present</option>
                            <option value="late" ${status === 'late' ? 'selected' : ''}>Late</option>
                            <option value="half_day" ${status === 'half_day' ? 'selected' : ''}>Half Day</option>
                            <option value="absent" ${status === 'absent' ? 'selected' : ''}>Absent</option>
                            <option value="leave" ${status === 'leave' ? 'selected' : ''}>On Leave</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="check_in_time" class="form-label">Check In Time</label>
                        <input type="datetime-local" class="form-control" id="check_in_time" name="check_in_time" 
                               value="${data.current_time}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Attendance
                        </button>
                    </div>
                </form>
            `;
            
            // Add form submit handler
            document.getElementById('attendanceForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitAttendance();
            });
        })
        .catch(() => {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    Error loading form
                </div>
            `;
        });
    
    if (!markAttendanceModal) {
        markAttendanceModal = new bootstrap.Modal(document.getElementById('markAttendanceModal'));
    }
    markAttendanceModal.show();
}

function markCheckOut(shiftId) {
    currentShiftId = shiftId;
    
    const modalBody = document.getElementById('markAttendanceModalBody');
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading...</p>
        </div>
    `;
    
    fetch('includes/get_checkout_form.php?shift_id=' + shiftId)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message || 'Failed to load form'}
                    </div>
                `;
                return;
            }
            
            modalBody.innerHTML = `
                <form id="checkoutForm">
                    <input type="hidden" name="attendance_id" value="${data.attendance_id}">
                    
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" value="${escapeHtml(data.employee_name)}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Check In Time</label>
                        <input type="text" class="form-control" value="${escapeHtml(data.check_in_time)}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="check_out_time" class="form-label">Check Out Time</label>
                        <input type="datetime-local" class="form-control" id="check_out_time" name="check_out_time" 
                               value="${data.current_time}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="overtime_minutes" class="form-label">Overtime (minutes)</label>
                        <input type="number" class="form-control" id="overtime_minutes" name="overtime_minutes" 
                               min="0" value="0">
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-right me-2"></i>Mark Check Out
                        </button>
                    </div>
                </form>
            `;
            
            // Add form submit handler
            document.getElementById('checkoutForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitCheckOut();
            });
        })
        .catch(() => {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    Error loading form
                </div>
            `;
        });
    
    if (!markAttendanceModal) {
        markAttendanceModal = new bootstrap.Modal(document.getElementById('markAttendanceModal'));
    }
    markAttendanceModal.show();
}

function editAttendance(shiftId) {
    window.location.href = 'shifts.php?source=view_attendance&shift_id=' + shiftId + '&date=<?php echo $filter_date; ?>';
}

function submitAttendance() {
    const form = document.getElementById('attendanceForm');
    const formData = new FormData(form);
    
    fetch('includes/save_attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            markAttendanceModal.hide();
            showSuccess(data.message || 'Attendance marked successfully');
            // Reload page after 1 second
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showError(data.message || 'Failed to save attendance');
        }
    })
    .catch(() => {
        showError('Server error occurred');
    });
}

function submitCheckOut() {
    const form = document.getElementById('checkoutForm');
    const formData = new FormData(form);
    
    fetch('includes/save_checkout.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            markAttendanceModal.hide();
            showSuccess(data.message || 'Check out recorded successfully');
            // Reload page after 1 second
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showError(data.message || 'Failed to save check out');
        }
    })
    .catch(() => {
        showError('Server error occurred');
    });
}

function markBulkAttendance() {
    fetch('includes/get_bulk_attendance_data.php?date=<?php echo $filter_date; ?>')
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.shifts || data.shifts.length === 0) {
                showError('No shifts available for bulk attendance');
                return;
            }
            
            const tbody = document.getElementById('bulkAttendanceBody');
            tbody.innerHTML = '';
            
            data.shifts.forEach(shift => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <input class="form-check-input bulk-select" type="checkbox" 
                               value="${shift.id}" id="bulk_${shift.id}" 
                               ${shift.attendance_status === 'present' ? 'disabled' : ''}>
                    </td>
                    <td>
                        <label class="form-check-label" for="bulk_${shift.id}">
                            ${escapeHtml(shift.full_name)}
                        </label>
                        <div class="small text-muted">${escapeHtml(shift.position)}</div>
                    </td>
                    <td>
                        <select class="form-select form-select-sm bulk-status" 
                                data-shift-id="${shift.id}" 
                                ${shift.attendance_status === 'present' ? 'disabled' : ''}>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="half_day">Half Day</option>
                            <option value="leave">On Leave</option>
                        </select>
                    </td>
                    <td>
                        <input type="datetime-local" class="form-control form-control-sm bulk-checkin" 
                               data-shift-id="${shift.id}" 
                               value="${shift.current_time}"
                               ${shift.attendance_status === 'present' ? 'disabled' : ''}>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm bulk-notes" 
                               data-shift-id="${shift.id}" 
                               placeholder="Notes"
                               ${shift.attendance_status === 'present' ? 'disabled' : ''}>
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            if (!bulkAttendanceModal) {
                bulkAttendanceModal = new bootstrap.Modal(document.getElementById('bulkAttendanceModal'));
            }
            bulkAttendanceModal.show();
        })
        .catch(() => {
            showError('Error loading bulk attendance data');
        });
}

function submitBulkAttendance() {
    const selectedShifts = [];
    const checkboxes = document.querySelectorAll('.bulk-select:checked');
    
    checkboxes.forEach(checkbox => {
        const shiftId = checkbox.value;
        const status = document.querySelector(`.bulk-status[data-shift-id="${shiftId}"]`).value;
        const checkInTime = document.querySelector(`.bulk-checkin[data-shift-id="${shiftId}"]`).value;
        const notes = document.querySelector(`.bulk-notes[data-shift-id="${shiftId}"]`).value;
        
        selectedShifts.push({
            shift_id: shiftId,
            status: status,
            check_in_time: checkInTime,
            notes: notes
        });
    });
    
    if (selectedShifts.length === 0) {
        showError('Please select at least one employee');
        return;
    }
    
    fetch('includes/save_bulk_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            date: '<?php echo $filter_date; ?>',
            shifts: selectedShifts
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            bulkAttendanceModal.hide();
            showSuccess(`Attendance marked for ${data.marked_count} employees`);
            // Reload page after 1 second
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showError(data.message || 'Failed to save bulk attendance');
        }
    })
    .catch(() => {
        showError('Server error occurred');
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showSuccess(msg) {
    document.getElementById('attendanceToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('attendanceSuccessToast')).show();
}

function showError(msg) {
    document.getElementById('attendanceErrorToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('attendanceErrorToast')).show();
}

// Initialize DataTable
document.addEventListener('DOMContentLoaded', () => {
    if ($.fn.DataTable.isDataTable('#attendanceTable')) {
        $('#attendanceTable').DataTable().destroy();
    }

    $('#attendanceTable').DataTable({
        pageLength: 25,
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: [6] }],
        responsive: true,
        language: {
            search: "Search attendance:",
            lengthMenu: "Show _MENU_ records per page"
        }
    });
});
</script>