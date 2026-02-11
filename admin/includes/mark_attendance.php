<?php
// Get shift ID from URL
$shift_id = isset($_GET['shift_id']) ? intval($_GET['shift_id']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// If shift_id is provided, fetch that specific shift
if ($shift_id > 0) {
    $stmt = $connection->prepare(
        "SELECT s.*, u.full_name, u.employee_id as emp_code, u.position, u.department
         FROM shifts s 
         JOIN users u ON s.employee_id = u.id 
         WHERE s.id = ?"
    );
    $stmt->bind_param("i", $shift_id);
    $stmt->execute();
    $shift_result = $stmt->get_result();
    
    if ($shift_result->num_rows > 0) {
        $shift = $shift_result->fetch_assoc();
        $date = $shift['shift_date'];
    }
    $stmt->close();
}

// Get all shifts for the selected date that don't have attendance marked
$query = "SELECT s.*, u.full_name, u.employee_id as emp_code, u.position, u.department,
          a.id as attendance_id, a.status as attendance_status, a.check_in_time, a.check_out_time,
          a.late_minutes, a.overtime_minutes
          FROM shifts s 
          JOIN users u ON s.employee_id = u.id 
          LEFT JOIN attendance a ON s.id = a.shift_id AND a.attendance_date = s.shift_date
          WHERE s.shift_date = ? AND s.is_active = 1
          ORDER BY s.start_time, u.full_name";

$stmt = $connection->prepare($query);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

// Get attendance statistics for the date
$stats_query = "SELECT 
    COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
    COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count,
    COUNT(CASE WHEN status = 'late' THEN 1 END) as late_count,
    COUNT(CASE WHEN status = 'half_day' THEN 1 END) as half_day_count,
    COUNT(CASE WHEN status = 'leave' THEN 1 END) as leave_count
    FROM attendance WHERE attendance_date = ?";
$stats_stmt = $connection->prepare($stats_query);
$stats_stmt->bind_param("s", $date);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stats_stmt->close();

// Current time for default check-in
$current_time = date('Y-m-d\TH:i', round(time() / 300) * 300);
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">
                <i class="bi bi-check-circle me-2"></i>
                Mark Attendance
            </h1>
            <div>
                <a href="shifts.php?source=attendance&date=<?php echo urlencode($date); ?>" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Back to Attendance
                </a>
                <a href="shifts.php?source=attendance" class="btn btn-outline-secondary">
                    <i class="bi bi-calendar-check"></i> Attendance Overview
                </a>
            </div>
        </div>

        <!-- Date Navigation -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <label for="attendance_date" class="me-3 fw-bold">Select Date:</label>
                            <input type="date" class="form-control w-auto" id="attendance_date" 
                                   value="<?php echo $date; ?>" 
                                   onchange="window.location.href='shifts.php?source=mark_attendance&date=' + this.value">
                            <button type="button" class="btn btn-outline-primary ms-2" 
                                    onclick="window.location.href='shifts.php?source=mark_attendance&date=' + document.getElementById('attendance_date').value">
                                <i class="bi bi-arrow-repeat"></i> Load
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            <div class="btn-group" role="group">
                                <a href="shifts.php?source=mark_attendance&date=<?php echo date('Y-m-d', strtotime($date . ' -1 day')); ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-chevron-left"></i> Previous Day
                                </a>
                                <a href="shifts.php?source=mark_attendance&date=<?php echo date('Y-m-d'); ?>" 
                                   class="btn btn-outline-secondary">
                                    <i class="bi bi-calendar"></i> Today
                                </a>
                                <a href="shifts.php?source=mark_attendance&date=<?php echo date('Y-m-d', strtotime($date . ' +1 day')); ?>" 
                                   class="btn btn-outline-primary">
                                    Next Day <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Shifts</h5>
                        <h2 class="mb-0"><?php echo $result->num_rows; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Present</h5>
                        <h2 class="mb-0"><?php echo $stats['present_count'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h5 class="card-title">Late</h5>
                        <h2 class="mb-0"><?php echo $stats['late_count'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Half Day</h5>
                        <h2 class="mb-0"><?php echo $stats['half_day_count'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-secondary text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">On Leave</h5>
                        <h2 class="mb-0"><?php echo $stats['leave_count'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Absent</h5>
                        <h2 class="mb-0"><?php echo $stats['absent_count'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <span class="fw-bold me-3"><i class="bi bi-lightning me-2"></i>Quick Actions:</span>
                        <button type="button" class="btn btn-success btn-sm me-2" onclick="markAllPresent()">
                            <i class="bi bi-check-all"></i> Mark All Present
                        </button>
                        <button type="button" class="btn btn-warning btn-sm me-2" onclick="markAllLate()">
                            <i class="bi bi-clock-history"></i> Mark All Late
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="markAllAbsent()">
                            <i class="bi bi-x-circle"></i> Mark All Absent
                        </button>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Click on status badge to change
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Marking Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-day me-2"></i>
                    Attendance for <?php echo date('l, F d, Y', strtotime($date)); ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="markAttendanceTable">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Employee</th>
                                    <th>Scheduled Shift</th>
                                    <th width="150">Status</th>
                                    <th width="180">Check In Time</th>
                                    <th width="180">Check Out Time</th>
                                    <th>Late (min)</th>
                                    <th>OT (min)</th>
                                    <th>Notes</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $counter = 1;
                                while ($shift = $result->fetch_assoc()): 
                                    $has_attendance = !empty($shift['attendance_id']);
                                    $status = $shift['attendance_status'] ?? 'absent';
                                    
                                    // Status badge class
                                    $status_badges = [
                                        'present' => 'bg-success',
                                        'absent' => 'bg-danger',
                                        'late' => 'bg-warning text-dark',
                                        'half_day' => 'bg-info',
                                        'leave' => 'bg-secondary',
                                        'holiday' => 'bg-purple'
                                    ];
                                    $badge_class = $status_badges[$status] ?? 'bg-secondary';
                                    
                                    // Status display text
                                    $status_texts = [
                                        'present' => 'Present',
                                        'absent' => 'Absent',
                                        'late' => 'Late',
                                        'half_day' => 'Half Day',
                                        'leave' => 'On Leave',
                                        'holiday' => 'Holiday'
                                    ];
                                    $status_text = $status_texts[$status] ?? 'Absent';
                                    
                                    // Calculate late minutes
                                    $late_minutes = $shift['late_minutes'] ?? 0;
                                    if ($status == 'present' && empty($shift['attendance_id'])) {
                                        // Auto-calculate if late based on current time
                                        $scheduled_start = new DateTime($date . ' ' . $shift['start_time']);
                                        $current = new DateTime();
                                        if ($current > $scheduled_start) {
                                            $diff = $scheduled_start->diff($current);
                                            $late_minutes = ($diff->h * 60) + $diff->i;
                                            if ($late_minutes > 30) {
                                                $status = 'late';
                                            }
                                        }
                                    }
                                ?>
                                    <tr id="attendance-row-<?php echo $shift['id']; ?>" 
                                        data-shift-id="<?php echo $shift['id']; ?>"
                                        data-employee-id="<?php echo $shift['employee_id']; ?>"
                                        data-attendance-id="<?php echo $shift['attendance_id']; ?>">
                                        
                                        <td><?php echo $counter++; ?></td>
                                        
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($shift['full_name']); ?></div>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($shift['position']); ?>
                                                <?php if ($shift['department']): ?>
                                                    • <?php echo htmlspecialchars($shift['department']); ?>
                                                <?php endif; ?>
                                            </small>
                                            <?php if ($shift['emp_code']): ?>
                                                <div><small class="text-info">ID: <?php echo htmlspecialchars($shift['emp_code']); ?></small></div>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td>
                                            <span class="badge bg-secondary shift-time">
                                                <?php echo date('g:i A', strtotime($shift['start_time'])); ?> - 
                                                <?php echo date('g:i A', strtotime($shift['end_time'])); ?>
                                            </span>
                                            <div class="small text-muted">
                                                <i class="bi bi-pin-map"></i> <?php echo htmlspecialchars($shift['location']); ?>
                                            </div>
                                        </td>
                                        
                                        <td>
                                            <select class="form-select form-select-sm status-select" 
                                                    onchange="updateAttendanceStatus(<?php echo $shift['id']; ?>, this.value)">
                                                <option value="present" <?php echo $status == 'present' ? 'selected' : ''; ?>>Present</option>
                                                <option value="late" <?php echo $status == 'late' ? 'selected' : ''; ?>>Late</option>
                                                <option value="half_day" <?php echo $status == 'half_day' ? 'selected' : ''; ?>>Half Day</option>
                                                <option value="absent" <?php echo $status == 'absent' ? 'selected' : ''; ?>>Absent</option>
                                                <option value="leave" <?php echo $status == 'leave' ? 'selected' : ''; ?>>On Leave</option>
                                                <option value="holiday" <?php echo $status == 'holiday' ? 'selected' : ''; ?>>Holiday</option>
                                            </select>
                                            
                                            <?php if ($late_minutes > 0): ?>
                                                <div class="mt-1">
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-clock-history"></i> Late: <?php echo $late_minutes; ?> min
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td>
                                            <input type="datetime-local" class="form-control form-control-sm checkin-time" 
                                                   value="<?php echo $shift['check_in_time'] ? date('Y-m-d\TH:i', strtotime($shift['check_in_time'])) : $current_time; ?>"
                                                   onchange="updateCheckInTime(<?php echo $shift['id']; ?>, this.value)">
                                        </td>
                                        
                                        <td>
                                            <input type="datetime-local" class="form-control form-control-sm checkout-time" 
                                                   value="<?php echo $shift['check_out_time'] ? date('Y-m-d\TH:i', strtotime($shift['check_out_time'])) : ''; ?>"
                                                   onchange="updateCheckOutTime(<?php echo $shift['id']; ?>, this.value)">
                                        </td>
                                        
                                        <td>
                                            <input type="number" class="form-control form-control-sm late-minutes" 
                                                   value="<?php echo $shift['late_minutes'] ?? 0; ?>" 
                                                   min="0" max="480" step="5"
                                                   onchange="updateLateMinutes(<?php echo $shift['id']; ?>, this.value)">
                                        </td>
                                        
                                        <td>
                                            <input type="number" class="form-control form-control-sm overtime-minutes" 
                                                   value="<?php echo $shift['overtime_minutes'] ?? 0; ?>" 
                                                   min="0" max="480" step="15"
                                                   onchange="updateOvertimeMinutes(<?php echo $shift['id']; ?>, this.value)">
                                        </td>
                                        
                                        <td>
                                            <input type="text" class="form-control form-control-sm notes" 
                                                   value="<?php echo htmlspecialchars($shift['notes'] ?? ''); ?>" 
                                                   placeholder="Notes"
                                                   onchange="updateNotes(<?php echo $shift['id']; ?>, this.value)">
                                        </td>
                                        
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if ($has_attendance): ?>
                                                    <button type="button" class="btn btn-outline-success" 
                                                            onclick="saveAttendance(<?php echo $shift['id']; ?>)"
                                                            title="Update">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteAttendance(<?php echo $shift['attendance_id']; ?>, <?php echo $shift['id']; ?>)"
                                                            title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            onclick="saveAttendance(<?php echo $shift['id']; ?>)"
                                                            title="Save">
                                                        <i class="bi bi-save"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button type="button" class="btn btn-outline-secondary" 
                                                        onclick="markCheckOut(<?php echo $shift['id']; ?>)"
                                                        title="Quick Check-out"
                                                        <?php echo empty($shift['check_in_time']) ? 'disabled' : ''; ?>>
                                                    <i class="bi bi-box-arrow-right"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-calendar-x display-1 d-block mb-3"></i>
                            <h3>No Shifts Scheduled</h3>
                            <p class="lead">There are no active shifts scheduled for <?php echo date('F d, Y', strtotime($date)); ?>.</p>
                            <div class="mt-4">
                                <a href="shifts.php?source=add_shift&date=<?php echo urlencode($date); ?>" class="btn btn-primary me-2">
                                    <i class="bi bi-plus-circle"></i> Schedule Shift
                                </a>
                                <a href="shifts.php?source=bulk_assign&start_date=<?php echo urlencode($date); ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-people-fill"></i> Bulk Assign
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Check-out Modal -->
<div class="modal fade" id="quickCheckoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-box-arrow-right me-2"></i>Quick Check-out</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="quickCheckoutBody">
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

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="attendanceSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="toastMessage">Attendance saved successfully!</span>
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
                <span id="errorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
let currentShiftId = null;
let quickCheckoutModal = null;

// Save attendance for a shift
function saveAttendance(shiftId) {
    const row = document.getElementById('attendance-row-' + shiftId);
    if (!row) return;
    
    const status = row.querySelector('.status-select').value;
    const checkInTime = row.querySelector('.checkin-time').value;
    const checkOutTime = row.querySelector('.checkout-time').value;
    const lateMinutes = row.querySelector('.late-minutes').value;
    const overtimeMinutes = row.querySelector('.overtime-minutes').value;
    const notes = row.querySelector('.notes').value;
    const attendanceId = row.dataset.attendanceId;
    
    const formData = new FormData();
    formData.append('shift_id', shiftId);
    formData.append('attendance_date', '<?php echo $date; ?>');
    formData.append('status', status);
    formData.append('check_in_time', checkInTime);
    formData.append('check_out_time', checkOutTime || '');
    formData.append('late_minutes', lateMinutes);
    formData.append('overtime_minutes', overtimeMinutes);
    formData.append('notes', notes);
    if (attendanceId) {
        formData.append('attendance_id', attendanceId);
    }
    
    // Show loading state
    const saveBtn = row.querySelector('.btn-outline-primary, .btn-outline-success');
    const originalHTML = saveBtn.innerHTML;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    saveBtn.disabled = true;
    
    fetch('includes/save_attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        saveBtn.innerHTML = originalHTML;
        saveBtn.disabled = false;
        
        if (data.success) {
            showSuccess(data.message || 'Attendance saved successfully');
            
            // Update attendance ID if it was newly created
            if (data.attendance_id) {
                row.dataset.attendanceId = data.attendance_id;
                
                // Change button from Save to Update
                saveBtn.classList.remove('btn-outline-primary');
                saveBtn.classList.add('btn-outline-success');
                saveBtn.innerHTML = '<i class="bi bi-check-lg"></i>';
                saveBtn.title = 'Update';
            }
        } else {
            showError(data.message || 'Failed to save attendance');
        }
    })
    .catch(error => {
        saveBtn.innerHTML = originalHTML;
        saveBtn.disabled = false;
        showError('Server error occurred');
        console.error('Error:', error);
    });
}

// Update status only
function updateAttendanceStatus(shiftId, status) {
    // Auto-calculate late minutes based on status
    if (status === 'late') {
        const row = document.getElementById('attendance-row-' + shiftId);
        const checkInTime = row.querySelector('.checkin-time').value;
        const scheduledStart = '<?php echo $date; ?>T' + row.querySelector('.shift-time').textContent.split(' - ')[0];
        
        if (checkInTime) {
            const checkIn = new Date(checkInTime);
            const scheduled = new Date(scheduledStart);
            
            if (checkIn > scheduled) {
                const diffMs = checkIn - scheduled;
                const diffMins = Math.round(diffMs / 60000);
                row.querySelector('.late-minutes').value = diffMins;
            }
        }
    }
}

// Update check-in time
function updateCheckInTime(shiftId, time) {
    const row = document.getElementById('attendance-row-' + shiftId);
    const scheduledStart = '<?php echo $date; ?>T' + row.querySelector('.shift-time').textContent.split(' - ')[0];
    
    if (time) {
        const checkIn = new Date(time);
        const scheduled = new Date(scheduledStart);
        
        if (checkIn > scheduled) {
            const diffMs = checkIn - scheduled;
            const diffMins = Math.round(diffMs / 60000);
            row.querySelector('.late-minutes').value = diffMins;
            
            // Auto-mark as late if more than 15 minutes
            if (diffMins > 15) {
                row.querySelector('.status-select').value = 'late';
            }
        }
    }
}

// Update check-out time
function updateCheckOutTime(shiftId, time) {
    // Calculate overtime if check-out is after scheduled end
    const row = document.getElementById('attendance-row-' + shiftId);
    const scheduledEnd = '<?php echo $date; ?>T' + row.querySelector('.shift-time').textContent.split(' - ')[1];
    
    if (time) {
        const checkOut = new Date(time);
        let scheduled = new Date(scheduledEnd);
        
        // Handle night shifts
        if (scheduled.getHours() === 0 && scheduled.getMinutes() === 0) {
            scheduled.setDate(scheduled.getDate() + 1);
        }
        
        if (checkOut > scheduled) {
            const diffMs = checkOut - scheduled;
            const diffMins = Math.round(diffMs / 60000);
            row.querySelector('.overtime-minutes').value = diffMins;
        }
    }
}

// Update late minutes
function updateLateMinutes(shiftId, minutes) {
    const row = document.getElementById('attendance-row-' + shiftId);
    if (parseInt(minutes) > 15) {
        row.querySelector('.status-select').value = 'late';
    }
}

// Update overtime minutes
function updateOvertimeMinutes(shiftId, minutes) {
    // Just store the value
}

// Update notes
function updateNotes(shiftId, notes) {
    // Notes are saved with the main save function
}

// Delete attendance record
function deleteAttendance(attendanceId, shiftId) {
    if (!confirm('Are you sure you want to delete this attendance record?')) {
        return;
    }
    
    fetch('includes/delete_attendance.php?id=' + attendanceId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showSuccess('Attendance record deleted');
            
            // Reset the row
            const row = document.getElementById('attendance-row-' + shiftId);
            row.dataset.attendanceId = '';
            row.querySelector('.status-select').value = 'absent';
            row.querySelector('.checkin-time').value = '<?php echo $current_time; ?>';
            row.querySelector('.checkout-time').value = '';
            row.querySelector('.late-minutes').value = '0';
            row.querySelector('.overtime-minutes').value = '0';
            row.querySelector('.notes').value = '';
            
            // Change button from Update to Save
            const saveBtn = row.querySelector('.btn-outline-success');
            if (saveBtn) {
                saveBtn.classList.remove('btn-outline-success');
                saveBtn.classList.add('btn-outline-primary');
                saveBtn.innerHTML = '<i class="bi bi-save"></i>';
                saveBtn.title = 'Save';
            }
        } else {
            showError(data.message || 'Failed to delete attendance');
        }
    })
    .catch(() => {
        showError('Server error occurred');
    });
}

// Quick check-out
function markCheckOut(shiftId) {
    currentShiftId = shiftId;
    
    const modalBody = document.getElementById('quickCheckoutBody');
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading...</p>
        </div>
    `;
    
    fetch('includes/get_checkout_form.php?shift_id=' + shiftId + '&date=<?php echo $date; ?>')
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
                <form id="quickCheckoutForm">
                    <input type="hidden" name="attendance_id" value="${data.attendance_id}">
                    <input type="hidden" name="shift_id" value="${shiftId}">
                    
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
                               min="0" max="480" step="15" value="0">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-right me-2"></i>Complete Check-out
                        </button>
                    </div>
                </form>
            `;
            
            // Add form submit handler
            document.getElementById('quickCheckoutForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitQuickCheckout();
            });
        })
        .catch(() => {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    Error loading form
                </div>
            `;
        });
    
    if (!quickCheckoutModal) {
        quickCheckoutModal = new bootstrap.Modal(document.getElementById('quickCheckoutModal'));
    }
    quickCheckoutModal.show();
}

// Submit quick checkout
function submitQuickCheckout() {
    const form = document.getElementById('quickCheckoutForm');
    const formData = new FormData(form);
    
    const btn = form.querySelector('button[type="submit"]');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    btn.disabled = true;
    
    fetch('includes/save_checkout.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        
        if (data.success) {
            quickCheckoutModal.hide();
            showSuccess('Check-out recorded successfully');
            
            // Update the table row
            if (currentShiftId) {
                const row = document.getElementById('attendance-row-' + currentShiftId);
                if (row) {
                    const checkoutInput = row.querySelector('.checkout-time');
                    checkoutInput.value = document.getElementById('check_out_time').value;
                    row.querySelector('.overtime-minutes').value = document.getElementById('overtime_minutes').value;
                }
            }
            
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showError(data.message || 'Failed to record check-out');
        }
    })
    .catch(() => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        showError('Server error occurred');
    });
}

// Mark all employees as present
function markAllPresent() {
    if (!confirm('Mark all employees as present? This will override existing records.')) {
        return;
    }
    
    const rows = document.querySelectorAll('[id^="attendance-row-"]');
    rows.forEach(row => {
        row.querySelector('.status-select').value = 'present';
        row.querySelector('.late-minutes').value = '0';
        saveAttendance(row.dataset.shiftId);
    });
}

// Mark all employees as late
function markAllLate() {
    if (!confirm('Mark all employees as late? This will override existing records.')) {
        return;
    }
    
    const rows = document.querySelectorAll('[id^="attendance-row-"]');
    rows.forEach(row => {
        row.querySelector('.status-select').value = 'late';
        row.querySelector('.late-minutes').value = '15';
        saveAttendance(row.dataset.shiftId);
    });
}

// Mark all employees as absent
function markAllAbsent() {
    if (!confirm('Mark all employees as absent? This will override existing records.')) {
        return;
    }
    
    const rows = document.querySelectorAll('[id^="attendance-row-"]');
    rows.forEach(row => {
        row.querySelector('.status-select').value = 'absent';
        row.querySelector('.checkin-time').value = '';
        row.querySelector('.checkout-time').value = '';
        row.querySelector('.late-minutes').value = '0';
        row.querySelector('.overtime-minutes').value = '0';
        saveAttendance(row.dataset.shiftId);
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show success toast
function showSuccess(message) {
    document.getElementById('toastMessage').textContent = message;
    const toast = new bootstrap.Toast(document.getElementById('attendanceSuccessToast'));
    toast.show();
}

// Show error toast
function showError(message) {
    document.getElementById('errorToastMessage').textContent = message;
    const toast = new bootstrap.Toast(document.getElementById('attendanceErrorToast'));
    toast.show();
}

// Initialize DataTable
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#markAttendanceTable')) {
        $('#markAttendanceTable').DataTable().destroy();
    }

    if ($.fn.DataTable) {
        $('#markAttendanceTable').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [3,4,5,6,7,8,9] }
            ],
            responsive: true,
            language: {
                search: "Search employees:",
                lengthMenu: "Show _MENU_ records per page"
            }
        });
    }
});
</script>

<style>
/* Custom styles for mark attendance page */
.status-select {
    min-width: 120px;
}

.checkin-time, .checkout-time {
    min-width: 180px;
}

.late-minutes, .overtime-minutes {
    min-width: 80px;
}

.notes {
    min-width: 150px;
}

.table td {
    vertical-align: middle;
}

.shift-time {
    font-size: 0.85rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

/* Quick action buttons */
.quick-actions {
    background-color: #f8f9fa;
    border-radius: 5px;
}

/* Toast positioning */
.position-fixed {
    position: fixed;
    z-index: 9999;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .checkin-time, .checkout-time {
        min-width: 140px;
    }
    
    .status-select {
        min-width: 100px;
    }
}
</style>