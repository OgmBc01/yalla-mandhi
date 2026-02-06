<?php
// Start output buffering at the beginning
// No output buffering needed; use modal for success feedback

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin'])) {
    header("Location: login.php");
    exit();
}

$errors = [];
$success_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate inputs
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $opening_hours = trim($_POST['opening_hours'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Basic validation
    if (empty($name)) {
        $errors[] = "Branch name is required";
    }
    if (empty($address)) {
        $errors[] = "Branch address is required";
    }
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Check if branch name already exists
    if (empty($errors)) {
        $check_stmt = $connection->prepare("SELECT id FROM branches WHERE name = ?");
        $check_stmt->bind_param("s", $name);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows > 0) {
            $errors[] = "A branch with this name already exists";
        }
        $check_stmt->close();
    }

    if (empty($errors)) {
        // Insert branch
        $stmt = $connection->prepare(
            "INSERT INTO branches (name, address, phone, email, opening_hours, is_active) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssssi", $name, $address, $phone, $email, $opening_hours, $is_active);
        if ($stmt->execute()) {
            $stmt->close();
            $success_message = "Branch added successfully";
            // Clear form fields
            $_POST = [];
        } else {
            $errors[] = "Failed to add branch: " . $connection->error;
        }
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Add New Branch</h1>
            <a href="branches.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Branches
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-building-add me-2"></i>Branch Information</h5>
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
                                <label for="name" class="form-label">Branch Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           <?php echo isset($_POST['is_active']) ? 'checked' : 'checked'; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active Branch
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="opening_hours" class="form-label">Opening Hours</label>
                        <textarea class="form-control" id="opening_hours" name="opening_hours" rows="3" 
                                  placeholder="e.g., Monday-Friday: 9:00 AM - 6:00 PM&#10;Saturday: 10:00 AM - 4:00 PM&#10;Sunday: Closed"><?php echo htmlspecialchars($_POST['opening_hours'] ?? ''); ?></textarea>
                        <div class="form-text">Enter each schedule on a new line</div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="branches.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Add Branch
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
                <h4 class="my-3">Branch Added Successfully!</h4>
                <p>The new branch has been added to the database.</p>
            </div>
            <div class="modal-footer">
                <a href="branches.php" class="btn btn-secondary">View All Branches</a>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Add Another Branch</button>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($success_message)): ?>
<script>
    window.addEventListener('load', function() {
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    });
</script>
<?php endif; ?>