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

// Fetch all categories
$sql = "SELECT mc.*, 
        (SELECT COUNT(*) FROM menu_items WHERE category_id = mc.id) as item_count
        FROM menu_categories mc 
        ORDER BY mc.sort_order ASC, mc.name ASC";
$result = $connection->query($sql);

// Check for errors
if (!$result) {
    die("Query failed: " . $connection->error);
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Menu Categories Management</h1>
            <div>
                <a href="categories.php?source=add_category" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> Add New Category
                </a>
                <a href="menu_items.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Menu Items
                </a>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Categories</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $count_query = "SELECT COUNT(*) as total FROM menu_categories";
                                    $count_result = $connection->query($count_query);
                                    echo $count_result ? $count_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-tags display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Active Categories</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $active_query = "SELECT COUNT(*) as total FROM menu_categories WHERE is_active = 1";
                                    $active_result = $connection->query($active_query);
                                    echo $active_result ? $active_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-check-circle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Menu Items</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $items_query = "SELECT COUNT(*) as total FROM menu_items";
                                    $items_result = $connection->query($items_query);
                                    echo $items_result ? $items_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-egg-fried display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-tags me-2"></i>All Categories</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="categoriesTable">
                        <thead>
                            <tr class="table-dark">
                                <th width="50">ID</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Items</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th width="150" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($category = $result->fetch_assoc()): ?>
                                    <tr id="category-row-<?php echo $category['id']; ?>">
                                        <td class="fw-bold">#<?php echo $category['id']; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($category['name']); ?></div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php 
                                                echo $category['description'] 
                                                    ? (strlen($category['description']) > 50 
                                                        ? htmlspecialchars(substr($category['description'], 0, 50)) . '...' 
                                                        : htmlspecialchars($category['description']))
                                                    : '<span class="text-muted">No description</span>';
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                                <?php echo $category['item_count']; ?> items
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $category['sort_order']; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($category['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href='categories.php?source=edit_category&id=<?php echo $category['id']; ?>'
                                                   class="btn btn-outline-warning"
                                                   title="Edit Category">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-danger"
                                                    onclick="showCategoryDeleteConfirmation(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars(addslashes($category['name'])); ?>', <?php echo $category['item_count']; ?>)"
                                                    title="Delete Category">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-tags display-4 d-block mb-2"></i>
                                            <h5>No categories found</h5>
                                            <p>Get started by creating your first category.</p>
                                            <a href="categories.php?source=add_category" class="btn btn-primary mt-2">
                                                <i class="bi bi-plus-circle"></i> Add Category
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Delete Confirmation Modal -->
<div class="modal fade" id="categoryDeleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete category <strong id="categoryDeleteName"></strong>?
                <div class="alert alert-warning mt-2" id="categoryWarning">
                    <!-- Warning about items in category will appear here -->
                </div>
                <div class="alert alert-danger mt-2" id="categoryDeleteWarning" style="display: none;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This category contains <span id="categoryItemCount">0</span> menu item(s). 
                    Deleting this category will also delete all items in it!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmCategoryDeleteBtn" onclick="deleteCategory()">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="categorySuccessToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="categoryToastMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="categoryErrorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="categoryErrorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<style>
    .table th {
        font-weight: 600;
        background-color: #2c3e50 !important;
        color: white;
        vertical-align: middle;
    }
    .table-dark {
        --bs-table-bg: #2c3e50;
        --bs-table-color: white;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    #categoriesTable tbody tr:hover {
        background-color: rgba(241, 191, 112, 0.1);
    }
    .btn-group .btn {
        border-radius: 4px !important;
    }
    .btn-group .btn:not(:last-child) {
        margin-right: 2px;
    }
</style>

<script>
// Global variables
let currentDeleteCategoryId = null;
let currentCategoryItemCount = 0;
let categoryDeleteModal = null;

// Show Delete Confirmation
function showCategoryDeleteConfirmation(categoryId, categoryName, itemCount) {
    console.log('Showing delete confirmation for category:', categoryId, categoryName, itemCount);
    
    if (!categoryId || categoryId <= 0) {
        showCategoryError('Invalid category ID');
        return;
    }
    
    currentDeleteCategoryId = categoryId;
    currentCategoryItemCount = itemCount;
    
    // Set category name in modal
    document.getElementById('categoryDeleteName').textContent = categoryName;
    
    // Show/hide warning based on item count
    const warningDiv = document.getElementById('categoryDeleteWarning');
    const itemCountSpan = document.getElementById('categoryItemCount');
    const categoryWarningDiv = document.getElementById('categoryWarning');
    
    if (itemCount > 0) {
        warningDiv.style.display = 'block';
        itemCountSpan.textContent = itemCount;
        categoryWarningDiv.innerHTML = '';
    } else {
        warningDiv.style.display = 'none';
        categoryWarningDiv.innerHTML = `
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                This action cannot be undone.
            </div>
        `;
    }
    
    // Show modal
    const modalEl = document.getElementById('categoryDeleteConfirmModal');
    categoryDeleteModal = new bootstrap.Modal(modalEl);
    categoryDeleteModal.show();
}

// Delete Category
function deleteCategory() {
    if (!currentDeleteCategoryId) {
        showCategoryError('No category selected for deletion');
        return;
    }
    
    console.log('Deleting category ID:', currentDeleteCategoryId);
    
    const deleteBtn = document.getElementById('confirmCategoryDeleteBtn');
    const originalText = deleteBtn.innerHTML;
    
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    deleteBtn.disabled = true;
    
    fetch('includes/delete_category.php?id=' + currentDeleteCategoryId)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
            
            if (data.success) {
                // Close modal
                if (categoryDeleteModal) {
                    categoryDeleteModal.hide();
                }
                
                // Remove row from table
                const row = document.getElementById('category-row-' + currentDeleteCategoryId);
                if (row) {
                    const table = $('#categoriesTable').DataTable();
                    if (table) {
                        table.row(row).remove().draw();
                    } else {
                        row.style.opacity = '0';
                        row.style.transition = 'opacity 0.4s';
                        setTimeout(() => {
                            row.remove();
                        }, 400);
                    }
                }
                
                // Show success message
                showCategorySuccess(data.message || 'Category deleted successfully!');
                currentDeleteCategoryId = null;
                currentCategoryItemCount = 0;
            } else {
                showCategoryError(data.message || 'Failed to delete category');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
            showCategoryError('Error deleting category: ' + error.message);
        });
}

// Show success message
function showCategorySuccess(message) {
    document.getElementById('categoryToastMessage').textContent = message;
    const toast = new bootstrap.Toast(document.getElementById('categorySuccessToast'));
    toast.show();
    
    setTimeout(() => toast.hide(), 5000);
}

// Show error message
function showCategoryError(message) {
    document.getElementById('categoryErrorToastMessage').textContent = message;
    const toast = new bootstrap.Toast(document.getElementById('categoryErrorToast'));
    toast.show();
    
    setTimeout(() => toast.hide(), 5000);
}

// Initialize DataTable
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        // Destroy existing DataTable instance if it exists
        if ($.fn.DataTable.isDataTable('#categoriesTable')) {
            $('#categoriesTable').DataTable().destroy();
        }
        
        $('#categoriesTable').DataTable({
            pageLength: 25,
            order: [[4, 'asc']], // Sort by sort order
            columnDefs: [
                { orderable: false, targets: [7] }, // Actions column
                { width: "50px", targets: [0] }, // ID column
                { width: "150px", targets: [7] } // Actions column
            ],
            responsive: true,
            language: {
                search: "Search categories:",
                lengthMenu: "Show _MENU_ categories per page",
                info: "Showing _START_ to _END_ of _TOTAL_ categories",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                zeroRecords: "No matching categories found",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }
});
</script>