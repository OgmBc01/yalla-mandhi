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