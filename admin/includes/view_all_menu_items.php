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

// Fetch all menu items with category name
$sql = "SELECT mi.*, mc.name as category_name 
        FROM menu_items mi 
        LEFT JOIN menu_categories mc ON mi.category_id = mc.id 
        ORDER BY mi.created_at DESC";
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
            <h1 class="page-title">Menu Item Management</h1>
            <div>
                <a href="menu_items.php?source=add_item" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> Add New Item
                </a>
                <a href="categories.php" class="btn btn-warning">
                    <i class="bi bi-tags"></i> Manage Categories
                </a>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Items</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $count_query = "SELECT COUNT(*) as total FROM menu_items";
                                    $count_result = $connection->query($count_query);
                                    echo $count_result ? $count_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-egg-fried display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Available</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $available_query = "SELECT COUNT(*) as total FROM menu_items WHERE is_available = 1";
                                    $available_result = $connection->query($available_query);
                                    echo $available_result ? $available_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-check-circle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Featured</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $featured_query = "SELECT COUNT(*) as total FROM menu_items WHERE is_featured = 1";
                                    $featured_result = $connection->query($featured_query);
                                    echo $featured_result ? $featured_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-star display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Categories</h6>
                                <h2 class="mb-0">
                                    <?php
                                    $category_query = "SELECT COUNT(*) as total FROM menu_categories WHERE is_active = 1";
                                    $category_result = $connection->query($category_query);
                                    echo $category_result ? $category_result->fetch_assoc()['total'] : '0';
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-tag display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Items Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-card-checklist me-2"></i>All Menu Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="menuItemsTable">
                        <thead>
                            <tr class="table-dark">
                                <th width="50">ID</th>
                                <th width="80">Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <th>Created</th>
                                <th width="150" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($item = $result->fetch_assoc()): ?>
                                    <tr id="item-row-<?php echo $item['id']; ?>">
                                        <td class="fw-bold">#<?php echo $item['id']; ?></td>
                                        <td>
                                            <?php if (!empty($item['image_url'])): ?>
                                                <img src="../uploads/menu/<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                     class="rounded" width="50" height="50"
                                                     onerror="this.src='https://via.placeholder.com/50?text=No+Image'">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($item['name']); ?></div>
                                            <small class="text-muted">
                                                <?php 
                                                echo strlen($item['description']) > 50 
                                                    ? htmlspecialchars(substr($item['description'], 0, 50)) . '...' 
                                                    : htmlspecialchars($item['description']);
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">$<?php echo number_format($item['price'], 2); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($item['is_available']): ?>
                                                <span class="badge bg-success">Available</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Unavailable</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['is_featured']): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-star-fill"></i> Featured
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary view-item-btn" 
                                                        onclick="viewMenuItem(<?php echo $item['id']; ?>)" 
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                              
                                                <a href='menu_items.php?source=edit_item&id=<?php echo $item['id']; ?>'
                                                   class="btn btn-outline-warning"
                                                   title="Edit Item">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-outline-danger delete-item-btn" 
                                                        onclick="menuShowDeleteConfirm(
                                                        <?php echo (int)$item['id']; ?>,
                                                        '<?php echo htmlspecialchars(addslashes($item['name']), ENT_QUOTES); ?>')"                                                        title="Delete Item">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-egg-fried display-4 d-block mb-2"></i>
                                            <h5>No menu items found</h5>
                                            <p>Get started by adding your first menu item.</p>
                                            <a href="menu_items.php?source=add_item" class="btn btn-primary mt-2">
                                                <i class="bi bi-plus-circle"></i> Add Menu Item
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

<!-- View Item Modal -->
<div class="modal fade" id="viewItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header card-header text-white">
                <h5 class="modal-title">Menu Item Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="itemDetails">
                <!-- Item details will be loaded here via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editItemBtn" class="btn btn-primary">Edit Item</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="menuDeleteItemName"></strong>? This action cannot be undone.
                <div class="alert alert-warning mt-2">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This will permanently remove the menu item from the system.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="menuConfirmDeleteBtn" onclick="menuDeleteItem()">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="toastMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="errorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
/* =====================================================
   GLOBAL STATE (MENU PAGE ONLY)
===================================================== */
let menuDeleteItemId = null;
let menuDeleteModalInstance = null;

