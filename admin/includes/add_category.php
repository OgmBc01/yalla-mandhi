<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ensure $connection is set
if (!isset($connection) || !$connection) {
    if (function_exists('getDBConnection')) {
        $connection = getDBConnection();
    } else {
        require_once dirname(__DIR__, 2) . '/includes/database.php';
        $connection = getDBConnection();
    }
}

// Initialize variables
$name = $description = '';
$sort_order = 0;
$is_active = 1;
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $sort_order = intval($_POST['sort_order']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validate required fields
    if (empty($name)) {
        $message = "Please enter a category name.";
        $message_type = "error";
    } else {
        // Check if category name already exists
        $check_sql = "SELECT id FROM menu_categories WHERE name = ?";
        $check_stmt = $connection->prepare($check_sql);
        $check_stmt->bind_param("s", $name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $message = "Category name already exists. Please choose a different name.";
            $message_type = "error";
            $check_stmt->close();
        } else {
            $check_stmt->close();
            
            // Insert into database
            $sql = "INSERT INTO menu_categories (name, description, sort_order, is_active) 
                    VALUES (?, ?, ?, ?)";
            
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ssii", $name, $description, $sort_order, $is_active);

            if ($stmt->execute()) {
                $new_category_id = $stmt->insert_id;
                $stmt->close();
                
                // Show success modal
                echo "
                <script>
                    window.addEventListener('load', function() {
                        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();
                    });
                </script>
                ";
                
                // Clear form after successful submission
                $name = $description = '';
                $sort_order = 0;
                $is_active = 1;
            } else {
                $message = "Failed to add category. Error: " . $connection->error;
                $message_type = "error";
                $stmt->close();
            }
        }
    }
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Add New Category</h1>
            <a href="categories.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Categories
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-tag me-2"></i>Category Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label"><i class="bi bi-card-text me-1"></i>Category Name *</label>
                                        <input type="text" id="name" name="name" class="form-control" 
                                               value="<?php echo htmlspecialchars($name); ?>" required>
                                        <div class="form-text">Unique name for the category (e.g., Appetizers, Main Course, Desserts)</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label"><i class="bi bi-text-paragraph me-1"></i>Description</label>
                                        <textarea id="description" name="description" class="form-control" 
                                                  rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                                        <div class="form-text">Optional description for the category</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="sort_order" class="form-label"><i class="bi bi-sort-numeric-down me-1"></i>Sort Order</label>
                                        <input type="number" id="sort_order" name="sort_order" class="form-control" 
                                               value="<?php echo htmlspecialchars($sort_order); ?>" min="0">
                                        <div class="form-text">Lower numbers appear first</div>
                                    </div>

                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="is_active" 
                                               name="is_active" value="1" 
                                               <?php echo $is_active ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            <i class="bi bi-check-circle me-1"></i> Active
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg me-2">
                                        <i class="bi bi-check-circle me-1"></i> Add Category
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
                <h4 class="my-3">Category Added Successfully!</h4>
                <p>The new category has been added and is now available for menu items.</p>
            </div>
            <div class="modal-footer">
                <a href="categories.php" class="btn btn-secondary">View All Categories</a>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Add Another Category</button>
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