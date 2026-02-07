<?php
// Get employee ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: employees.php");
    exit();
}

$employee_id = (int)$_GET['id'];

// Fetch employee data
$stmt = $connection->prepare("SELECT * FROM users WHERE id = ? AND role IN ('employee', 'admin', 'super-admin')");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: employees.php");
    exit();
}

$employee = $result->fetch_assoc();
$stmt->close();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    // Validate inputs
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $employee_id_num = trim($_POST['employee_id'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $salary = floatval($_POST['salary'] ?? 0);
    $hire_date = trim($_POST['hire_date'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $role = trim($_POST['role'] ?? 'employee');
    
    // Check if username already exists (excluding current employee)
    $check_username = $connection->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $check_username->bind_param("si", $username, $employee_id);
    $check_username->execute();
    $check_username->store_result();
    
    if ($check_username->num_rows > 0) {
        $errors[] = "Username already exists";
    }
    $check_username->close();
    
    // Check if email already exists (excluding current employee)
    $check_email = $connection->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_email->bind_param("si", $email, $employee_id);
    $check_email->execute();
    $check_email->store_result();
    
    if ($check_email->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    $check_email->close();
    
    // Check if employee ID already exists (excluding current employee)
    if (!empty($employee_id_num)) {
        $check_emp_id = $connection->prepare("SELECT id FROM users WHERE employee_id = ? AND id != ?");
        $check_emp_id->bind_param("si", $employee_id_num, $employee_id);
        $check_emp_id->execute();
        $check_emp_id->store_result();
        
        if ($check_emp_id->num_rows > 0) {
            $errors[] = "Employee ID already exists";
        }
        $check_emp_id->close();
    }
    
    if (empty($errors)) {
        // Update employee
        $stmt = $connection->prepare(
            "UPDATE users SET 
                full_name = ?, 
                username = ?, 
                email = ?, 
                phone = ?, 
                address = ?, 
                employee_id = ?,
                position = ?,
                department = ?,
                salary = ?,
                hire_date = ?,
                is_active = ?,
                role = ?,
                updated_at = NOW()
             WHERE id = ?"
        );
        
        // FIXED: The type string was incorrect. We have 13 parameters:
        // Breakdown: 
        // 1. $full_name (s)
        // 2. $username (s)
        // 3. $email (s)
        // 4. $phone (s)
        // 5. $address (s)
        // 6. $employee_id_num (s)
        // 7. $position (s)
        // 8. $department (s)
        // 9. $salary (d) - float/double
        // 10. $hire_date (s)
        // 11. $is_active (i) - integer
        // 12. $role (s)
        // 13. $employee_id (i) - integer (WHERE clause)
        
        // So the type string should be: "ssssssssdsisi"
        $stmt->bind_param("ssssssssdsisi", 
            $full_name, $username, $email, $phone, $address,
            $employee_id_num, $position, $department, $salary, $hire_date,
            $is_active, $role, $employee_id
        );
        
        if ($stmt->execute()) {
            // Update $employee array for form re-population
            $employee['full_name'] = $full_name;
            $employee['username'] = $username;
            $employee['email'] = $email;
            $employee['phone'] = $phone;
            $employee['address'] = $address;
            $employee['employee_id'] = $employee_id_num;
            $employee['position'] = $position;
            $employee['department'] = $department;
            $employee['salary'] = $salary;
            $employee['hire_date'] = $hire_date;
            $employee['is_active'] = $is_active;
            $employee['role'] = $role;
            
            // Show success modal
            $show_success_modal = true;
        } else {
            $errors[] = "Failed to update employee: " . $connection->error;
        }
        $stmt->close();
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Edit Employee</h1>
            <a href="employees.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Employees
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Edit Employee Information</h5>
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
                        <i class="bi bi-check-circle-fill me-2"></i>Employee updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($employee['full_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($employee['username']); ?>" required>
                                <div class="form-text">Must be unique</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($employee['phone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="employee_id" name="employee_id" 
                                       value="<?php echo htmlspecialchars($employee['employee_id'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hire_date" class="form-label">Hire Date</label>
                                <input type="date" class="form-control" id="hire_date" name="hire_date" 
                                       value="<?php echo htmlspecialchars($employee['hire_date'] ?? date('Y-m-d')); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="position" name="position" 
                                       value="<?php echo htmlspecialchars($employee['position'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department">
                                    <option value="">Select Department</option>
                                    <option value="Administration" <?php echo ($employee['department'] ?? '') == 'Administration' ? 'selected' : ''; ?>>Administration</option>
                                    <option value="Kitchen" <?php echo ($employee['department'] ?? '') == 'Kitchen' ? 'selected' : ''; ?>>Kitchen</option>
                                    <option value="Service" <?php echo ($employee['department'] ?? '') == 'Service' ? 'selected' : ''; ?>>Service</option>
                                    <option value="Delivery" <?php echo ($employee['department'] ?? '') == 'Delivery' ? 'selected' : ''; ?>>Delivery</option>
                                    <option value="Management" <?php echo ($employee['department'] ?? '') == 'Management' ? 'selected' : ''; ?>>Management</option>
                                    <option value="Marketing" <?php echo ($employee['department'] ?? '') == 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                                    <option value="Other" <?php echo ($employee['department'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="employee" <?php echo ($employee['role'] ?? '') == 'employee' ? 'selected' : ''; ?>>Employee</option>
                                    <option value="admin" <?php echo ($employee['role'] ?? '') == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="super-admin" <?php echo ($employee['role'] ?? '') == 'super-admin' ? 'selected' : ''; ?>>Super Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="salary" class="form-label">Salary (AED)</label>
                                <input type="number" class="form-control" id="salary" name="salary" 
                                       value="<?php echo $employee['salary'] ?? 0; ?>" min="0" step="0.01">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           <?php echo $employee['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active Account
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($employee['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="employees.php?source=view_employee&id=<?php echo $employee_id; ?>" 
                               class="btn btn-outline-info">
                                <i class="bi bi-eye me-2"></i>View Details
                            </a>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="employees.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Update Employee
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
                <h4 class="my-3">Employee Updated Successfully!</h4>
                <p>The employee information has been updated.</p>
            </div>
            <div class="modal-footer">
                <a href="employees.php" class="btn btn-secondary">View All Employees</a>
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