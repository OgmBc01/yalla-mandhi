<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$name = $description = $price = $current_image = '';
$category_id = '';
$is_available = 1;
$is_featured = 0;
$message = '';
$message_type = '';

// Fetch categories for dropdown
$categories = [];
$category_query = "SELECT id, name FROM menu_categories WHERE is_active = 1 ORDER BY sort_order, name";
$category_result = $connection->query($category_query);
if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch item data if editing existing item
if ($item_id > 0) {
    $sql = "SELECT * FROM menu_items WHERE id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $item = $result->fetch_assoc();
        $name = $item['name'];
        $description = $item['description'];
        $category_id = $item['category_id'];
        $price = $item['price'];
        $current_image = $item['image_url'];
        $is_available = $item['is_available'];
        $is_featured = $item['is_featured'];
    } else {
        $message = "Menu item not found.";
        $message_type = "error";
        $item_id = 0;
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $item_id = intval($_POST['item_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Handle file upload
    $image_url = $current_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $upload_dir = "../uploads/menu/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = "menu_" . time() . "_" . rand(1000, 9999) . ".{$ext}";
            $target = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target)) {
                // Delete old image if it exists
                if (!empty($current_image) && file_exists($upload_dir . $current_image)) {
                    @unlink($upload_dir . $current_image);
                }
                $image_url = $new_filename;
            }
        }
    }

    // Validate required fields
    if (empty($name) || empty($category_id) || empty($price)) {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    } elseif ($price <= 0) {
        $message = "Price must be greater than 0.";
        $message_type = "error";
    } else {
        // Update database
        $sql = "UPDATE menu_items SET 
                name = ?, 
                description = ?, 
                category_id = ?, 
                price = ?, 
                image_url = ?, 
                is_available = ?, 
                is_featured = ? 
                WHERE id = ?";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ssidsiii", $name, $description, $category_id, $price, 
                 $image_url, $is_available, $is_featured, $item_id);

        if ($stmt->execute()) {
            $stmt->close();
            
            // Update current image variable
            $current_image = $image_url;
            
            // Show success modal
            echo "
            <script>
                window.addEventListener('load', function() {
                    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                });
            </script>
            ";
        } else {
            $message = "Failed to update menu item. Error: " . $connection->error;
            $message_type = "error";
            $stmt->close();
        }
    }
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><?php echo $item_id > 0 ? 'Edit Menu Item' : 'Add Menu Item'; ?></h1>
            <a href="menu_items.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Menu Items
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Menu Item Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                            
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label"><i class="bi bi-card-text me-1"></i>Item Name *</label>
                                        <input type="text" id="name" name="name" class="form-control" 
                                               value="<?php echo htmlspecialchars($name); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="category_id" class="form-label"><i class="bi bi-tag me-1"></i>Category *</label>
                                        <select id="category_id" name="category_id" class="form-select" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="price" class="form-label"><i class="bi bi-currency-dollar me-1"></i>Price *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" id="price" name="price" class="form-control" 
                                                   value="<?php echo htmlspecialchars($price); ?>" 
                                                   step="0.01" min="0.01" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label"><i class="bi bi-text-paragraph me-1"></i>Description</label>
                                        <textarea id="description" name="description" class="form-control" 
                                                  rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="image" class="form-label"><i class="bi bi-image me-1"></i>Item Image</label>
                                        <input type="file" id="image" name="image" class="form-control" 
                                               accept="image/jpeg,image/png,image/gif,image/webp">
                                        <div class="form-text">Accepted formats: JPG, PNG, GIF, WebP. Max size: 2MB</div>
                                        
                                        <?php if (!empty($current_image)): ?>
                                        <div class="mt-2">
                                            <small>Current Image: </small>
                                            <img src="../uploads/menu/<?php echo htmlspecialchars($current_image); ?>" 
                                                 alt="<?php echo htmlspecialchars($name); ?>"
                                                 class="img-thumbnail ms-2" width="100" height="100"
                                                 onerror="this.src='https://via.placeholder.com/100?text=No+Image'">
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="mt-2" id="imagePreview"></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="is_available" 
                                                       name="is_available" value="1" 
                                                       <?php echo $is_available ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="is_available">
                                                    <i class="bi bi-check-circle me-1"></i> Available
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="is_featured" 
                                                       name="is_featured" value="1" 
                                                       <?php echo $is_featured ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="is_featured">
                                                    <i class="bi bi-star me-1"></i> Featured
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="bi bi-info-circle me-1"></i>Item Status</h6>
                                            <ul class="small mb-0">
                                                <li><strong>Available:</strong> Item can be ordered by customers</li>
                                                <li><strong>Featured:</strong> Item will be highlighted in special sections</li>
                                                <li><strong>Last Updated:</strong> <?php echo $item_id > 0 ? date('M d, Y', strtotime($item['updated_at'] ?? '')) : 'New Item'; ?></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg me-2">
                                        <i class="bi bi-check-circle me-1"></i> <?php echo $item_id > 0 ? 'Update Menu Item' : 'Add Menu Item'; ?>
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-x-circle me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
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
                <h4 class="my-3">Menu Item Updated Successfully!</h4>
                <p>The menu item has been updated in the database.</p>
            </div>
            <div class="modal-footer">
                <a href="menu_items.php" class="btn btn-secondary">View All Items</a>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Continue Editing</button>
            </div>
        </div>
    </div>
</div>

<style>
    .form-label {
        font-weight: 500;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }
    .card {
        border: none;
        border-radius: 12px;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        border-radius: 12px 12px 0 0 !important;
    }
    .btn-primary {
        background: #f1bf70;
        border-color: #f1bf70;
        color: #0f172a;
        font-weight: 600;
    }
    .btn-primary:hover {
        background: #e5b465;
        border-color: #e5b465;
        color: #0f172a;
    }
    .form-check-input:checked {
        background-color: #f1bf70;
        border-color: #f1bf70;
    }
</style>

<script>
// Image preview
document.getElementById('image').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'img-thumbnail mt-2';
            img.style.maxWidth = '200px';
            img.style.maxHeight = '150px';
            preview.appendChild(img);
        }
        
        reader.readAsDataURL(this.files[0]);
    }
});
</script>