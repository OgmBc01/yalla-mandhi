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
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $badge_text = trim($_POST['badge_text'] ?? 'Limited Offer');
    $badge_color = trim($_POST['badge_color'] ?? 'var(--color-red)');
    
    // Pricing
    $original_price = isset($_POST['original_price']) && $_POST['original_price'] !== '' ? floatval($_POST['original_price']) : null;
    $offer_price = isset($_POST['offer_price']) ? floatval($_POST['offer_price']) : 0;
    $discount_percent = isset($_POST['discount_percent']) && $_POST['discount_percent'] !== '' ? floatval($_POST['discount_percent']) : null;
    $currency = trim($_POST['currency'] ?? 'AED');
    
    // Details
    $valid_from = !empty($_POST['valid_from']) ? $_POST['valid_from'] : null;
    $valid_until = !empty($_POST['valid_until']) ? $_POST['valid_until'] : null;
    $time_slot = trim($_POST['time_slot'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $min_persons = isset($_POST['min_persons']) && $_POST['min_persons'] !== '' ? intval($_POST['min_persons']) : null;
    $max_persons = isset($_POST['max_persons']) && $_POST['max_persons'] !== '' ? intval($_POST['max_persons']) : null;
    $min_order_amount = isset($_POST['min_order_amount']) && $_POST['min_order_amount'] !== '' ? floatval($_POST['min_order_amount']) : null;
    
    // Offer type
    $offer_type = trim($_POST['offer_type'] ?? 'other');
    
    // Status
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_highlighted = isset($_POST['is_highlighted']) ? 1 : 0;
    
    // CTA
    $cta_text = trim($_POST['cta_text'] ?? 'Book Now');
    $cta_link = trim($_POST['cta_link'] ?? 'contact.php');
    $cta_icon = trim($_POST['cta_icon'] ?? 'bi-calendar-check');
    
    // Display order
    $display_order = isset($_POST['display_order']) ? intval($_POST['display_order']) : 0;
    
    // Basic validation
    if (empty($title)) {
        $errors[] = "Promotion title is required";
    }
    
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    
    if ($offer_price <= 0) {
        $errors[] = "Offer price must be greater than 0";
    }
    
    if ($original_price !== null && $original_price <= 0) {
        $errors[] = "Original price must be greater than 0 if provided";
    }
    
    if ($discount_percent !== null && ($discount_percent < 0 || $discount_percent > 100)) {
        $errors[] = "Discount percentage must be between 0 and 100";
    }
    
    // Handle file upload
    $image_url = '';
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($_FILES['image_url']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Only JPG, JPEG, PNG, GIF, and WebP files are allowed";
        }
        
        if ($_FILES['image_url']['size'] > 10 * 1024 * 1024) { // 10MB
            $errors[] = "File size must be less than 10MB";
        }
        
        if (empty($errors)) {
            $upload_dir = __DIR__ . '/../../uploads/promotions/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $image_url = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $image_url;
            
            if (!move_uploaded_file($_FILES['image_url']['tmp_name'], $target_file)) {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    // Auto-calculate discount percentage if not provided
    if ($original_price !== null && $offer_price > 0 && $discount_percent === null) {
        $discount_percent = round((($original_price - $offer_price) / $original_price) * 100, 2);
    }
    
    if (empty($errors)) {
        // Insert promotion
        $stmt = $connection->prepare(
            "INSERT INTO promotions (
                title, subtitle, description, short_description, badge_text, badge_color, image_url,
                original_price, offer_price, discount_percent, currency, valid_from, valid_until,
                time_slot, requirements, min_persons, max_persons, min_order_amount, offer_type,
                is_active, is_featured, is_highlighted, cta_text, cta_link, cta_icon, display_order
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param(
            "sssssssdddsdsssiiidisssssi",
            $title, $subtitle, $description, $short_description, $badge_text, $badge_color, $image_url,
            $original_price, $offer_price, $discount_percent, $currency, $valid_from, $valid_until,
            $time_slot, $requirements, $min_persons, $max_persons, $min_order_amount, $offer_type,
            $is_active, $is_featured, $is_highlighted, $cta_text, $cta_link, $cta_icon, $display_order
        );
        
        if ($stmt->execute()) {
            $stmt->close();
            $success_message = "Promotion added successfully";
            // Clear form fields
            $_POST = [];
            $_FILES = [];
        } else {
            $errors[] = "Failed to add promotion: " . $connection->error;
        }
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Add New Promotion</h1>
            <a href="promotions.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Promotions
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-tag me-2"></i>Promotion Information</h5>
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
                
                <form method="POST" action="" enctype="multipart/form-data" id="promotionForm">
                    <h5 class="mb-3">Basic Information</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Promotion Title *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subtitle" class="form-label">Subtitle (Optional)</label>
                                <input type="text" class="form-control" id="subtitle" name="subtitle" 
                                       value="<?php echo htmlspecialchars($_POST['subtitle'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="short_description" class="form-label">Short Description (Optional - for cards)</label>
                                <textarea class="form-control" id="short_description" name="short_description" rows="2"><?php echo htmlspecialchars($_POST['short_description'] ?? ''); ?></textarea>
                                <div class="form-text">Max 120 characters. Will be used in promotion cards.</div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Visual Elements</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="badge_text" class="form-label">Badge Text</label>
                                <input type="text" class="form-control" id="badge_text" name="badge_text" 
                                       value="<?php echo htmlspecialchars($_POST['badge_text'] ?? 'Limited Offer'); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="badge_color" class="form-label">Badge Color</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="badge_color_picker" 
                                           value="<?php echo htmlspecialchars($_POST['badge_color'] ?? '#dc3545'); ?>">
                                    <input type="text" class="form-control" id="badge_color" name="badge_color" 
                                           value="<?php echo htmlspecialchars($_POST['badge_color'] ?? 'var(--color-red)'); ?>">
                                </div>
                                <div class="form-text">CSS color value (hex, rgb, or CSS variable)</div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="image_url" class="form-label">Promotion Image</label>
                                <input type="file" class="form-control" id="image_url" name="image_url" 
                                       accept="image/*">
                                <div class="form-text">Max file size: 10MB. Allowed: JPG, PNG, GIF, WebP</div>
                                
                                <div id="imagePreview" class="mt-2" style="display: none;">
                                    <img src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Pricing Information</h5>
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-select" id="currency" name="currency">
                                    <option value="AED" <?php echo ($_POST['currency'] ?? 'AED') == 'AED' ? 'selected' : ''; ?>>AED</option>
                                    <option value="USD" <?php echo ($_POST['currency'] ?? '') == 'USD' ? 'selected' : ''; ?>>USD</option>
                                    <option value="EUR" <?php echo ($_POST['currency'] ?? '') == 'EUR' ? 'selected' : ''; ?>>EUR</option>
                                    <option value="AED" <?php echo ($_POST['currency'] ?? '') == 'AED' ? 'selected' : ''; ?>>AED</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="original_price" class="form-label">Original Price (Optional)</label>
                                <input type="number" class="form-control" id="original_price" name="original_price" 
                                       value="<?php echo htmlspecialchars($_POST['original_price'] ?? ''); ?>" 
                                       min="0" step="0.01">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="offer_price" class="form-label">Offer Price *</label>
                                <input type="number" class="form-control" id="offer_price" name="offer_price" 
                                       value="<?php echo htmlspecialchars($_POST['offer_price'] ?? ''); ?>" 
                                       min="0.01" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="discount_percent" class="form-label">Discount % (Optional)</label>
                                <input type="number" class="form-control" id="discount_percent" name="discount_percent" 
                                       value="<?php echo htmlspecialchars($_POST['discount_percent'] ?? ''); ?>" 
                                       min="0" max="100" step="0.01">
                                <div class="form-text">Will auto-calculate if original price is provided</div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Offer Details</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="offer_type" class="form-label">Offer Type</label>
                                <select class="form-select" id="offer_type" name="offer_type">
                                    <option value="family" <?php echo ($_POST['offer_type'] ?? '') == 'family' ? 'selected' : ''; ?>>Family</option>
                                    <option value="business" <?php echo ($_POST['offer_type'] ?? '') == 'business' ? 'selected' : ''; ?>>Business</option>
                                    <option value="early_bird" <?php echo ($_POST['offer_type'] ?? '') == 'early_bird' ? 'selected' : ''; ?>>Early Bird</option>
                                    <option value="birthday" <?php echo ($_POST['offer_type'] ?? '') == 'birthday' ? 'selected' : ''; ?>>Birthday</option>
                                    <option value="takeaway" <?php echo ($_POST['offer_type'] ?? '') == 'takeaway' ? 'selected' : ''; ?>>Takeaway</option>
                                    <option value="student" <?php echo ($_POST['offer_type'] ?? '') == 'student' ? 'selected' : ''; ?>>Student</option>
                                    <option value="seasonal" <?php echo ($_POST['offer_type'] ?? '') == 'seasonal' ? 'selected' : ''; ?>>Seasonal</option>
                                    <option value="other" <?php echo ($_POST['offer_type'] ?? 'other') == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="time_slot" class="form-label">Time Slot (Optional)</label>
                                <input type="text" class="form-control" id="time_slot" name="time_slot" 
                                       value="<?php echo htmlspecialchars($_POST['time_slot'] ?? ''); ?>"
                                       placeholder="e.g., Weekdays 12-3 PM, Daily 5-7 PM">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valid_from" class="form-label">Valid From (Optional)</label>
                                <input type="date" class="form-control" id="valid_from" name="valid_from" 
                                       value="<?php echo htmlspecialchars($_POST['valid_from'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valid_until" class="form-label">Valid Until (Optional)</label>
                                <input type="date" class="form-control" id="valid_until" name="valid_until" 
                                       value="<?php echo htmlspecialchars($_POST['valid_until'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="min_persons" class="form-label">Min. Persons (Optional)</label>
                                <input type="number" class="form-control" id="min_persons" name="min_persons" 
                                       value="<?php echo htmlspecialchars($_POST['min_persons'] ?? ''); ?>" 
                                       min="1">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="max_persons" class="form-label">Max. Persons (Optional)</label>
                                <input type="number" class="form-control" id="max_persons" name="max_persons" 
                                       value="<?php echo htmlspecialchars($_POST['max_persons'] ?? ''); ?>" 
                                       min="1">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="min_order_amount" class="form-label">Min. Order Amount (Optional)</label>
                                <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" 
                                       value="<?php echo htmlspecialchars($_POST['min_order_amount'] ?? ''); ?>" 
                                       min="0" step="0.01">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="requirements" class="form-label">Requirements / Conditions (Optional)</label>
                                <textarea class="form-control" id="requirements" name="requirements" rows="2"><?php echo htmlspecialchars($_POST['requirements'] ?? ''); ?></textarea>
                                <div class="form-text">e.g., Valid ID required, Dine-in only, Weekends only</div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Call to Action</h5>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cta_text" class="form-label">Button Text</label>
                                <input type="text" class="form-control" id="cta_text" name="cta_text" 
                                       value="<?php echo htmlspecialchars($_POST['cta_text'] ?? 'Book Now'); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cta_link" class="form-label">Button Link</label>
                                <input type="text" class="form-control" id="cta_link" name="cta_link" 
                                       value="<?php echo htmlspecialchars($_POST['cta_link'] ?? 'contact.php'); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cta_icon" class="form-label">Button Icon</label>
                                <input type="text" class="form-control" id="cta_icon" name="cta_icon" 
                                       value="<?php echo htmlspecialchars($_POST['cta_icon'] ?? 'bi-calendar-check'); ?>">
                                <div class="form-text">Bootstrap Icons class (e.g., bi-calendar-check)</div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Settings</h5>
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" 
                                       value="<?php echo htmlspecialchars($_POST['display_order'] ?? 0); ?>">
                                <div class="form-text">Lower numbers display first</div>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                                   <?php echo isset($_POST['is_active']) ? 'checked' : 'checked'; ?>>
                                            <label class="form-check-label" for="is_active">
                                                Active Promotion
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" 
                                                   <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_featured">
                                                Featured (shows as special)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_highlighted" name="is_highlighted" value="1" 
                                                   <?php echo isset($_POST['is_highlighted']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_highlighted">
                                                Highlighted (main banner)
                                            </label>
                                            <div class="form-text">Only one should be highlighted</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="promotions.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Add Promotion
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
                <h4 class="my-3">Promotion Added Successfully!</h4>
                <p>The new promotion has been added to the database.</p>
            </div>
            <div class="modal-footer">
                <a href="promotions.php" class="btn btn-secondary">View All Promotions</a>
                <button type="button" class="btn btn-success" onclick="resetForm()" data-bs-dismiss="modal">Add Another Promotion</button>
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
document.getElementById('image_url').addEventListener('change', function(e) {
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

// Color picker synchronization
const colorPicker = document.getElementById('badge_color_picker');
const colorInput = document.getElementById('badge_color');

colorPicker.addEventListener('input', function() {
    colorInput.value = this.value;
});

colorInput.addEventListener('input', function() {
    // Check if it's a hex color
    if (this.value.match(/^#[0-9A-F]{6}$/i)) {
        colorPicker.value = this.value;
    }
});

// Auto-calculate discount
function calculateDiscount() {
    const originalPrice = parseFloat(document.getElementById('original_price').value) || 0;
    const offerPrice = parseFloat(document.getElementById('offer_price').value) || 0;
    const discountInput = document.getElementById('discount_percent');
    
    if (originalPrice > 0 && offerPrice > 0) {
        const discount = ((originalPrice - offerPrice) / originalPrice) * 100;
        if (!discountInput.value || discountInput.value === '') {
            discountInput.value = discount.toFixed(2);
        }
    }
}

document.getElementById('original_price').addEventListener('blur', calculateDiscount);
document.getElementById('offer_price').addEventListener('blur', calculateDiscount);

// Form reset function
function resetForm() {
    document.getElementById('promotionForm').reset();
    document.getElementById('imagePreview').style.display = 'none';
    // Reset color picker
    document.getElementById('badge_color_picker').value = '#dc3545';
    document.getElementById('badge_color').value = 'var(--color-red)';
}
</script>