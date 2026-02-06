<?php

// Get customer ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: customers.php");
    exit();
}

$customer_id = (int)$_GET['id'];

// Fetch customer data
$stmt = $connection->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: customers.php");
    exit();
}

$customer = $result->fetch_assoc();
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
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $loyalty_points = intval($_POST['loyalty_points'] ?? 0);
    $preferred_branch = intval($_POST['preferred_branch'] ?? 1);
    
    // Check if username already exists (excluding current customer)
    $check_username = $connection->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $check_username->bind_param("si", $username, $customer_id);
    $check_username->execute();
    $check_username->store_result();
    
    if ($check_username->num_rows > 0) {
        $errors[] = "Username already exists";
    }
    $check_username->close();
    
    // Check if email already exists (excluding current customer)
    $check_email = $connection->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_email->bind_param("si", $email, $customer_id);
    $check_email->execute();
    $check_email->store_result();
    
    if ($check_email->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    $check_email->close();
    
    if (empty($errors)) {
        // Update customer
        $stmt = $connection->prepare(
            "UPDATE users SET 
                full_name = ?, 
                username = ?, 
                email = ?, 
                phone = ?, 
                address = ?, 
                is_active = ?, 
                loyalty_points = ?, 
                preferred_branch = ?,
                updated_at = NOW()
             WHERE id = ?"
        );
        $stmt->bind_param("sssssiiii", 
            $full_name, $username, $email, $phone, $address,
            $is_active, $loyalty_points, $preferred_branch, $customer_id
        );
        if ($stmt->execute()) {
            // Update $customer array for form re-population
            $customer['full_name'] = $full_name;
            $customer['username'] = $username;
            $customer['email'] = $email;
            $customer['phone'] = $phone;
            $customer['address'] = $address;
            $customer['is_active'] = $is_active;
            $customer['loyalty_points'] = $loyalty_points;
            $customer['preferred_branch'] = $preferred_branch;
            // Show success modal (structure and JS like edit_reservation.php)
            echo "\n<!-- Success Modal -->\n";
            echo '<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">';
            echo '  <div class="modal-dialog modal-dialog-centered">';
            echo '    <div class="modal-content">';
            echo '      <div class="modal-header bg-success text-white">';
            echo '        <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success</h5>';
            echo '        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>';
            echo '      </div>';
            echo '      <div class="modal-body text-center py-4">';
            echo '        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>';
            echo '        <h4 class="my-3">Customer Updated Successfully!</h4>';
            echo '        <p>The customer information has been updated in the database.</p>';
            echo '      </div>';
            echo '      <div class="modal-footer">';
            echo '        <a href="customers.php" class="btn btn-secondary">View All Customers</a>';
            echo '        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Continue Editing</button>';
            echo '      </div>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
            echo '<script>window.addEventListener(\'load\',function(){var m=new bootstrap.Modal(document.getElementById(\'successModal\'));m.show();});</script>';
        } else {
            $errors[] = "Failed to update customer: " . $connection->error;
        }
        $stmt->close();
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Edit Customer</h1>
            <a href="customers.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Customers
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Edit Customer Information</h5>
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
                                       value="<?php echo htmlspecialchars($customer['full_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($customer['username']); ?>" required>
                                <div class="form-text">Must be unique</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="loyalty_points" class="form-label">Loyalty Points</label>
                                <input type="number" class="form-control" id="loyalty_points" name="loyalty_points" 
                                       value="<?php echo $customer['loyalty_points']; ?>" min="0">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="preferred_branch" class="form-label">Preferred Branch</label>
                                <select class="form-select" id="preferred_branch" name="preferred_branch">
                                    <?php
                                    $branches = $connection->query("SELECT id, name FROM branches WHERE is_active = 1");
                                    while ($branch = $branches->fetch_assoc()) {
                                        $selected = $customer['preferred_branch'] == $branch['id'] ? 'selected' : '';
                                        echo "<option value='{$branch['id']}' $selected>{$branch['name']}</option>";
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
                                           <?php echo $customer['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active Account
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="customers.php?source=view_customer&id=<?php echo $customer_id; ?>" 
                               class="btn btn-outline-info">
                                <i class="bi bi-eye me-2"></i>View Details
                            </a>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="customers.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Update Customer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>