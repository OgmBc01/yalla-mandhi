<?php
// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'employee'])) {
    header("Location: login.php");
    exit();
}

// Fetch all testimonials
$sql = "SELECT * FROM testimonials ORDER BY created_at DESC";
$result = $connection->query($sql);

// Check for errors
if (!$result) {
    die("Query failed: " . $connection->error);
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_testimonials,
    COUNT(CASE WHEN is_approved = 1 THEN 1 END) as approved_testimonials,
    COUNT(CASE WHEN is_approved = 0 THEN 1 END) as pending_testimonials,
    AVG(rating) as avg_rating
    FROM testimonials";
$stats_result = $connection->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!-- Main Content -->
<div class="main-content" id="testimonialsMainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Testimonials Management</h1>
            <div>
                <a href="testimonials.php?source=add_testimonial" class="btn btn-primary me-2">
                    <i class="bi bi-chat-square-quote"></i> Add New Testimonial
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
                                <h6 class="card-title">Total</h6>
                                <h2 class="mb-0"><?php echo $stats['total_testimonials'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-chat-quote display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Approved</h6>
                                <h2 class="mb-0"><?php echo $stats['approved_testimonials'] ?? 0; ?></h2>
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
                                <h6 class="card-title">Pending</h6>
                                <h2 class="mb-0"><?php echo $stats['pending_testimonials'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-clock display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Avg Rating</h6>
                                <h2 class="mb-0"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?>/5</h2>
                            </div>
                            <i class="bi bi-star display-4 opacity-50"></i>
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
                        <input type="text" class="form-control" id="testimonialSearch" 
                               placeholder="Search by customer name or review..." 
                               onkeyup="searchTestimonials()">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter" onchange="filterTestimonials()">
                            <option value="">All Status</option>
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="ratingFilter" onchange="filterTestimonials()">
                            <option value="">All Ratings</option>
                            <option value="5">5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="2">2 Stars</option>
                            <option value="1">1 Star</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100" onclick="resetTestimonialFilters()">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testimonials Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-chat-square-text me-2"></i>All Testimonials</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="testimonialsTable">
                        <thead>
                            <tr class="table-dark">
                                <th width="60">ID</th>
                                <th width="80">Image</th>
                                <th>Customer</th>
                                <th>Review</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th width="180" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($testimonial = $result->fetch_assoc()): ?>
                                    <tr id="testimonial-row-<?php echo $testimonial['id']; ?>" 
                                        class="testimonial-row"
                                        data-status="<?php echo $testimonial['is_approved'] ? 'approved' : 'pending'; ?>"
                                        data-rating="<?php echo $testimonial['rating'] ?? 0; ?>">
                                        <td class="fw-bold">#<?php echo $testimonial['id']; ?></td>
                                        <td>
                                            <?php if (!empty($testimonial['customer_image'])): ?>
                                                <img src="../uploads/testimonials/<?php echo htmlspecialchars($testimonial['customer_image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($testimonial['customer_name']); ?>"
                                                     class="rounded-circle" width="50" height="50"
                                                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($testimonial['customer_name']); ?>&background=random'">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="bi bi-person text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($testimonial['customer_name']); ?></div>
                                        </td>
                                        <td>
                                            <div class="review-text">
                                                <?php 
                                                echo strlen($testimonial['review']) > 100 
                                                    ? htmlspecialchars(substr($testimonial['review'], 0, 100)) . '...' 
                                                    : htmlspecialchars($testimonial['review']);
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="rating-stars">
                                                <?php
                                                $rating = $testimonial['rating'] ?? 0;
                                                for ($i = 1; $i <= 5; $i++):
                                                    if ($i <= $rating):
                                                        echo '<i class="bi bi-star-fill text-warning"></i>';
                                                    else:
                                                        echo '<i class="bi bi-star text-muted"></i>';
                                                    endif;
                                                endfor;
                                                ?>
                                                <small class="text-muted ms-1">(<?php echo $rating; ?>)</small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($testimonial['is_approved']): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Approved
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock"></i> Pending
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($testimonial['created_at'])); ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-info view-testimonial-btn" 
                                                        onclick="testimonialShowViewModal(<?php echo $testimonial['id']; ?>)" 
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                              
                                                <a href='testimonials.php?source=edit_testimonial&id=<?php echo $testimonial['id']; ?>'
                                                   class="btn btn-outline-warning"
                                                   title="Edit Testimonial">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-outline-danger delete-testimonial-btn" 
                                                        onclick="testimonialShowDeleteConfirm(
                                                        <?php echo (int)$testimonial['id']; ?>,
                                                        '<?php echo htmlspecialchars(addslashes($testimonial['customer_name']), ENT_QUOTES); ?>')"
                                                        title="Delete Testimonial">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                
                                                <?php if (!$testimonial['is_approved']): ?>
                                                    <button type="button" class="btn btn-outline-success" 
                                                            onclick="toggleTestimonialApproval(<?php echo $testimonial['id']; ?>, 1)"
                                                            title="Approve">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline-secondary" 
                                                            onclick="toggleTestimonialApproval(<?php echo $testimonial['id']; ?>, 0)"
                                                            title="Unapprove">
                                                        <i class="bi bi-x-lg"></i>
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
                                            <i class="bi bi-chat-quote display-4 d-block mb-2"></i>
                                            <h5>No testimonials found</h5>
                                            <p>Get started by adding your first testimonial.</p>
                                            <a href="testimonials.php?source=add_testimonial" class="btn btn-primary mt-2">
                                                <i class="bi bi-chat-square-quote"></i> Add Testimonial
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

<!-- View Testimonial Modal -->
<div class="modal fade" id="viewTestimonialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content reservation-modal-theme">
            <div class="modal-header reservation-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-chat-left-quote display-6 text-theme"></i>
                    <h5 class="modal-title mb-0">Testimonial Details</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body reservation-modal-body" id="testimonialDetails">
                <!-- Testimonial details will be loaded here via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-theme" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer reservation-modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editTestimonialBtn" class="btn btn-primary">Edit Testimonial</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Testimonial Modal -->
<div class="modal fade" id="deleteTestimonialConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete Testimonial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the testimonial from <strong id="deleteTestimonialName"></strong>? This action cannot be undone.
                <div class="alert alert-danger mt-2">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This will permanently remove the testimonial.
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmTestimonialDeleteCheckbox">
                    <label class="form-check-label" for="confirmTestimonialDeleteCheckbox">
                        I understand this action is irreversible
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteTestimonialBtn" 
                        onclick="testimonialDeleteItem()" disabled>
                    Delete Testimonial
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="testimonialSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="testimonialToastMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="testimonialErrorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="testimonialErrorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
/* =====================================================
   GLOBAL STATE (TESTIMONIAL PAGE ONLY)
===================================================== */
let testimonialDeleteItemId = null;
let testimonialDeleteModalInstance = null;

/* =====================================================
   VIEW TESTIMONIAL DETAILS
===================================================== */
function testimonialShowViewModal(testimonialId) {
    if (!testimonialId) {
        testimonialShowError('Invalid testimonial ID');
        return;
    }

    const detailsEl = document.getElementById('testimonialDetails');
    detailsEl.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading testimonial details...</p>
        </div>
    `;

    const viewModal = new bootstrap.Modal(
        document.getElementById('viewTestimonialModal')
    );
    viewModal.show();

    fetch('includes/get_testimonial_details.php?id=' + testimonialId)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (!data.success || !data.testimonial) {
                detailsEl.innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load testimonial details
                    </div>`;
                return;
            }

            const testimonial = data.testimonial;
            const statusBadge = testimonial.is_approved 
                ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Approved</span>'
                : '<span class="badge bg-warning"><i class="bi bi-clock"></i> Pending</span>';

            // Generate star rating HTML
            let starsHtml = '';
            const rating = testimonial.rating || 0;
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    starsHtml += '<i class="bi bi-star-fill text-warning fs-4"></i>';
                } else {
                    starsHtml += '<i class="bi bi-star text-muted fs-4"></i>';
                }
            }

            // Generate image HTML
            let imageHtml = '';
            if (testimonial.customer_image) {
                imageHtml = `<img src="../uploads/testimonials/${escapeHtml(testimonial.customer_image)}" 
                              class="img-fluid rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;"
                              onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(testimonial.customer_name)}&background=random'">`;
            } else {
                imageHtml = `<div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 120px; height: 120px;">
                                <i class="bi bi-person display-3 text-muted"></i>
                            </div>`;
            }

            detailsEl.innerHTML = `
                <div class="text-center mb-4">
                    ${imageHtml}
                    <h3>${escapeHtml(testimonial.customer_name)}</h3>
                    <div class="mb-3">
                        ${starsHtml}
                        <span class="ms-2 fw-bold">${rating}/5</span>
                    </div>
                    ${statusBadge}
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-chat-left-quote me-2"></i>Review</h5>
                        <p class="card-text" style="white-space: pre-wrap;">${escapeHtml(testimonial.review)}</p>
                    </div>
                </div>
                
                <div class="mt-3 text-muted">
                    <i class="bi bi-calendar me-1"></i> Submitted on ${formatDate(testimonial.created_at)}
                </div>
            `;

            // Set edit button URL
            document.getElementById('editTestimonialBtn').href =
                'testimonials.php?source=edit_testimonial&id=' + testimonialId;
        })
        .catch(() => testimonialShowError('Error loading testimonial details'));
}

