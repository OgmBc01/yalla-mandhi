<?php
// Initialize variables
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
$selected_employees = $_GET['employees'] ?? [];
$shift_type = $_GET['shift_type'] ?? 'morning';
$location = $_GET['location'] ?? 'Main Restaurant';
$include_weekends = isset($_GET['include_weekends']) ? 1 : 0;
$skip_existing = isset($_GET['skip_existing']) ? 1 : 0; // FIXED: Initialize skip_existing
$notes = '';
$errors = [];
$success = false;
$bulk_results = [];

// Get all active employees
$employees_query = "SELECT id, full_name, employee_id, position, department 
                   FROM users 
                   WHERE role IN ('employee', 'admin') AND is_active = 1 
                   ORDER BY full_name";
$employees_result = $connection->query($employees_query);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $selected_employees = $_POST['employees'] ?? [];
    $shift_type = trim($_POST['shift_type'] ?? 'morning');
    $start_time = trim($_POST['start_time'] ?? '09:00');
    $end_time = trim($_POST['end_time'] ?? '17:00');
    $location = trim($_POST['location'] ?? 'Main Restaurant');
    $notes = trim($_POST['notes'] ?? '');
    $include_weekends = isset($_POST['include_weekends']) ? 1 : 0;
    $skip_existing = isset($_POST['skip_existing']) ? 1 : 0; // FIXED: Initialize from POST

    // Validation
    if (empty($start_date)) {
        $errors[] = "Start date is required";
    }
    
    if (empty($end_date)) {
        $errors[] = "End date is required";
    }
    
    if (strtotime($end_date) < strtotime($start_date)) {
        $errors[] = "End date must be after start date";
    }
    
    if (empty($selected_employees)) {
        $errors[] = "Please select at least one employee";
    }
    
    if (empty($start_time) || empty($end_time)) {
        $errors[] = "Start time and end time are required";
    }
    
    if (strtotime($end_time) <= strtotime($start_time)) {
        $errors[] = "End time must be after start time";
    }

    if (empty($errors)) {
        // Calculate date range
        $current_date = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        $shifts_created = 0;
        $shifts_skipped = 0;
        $errors_list = [];

        // Define shift types with preset times if not manually set
        $shift_presets = [
            'morning' => ['start' => '06:00', 'end' => '14:00'],
            'afternoon' => ['start' => '14:00', 'end' => '22:00'],
            'evening' => ['start' => '16:00', 'end' => '00:00'],
            'night' => ['start' => '22:00', 'end' => '06:00'],
            'custom' => ['start' => $start_time, 'end' => $end_time]
        ];

        $shift_times = $shift_presets[$shift_type] ?? $shift_presets['custom'];
        $final_start_time = $shift_times['start'];
        $final_end_time = $shift_times['end'];

        // Loop through each date
        while ($current_date <= $end_timestamp) {
            $date = date('Y-m-d', $current_date);
            $day_of_week = date('w', $current_date);
            
            // Skip weekends if not included
            if (!$include_weekends && ($day_of_week == 0 || $day_of_week == 6)) {
                $current_date = strtotime('+1 day', $current_date);
                continue;
            }

            // Loop through each selected employee
            foreach ($selected_employees as $employee_id) {
                // Check if shift already exists
                if ($skip_existing) {
                    $check_stmt = $connection->prepare(
                        "SELECT id FROM shifts WHERE employee_id = ? AND shift_date = ?"
                    );
                    $check_stmt->bind_param("is", $employee_id, $date);
                    $check_stmt->execute();
                    $check_stmt->store_result();
                    
                    if ($check_stmt->num_rows > 0) {
                        $shifts_skipped++;
                        $check_stmt->close();
                        continue;
                    }
                    $check_stmt->close();
                }

                // Insert shift
                $stmt = $connection->prepare(
                    "INSERT INTO shifts (employee_id, shift_date, shift_type, start_time, end_time, 
                                       location, notes, is_active, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())"
                );
                
                $stmt->bind_param("issssss", 
                    $employee_id, $date, $shift_type, $final_start_time, $final_end_time,
                    $location, $notes
                );
                
                if ($stmt->execute()) {
                    $shifts_created++;
                } else {
                    $errors_list[] = "Failed to create shift for employee ID $employee_id on $date: " . $connection->error;
                }
                $stmt->close();
            }

            $current_date = strtotime('+1 day', $current_date);
        }

        if ($shifts_created > 0) {
            $success = true;
            $bulk_results = [
                'created' => $shifts_created,
                'skipped' => $shifts_skipped,
                'errors' => $errors_list
            ];
        } else {
            $errors[] = "No shifts were created. " . implode(" ", $errors_list);
        }
    }
}

