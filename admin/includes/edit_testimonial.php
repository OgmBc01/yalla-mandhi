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

// Get testimonial ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: testimonials.php");
    exit();
}

$testimonial_id = (int)$_GET['id'];

// Fetch testimonial data
$stmt = $connection->prepare("SELECT * FROM testimonials WHERE id = ?");
$stmt->bind_param("i", $testimonial_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: testimonials.php");
    exit();
}

$testimonial = $result->fetch_assoc();
$stmt->close();

$errors = [];
$current_image = $testimonial['customer_image'] ?? '';
$success_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Validate inputs
    $customer_name = trim($_POST['customer_name'] ?? '');
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review = trim($_POST['review'] ?? '');
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;
    $remove_image = isset($_POST['remove_image']) ? 1 : 0;
    
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
    $customer_image = $current_image;
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
            
            // Delete old image if exists
            if ($current_image && file_exists($upload_dir . $current_image)) {
                @unlink($upload_dir . $current_image);
            }
            
            $customer_image = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $customer_image;
            
            if (!move_uploaded_file($_FILES['customer_image']['tmp_name'], $target_file)) {
                $errors[] = "Failed to upload image";
                $customer_image = $current_image; // Keep old image if upload fails
            }
        }
    } elseif ($remove_image && $current_image) {
        // Remove existing image
        $upload_dir = __DIR__ . '/../../uploads/testimonials/';
        if (file_exists($upload_dir . $current_image)) {
            @unlink($upload_dir . $current_image);
        }
        $customer_image = '';
    }
    
    // Permission check for employees
    if (($_SESSION['role'] ?? '') === 'employee' && $testimonial['is_approved'] != $is_approved) {
        $errors[] = "Employees cannot change approval status";
    }
    
    if (empty($errors)) {
        // Update testimonial
        $stmt = $connection->prepare(
            "UPDATE testimonials SET 
                customer_name = ?, 
                customer_image = ?, 
                rating = ?, 
                review = ?, 
                is_approved = ? 
             WHERE id = ?"
        );
        $stmt->bind_param("ssisii", 
            $customer_name, $customer_image, $rating, $review, $is_approved, $testimonial_id
        );
        
        if ($stmt->execute()) {
            $stmt->close();
            $success_message = "Testimonial updated successfully";
            
            // Update the testimonial data with new values
            $testimonial['customer_name'] = $customer_name;
            $testimonial['rating'] = $rating;
            $testimonial['review'] = $review;
            $testimonial['is_approved'] = $is_approved;
            $testimonial['customer_image'] = $customer_image;
            $current_image = $customer_image;
        } else {
            $errors[] = "Failed to update testimonial: " . $connection->error;
        }
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Edit Testimonial</h1>
            <a href="testimonials.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Testimonials
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-chat-square-text me-2"></i>Edit Testimonial Information</h5>
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
                
                <form method="POST" action="" enctype="multipart/form-data" id="editTestimonialForm">
                    <input type="hidden" name="id" value="<?php echo $testimonial_id; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Customer Name *</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                       value="<?php echo htmlspecialchars($testimonial['customer_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating *</label>
                                <div class="rating-input">
                                    <div class="d-flex align-items-center">
                                        <?php 
                                        $selected_rating = $testimonial['rating'] ?? 5;
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
                        <textarea class="form-control" id="review" name="review" rows="5" required><?php echo htmlspecialchars($testimonial['review']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_image" class="form-label">Customer Image</label>
                                <input type="file" class="form-control" id="customer_image" name="customer_image" 
                                       accept="image/*">
                                <div class="form-text">Max file size: 5MB. Allowed: JPG, PNG, GIF</div>
                                
                                <!-- New image preview -->
                                <div id="newImagePreview" class="mt-2" style="display: none;">
                                    <p class="text-success mb-1"><i class="bi bi-info-circle"></i> New image selected:</p>
                                    <img src="" alt="Preview" class="img-thumbnail" style="max-width: 150px;">
                                </div>
                                
                                <!-- Current image display -->
                                <?php if ($current_image): ?>
                                    <div class="mt-2">
                                        <p class="text-muted mb-1">Current image:</p>
                                        <img src="../uploads/testimonials/<?php echo htmlspecialchars($current_image); ?>" 
                                             alt="Current Image" class="img-thumbnail" style="max-width: 150px;"
                                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($testimonial['customer_name']); ?>&background=random'">
                                        <div class="mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                                <label class="form-check-label" for="remove_image">
                                                    Remove current image
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Approval Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" value="1" 
                                           <?php echo $testimonial['is_approved'] ? 'checked' : ''; ?>
                                           <?php echo ($_SESSION['role'] ?? '') === 'employee' ? 'disabled' : ''; ?>>
                                    <label class="form-check-label" for="is_approved">
                                        Approved (visible to public)
                                    </label>
                                </div>
                                <?php if (($_SESSION['role'] ?? '') === 'employee'): ?>
                                    <div class="alert alert-info mt-2">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Approval status can only be changed by admin.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="testimonials.php?source=view_testimonial&id=<?php echo $testimonial_id; ?>" 
                               class="btn btn-outline-info">
                                <i class="bi bi-eye me-2"></i>View Details
                            </a>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="testimonials.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Update Testimonial
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
                <h4 class="my-3">Testimonial Updated Successfully!</h4>
                <p>The testimonial has been updated in the database.</p>
            </div>
            <div class="modal-footer">
                <a href="testimonials.php" class="btn btn-secondary">View All Testimonials</a>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Continue Editing</button>
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
// New image preview functionality
document.getElementById('customer_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('newImagePreview');
    const img = preview.querySelector('img');
    const removeCheckbox = document.getElementById('remove_image');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.style.display = 'block';
            
            // Uncheck remove image checkbox if new image is selected
            if (removeCheckbox) {
                removeCheckbox.checked = false;
            }
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

// Remove image checkbox logic
const removeCheckbox = document.getElementById('remove_image');
if (removeCheckbox) {
    removeCheckbox.addEventListener('change', function() {
        const fileInput = document.getElementById('customer_image');
        const preview = document.getElementById('newImagePreview');
        
        if (this.checked) {
            // Clear file input and hide preview
            fileInput.value = '';
            preview.style.display = 'none';
        }
    });
}

// File input change should uncheck remove image
document.getElementById('customer_image').addEventListener('change', function() {
    const removeCheckbox = document.getElementById('remove_image');
    if (removeCheckbox && this.value) {
        removeCheckbox.checked = false;
    }
});
</script>