/* =====================================================
   DELETE TESTIMONIAL FUNCTIONS
===================================================== */
function testimonialShowDeleteConfirm(testimonialId, customerName) {
    if (!testimonialId) {
        testimonialShowError('Invalid testimonial ID');
        return;
    }

    testimonialDeleteItemId = testimonialId;

    const nameEl = document.getElementById('deleteTestimonialName');
    if (!nameEl) {
        console.error('deleteTestimonialName not found');
        return;
    }

    nameEl.textContent = customerName;

    if (!testimonialDeleteModalInstance) {
        testimonialDeleteModalInstance = new bootstrap.Modal(
            document.getElementById('deleteTestimonialConfirmModal')
        );
    }

    // Reset checkbox
    document.getElementById('confirmTestimonialDeleteCheckbox').checked = false;
    document.getElementById('confirmDeleteTestimonialBtn').disabled = true;
    
    // Add checkbox listener
    document.getElementById('confirmTestimonialDeleteCheckbox').onchange = function() {
        document.getElementById('confirmDeleteTestimonialBtn').disabled = !this.checked;
    };

    testimonialDeleteModalInstance.show();
}

function testimonialDeleteItem() {
    if (!testimonialDeleteItemId) {
        testimonialShowError('No testimonial selected');
        return;
    }

    const btn = document.getElementById('confirmDeleteTestimonialBtn');
    const originalHTML = btn.innerHTML;

    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    btn.disabled = true;

    fetch('includes/delete_testimonial.php?id=' + testimonialDeleteItemId, {
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
                testimonialShowError(data.message || 'Delete failed');
                return;
            }

            testimonialDeleteModalInstance.hide();

            const row = document.getElementById('testimonial-row-' + testimonialDeleteItemId);
            if (row) {
                if ($.fn.DataTable.isDataTable('#testimonialsTable')) {
                    $('#testimonialsTable').DataTable().row(row).remove().draw();
                } else {
                    row.remove();
                }
            }

            testimonialShowSuccess(data.message || 'Testimonial deleted successfully');
            testimonialDeleteItemId = null;
        })
        .catch((error) => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            console.error('Delete error:', error);
            testimonialShowError('Server error while deleting');
        });
}