// Preset time settings for display
$shift_presets_display = [
    'morning' => ['start' => '06:00', 'end' => '14:00', 'name' => 'Morning Shift'],
    'afternoon' => ['start' => '14:00', 'end' => '22:00', 'name' => 'Afternoon Shift'],
    'evening' => ['start' => '16:00', 'end' => '00:00', 'name' => 'Evening Shift'],
    'night' => ['start' => '22:00', 'end' => '06:00', 'name' => 'Night Shift'],
    'custom' => ['start' => '09:00', 'end' => '17:00', 'name' => 'Custom Shift']
];
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Bulk Shift Assignment</h1>
            <div>
                <a href="shifts.php?source=add_shift" class="btn btn-outline-primary me-2">
                    <i class="bi bi-plus-circle"></i> Single Shift
                </a>
                <a href="shifts.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Shifts
                </a>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Bulk Assignment Instructions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li>Select date range for shift assignment</li>
                            <li>Choose one or multiple employees</li>
                            <li>Select shift type or set custom times</li>
                            <li>Weekends can be included or excluded</li>
                            <li>Option to skip dates with existing shifts</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> Maximum date range is 31 days. For longer periods, please assign in batches.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <h6 class="alert-heading"><i class="bi bi-exclamation-circle me-2"></i>Errors Found:</h6>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill display-6 me-3"></i>
                    <div>
                        <h4 class="alert-heading mb-1">Bulk Assignment Complete!</h4>
                        <p class="mb-0">
                            Successfully created <?php echo $bulk_results['created']; ?> shifts.
                            <?php if ($bulk_results['skipped'] > 0): ?>
                                Skipped <?php echo $bulk_results['skipped']; ?> existing shifts.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php if (!empty($bulk_results['errors'])): ?>
                    <hr>
                    <h6 class="alert-heading">Some errors occurred:</h6>
                    <ul class="mb-0 small">
                        <?php foreach (array_slice($bulk_results['errors'], 0, 5) as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                        <?php if (count($bulk_results['errors']) > 5): ?>
                            <li>... and <?php echo count($bulk_results['errors']) - 5; ?> more errors</li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Bulk Assignment Form -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-range me-2"></i>Bulk Shift Assignment Form</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="bulkShiftForm">
                    <!-- Date Range Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">1. Select Date Range</h6>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo htmlspecialchars($start_date); ?>" 
                                       min="<?php echo date('Y-m-d'); ?>" 
                                       max="<?php echo date('Y-m-d', strtotime('+3 months')); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date *</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?php echo htmlspecialchars($end_date); ?>" 
                                       min="<?php echo date('Y-m-d'); ?>" 
                                       max="<?php echo date('Y-m-d', strtotime('+3 months')); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_weekends" name="include_weekends" value="1" 
                                           <?php echo $include_weekends ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="include_weekends">
                                        Include Weekends
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Selection Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">2. Select Employees</h6>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Select employees to assign shifts:</span>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllEmployees()">
                                            <i class="bi bi-check-all"></i> Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllEmployees()">
                                            <i class="bi bi-x"></i> Deselect All
                                        </button>
                                    </div>
                                </div>
                                <div class="employee-list border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    <?php if ($employees_result && $employees_result->num_rows > 0): ?>
                                        <?php while ($emp = $employees_result->fetch_assoc()): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input employee-checkbox" 
                                                       type="checkbox" 
                                                       name="employees[]" 
                                                       value="<?php echo $emp['id']; ?>"
                                                       id="emp_<?php echo $emp['id']; ?>"
                                                       <?php echo in_array($emp['id'], (array)$selected_employees) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="emp_<?php echo $emp['id']; ?>">
                                                    <strong><?php echo htmlspecialchars($emp['full_name']); ?></strong>
                                                    <?php if ($emp['position']): ?>
                                                        - <?php echo htmlspecialchars($emp['position']); ?>
                                                    <?php endif; ?>
                                                    <?php if ($emp['department']): ?>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($emp['department']); ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($emp['employee_id']): ?>
                                                        <small class="text-muted">(ID: <?php echo htmlspecialchars($emp['employee_id']); ?>)</small>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="text-muted text-center py-3">
                                            <i class="bi bi-people display-6 d-block mb-2"></i>
                                            <p>No active employees found.</p>
                                            <a href="employees.php?source=add_employee" class="btn btn-sm btn-primary">
                                                Add Employee
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-text mt-2">
                                    <span id="selectedCount">0</span> employees selected
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shift Details Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">3. Shift Details</h6>
                        </div>
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
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time *</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" 
                                       value="<?php echo htmlspecialchars($shift_presets[$shift_type]['start'] ?? '09:00'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time *</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" 
                                       value="<?php echo htmlspecialchars($shift_presets[$shift_type]['end'] ?? '17:00'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="skip_existing" name="skip_existing" value="1" 
                                           <?php echo $skip_existing ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="skip_existing">
                                        Skip Existing
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label">Location *</label>
                                <select class="form-select" id="location" name="location" required>
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
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo htmlspecialchars($notes ?? ''); ?></textarea>
                                <div class="form-text">These notes will be applied to all assigned shifts</div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-eye me-2"></i>Assignment Preview</h6>
                                    <div id="previewContent" class="small">
                                        <?php
                                        $start_timestamp = strtotime($start_date);
                                        $end_timestamp = strtotime($end_date);
                                        $days_count = ceil(($end_timestamp - $start_timestamp) / (60 * 60 * 24)) + 1;
                                        $employee_count = count($selected_employees);
                                        $weekend_days = 0;
                                        
                                        if (!$include_weekends) {
                                            $temp_date = $start_timestamp;
                                            while ($temp_date <= $end_timestamp) {
                                                $day = date('w', $temp_date);
                                                if ($day == 0 || $day == 6) {
                                                    $weekend_days++;
                                                }
                                                $temp_date = strtotime('+1 day', $temp_date);
                                            }
                                            $days_count -= $weekend_days;
                                        }
                                        
                                        $total_shifts = $days_count * $employee_count;
                                        ?>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Date Range:</strong> <?php echo date('M d, Y', $start_timestamp); ?> - <?php echo date('M d, Y', $end_timestamp); ?>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>Days:</strong> <?php echo $days_count; ?> days
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Employees:</strong> <?php echo $employee_count; ?> selected
                                            </div>
                                            <div class="col-md-2">
                                                <strong>Total Shifts:</strong> <?php echo $total_shifts; ?>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>Duration:</strong> 
                                                <?php 
                                                $display_start = $shift_presets_display[$shift_type]['start'] ?? '09:00';
                                                $display_end = $shift_presets_display[$shift_type]['end'] ?? '17:00';
                                                echo $display_start . ' - ' . $display_end; 
                                                ?>
                                            </div>
                                        </div>
                                        <?php if (!$include_weekends && $weekend_days > 0): ?>
                                            <div class="mt-2 text-muted">
                                                <i class="bi bi-info-circle"></i> Excluding <?php echo $weekend_days; ?> weekend day(s)
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-outline-secondary" onclick="window.history.back();">
                                Cancel
                            </button>
                        </div>
                        <div>
                            <button type="reset" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-all me-2"></i>Assign Shifts
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Update selected employees count
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.employee-checkbox:checked');
    document.getElementById('selectedCount').innerText = checkboxes.length;
}

