<?php
// Get shift ID and date
if (!isset($_GET['shift_id']) || !is_numeric($_GET['shift_id'])) {
    header("Location: shifts.php?source=attendance");
    exit();
}

$shift_id = (int)$_GET['shift_id'];
$date = $_GET['date'] ?? date('Y-m-d');

// Fetch attendance details
$stmt = $connection->prepare(
    "SELECT a.*, s.shift_date, s.start_time as scheduled_start, s.end_time as scheduled_end,
            s.location, s.shift_type, u.full_name, u.position, u.department, u.employee_id as emp_code
     FROM attendance a
     JOIN shifts s ON a.shift_id = s.id
     JOIN users u ON a.employee_id = u.id
     WHERE a.shift_id = ? AND a.attendance_date = ?"
);
$stmt->bind_param("is", $shift_id, $date);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // No attendance record yet, show shift details
    $shift_stmt = $connection->prepare(
        "SELECT s.*, u.full_name, u.position, u.department, u.employee_id as emp_code
         FROM shifts s 
         JOIN users u ON s.employee_id = u.id 
         WHERE s.id = ?"
    );
    $shift_stmt->bind_param("i", $shift_id);
    $shift_stmt->execute();
    $shift_result = $shift_stmt->get_result();
    
    if ($shift_result->num_rows === 0) {
        header("Location: shifts.php?source=attendance");
        exit();
    }
    
    $shift = $shift_result->fetch_assoc();
    $attendance = null;
    $shift_stmt->close();
} else {
    $attendance = $result->fetch_assoc();
    $shift = [
        'shift_date' => $attendance['shift_date'],
        'start_time' => $attendance['scheduled_start'],
        'end_time' => $attendance['scheduled_end'],
        'location' => $attendance['location'],
        'shift_type' => $attendance['shift_type'],
        'full_name' => $attendance['full_name'],
        'position' => $attendance['position'],
        'department' => $attendance['department'],
        'emp_code' => $attendance['emp_code']
    ];
}

$stmt->close();

// Calculate working hours if available
$working_hours = null;
if ($attendance && $attendance['check_in_time'] && $attendance['check_out_time']) {
    $start = new DateTime($attendance['check_in_time']);
    $end = new DateTime($attendance['check_out_time']);
    $diff = $start->diff($end);
    $working_hours = $diff->format('%h hours %i minutes');
    
    // Add overtime if any
    if ($attendance['overtime_minutes'] > 0) {
        $working_hours .= ' + ' . $attendance['overtime_minutes'] . ' min overtime';
    }
}