/* =====================================================
   TOGGLE TESTIMONIAL APPROVAL
===================================================== */
function toggleTestimonialApproval(testimonialId, newStatus) {
    if (!testimonialId) {
        testimonialShowError('Invalid testimonial ID');
        return;
    }

    fetch('includes/toggle_testimonial_approval.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'id=' + testimonialId + '&status=' + newStatus
    })
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (!data.success) {
                testimonialShowError(data.message || 'Status update failed');
                return;
            }

            // Update button in table
            const row = document.getElementById('testimonial-row-' + testimonialId);
            if (row) {
                const btnGroup = row.querySelector('.btn-group');
                if (btnGroup) {
                    if (newStatus == 0) {
                        // Change to approve button
                        btnGroup.innerHTML = btnGroup.innerHTML.replace(
                            'bi-x-lg">',
                            'bi-check-lg"></i>'
                        ).replace(
                            'onclick="toggleTestimonialApproval(' + testimonialId + ', 0)"',
                            'onclick="toggleTestimonialApproval(' + testimonialId + ', 1)"'
                        ).replace(
                            'class="btn btn-outline-secondary"',
                            'class="btn btn-outline-success"'
                        ).replace(
                            'Unapprove',
                            'Approve'
                        );
                        
                        // Update status badge
                        const statusCell = row.cells[5];
                        statusCell.innerHTML = '<span class="badge bg-warning"><i class="bi bi-clock"></i> Pending</span>';
                    } else {
                        // Change to unapprove button
                        btnGroup.innerHTML = btnGroup.innerHTML.replace(
                            'bi-check-lg">',
                            'bi-x-lg"></i>'
                        ).replace(
                            'onclick="toggleTestimonialApproval(' + testimonialId + ', 1)"',
                            'onclick="toggleTestimonialApproval(' + testimonialId + ', 0)"'
                        ).replace(
                            'class="btn btn-outline-success"',
                            'class="btn btn-outline-secondary"'
                        ).replace(
                            'Approve',
                            'Unapprove'
                        );
                        
                        // Update status badge
                        const statusCell = row.cells[5];
                        statusCell.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Approved</span>';
                    }
                }
            }

            testimonialShowSuccess(data.message || 'Testimonial status updated successfully');
        })
        .catch((error) => {
            console.error('Approval toggle error:', error);
            testimonialShowError('Server error');
        });
}