// Select all employees
function selectAllEmployees() {
    document.querySelectorAll('.employee-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectedCount();
}

// Deselect all employees
function deselectAllEmployees() {
    document.querySelectorAll('.employee-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelectedCount();
}

// Update time fields based on shift type
document.getElementById('shift_type').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const startTime = selected.dataset.start;
    const endTime = selected.dataset.end;
    
    if (startTime && endTime) {
        document.getElementById('start_time').value = startTime;
        document.getElementById('end_time').value = endTime;
    }
});

// Validate date range
function validateDateRange() {
    const startDate = new Date(document.getElementById('start_date').value);
    const endDate = new Date(document.getElementById('end_date').value);
    const diffTime = Math.abs(endDate - startDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays > 31) {
        alert('Warning: Date range exceeds 31 days. For better performance, please assign shifts in smaller batches.');
    }
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Initialize selected count
    updateSelectedCount();
    
    // Add change listener to all checkboxes
    document.querySelectorAll('.employee-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    // Add date validation
    document.getElementById('start_date').addEventListener('change', validateDateRange);
    document.getElementById('end_date').addEventListener('change', validateDateRange);
    
    // Form submit confirmation
    document.getElementById('bulkShiftForm').addEventListener('submit', function(e) {
        const employeeCount = document.querySelectorAll('.employee-checkbox:checked').length;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (employeeCount === 0) {
            e.preventDefault();
            alert('Please select at least one employee.');
            return;
        }
        
        if (!confirm(`Are you sure you want to create shifts for ${employeeCount} employee(s) from ${startDate} to ${endDate}?`)) {
            e.preventDefault();
        }
    });
});

// Update preview when form changes
function updatePreview() {
    // You can implement AJAX call here to update preview dynamically
    // For now, we'll just reload the page with selected parameters
}
</script>