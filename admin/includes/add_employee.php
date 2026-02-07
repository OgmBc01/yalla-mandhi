<?php
// Initialize variables

$full_name = $username = $email = $phone = $address = $employee_id = $position = $department = '';
$salary = 0;
$hire_date = date('Y-m-d');
$is_active = 1;
$errors = [];
$success = false;
$temp_password = '';
$role = 'employee'; // Default role for GET requests

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $employee_id = trim($_POST['employee_id'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $salary = floatval($_POST['salary'] ?? 0);
    $hire_date = trim($_POST['hire_date'] ?? date('Y-m-d'));
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $role = trim($_POST['role'] ?? 'employee');

    // Generate a random password
    $temp_password = bin2hex(random_bytes(8)); // 16 character password
    $password_hash = password_hash($temp_password, PASSWORD_DEFAULT);

    // Check if username already exists
    $check_username = $connection->prepare("SELECT id FROM users WHERE username = ?");
    $check_username->bind_param("s", $username);
    $check_username->execute();
    $check_username->store_result();
    if ($check_username->num_rows > 0) {
        $errors[] = "Username already exists";
    }
    $check_username->close();

    // Check if email already exists
    $check_email = $connection->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();
    if ($check_email->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    $check_email->close();

    // Check if employee ID already exists (if provided)
    if (!empty($employee_id)) {
        $check_emp_id = $connection->prepare("SELECT id FROM users WHERE employee_id = ?");
        $check_emp_id->bind_param("s", $employee_id);
        $check_emp_id->execute();
        $check_emp_id->store_result();
        if ($check_emp_id->num_rows > 0) {
            $errors[] = "Employee ID already exists";
        }
        $check_emp_id->close();
    }

    if (empty($errors)) {
        // Insert employee
        $stmt = $connection->prepare(
            "INSERT INTO users (full_name, username, email, password_hash, phone, address, 
                              role, employee_id, position, department, salary, hire_date, 
                              is_active, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        
        // FIXED: Correct type string - we have 13 parameters:
        // 12 strings + 1 integer (is_active)
        // Note: salary is float, but MySQLi treats it as string in bind_param
        $stmt->bind_param("ssssssssssdsi", 
            $full_name, $username, $email, $password_hash, $phone, $address,
            $role, $employee_id, $position, $department, $salary, $hire_date, $is_active
        );
        
        if ($stmt->execute()) {
            $new_employee_id = $stmt->insert_id;
            $stmt->close();
            $success = true;
        } else {
            $errors[] = "Failed to add employee: " . $connection->error;
        }
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Add New Employee</h1>
            <a href="employees.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Employees
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Employee Information</h5>
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
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($full_name); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($username); ?>" required>
                                <div class="form-text">Must be unique</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($phone); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="employee_id" name="employee_id" 
                                       value="<?php echo htmlspecialchars($employee_id); ?>">
                                <div class="form-text">Leave blank for auto-generation</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hire_date" class="form-label">Hire Date</label>
                                <input type="date" class="form-control" id="hire_date" name="hire_date" 
                                       value="<?php echo htmlspecialchars($hire_date); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="position" name="position" 
                                       value="<?php echo htmlspecialchars($position); ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department">
                                    <option value="">Select Department</option>
                                    <option value="Administration" <?php echo $department == 'Administration' ? 'selected' : ''; ?>>Administration</option>
                                    <option value="Kitchen" <?php echo $department == 'Kitchen' ? 'selected' : ''; ?>>Kitchen</option>
                                    <option value="Service" <?php echo $department == 'Service' ? 'selected' : ''; ?>>Service</option>
                                    <option value="Delivery" <?php echo $department == 'Delivery' ? 'selected' : ''; ?>>Delivery</option>
                                    <option value="Management" <?php echo $department == 'Management' ? 'selected' : ''; ?>>Management</option>
                                    <option value="Marketing" <?php echo $department == 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                                    <option value="Other" <?php echo $department == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="employee" <?php echo $role == 'employee' ? 'selected' : ''; ?>>Employee</option>
                                    <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="super-admin" <?php echo $role == 'super-admin' ? 'selected' : ''; ?>>Super Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="salary" class="form-label">Salary (AED)</label>
                                <input type="number" class="form-control" id="salary" name="salary" 
                                       value="<?php echo $salary; ?>" min="0" step="0.01">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           <?php echo $is_active == 1 ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active Account
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        A temporary password will be generated and can be reset by the employee.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="employees.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Add Employee
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
                <h4 class="my-3">Employee Added Successfully!</h4>
                <p>The new employee has been added to the database.</p>
                <?php if (!empty($temp_password)): ?>
                <div class="alert alert-warning mt-3">
                    <strong>Temporary Password:</strong> <?php echo htmlspecialchars($temp_password); ?><br>
                    <small>Please provide this password to the employee securely.</small>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <a href="employees.php" class="btn btn-secondary">View All Employees</a>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Add Another Employee</button>
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