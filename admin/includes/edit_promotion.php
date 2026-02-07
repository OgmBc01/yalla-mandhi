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

// Get promotion ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: promotions.php");
    exit();
}

$promotion_id = (int)$_GET['id'];

// Fetch promotion data
$stmt = $connection->prepare("SELECT * FROM promotions WHERE id = ?");
$stmt->bind_param("i", $promotion_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: promotions.php");
    exit();
}

$promotion = $result->fetch_assoc();
$stmt->close();

$errors = [];
$current_image = $promotion['image_url'] ?? '';
$success_message = '';
$show_success_modal = false; // New flag for modal

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Validate inputs (same as add_promotion.php)
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
    
    // Handle file upload
    $image_url = $current_image;
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
            
            // Delete old image if exists
            if ($current_image && file_exists($upload_dir . $current_image)) {
                @unlink($upload_dir . $current_image);
            }
            
            $image_url = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $image_url;
            
            if (!move_uploaded_file($_FILES['image_url']['tmp_name'], $target_file)) {
                $errors[] = "Failed to upload image";
                $image_url = $current_image;
            }
        }
    }
    
    // Handle image removal if checkbox is checked
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        $upload_dir = __DIR__ . '/../../uploads/promotions/';
        if ($current_image && file_exists($upload_dir . $current_image)) {
            @unlink($upload_dir . $current_image);
        }
        $image_url = ''; // Set to empty string
    }
    
    // Auto-calculate discount percentage if not provided
    if ($original_price !== null && $offer_price > 0 && $discount_percent === null) {
        $discount_percent = round((($original_price - $offer_price) / $original_price) * 100, 2);
    }
    
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
    
    if (empty($errors)) {
        // Update promotion
        $stmt = $connection->prepare(
            "UPDATE promotions SET 
                title = ?, subtitle = ?, description = ?, short_description = ?, 
                badge_text = ?, badge_color = ?, image_url = ?, original_price = ?, 
                offer_price = ?, discount_percent = ?, currency = ?, valid_from = ?, 
                valid_until = ?, time_slot = ?, requirements = ?, min_persons = ?, 
                max_persons = ?, min_order_amount = ?, offer_type = ?, is_active = ?, 
                is_featured = ?, is_highlighted = ?, cta_text = ?, cta_link = ?, 
                cta_icon = ?, display_order = ? 
             WHERE id = ?"
        );
        
        $params = [
            $title, $subtitle, $description, $short_description, $badge_text, 
            $badge_color, $image_url, $original_price, $offer_price, 
            $discount_percent, $currency, $valid_from, $valid_until, $time_slot, 
            $requirements, $min_persons, $max_persons, $min_order_amount, 
            $offer_type, $is_active, $is_featured, $is_highlighted, $cta_text, 
            $cta_link, $cta_icon, $display_order, $promotion_id
        ];
        
        // Create type string based on parameter types
        $type_string = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $type_string .= 'i';
            } elseif (is_float($param)) {
                $type_string .= 'd';
            } elseif (is_null($param)) {
                $type_string .= 's'; // MySQLi treats NULL as string in bind_param
            } else {
                $type_string .= 's';
            }
        }
        
        $stmt->bind_param($type_string, ...$params);
        
        if ($stmt->execute()) {
            $success_message = "Promotion updated successfully";
            $show_success_modal = true; // Set flag to show modal
            
            // Update promotion data for display
            $promotion['title'] = $title;
            $promotion['subtitle'] = $subtitle;
            $promotion['description'] = $description;
            $promotion['short_description'] = $short_description;
            $promotion['badge_text'] = $badge_text;
            $promotion['badge_color'] = $badge_color;
            $promotion['image_url'] = $image_url;
            $promotion['original_price'] = $original_price;
            $promotion['offer_price'] = $offer_price;
            $promotion['discount_percent'] = $discount_percent;
            $promotion['currency'] = $currency;
            $promotion['valid_from'] = $valid_from;
            $promotion['valid_until'] = $valid_until;
            $promotion['time_slot'] = $time_slot;
            $promotion['requirements'] = $requirements;
            $promotion['min_persons'] = $min_persons;
            $promotion['max_persons'] = $max_persons;
            $promotion['min_order_amount'] = $min_order_amount;
            $promotion['offer_type'] = $offer_type;
            $promotion['is_active'] = $is_active;
            $promotion['is_featured'] = $is_featured;
            $promotion['is_highlighted'] = $is_highlighted;
            $promotion['cta_text'] = $cta_text;
            $promotion['cta_link'] = $cta_link;
            $promotion['cta_icon'] = $cta_icon;
            $promotion['display_order'] = $display_order;
            $current_image = $image_url;
        } else {
            $errors[] = "Failed to update promotion: " . $connection->error;
        }
        $stmt->close();
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Edit Promotion</h1>
            <a href="promotions.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Promotions
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-tag-fill me-2"></i>Edit Promotion Information</h5>
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
                
                <?php if (!empty($success_message) && !$show_success_modal): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data" id="editPromotionForm">
                    <input type="hidden" name="id" value="<?php echo $promotion_id; ?>">
                    
                    <h5 class="mb-3">Basic Information</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Promotion Title *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($promotion['title']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subtitle" class="form-label">Subtitle (Optional)</label>
                                <input type="text" class="form-control" id="subtitle" name="subtitle" 
                                       value="<?php echo htmlspecialchars($promotion['subtitle'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($promotion['description']); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="short_description" class="form-label">Short Description (Optional - for cards)</label>
                                <textarea class="form-control" id="short_description" name="short_description" rows="2"><?php echo htmlspecialchars($promotion['short_description'] ?? ''); ?></textarea>
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
                                       value="<?php echo htmlspecialchars($promotion['badge_text'] ?? 'Limited Offer'); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="badge_color" class="form-label">Badge Color</label>
                                <div class="input-group">
                                    <?php 
                                    $badge_color_value = $promotion['badge_color'] ?? 'var(--color-red)';
                                    $color_picker_value = $badge_color_value;
                                    if (strpos($badge_color_value, 'var(--') === 0) {
                                        // Convert CSS variable to hex for color picker
                                        $color_picker_value = '#dc3545'; // Default red
                                    }
                                    ?>
                                    <input type="color" class="form-control form-control-color" id="badge_color_picker" 
                                           value="<?php echo htmlspecialchars($color_picker_value); ?>">
                                    <input type="text" class="form-control" id="badge_color" name="badge_color" 
                                           value="<?php echo htmlspecialchars($badge_color_value); ?>">
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
                                
                                <!-- New image preview -->
                                <div id="newImagePreview" class="mt-2" style="display: none;">
                                    <p class="text-success mb-1"><i class="bi bi-info-circle"></i> New image selected:</p>
                                    <img src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                                
                                <!-- Current image display -->
                                <?php if ($current_image): ?>
                                    <div class="mt-2">
                                        <p class="text-muted mb-1">Current image:</p>
                                        <img src="../uploads/promotions/<?php echo htmlspecialchars($current_image); ?>" 
                                             alt="Current Image" class="img-thumbnail" style="max-width: 200px;"
                                             onerror="this.src='https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
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
                    </div>
                    
                    <h5 class="mb-3">Pricing Information</h5>
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-select" id="currency" name="currency">
                                    <option value="AED" <?php echo ($promotion['currency'] ?? 'AED') == 'AED' ? 'selected' : ''; ?>>AED</option>
                                    <option value="USD" <?php echo ($promotion['currency'] ?? '') == 'USD' ? 'selected' : ''; ?>>USD</option>
                                    <option value="EUR" <?php echo ($promotion['currency'] ?? '') == 'EUR' ? 'selected' : ''; ?>>EUR</option>
                                    <option value="SAR" <?php echo ($promotion['currency'] ?? '') == 'SAR' ? 'selected' : ''; ?>>SAR</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="original_price" class="form-label">Original Price (Optional)</label>
                                <input type="number" class="form-control" id="original_price" name="original_price" 
                                       value="<?php echo htmlspecialchars($promotion['original_price'] ?? ''); ?>" 
                                       min="0" step="0.01">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="offer_price" class="form-label">Offer Price *</label>
                                <input type="number" class="form-control" id="offer_price" name="offer_price" 
                                       value="<?php echo htmlspecialchars($promotion['offer_price']); ?>" 
                                       min="0.01" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="discount_percent" class="form-label">Discount % (Optional)</label>
                                <input type="number" class="form-control" id="discount_percent" name="discount_percent" 
                                       value="<?php echo htmlspecialchars($promotion['discount_percent'] ?? ''); ?>" 
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
                                    <option value="family" <?php echo ($promotion['offer_type'] ?? '') == 'family' ? 'selected' : ''; ?>>Family</option>
                                    <option value="business" <?php echo ($promotion['offer_type'] ?? '') == 'business' ? 'selected' : ''; ?>>Business</option>
                                    <option value="early_bird" <?php echo ($promotion['offer_type'] ?? '') == 'early_bird' ? 'selected' : ''; ?>>Early Bird</option>
                                    <option value="birthday" <?php echo ($promotion['offer_type'] ?? '') == 'birthday' ? 'selected' : ''; ?>>Birthday</option>
                                    <option value="takeaway" <?php echo ($promotion['offer_type'] ?? '') == 'takeaway' ? 'selected' : ''; ?>>Takeaway</option>
                                    <option value="student" <?php echo ($promotion['offer_type'] ?? '') == 'student' ? 'selected' : ''; ?>>Student</option>
                                    <option value="seasonal" <?php echo ($promotion['offer_type'] ?? '') == 'seasonal' ? 'selected' : ''; ?>>Seasonal</option>
                                    <option value="other" <?php echo ($promotion['offer_type'] ?? 'other') == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="time_slot" class="form-label">Time Slot (Optional)</label>
                                <input type="text" class="form-control" id="time_slot" name="time_slot" 
                                       value="<?php echo htmlspecialchars($promotion['time_slot'] ?? ''); ?>"
                                       placeholder="e.g., Weekdays 12-3 PM, Daily 5-7 PM">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valid_from" class="form-label">Valid From (Optional)</label>
                                <input type="date" class="form-control" id="valid_from" name="valid_from" 
                                       value="<?php echo htmlspecialchars($promotion['valid_from'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valid_until" class="form-label">Valid Until (Optional)</label>
                                <input type="date" class="form-control" id="valid_until" name="valid_until" 
                                       value="<?php echo htmlspecialchars($promotion['valid_until'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="min_persons" class="form-label">Min. Persons (Optional)</label>
                                <input type="number" class="form-control" id="min_persons" name="min_persons" 
                                       value="<?php echo htmlspecialchars($promotion['min_persons'] ?? ''); ?>" 
                                       min="1">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="max_persons" class="form-label">Max. Persons (Optional)</label>
                                <input type="number" class="form-control" id="max_persons" name="max_persons" 
                                       value="<?php echo htmlspecialchars($promotion['max_persons'] ?? ''); ?>" 
                                       min="1">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="min_order_amount" class="form-label">Min. Order Amount (Optional)</label>
                                <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" 
                                       value="<?php echo htmlspecialchars($promotion['min_order_amount'] ?? ''); ?>" 
                                       min="0" step="0.01">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="requirements" class="form-label">Requirements / Conditions (Optional)</label>
                                <textarea class="form-control" id="requirements" name="requirements" rows="2"><?php echo htmlspecialchars($promotion['requirements'] ?? ''); ?></textarea>
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
                                       value="<?php echo htmlspecialchars($promotion['cta_text'] ?? 'Book Now'); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cta_link" class="form-label">Button Link</label>
                                <input type="text" class="form-control" id="cta_link" name="cta_link" 
                                       value="<?php echo htmlspecialchars($promotion['cta_link'] ?? 'contact.php'); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cta_icon" class="form-label">Button Icon</label>
                                <input type="text" class="form-control" id="cta_icon" name="cta_icon" 
                                       value="<?php echo htmlspecialchars($promotion['cta_icon'] ?? 'bi-calendar-check'); ?>">
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
                                       value="<?php echo htmlspecialchars($promotion['display_order'] ?? 0); ?>">
                                <div class="form-text">Lower numbers display first</div>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                                   <?php echo $promotion['is_active'] ? 'checked' : ''; ?>>
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
                                                   <?php echo $promotion['is_featured'] ? 'checked' : ''; ?>>
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
                                                   <?php echo $promotion['is_highlighted'] ? 'checked' : ''; ?>>
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
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="promotions.php?source=view_promotion&id=<?php echo $promotion_id; ?>" 
                               class="btn btn-outline-info">
                                <i class="bi bi-eye me-2"></i>View Details
                            </a>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="promotions.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Update Promotion
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel"><i class="bi bi-check-circle-fill me-2"></i>Success</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <h4 class="my-3">Promotion Updated Successfully!</h4>
                <p>The promotion has been updated in the database.</p>
            </div>
            <div class="modal-footer">
                <a href="promotions.php" class="btn btn-secondary">View All Promotions</a>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Continue Editing</button>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview functionality
document.getElementById('image_url').addEventListener('change', function(e) {
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

// Remove image checkbox logic
const removeCheckbox = document.getElementById('remove_image');
if (removeCheckbox) {
    removeCheckbox.addEventListener('change', function() {
        const fileInput = document.getElementById('image_url');
        const preview = document.getElementById('newImagePreview');
        
        if (this.checked) {
            // Clear file input and hide preview
            fileInput.value = '';
            preview.style.display = 'none';
        }
    });
}

// File input change should uncheck remove image
document.getElementById('image_url').addEventListener('change', function() {
    const removeCheckbox = document.getElementById('remove_image');
    if (removeCheckbox && this.value) {
        removeCheckbox.checked = false;
    }
});

<?php if ($show_success_modal): ?>
// Show success modal after form submission
document.addEventListener('DOMContentLoaded', function() {
    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
});
<?php endif; ?>
</script>