/* =====================================================
   VIEW MENU ITEM
===================================================== */
function viewMenuItem(itemId) {
    if (!itemId) {
        showError('Invalid item ID');
        return;
    }

    const detailsEl = document.getElementById('itemDetails');
    detailsEl.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading item details...</p>
        </div>
    `;

    const viewModal = new bootstrap.Modal(
        document.getElementById('viewItemModal')
    );
    viewModal.show();

    fetch('includes/get_menu_item_details.php?id=' + itemId)
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.item) {
                detailsEl.innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load item details
                    </div>`;
                return;
            }

            const item = data.item;
            const imageUrl = item.image_url
                ? '../uploads/menu/' + item.image_url
                : 'https://via.placeholder.com/300x200?text=No+Image';

            detailsEl.innerHTML = `
                <div class="row">
                    <div class="col-md-5 text-center">
                        <img src="${imageUrl}" class="img-fluid rounded mb-3"
                             style="max-height:250px;object-fit:cover"
                             onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge ${item.is_available ? 'bg-success' : 'bg-danger'}">
                                ${item.is_available ? 'Available' : 'Unavailable'}
                            </span>
                            ${item.is_featured
                                ? '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Featured</span>'
                                : ''}
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h4>${escapeHtml(item.name)}</h4>
                        <p><span class="badge bg-secondary">
                            ${escapeHtml(item.category_name || 'Uncategorized')}
                        </span></p>
                        <h3 class="text-success">$${parseFloat(item.price).toFixed(2)}</h3>
                        <p>${item.description
                            ? escapeHtml(item.description).replace(/\n/g,'<br>')
                            : '<span class="text-muted">No description</span>'}</p>
                    </div>
                </div>
            `;

            document.getElementById('editItemBtn').href =
                'menu_items.php?source=edit_item&id=' + itemId;
        })
        .catch(() => showError('Error loading item details'));
}

/* =====================================================
   DELETE MENU ITEM (ISOLATED & SAFE)
===================================================== */
function menuShowDeleteConfirm(itemId, itemName) {
    if (!itemId) {
        showError('Invalid item ID');
        return;
    }

    menuDeleteItemId = itemId;

    const nameEl = document.getElementById('menuDeleteItemName');
    if (!nameEl) {
        console.error('menuDeleteItemName not found');
        return;
    }

    nameEl.textContent = itemName;

    if (!menuDeleteModalInstance) {
        menuDeleteModalInstance = new bootstrap.Modal(
            document.getElementById('deleteConfirmModal')
        );
    }

    menuDeleteModalInstance.show();
}

function menuDeleteItem() {
    if (!menuDeleteItemId) {
        showError('No item selected');
        return;
    }

    const btn = document.getElementById('menuConfirmDeleteBtn');
    const originalHTML = btn.innerHTML;

    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    btn.disabled = true;

    fetch('includes/delete_menu_item.php?id=' + menuDeleteItemId, {
        method: 'GET',
        cache: 'no-store'
    })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;

            if (!data.success) {
                showError(data.message || 'Delete failed');
                return;
            }

            menuDeleteModalInstance.hide();

            const row = document.getElementById('item-row-' + menuDeleteItemId);
            if (row) {
                if ($.fn.DataTable.isDataTable('#menuItemsTable')) {
                    $('#menuItemsTable').DataTable().row(row).remove().draw();
                } else {
                    row.remove();
                }
            }

            showSuccess(data.message || 'Menu item deleted');
            menuDeleteItemId = null;
        })
        .catch(() => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            showError('Server error while deleting');
        });
}

/* =====================================================
   HELPERS
===================================================== */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDateTime(str) {
    return new Date(str).toLocaleString();
}

function showSuccess(msg) {
    document.getElementById('toastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('successToast')).show();
}

function showError(msg) {
    document.getElementById('errorToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('errorToast')).show();
}

/* =====================================================
   DATATABLE INIT
===================================================== */
document.addEventListener('DOMContentLoaded', () => {
    if ($.fn.DataTable.isDataTable('#menuItemsTable')) {
        $('#menuItemsTable').DataTable().destroy();
    }

    $('#menuItemsTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [8] }],
        responsive: true
    });
});
</script>