// Status badge
$status_badges = [
    'present' => 'bg-success',
    'absent' => 'bg-danger',
    'late' => 'bg-warning',
    'half_day' => 'bg-info',
    'leave' => 'bg-secondary',
    'holiday' => 'bg-purple'
];
$status = $attendance['status'] ?? 'absent';
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

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Attendance Details</h1>
            <a href="shifts.php?source=attendance&date=<?php echo $date; ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Attendance
            </a>
        </div>
        
        <div class="row">
            <!-- Attendance Details Card -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Attendance Record</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($attendance): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="bi bi-calendar me-2"></i> Date</h6>
                                    <h4 class="text-primary"><?php echo date('l, F d, Y', strtotime($attendance['attendance_date'])); ?></h4>
                                    
                                    <h6 class="mt-3"><i class="bi bi-info-circle me-2"></i> Status</h6>
                                    <span class="badge <?php echo $status_badge; ?> fs-6">
                                        <?php echo $status_text; ?>
                                    </span>
                                    
                                    <?php if ($attendance['late_minutes'] > 0): ?>
                                        <div class="mt-2">
                                            <span class="badge bg-warning">
                                                <i class="bi bi-clock-history"></i> Late: <?php echo $attendance['late_minutes']; ?> minutes
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($attendance['overtime_minutes'] > 0): ?>
                                        <div class="mt-2">
                                            <span class="badge bg-info">
                                                <i class="bi bi-plus-circle"></i> Overtime: <?php echo $attendance['overtime_minutes']; ?> minutes
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6><i class="bi bi-clock me-2"></i> Time Record</h6>
                                    <div class="alert alert-light">
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>Check In:</strong><br>
                                                <?php echo $attendance['check_in_time'] ? date('g:i A', strtotime($attendance['check_in_time'])) : '--'; ?>
                                            </div>
                                            <div class="col-6">
                                                <strong>Check Out:</strong><br>
                                                <?php echo $attendance['check_out_time'] ? date('g:i A', strtotime($attendance['check_out_time'])) : '--'; ?>
                                            </div>
                                        </div>
                                        
                                        <?php if ($working_hours): ?>
                                            <hr class="my-2">
                                            <strong>Total Working Hours:</strong><br>
                                            <?php echo $working_hours; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h6><i class="bi bi-clock-history me-2"></i> Scheduled Shift</h6>
                                    <p>
                                        <strong>Time:</strong> 
                                        <?php echo date('g:i A', strtotime($shift['start_time'])); ?> - 
                                        <?php echo date('g:i A', strtotime($shift['end_time'])); ?><br>
                                        <strong>Type:</strong> <?php echo ucfirst($shift['shift_type']); ?><br>
                                        <strong>Location:</strong> <?php echo htmlspecialchars($shift['location']); ?>
                                    </p>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6><i class="bi bi-calendar-event me-2"></i> Record Information</h6>
                                    <p>
                                        <strong>Created:</strong> <?php echo date('M d, Y g:i A', strtotime($attendance['created_at'])); ?><br>
                                        <strong>Last Updated:</strong> <?php echo date('M d, Y g:i A', strtotime($attendance['updated_at'])); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if (!empty($attendance['notes'])): ?>
                                <hr>
                                <h6><i class="bi bi-sticky me-2"></i> Notes</h6>
                                <div class="alert alert-light">
                                    <?php echo nl2br(htmlspecialchars($attendance['notes'])); ?>
                                </div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-calendar-x display-4 text-muted mb-3"></i>
                                <h4>No Attendance Record</h4>
                                <p class="text-muted">Attendance has not been marked for this shift.</p>
                                <a href="shifts.php?source=attendance&date=<?php echo $date; ?>" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i> Mark Attendance
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Employee Details Card -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Employee Details</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 100px; height: 100px;">
                            <i class="bi bi-person display-4 text-muted"></i>
                        </div>
                        
                        <h4><?php echo htmlspecialchars($shift['full_name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($shift['position']); ?></p>
                        
                        <div class="text-start">
                            <?php if ($shift['emp_code']): ?>
                                <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($shift['emp_code']); ?></p>
                            <?php endif; ?>
                            
                            <p><strong>Department:</strong> <?php echo htmlspecialchars($shift['department']); ?></p>
                            
                            <p><strong>Shift Date:</strong> <?php echo date('M d, Y', strtotime($shift['shift_date'])); ?></p>
                            
                            <p><strong>Shift Type:</strong> <span class="badge bg-secondary"><?php echo ucfirst($shift['shift_type']); ?></span></p>
                        </div>
                    </div>
                </div>
                
                <!-- Actions Card -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if (!$attendance): ?>
                                <a href="shifts.php?source=attendance&date=<?php echo $date; ?>" 
                                   class="btn btn-success">
                                    <i class="bi bi-check-circle me-2"></i> Mark Attendance
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-warning" 
                                        onclick="editAttendanceRecord(<?php echo $attendance['id']; ?>)">
                                    <i class="bi bi-pencil me-2"></i> Edit Record
                                </button>
                                
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="deleteAttendanceRecord(<?php echo $attendance['id']; ?>)">
                                    <i class="bi bi-trash me-2"></i> Delete Record
                                </button>
                            <?php endif; ?>
                            
                            <a href="shifts.php?source=view_shift&id=<?php echo $shift_id; ?>" 
                               class="btn btn-outline-info">
                                <i class="bi bi-clock me-2"></i> View Shift Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Attendance Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Attendance Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editAttendanceModalBody">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this attendance record?
                <div class="alert alert-danger mt-2">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAttendanceBtn" 
                        onclick="confirmDeleteAttendance()">
                    Delete Record
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentAttendanceId = null;
let editAttendanceModal = null;
let deleteAttendanceModal = null;

function editAttendanceRecord(attendanceId) {
    currentAttendanceId = attendanceId;
    
    const modalBody = document.getElementById('editAttendanceModalBody');
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-warning"></div>
            <p class="mt-2">Loading...</p>
        </div>
    `;
    
    fetch('includes/get_edit_attendance_form.php?id=' + attendanceId)
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
                <form id="editAttendanceForm">
                    <input type="hidden" name="id" value="${attendanceId}">
                    
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" value="${escapeHtml(data.employee_name)}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Attendance Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="present" ${data.status === 'present' ? 'selected' : ''}>Present</option>
                            <option value="late" ${data.status === 'late' ? 'selected' : ''}>Late</option>
                            <option value="half_day" ${data.status === 'half_day' ? 'selected' : ''}>Half Day</option>
                            <option value="absent" ${data.status === 'absent' ? 'selected' : ''}>Absent</option>
                            <option value="leave" ${data.status === 'leave' ? 'selected' : ''}>On Leave</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="check_in_time" class="form-label">Check In Time</label>
                                <input type="datetime-local" class="form-control" id="check_in_time" name="check_in_time" 
                                       value="${data.check_in_time || ''}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="check_out_time" class="form-label">Check Out Time</label>
                                <input type="datetime-local" class="form-control" id="check_out_time" name="check_out_time" 
                                       value="${data.check_out_time || ''}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="late_minutes" class="form-label">Late Minutes</label>
                                <input type="number" class="form-control" id="late_minutes" name="late_minutes" 
                                       min="0" value="${data.late_minutes || 0}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="overtime_minutes" class="form-label">Overtime Minutes</label>
                                <input type="number" class="form-control" id="overtime_minutes" name="overtime_minutes" 
                                       min="0" value="${data.overtime_minutes || 0}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">${escapeHtml(data.notes || '')}</textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save me-2"></i>Update Record
                        </button>
                    </div>
                </form>
            `;
            
            // Add form submit handler
            document.getElementById('editAttendanceForm').addEventListener('submit', function(e) {
                e.preventDefault();
                updateAttendance();
            });
        })
        .catch(() => {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    Error loading form
                </div>
            `;
        });
    
    if (!editAttendanceModal) {
        editAttendanceModal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
    }
    editAttendanceModal.show();
}

function updateAttendance() {
    const form = document.getElementById('editAttendanceForm');
    const formData = new FormData(form);
    
    fetch('includes/update_attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            editAttendanceModal.hide();
            showSuccess(data.message || 'Attendance updated successfully');
            // Reload page after 1 second
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showError(data.message || 'Failed to update attendance');
        }
    })
    .catch(() => {
        showError('Server error occurred');
    });
}

function deleteAttendanceRecord(attendanceId) {
    currentAttendanceId = attendanceId;
    
    if (!deleteAttendanceModal) {
        deleteAttendanceModal = new bootstrap.Modal(document.getElementById('deleteAttendanceModal'));
    }
    deleteAttendanceModal.show();
}

function confirmDeleteAttendance() {
    if (!currentAttendanceId) return;
    
    const btn = document.getElementById('confirmDeleteAttendanceBtn');
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    btn.disabled = true;
    
    fetch('includes/delete_attendance.php?id=' + currentAttendanceId, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        
        if (data.success) {
            deleteAttendanceModal.hide();
            showSuccess(data.message || 'Attendance record deleted');
            // Redirect to attendance page
            setTimeout(() => {
                window.location.href = 'shifts.php?source=attendance&date=<?php echo $date; ?>';
            }, 1000);
        } else {
            showError(data.message || 'Failed to delete record');
        }
    })
    .catch(() => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        showError('Server error occurred');
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showSuccess(msg) {
    const toast = new bootstrap.Toast(document.getElementById('attendanceSuccessToast'));
    document.getElementById('attendanceToastMessage').textContent = msg;
    toast.show();
}

function showError(msg) {
    const toast = new bootstrap.Toast(document.getElementById('attendanceErrorToast'));
    document.getElementById('attendanceErrorToastMessage').textContent = msg;
    toast.show();
}
</script>

<!-- Success Toast (Reuse from attendance.php) -->
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

<!-- Error Toast (Reuse from attendance.php) -->
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