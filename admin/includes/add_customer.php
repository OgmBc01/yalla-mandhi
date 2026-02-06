<?php
// Initialize variables
$full_name = $username = $email = $phone = $address = '';
$is_active = 1;
$loyalty_points = 0;
$preferred_branch = 1;
$errors = [];
$success = false;
$temp_password = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $loyalty_points = intval($_POST['loyalty_points'] ?? 0);
    $preferred_branch = intval($_POST['preferred_branch'] ?? 1);

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

    if (empty($errors)) {
        // Insert customer
        $stmt = $connection->prepare(
            "INSERT INTO users (full_name, username, email, password_hash, phone, address, 
                              role, is_active, loyalty_points, preferred_branch, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, 'customer', ?, ?, ?, NOW())"
        );
        $stmt->bind_param("ssssssiii", 
            $full_name, $username, $email, $password_hash, $phone, $address,
            $is_active, $loyalty_points, $preferred_branch
        );
        if ($stmt->execute()) {
            $customer_id = $stmt->insert_id;
            $stmt->close();
            $success = true;
            // Clear form fields
            $full_name = $username = $email = $phone = $address = '';
            $is_active = 1;
            $loyalty_points = 0;
            $preferred_branch = 1;
        } else {
            $errors[] = "Failed to add customer: " . $connection->error;
        }
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Add New Customer</h1>
            <a href="customers.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Customers
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Customer Information</h5>
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

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="loyalty_points" class="form-label">Initial Loyalty Points</label>
                                <input type="number" class="form-control" id="loyalty_points" name="loyalty_points" 
                                       value="<?php echo $loyalty_points; ?>" min="0">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="preferred_branch" class="form-label">Preferred Branch</label>
                                <select class="form-select" id="preferred_branch" name="preferred_branch">
                                    <?php
                                    $branches = $connection->query("SELECT id, name FROM branches WHERE is_active = 1");
                                    if ($branches && $branches->num_rows > 0) {
                                        while ($branch = $branches->fetch_assoc()) {
                                            $selected = ($preferred_branch ?? 1) == $branch['id'] ? 'selected' : '';
                                            echo "<option value='{$branch['id']}' $selected>{$branch['name']}</option>";
                                        }
                                    } else {
                                        echo "<option value='1'>Main Branch</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
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

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        A temporary password will be generated and can be reset by the customer.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="customers.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Add Customer
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
                <h4 class="my-3">Customer Added Successfully!</h4>
                <p>The new customer has been added to the database.</p>
                <?php if (!empty($temp_password)): ?>
                <div class="alert alert-info mt-3">
                    <strong>Temporary Password:</strong> <?php echo htmlspecialchars($temp_password); ?><br>
                    Please provide this password to the customer.
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <a href="customers.php" class="btn btn-secondary">View All Customers</a>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Add Another Customer</button>
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