/* =====================================================
   SEARCH AND FILTER FUNCTIONS
===================================================== */
function searchTestimonials() {
    const searchTerm = document.getElementById('testimonialSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const ratingFilter = document.getElementById('ratingFilter').value;
    
    document.querySelectorAll('.testimonial-row').forEach(row => {
        const name = row.cells[2].textContent.toLowerCase();
        const review = row.cells[3].querySelector('.review-text').textContent.toLowerCase();
        const status = row.dataset.status;
        const rating = parseInt(row.dataset.rating);
        
        let show = true;
        
        // Search term filter
        if (searchTerm && !name.includes(searchTerm) && !review.includes(searchTerm)) {
            show = false;
        }
        
        // Status filter
        if (statusFilter && status !== statusFilter) {
            show = false;
        }
        
        // Rating filter
        if (ratingFilter && rating != ratingFilter) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function filterTestimonials() {
    searchTestimonials(); // Reuse search function for filtering
}

function resetTestimonialFilters() {
    document.getElementById('testimonialSearch').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('ratingFilter').value = '';
    document.querySelectorAll('.testimonial-row').forEach(row => {
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

function testimonialShowSuccess(msg) {
    document.getElementById('testimonialToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('testimonialSuccessToast')).show();
}

function testimonialShowError(msg) {
    document.getElementById('testimonialErrorToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('testimonialErrorToast')).show();
}

/* =====================================================
   DATATABLE INIT
===================================================== */
document.addEventListener('DOMContentLoaded', () => {
    if ($.fn.DataTable.isDataTable('#testimonialsTable')) {
        $('#testimonialsTable').DataTable().destroy();
    }

    $('#testimonialsTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [7] }],
        responsive: true,
        language: {
            search: "Search testimonials:",
            lengthMenu: "Show _MENU_ testimonials per page"
        }
    });
});
</script>