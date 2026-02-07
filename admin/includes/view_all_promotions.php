<?php
// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'employee'])) {
    header("Location: login.php");
    exit();
}

// Fetch all promotions
$sql = "SELECT * FROM promotions ORDER BY display_order, created_at DESC";
$result = $connection->query($sql);

// Check for errors
if (!$result) {
    die("Query failed: " . $connection->error);
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_promotions,
    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_promotions,
    COUNT(CASE WHEN is_featured = 1 THEN 1 END) as featured_promotions,
    COUNT(CASE WHEN is_highlighted = 1 THEN 1 END) as highlighted_promotions
    FROM promotions";
$stats_result = $connection->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!-- Main Content -->
<div class="main-content" id="promotionsMainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Promotions Management</h1>
            <div>
                <a href="promotions.php?source=add_promotion" class="btn btn-primary me-2">
                    <i class="bi bi-tag"></i> Add New Promotion
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
                                <h6 class="card-title">Total Promotions</h6>
                                <h2 class="mb-0"><?php echo $stats['total_promotions'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-tags display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Active</h6>
                                <h2 class="mb-0"><?php echo $stats['active_promotions'] ?? 0; ?></h2>
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
                                <h2 class="mb-0"><?php echo $stats['featured_promotions'] ?? 0; ?></h2>
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
                                <h6 class="card-title">Highlighted</h6>
                                <h2 class="mb-0"><?php echo $stats['highlighted_promotions'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-megaphone display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <input type="text" class="form-control" id="promotionSearch" 
                               placeholder="Search by title or description..." 
                               onkeyup="searchPromotions()">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter" onchange="filterPromotions()">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="typeFilter" onchange="filterPromotions()">
                            <option value="">All Types</option>
                            <option value="family">Family</option>
                            <option value="business">Business</option>
                            <option value="early_bird">Early Bird</option>
                            <option value="birthday">Birthday</option>
                            <option value="takeaway">Takeaway</option>
                            <option value="student">Student</option>
                            <option value="seasonal">Seasonal</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100" onclick="resetPromotionFilters()">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promotions Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-percent me-2"></i>All Promotions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="promotionsTable">
                        <thead>
                            <tr class="table-dark">
                                <th width="60">ID</th>
                                <th width="100">Image</th>
                                <th>Promotion</th>
                                <th>Pricing</th>
                                <th>Status</th>
                                <th>Type</th>
                                <th>Validity</th>
                                <th width="180" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($promotion = $result->fetch_assoc()): ?>
                                    <tr id="promotion-row-<?php echo $promotion['id']; ?>" 
                                        class="promotion-row"
                                        data-status="<?php echo $promotion['is_active'] ? 'active' : 'inactive'; ?>"
                                        data-type="<?php echo $promotion['offer_type']; ?>">
                                        <td class="fw-bold">#<?php echo $promotion['id']; ?></td>
                                        <td>
                                            <?php if (!empty($promotion['image_url'])): ?>
                                                <img src="../uploads/promotions/<?php echo htmlspecialchars($promotion['image_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($promotion['title']); ?>"
                                                     class="rounded" width="60" height="60" style="object-fit: cover;"
                                                     onerror="this.src='https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80'">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($promotion['title']); ?></div>
                                            <?php if ($promotion['subtitle']): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($promotion['subtitle']); ?></small>
                                            <?php endif; ?>
                                            <div class="mt-1">
                                                <span class="badge" style="background-color: <?php echo htmlspecialchars($promotion['badge_color'] ?? 'var(--color-red)'); ?>; color: white;">
                                                    <?php echo htmlspecialchars($promotion['badge_text'] ?? 'Offer'); ?>
                                                </span>
                                                <?php if ($promotion['is_featured']): ?>
                                                    <span class="badge bg-warning text-dark ms-1">
                                                        <i class="bi bi-star-fill"></i> Featured
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($promotion['is_highlighted']): ?>
                                                    <span class="badge bg-info ms-1">
                                                        <i class="bi bi-megaphone"></i> Highlighted
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-success">
                                                <?php echo htmlspecialchars($promotion['currency'] . ' ' . number_format($promotion['offer_price'], 2)); ?>
                                            </div>
                                            <?php if ($promotion['original_price']): ?>
                                                <div class="text-muted">
                                                    <small>
                                                        <del><?php echo htmlspecialchars($promotion['currency'] . ' ' . number_format($promotion['original_price'], 2)); ?></del>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($promotion['discount_percent']): ?>
                                                <span class="badge bg-danger">
                                                    <?php echo htmlspecialchars($promotion['discount_percent']); ?>% OFF
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($promotion['is_active']): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i> Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $type_badges = [
                                                'family' => 'primary',
                                                'business' => 'success',
                                                'early_bird' => 'warning',
                                                'birthday' => 'info',
                                                'takeaway' => 'dark',
                                                'student' => 'secondary',
                                                'seasonal' => 'copper',
                                                'other' => 'light text-dark'
                                            ];
                                            $type_badge = $type_badges[$promotion['offer_type']] ?? 'light text-dark';
                                            ?>
                                            <span class="badge bg-<?php echo $type_badge; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $promotion['offer_type'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($promotion['valid_until']): ?>
                                                <div class="text-muted">
                                                    <small>Until <?php echo date('M d, Y', strtotime($promotion['valid_until'])); ?></small>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($promotion['time_slot']): ?>
                                                <div><small><?php echo htmlspecialchars($promotion['time_slot']); ?></small></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-info view-promotion-btn" 
                                                        onclick="promotionShowViewModal(<?php echo $promotion['id']; ?>)" 
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                              
                                                <a href='promotions.php?source=edit_promotion&id=<?php echo $promotion['id']; ?>'
                                                   class="btn btn-outline-warning"
                                                   title="Edit Promotion">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-outline-danger delete-promotion-btn" 
                                                        onclick="promotionShowDeleteConfirm(
                                                        <?php echo (int)$promotion['id']; ?>,
                                                        '<?php echo htmlspecialchars(addslashes($promotion['title']), ENT_QUOTES); ?>')"
                                                        title="Delete Promotion">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                
                                                <?php if ($promotion['is_active']): ?>
                                                    <button type="button" class="btn btn-outline-secondary" 
                                                            onclick="togglePromotionStatus(<?php echo $promotion['id']; ?>, 0)"
                                                            title="Deactivate">
                                                        <i class="bi bi-toggle-on"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline-success" 
                                                            onclick="togglePromotionStatus(<?php echo $promotion['id']; ?>, 1)"
                                                            title="Activate">
                                                        <i class="bi bi-toggle-off"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-tag display-4 d-block mb-2"></i>
                                            <h5>No promotions found</h5>
                                            <p>Get started by adding your first promotion.</p>
                                            <a href="promotions.php?source=add_promotion" class="btn btn-primary mt-2">
                                                <i class="bi bi-tag"></i> Add Promotion
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

<!-- View Promotion Modal -->
<div class="modal fade" id="viewPromotionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content reservation-modal-theme">
            <div class="modal-header reservation-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-percent display-6 text-theme"></i>
                    <h5 class="modal-title mb-0">Promotion Details</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body reservation-modal-body" id="promotionDetails">
                <!-- Promotion details will be loaded here via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-theme" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer reservation-modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editPromotionBtn" class="btn btn-theme"><i class="bi bi-pencil me-2"></i>Edit Promotion</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Promotion Modal -->
<div class="modal fade" id="deletePromotionConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete Promotion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deletePromotionName"></strong>? This action cannot be undone.
                <div class="alert alert-danger mt-2">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This will permanently remove the promotion.
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmPromotionDeleteCheckbox">
                    <label class="form-check-label" for="confirmPromotionDeleteCheckbox">
                        I understand this action is irreversible
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeletePromotionBtn" 
                        onclick="promotionDeleteItem()" disabled>
                    Delete Promotion
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="promotionSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="promotionToastMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="promotionErrorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="promotionErrorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Success Toast for Promotion Deletion -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="promotionDeleteSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="promotionDeleteToastMessage">Promotion deleted successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
/* =====================================================
   GLOBAL STATE (PROMOTION PAGE ONLY)
===================================================== */
let promotionDeleteItemId = null;
let promotionDeleteModalInstance = null;

/* =====================================================
   VIEW PROMOTION DETAILS
===================================================== */
function promotionShowViewModal(promotionId) {
    if (!promotionId) {
        promotionShowError('Invalid promotion ID');
        return;
    }

    const detailsEl = document.getElementById('promotionDetails');
    detailsEl.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-theme"></div>
            <p class="mt-2">Loading promotion details...</p>
        </div>
    `;

    const viewModal = new bootstrap.Modal(
        document.getElementById('viewPromotionModal')
    );
    viewModal.show();

    fetch('includes/get_promotion_details.php?id=' + promotionId)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (!data.success || !data.promotion) {
                detailsEl.innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load promotion details
                    </div>`;
                return;
            }

            const promotion = data.promotion;
            const imageUrl = promotion.image_url
                ? '../uploads/promotions/' + promotion.image_url
                : 'https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';

            detailsEl.innerHTML = `
                <div class="row">
                    <div class="col-md-5 text-center">
                        <img src="${imageUrl}" class="img-fluid rounded mb-3 border"
                             style="max-height:250px;object-fit:cover"
                             onerror="this.src='https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <span class="badge ${promotion.is_active ? 'bg-success' : 'bg-danger'}">
                                ${promotion.is_active ? 'Active' : 'Inactive'}
                            </span>
                            ${promotion.is_featured
                                ? '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Featured</span>'
                                : ''}
                            ${promotion.is_highlighted
                                ? '<span class="badge bg-info"><i class="bi bi-megaphone"></i> Highlighted</span>'
                                : ''}
                        </div>
                        <span class="badge bg-secondary mb-2">
                            ${promotion.offer_type ? promotion.offer_type.replace('_', ' ').toUpperCase() : 'Other'}
                        </span>
                        <span class="badge" style="background-color: ${promotion.badge_color}; color: white;">
                            ${promotion.badge_text || 'Offer'}
                        </span>
                    </div>
                    <div class="col-md-7">
                        <h4>${escapeHtml(promotion.title)}</h4>
                        ${promotion.subtitle ? `<div class="text-muted mb-2">${escapeHtml(promotion.subtitle)}</div>` : ''}
                        <h3 class="text-success">${promotion.currency} ${parseFloat(promotion.offer_price).toFixed(2)}</h3>
                        ${promotion.original_price ? `<div class="text-muted"><small><del>${promotion.currency} ${parseFloat(promotion.original_price).toFixed(2)}</del></small></div>` : ''}
                        ${promotion.discount_percent ? `<span class="badge bg-danger mb-2">${parseFloat(promotion.discount_percent)}% OFF</span>` : ''}
                        <div class="mb-3">
                            <h6>Description</h6>
                            <p>${promotion.description
                                ? escapeHtml(promotion.description).replace(/\n/g,'<br>')
                                : '<span class="text-muted">No description</span>'}</p>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <h6><i class="bi bi-calendar me-2"></i> Validity</h6>
                                <p>${promotion.valid_from ? formatDate(promotion.valid_from) : 'N/A'} - ${promotion.valid_until ? formatDate(promotion.valid_until) : 'N/A'}</p>
                            </div>
                            <div class="col-6">
                                <h6><i class="bi bi-clock me-2"></i> Time Slot</h6>
                                <p>${promotion.time_slot ? escapeHtml(promotion.time_slot) : 'Any time'}</p>
                            </div>
                        </div>
                        ${promotion.requirements ? `
                            <div class="mb-3">
                                <h6><i class="bi bi-list-check me-2"></i> Requirements</h6>
                                <p>${escapeHtml(promotion.requirements)}</p>
                            </div>
                        ` : ''}
                        <div class="row mb-3">
                            <div class="col-6">
                                <h6><i class="bi bi-people me-2"></i> Persons</h6>
                                <p>${promotion.min_persons || promotion.max_persons ? 
                                    `${promotion.min_persons || ''}${promotion.min_persons && promotion.max_persons ? '-' : ''}${promotion.max_persons || ''} people` : 
                                    'No limit'}</p>
                            </div>
                            <div class="col-6">
                                <h6><i class="bi bi-calculator me-2"></i> Min. Order</h6>
                                <p>${promotion.min_order_amount ? promotion.currency + ' ' + parseFloat(promotion.min_order_amount).toFixed(2) : 'No minimum'}</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6><i class="bi bi-link-45deg me-2"></i> Call to Action</h6>
                            <span class="badge bg-primary"><i class="${escapeHtml(promotion.cta_icon)} me-1"></i>${escapeHtml(promotion.cta_text)}</span>
                            <span class="ms-2 text-muted">${escapeHtml(promotion.cta_link)}</span>
                        </div>
                        <div class="mb-3">
                            <h6><i class="bi bi-sort-numeric-up me-2"></i> Display Order</h6>
                            <span class="badge bg-secondary">${promotion.display_order}</span>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('editPromotionBtn').href =
                'promotions.php?source=edit_promotion&id=' + promotionId;
        })
        .catch(() => promotionShowError('Error loading promotion details'));
}

/* =====================================================
   DELETE PROMOTION FUNCTIONS
===================================================== */
function promotionShowDeleteConfirm(promotionId, promotionName) {
    if (!promotionId) {
        promotionShowError('Invalid promotion ID');
        return;
    }

    promotionDeleteItemId = promotionId;

    const nameEl = document.getElementById('deletePromotionName');
    if (!nameEl) {
        console.error('deletePromotionName not found');
        return;
    }

    nameEl.textContent = promotionName;

    if (!promotionDeleteModalInstance) {
        promotionDeleteModalInstance = new bootstrap.Modal(
            document.getElementById('deletePromotionConfirmModal')
        );
    }

    // Reset checkbox
    document.getElementById('confirmPromotionDeleteCheckbox').checked = false;
    document.getElementById('confirmDeletePromotionBtn').disabled = true;
    
    // Add checkbox listener
    document.getElementById('confirmPromotionDeleteCheckbox').onchange = function() {
        document.getElementById('confirmDeletePromotionBtn').disabled = !this.checked;
    };

    promotionDeleteModalInstance.show();
}

function promotionDeleteItem() {
    if (!promotionDeleteItemId) {
        promotionShowError('No promotion selected');
        return;
    }

    const btn = document.getElementById('confirmDeletePromotionBtn');
    const originalHTML = btn.innerHTML;

    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    btn.disabled = true;

    fetch('includes/delete_promotion.php?id=' + promotionDeleteItemId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'confirm=1'
    })
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;

            if (!data.success) {
                promotionShowError(data.message || 'Delete failed');
                return;
            }

            promotionDeleteModalInstance.hide();

            const row = document.getElementById('promotion-row-' + promotionDeleteItemId);
            if (row) {
                if ($.fn.DataTable.isDataTable('#promotionsTable')) {
                    $('#promotionsTable').DataTable().row(row).remove().draw();
                } else {
                    row.remove();
                }
            }

            promotionShowSuccess(data.message || 'Promotion deleted successfully');
            showPromotionDeleteToast(data.message || 'Promotion deleted successfully');
            promotionDeleteItemId = null;
        })
        .catch((error) => {
            console.error('Delete error:', error);
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            promotionShowError('Server error while deleting');
        });
}

/* =====================================================
   TOGGLE PROMOTION STATUS
===================================================== */

document.addEventListener('DOMContentLoaded', function() {
    // Status toggle buttons
    const toggleButtons = document.querySelectorAll('.toggle-status-btn');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const promotionId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');
            const newStatus = currentStatus === '1' ? '0' : '1';
            const button = this;
            const statusBadge = document.querySelector(`.status-badge-${promotionId}`);
            
            // Show loading state
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
            button.disabled = true;
            
            // Get CSRF token if available
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            // Send AJAX request
            fetch('includes/toggle_promotion_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    id: promotionId,
                    status: newStatus,
                    csrf_token: csrfToken
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update button appearance
                    if (newStatus === '1') {
                        button.innerHTML = '<i class="bi bi-toggle-on"></i> Deactivate';
                        button.classList.remove('btn-outline-success');
                        button.classList.add('btn-outline-danger');
                        if (statusBadge) {
                            statusBadge.innerHTML = '<i class="bi bi-check-circle-fill"></i> Active';
                            statusBadge.classList.remove('bg-secondary');
                            statusBadge.classList.add('bg-success');
                        }
                    } else {
                        button.innerHTML = '<i class="bi bi-toggle-off"></i> Activate';
                        button.classList.remove('btn-outline-danger');
                        button.classList.add('btn-outline-success');
                        if (statusBadge) {
                            statusBadge.innerHTML = '<i class="bi bi-x-circle-fill"></i> Inactive';
                            statusBadge.classList.remove('bg-success');
                            statusBadge.classList.add('bg-secondary');
                        }
                    }
                    
                    // Update data attributes
                    button.setAttribute('data-status', newStatus);
                    
                    // Show success message
                    showAlert('success', data.message);
                } else {
                    // Show error message
                    showAlert('danger', data.message || 'Failed to update status');
                    // Revert button to original state
                    button.innerHTML = originalHTML;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Network error occurred. Please try again.');
                button.innerHTML = originalHTML;
            })
            .finally(() => {
                button.disabled = false;
            });
        });
    });
    
    function showAlert(type, message) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert-auto-dismiss');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-auto-dismiss position-fixed top-0 end-0 m-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Add to body
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});

/* =====================================================
   SEARCH AND FILTER FUNCTIONS
===================================================== */
function searchPromotions() {
    const searchTerm = document.getElementById('promotionSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const typeFilter = document.getElementById('typeFilter').value;
    
    document.querySelectorAll('.promotion-row').forEach(row => {
        const title = row.cells[2].textContent.toLowerCase();
        const status = row.dataset.status;
        const type = row.dataset.type;
        
        let show = true;
        
        // Search term filter
        if (searchTerm && !title.includes(searchTerm)) {
            show = false;
        }
        
        // Status filter
        if (statusFilter && status !== statusFilter) {
            show = false;
        }
        
        // Type filter
        if (typeFilter && type !== typeFilter) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function filterPromotions() {
    searchPromotions(); // Reuse search function for filtering
}

function resetPromotionFilters() {
    document.getElementById('promotionSearch').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('typeFilter').value = '';
    document.querySelectorAll('.promotion-row').forEach(row => {
        row.style.display = '';
    });
}

/* =====================================================
   HELPER FUNCTIONS
===================================================== */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDateTime(str) {
    if (!str) return 'N/A';
    return new Date(str).toLocaleString();
}

function formatDate(str) {
    if (!str) return 'N/A';
    return new Date(str).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function promotionShowSuccess(msg) {
    document.getElementById('promotionToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('promotionSuccessToast')).show();
}

function promotionShowError(msg) {
    document.getElementById('promotionErrorToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('promotionErrorToast')).show();
}

function showPromotionDeleteToast(message) {
    document.getElementById('promotionDeleteToastMessage').textContent = message || 'Promotion deleted successfully!';
    new bootstrap.Toast(document.getElementById('promotionDeleteSuccessToast')).show();
}

/* =====================================================
   DATATABLE INIT
===================================================== */
document.addEventListener('DOMContentLoaded', () => {
    if ($.fn.DataTable.isDataTable('#promotionsTable')) {
        $('#promotionsTable').DataTable().destroy();
    }

    $('#promotionsTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [7] }],
        responsive: true,
        language: {
            search: "Search promotions:",
            lengthMenu: "Show _MENU_ promotions per page"
        }
    });
});
</script>