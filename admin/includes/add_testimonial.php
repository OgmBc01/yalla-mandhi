<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'employee'])) {
    header("Location: login.php");
    exit();
}

$errors = [];
$success_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Validate inputs
    $customer_name = trim($_POST['customer_name'] ?? '');
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review = trim($_POST['review'] ?? '');
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;
    
    // Basic validation
    if (empty($customer_name)) {
        $errors[] = "Customer name is required";
    }
    
    if ($rating < 1 || $rating > 5) {
        $errors[] = "Rating must be between 1 and 5";
    }
    
    if (empty($review)) {
        $errors[] = "Review text is required";
    }
    
    // Handle file upload
    $customer_image = '';
    if (isset($_FILES['customer_image']) && $_FILES['customer_image']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['customer_image']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed";
        }
        
        if ($_FILES['customer_image']['size'] > 5 * 1024 * 1024) { // 5MB
            $errors[] = "File size must be less than 5MB";
        }
        
        if (empty($errors)) {
            $upload_dir = __DIR__ . '/../../uploads/testimonials/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $customer_image = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $customer_image;
            
            if (!move_uploaded_file($_FILES['customer_image']['tmp_name'], $target_file)) {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    if (empty($errors)) {
        // Insert testimonial
        $stmt = $connection->prepare(
            "INSERT INTO testimonials (customer_name, customer_image, rating, review, is_approved) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssisi", $customer_name, $customer_image, $rating, $review, $is_approved);
        
        if ($stmt->execute()) {
            $stmt->close();
            $success_message = "Testimonial added successfully";
            // Clear form fields
            $_POST = [];
            $_FILES = [];
        } else {
            $errors[] = "Failed to add testimonial: " . $connection->error;
        }
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Add New Testimonial</h1>
            <a href="testimonials.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Testimonials
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-chat-square-quote me-2"></i>Testimonial Information</h5>
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
                
                <form method="POST" action="" enctype="multipart/form-data" id="testimonialForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Customer Name *</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                       value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating *</label>
                                <div class="rating-input">
                                    <div class="d-flex align-items-center">
                                        <?php 
                                        $selected_rating = $_POST['rating'] ?? 5;
                                        for ($i = 5; $i >= 1; $i--): 
                                        ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="rating" id="rating<?php echo $i; ?>" 
                                                       value="<?php echo $i; ?>" <?php echo $selected_rating == $i ? 'checked' : ''; ?> required>
                                                <label class="form-check-label" for="rating<?php echo $i; ?>">
                                                    <?php for ($j = 1; $j <= $i; $j++): ?>
                                                        <i class="bi bi-star-fill text-warning"></i>
                                                    <?php endfor; ?>
                                                    <?php for ($j = $i + 1; $j <= 5; $j++): ?>
                                                        <i class="bi bi-star text-muted"></i>
                                                    <?php endfor; ?>
                                                    <span class="ms-1">(<?php echo $i; ?>)</span>
                                                </label>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="review" class="form-label">Review *</label>
                        <textarea class="form-control" id="review" name="review" rows="5" required
                                  placeholder="Write the customer's testimonial here..."><?php echo htmlspecialchars($_POST['review'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_image" class="form-label">Customer Image</label>
                                <input type="file" class="form-control" id="customer_image" name="customer_image" 
                                       accept="image/*">
                                <div class="form-text">Max file size: 5MB. Allowed: JPG, PNG, GIF</div>
                                
                                <!-- Image preview container -->
                                <div id="imagePreview" class="mt-2" style="display: none;">
                                    <img src="" alt="Preview" class="img-thumbnail" style="max-width: 150px;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Approval Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" value="1" 
                                           <?php 
                                           $is_approved_checked = $_POST['is_approved'] ?? '';
                                           echo ($is_approved_checked === '1' || (empty($is_approved_checked) && in_array($_SESSION['role'] ?? '', ['admin', 'super-admin']))) ? 'checked' : ''; 
                                           ?>>
                                    <label class="form-check-label" for="is_approved">
                                        Approved (visible to public)
                                    </label>
                                </div>
                                <?php if (in_array($_SESSION['role'] ?? '', ['employee'])): ?>
                                    <div class="alert alert-info mt-2">
                                        <i class="bi bi-info-circle me-2"></i>
                                        As an employee, you can add testimonials but they require admin approval.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="testimonials.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Add Testimonial
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
                <h4 class="my-3">Testimonial Added Successfully!</h4>
                <p>The new testimonial has been added to the database.</p>
            </div>
            <div class="modal-footer">
                <a href="testimonials.php" class="btn btn-secondary">View All Testimonials</a>
                <button type="button" class="btn btn-success" onclick="resetForm()" data-bs-dismiss="modal">Add Another Testimonial</button>
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

<script>
// Image preview functionality
document.getElementById('customer_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    const img = preview.querySelector('img');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

// Form reset function
function resetForm() {
    document.getElementById('testimonialForm').reset();
    document.getElementById('imagePreview').style.display = 'none';
    // Reset radio buttons to default (5 stars)
    document.getElementById('rating5').checked = true;
}
</script>