<?php
// Initialize variables
$employee_id = $shift_date = $shift_type = $start_time = $end_time = $location = $notes = '';
$is_active = 1;
$errors = [];
$success = false;

// Get all active employees
$employees_query = "SELECT id, full_name, employee_id, department, position 
                   FROM users 
                   WHERE role IN ('employee', 'admin') AND is_active = 1 
                   ORDER BY full_name";
$employees_result = $connection->query($employees_query);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
    
    // Check if employee already has a shift on this date
    if (empty($errors)) {
        $check_shift = $connection->prepare(
            "SELECT id FROM shifts WHERE employee_id = ? AND shift_date = ?"
        );
        $check_shift->bind_param("is", $employee_id, $shift_date);
        $check_shift->execute();
        $check_shift->store_result();
        
        if ($check_shift->num_rows > 0) {
            $errors[] = "This employee already has a shift assigned on this date";
        }
        $check_shift->close();
    }

    if (empty($errors)) {
        // Insert shift
        $stmt = $connection->prepare(
            "INSERT INTO shifts (employee_id, shift_date, shift_type, start_time, end_time, 
                               location, notes, is_active) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param("issssssi", 
            $employee_id, $shift_date, $shift_type, $start_time, $end_time,
            $location, $notes, $is_active
        );
        
        if ($stmt->execute()) {
            $shift_id = $stmt->insert_id;
            $stmt->close();
            $success = true;
            
            // Clear form fields
            $employee_id = $shift_date = $shift_type = $start_time = $end_time = $location = $notes = '';
            $is_active = 1;
        } else {
            $errors[] = "Failed to add shift: " . $connection->error;
        }
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Add New Shift</h1>
            <div>
                <a href="shifts.php?source=bulk_assign" class="btn btn-info me-2">
                    <i class="bi bi-people-fill"></i> Bulk Assign
                </a>
                <a href="shifts.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Shifts
                </a>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Shift Information</h5>
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
                                                    <?php echo $employee_id == $emp['id'] ? 'selected' : ''; ?>>
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
                                       value="<?php echo htmlspecialchars($shift_date ?: date('Y-m-d')); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="shift_type" class="form-label">Shift Type *</label>
                                <select class="form-select" id="shift_type" name="shift_type" required>
                                    <option value="morning" <?php echo $shift_type == 'morning' ? 'selected' : ''; ?>>Morning Shift (6 AM - 2 PM)</option>
                                    <option value="afternoon" <?php echo $shift_type == 'afternoon' ? 'selected' : ''; ?>>Afternoon Shift (2 PM - 10 PM)</option>
                                    <option value="evening" <?php echo $shift_type == 'evening' ? 'selected' : ''; ?>>Evening Shift (4 PM - 12 AM)</option>
                                    <option value="night" <?php echo $shift_type == 'night' ? 'selected' : ''; ?>>Night Shift (10 PM - 6 AM)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time *</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" 
                                       value="<?php echo htmlspecialchars($start_time ?: '09:00'); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time *</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" 
                                       value="<?php echo htmlspecialchars($end_time ?: '17:00'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <select class="form-select" id="location" name="location">
                                    <option value="Main Restaurant" <?php echo $location == 'Main Restaurant' ? 'selected' : ''; ?>>Main Restaurant</option>
                                    <option value="Takeaway Counter" <?php echo $location == 'Takeaway Counter' ? 'selected' : ''; ?>>Takeaway Counter</option>
                                    <option value="Kitchen" <?php echo $location == 'Kitchen' ? 'selected' : ''; ?>>Kitchen</option>
                                    <option value="Delivery" <?php echo $location == 'Delivery' ? 'selected' : ''; ?>>Delivery</option>
                                    <option value="Cashier" <?php echo $location == 'Cashier' ? 'selected' : ''; ?>>Cashier</option>
                                    <option value="Other" <?php echo $location == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           <?php echo $is_active == 1 ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active Shift
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes / Special Instructions</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($notes); ?></textarea>
                        <div class="form-text">Optional: Add any special instructions or notes for this shift</div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Shift Duration:</strong> Make sure the end time is after the start time. 
                        Night shifts that go past midnight will be automatically handled.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="shifts.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Add Shift
                        </button>
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
                <h4 class="my-3">Shift Added Successfully!</h4>
                <p>The new shift has been added to the schedule.</p>
            </div>
            <div class="modal-footer">
                <a href="shifts.php" class="btn btn-secondary">View All Shifts</a>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Add Another Shift</button>
            </div>
        </div>
    </div>
</div>

<?php if ($success): ?>
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

// Validate time on form submit
document.querySelector('form').addEventListener('submit', function(e) {
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    
    if (startTime && endTime) {
        const start = new Date('2000-01-01T' + startTime + ':00');
        const end = new Date('2000-01-01T' + endTime + ':00');
        
        // Handle night shift (end time is next day)
        if (end <= start) {
            // For night shifts, add 24 hours to end time for comparison
            const endNextDay = new Date(end.getTime() + (24 * 60 * 60 * 1000));
            if (endNextDay - start > (12 * 60 * 60 * 1000)) { // More than 12 hours
                e.preventDefault();
                alert('Shift duration seems too long. Please check the times.');
            }
        } else if (end - start > (12 * 60 * 60 * 1000)) { // More than 12 hours
            e.preventDefault();
            alert('Shift duration cannot exceed 12 hours.');
        }
    }
});
</script>