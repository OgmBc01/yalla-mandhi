<?php
// Get shift ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: shifts.php");
    exit();
}

$shift_id = (int)$_GET['id'];

// Fetch shift data with employee details
$stmt = $connection->prepare(
    "SELECT s.*, u.full_name, u.email, u.phone, u.position, u.department, u.employee_id as emp_code
     FROM shifts s 
     JOIN users u ON s.employee_id = u.id 
     WHERE s.id = ?"
);
$stmt->bind_param("i", $shift_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: shifts.php");
    exit();
}

$shift = $result->fetch_assoc();
$stmt->close();

// Calculate duration
$start = new DateTime($shift['start_time']);
$end = new DateTime($shift['end_time']);
if ($end <= $start) {
    $end->modify('+1 day'); // For night shifts
}
$duration = $start->diff($end);
$duration_str = $duration->format('%h hours %i minutes');

// Shift type badge
$type_badges = [
    'morning' => 'bg-info',
    'afternoon' => 'bg-warning',
    'evening' => 'bg-primary',
    'night' => 'bg-dark'
];
$type_badge = $type_badges[$shift['shift_type']] ?? 'bg-secondary';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Shift Details</h1>
            <a href="shifts.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Shifts
            </a>
        </div>
        
        <div class="row">
            <!-- Shift Details Card -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Shift Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-calendar me-2"></i> Date</h6>
                                <h4 class="text-primary"><?php echo date('l, F d, Y', strtotime($shift['shift_date'])); ?></h4>
                                
                                <h6 class="mt-3"><i class="bi bi-clock me-2"></i> Time</h6>
                                <p>
                                    <strong>Start:</strong> <?php echo date('g:i A', strtotime($shift['start_time'])); ?><br>
                                    <strong>End:</strong> <?php echo date('g:i A', strtotime($shift['end_time'])); ?>
                                </p>
                            </div>
                            
                            <div class="col-md-6">
                                <h6><i class="bi bi-hourglass-split me-2"></i> Duration</h6>
                                <h4><?php echo $duration_str; ?></h4>
                                
                                <h6 class="mt-3"><i class="bi bi-pin-map me-2"></i> Location</h6>
                                <p><?php echo htmlspecialchars($shift['location']); ?></p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-tags me-2"></i> Shift Type</h6>
                                <span class="badge <?php echo $type_badge; ?> text-uppercase">
                                    <?php echo htmlspecialchars($shift['shift_type']); ?>
                                </span>
                            </div>
                            
                            <div class="col-md-6">
                                <h6><i class="bi bi-info-circle me-2"></i> Status</h6>
                                <?php if ($shift['is_active']): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Active
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle"></i> Inactive
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($shift['notes'])): ?>
                            <hr>
                            <h6><i class="bi bi-sticky me-2"></i> Notes</h6>
                            <div class="alert alert-light">
                                <?php echo nl2br(htmlspecialchars($shift['notes'])); ?>
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
                            
                            <?php if ($shift['email']): ?>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($shift['email']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($shift['phone']): ?>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($shift['phone']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="shifts.php?source=edit_shift&id=<?php echo $shift_id; ?>" 
                               class="btn btn-warning">
                                <i class="bi bi-pencil me-2"></i> Edit Shift
                            </a>
                            
                            <button class="btn btn-outline-danger" 
                                    onclick="showDeleteConfirm(
                                        <?php echo $shift_id; ?>,
                                        '<?php echo htmlspecialchars(addslashes($shift['full_name']), ENT_QUOTES); ?>',
                                        '<?php echo date('M d, Y', strtotime($shift['shift_date'])); ?>')">
                                <i class="bi bi-trash me-2"></i> Delete Shift
                            </button>
                        </div>
                    </div>
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
    if (!deleteShiftId) return;
    
    const btn = document.getElementById('confirmDeleteShiftBtn');
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    btn.disabled = true;
    
    fetch('includes/delete_shift.php?id=' + deleteShiftId, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + deleteShiftId
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        
        if (!data.success) {
            alert(data.message || 'Delete failed');
            return;
        }
        
        deleteModalInstance.hide();
        window.location.href = 'shifts.php';
    })
    .catch(() => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        alert('Server error');
    });
}
</script>