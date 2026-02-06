<?php
// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch all branches
$sql = "SELECT * FROM branches ORDER BY created_at DESC";
$result = $connection->query($sql);

// Check for errors
if (!$result) {
    die("Query failed: " . $connection->error);
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_branches,
    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_branches,
    COUNT(CASE WHEN is_active = 0 THEN 1 END) as inactive_branches
    FROM branches";
$stats_result = $connection->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!-- Main Content -->
<div class="main-content" id="branchesMainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Branch Management</h1>
            <div>
                <a href="branches.php?source=add_branch" class="btn btn-primary me-2">
                    <i class="bi bi-building-add"></i> Add New Branch
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
                                <h6 class="card-title">Total Branches</h6>
                                <h2 class="mb-0"><?php echo $stats['total_branches'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-buildings display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Active</h6>
                                <h2 class="mb-0"><?php echo $stats['active_branches'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-check-circle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Inactive</h6>
                                <h2 class="mb-0"><?php echo $stats['inactive_branches'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-x-circle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="branchSearch" 
                               placeholder="Search by branch name, address, or phone..." 
                               onkeyup="searchBranches()">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="statusFilter" onchange="filterBranches()">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100" onclick="resetBranchFilters()">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branches Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>All Branches</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="branchesTable">
                        <thead>
                            <tr class="table-dark">
                                <th width="60">ID</th>
                                <th>Branch Name</th>
                                <th>Contact Info</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th width="150" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($branch = $result->fetch_assoc()): ?>
                                    <tr id="branch-row-<?php echo $branch['id']; ?>" 
                                        class="branch-row"
                                        data-status="<?php echo $branch['is_active'] ? 'active' : 'inactive'; ?>">
                                        <td class="fw-bold">#<?php echo $branch['id']; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($branch['name']); ?></div>
                                            <?php if ($branch['opening_hours']): ?>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock me-1"></i>
                                                    <?php 
                                                    echo strlen($branch['opening_hours']) > 50 
                                                        ? htmlspecialchars(substr($branch['opening_hours'], 0, 50)) . '...' 
                                                        : htmlspecialchars($branch['opening_hours']);
                                                    ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($branch['phone']): ?>
                                                <div><i class="bi bi-telephone me-1"></i> <?php echo htmlspecialchars($branch['phone']); ?></div>
                                            <?php endif; ?>
                                            <?php if ($branch['email']): ?>
                                                <div><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($branch['email']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            echo strlen($branch['address']) > 100 
                                                ? htmlspecialchars(substr($branch['address'], 0, 100)) . '...' 
                                                : htmlspecialchars($branch['address']);
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($branch['is_active']): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i> Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($branch['created_at'])); ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-info view-branch-btn" 
                                                        onclick="branchShowViewModal(<?php echo $branch['id']; ?>)" 
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                              
                                                <a href='branches.php?source=edit_branch&id=<?php echo $branch['id']; ?>'
                                                   class="btn btn-outline-warning"
                                                   title="Edit Branch">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-outline-danger delete-branch-btn" 
                                                        onclick="branchShowDeleteConfirm(
                                                        <?php echo (int)$branch['id']; ?>,
                                                        '<?php echo htmlspecialchars(addslashes($branch['name']), ENT_QUOTES); ?>')"
                                                        title="Delete Branch">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                
                                                <?php if ($branch['is_active']): ?>
                                                    <button type="button" class="btn btn-outline-secondary" 
                                                            onclick="toggleBranchStatus(<?php echo $branch['id']; ?>, 0)"
                                                            title="Deactivate">
                                                        <i class="bi bi-toggle-on"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline-success" 
                                                            onclick="toggleBranchStatus(<?php echo $branch['id']; ?>, 1)"
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
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-building display-4 d-block mb-2"></i>
                                            <h5>No branches found</h5>
                                            <p>Get started by adding your first branch.</p>
                                            <a href="branches.php?source=add_branch" class="btn btn-primary mt-2">
                                                <i class="bi bi-building-add"></i> Add Branch
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

<!-- View Branch Modal -->
<div class="modal fade" id="viewBranchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content reservation-modal-theme">
            <div class="modal-header reservation-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-building display-6 text-theme"></i>
                    <h5 class="modal-title mb-0">Branch Details</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body reservation-modal-body" id="branchDetails">
                <!-- Branch details will be loaded here via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-theme" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer reservation-modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editBranchBtn" class="btn btn-primary">Edit Branch</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Branch Modal -->
<div class="modal fade" id="deleteBranchConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteBranchName"></strong>? This action cannot be undone.
                <div class="alert alert-danger mt-2">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Deleting this branch will affect all associated data.
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmBranchDeleteCheckbox">
                    <label class="form-check-label" for="confirmBranchDeleteCheckbox">
                        I understand this action is irreversible
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBranchBtn" 
                        onclick="branchDeleteItem()" disabled>
                    Delete Branch
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="branchSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="branchToastMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="branchErrorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="branchErrorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
/* =====================================================
   GLOBAL STATE (BRANCH PAGE ONLY)
===================================================== */
let branchDeleteItemId = null;
let branchDeleteModalInstance = null;

/* =====================================================
   VIEW BRANCH DETAILS
===================================================== */
function branchShowViewModal(branchId) {
    if (!branchId) {
        branchShowError('Invalid branch ID');
        return;
    }

    const detailsEl = document.getElementById('branchDetails');
    detailsEl.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading branch details...</p>
        </div>
    `;

    const viewModal = new bootstrap.Modal(
        document.getElementById('viewBranchModal')
    );
    viewModal.show();

    fetch('includes/get_branch_details.php?id=' + branchId)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (!data.success || !data.branch) {
                detailsEl.innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load branch details
                    </div>`;
                return;
            }

            const branch = data.branch;
            const statusBadge = branch.is_active 
                ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>'
                : '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Inactive</span>';

            detailsEl.innerHTML = `
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 120px; height: 120px;">
                            <i class="bi bi-building display-3 text-muted"></i>
                        </div>
                        <h4>${escapeHtml(branch.name)}</h4>
                        ${statusBadge}
                    </div>
                    <div class="col-md-8">
                        <div class="row mb-3">
                            <div class="col-6">
                                <h6><i class="bi bi-geo-alt me-2"></i> Address</h6>
                                <p class="text-muted">${escapeHtml(branch.address).replace(/\n/g,'<br>')}</p>
                            </div>
                            <div class="col-6">
                                <h6><i class="bi bi-clock me-2"></i> Opening Hours</h6>
                                <p>${branch.opening_hours ? escapeHtml(branch.opening_hours).replace(/\n/g,'<br>') : 'Not specified'}</p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <h6><i class="bi bi-telephone me-2"></i> Phone</h6>
                                <p>${branch.phone ? escapeHtml(branch.phone) : 'Not provided'}</p>
                            </div>
                            <div class="col-6">
                                <h6><i class="bi bi-envelope me-2"></i> Email</h6>
                                <p>${branch.email ? escapeHtml(branch.email) : 'Not provided'}</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <h6><i class="bi bi-calendar-plus me-2"></i> Created</h6>
                                <p>${formatDate(branch.created_at)}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Set edit button URL
            document.getElementById('editBranchBtn').href =
                'branches.php?source=edit_branch&id=' + branchId;
        })
        .catch(() => branchShowError('Error loading branch details'));
}

/* =====================================================
   DELETE BRANCH FUNCTIONS
===================================================== */
function branchShowDeleteConfirm(branchId, branchName) {
    if (!branchId) {
        branchShowError('Invalid branch ID');
        return;
    }

    branchDeleteItemId = branchId;

    const nameEl = document.getElementById('deleteBranchName');
    if (!nameEl) {
        console.error('deleteBranchName not found');
        return;
    }

    nameEl.textContent = branchName;

    if (!branchDeleteModalInstance) {
        branchDeleteModalInstance = new bootstrap.Modal(
            document.getElementById('deleteBranchConfirmModal')
        );
    }

    // Reset checkbox
    document.getElementById('confirmBranchDeleteCheckbox').checked = false;
    document.getElementById('confirmDeleteBranchBtn').disabled = true;
    
    // Add checkbox listener
    document.getElementById('confirmBranchDeleteCheckbox').onchange = function() {
        document.getElementById('confirmDeleteBranchBtn').disabled = !this.checked;
    };

    branchDeleteModalInstance.show();
}

function branchDeleteItem() {
    if (!branchDeleteItemId) {
        branchShowError('No branch selected');
        return;
    }

    const btn = document.getElementById('confirmDeleteBranchBtn');
    const originalHTML = btn.innerHTML;

    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    btn.disabled = true;

    fetch('includes/delete_branch.php?id=' + branchDeleteItemId, {
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
                branchShowError(data.message || 'Delete failed');
                return;
            }

            branchDeleteModalInstance.hide();

            const row = document.getElementById('branch-row-' + branchDeleteItemId);
            if (row) {
                if ($.fn.DataTable.isDataTable('#branchesTable')) {
                    $('#branchesTable').DataTable().row(row).remove().draw();
                } else {
                    row.remove();
                }
            }

            branchShowSuccess(data.message || 'Branch deleted successfully');
            branchDeleteItemId = null;
        })
        .catch((error) => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            console.error('Delete error:', error);
            branchShowError('Server error while deleting');
        });
}

/* =====================================================
   TOGGLE BRANCH STATUS
===================================================== */
function toggleBranchStatus(branchId, newStatus) {
    if (!branchId) {
        branchShowError('Invalid branch ID');
        return;
    }

    fetch('includes/toggle_branch_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'id=' + branchId + '&status=' + newStatus
    })
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (!data.success) {
                branchShowError(data.message || 'Status update failed');
                return;
            }

            // Update button in table
            const row = document.getElementById('branch-row-' + branchId);
            if (row) {
                const btnGroup = row.querySelector('.btn-group');
                if (btnGroup) {
                    if (newStatus == 0) {
                        // Change to activate button
                        btnGroup.innerHTML = btnGroup.innerHTML.replace(
                            'bi-toggle-on">',
                            'bi-toggle-off"></i>'
                        ).replace(
                            'onclick="toggleBranchStatus(' + branchId + ', 0)"',
                            'onclick="toggleBranchStatus(' + branchId + ', 1)"'
                        ).replace(
                            'class="btn btn-outline-secondary"',
                            'class="btn btn-outline-success"'
                        ).replace(
                            'Deactivate',
                            'Activate'
                        );
                        
                        // Update status badge
                        const statusCell = row.cells[4];
                        statusCell.innerHTML = '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Inactive</span>';
                    } else {
                        // Change to deactivate button
                        btnGroup.innerHTML = btnGroup.innerHTML.replace(
                            'bi-toggle-off">',
                            'bi-toggle-on"></i>'
                        ).replace(
                            'onclick="toggleBranchStatus(' + branchId + ', 1)"',
                            'onclick="toggleBranchStatus(' + branchId + ', 0)"'
                        ).replace(
                            'class="btn btn-outline-success"',
                            'class="btn btn-outline-secondary"'
                        ).replace(
                            'Activate',
                            'Deactivate'
                        );
                        
                        // Update status badge
                        const statusCell = row.cells[4];
                        statusCell.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>';
                    }
                }
            }

            branchShowSuccess(data.message || 'Status updated successfully');
        })
        .catch((error) => {
            console.error('Status toggle error:', error);
            branchShowError('Server error');
        });
}

/* =====================================================
   SEARCH AND FILTER FUNCTIONS
===================================================== */
function searchBranches() {
    const searchTerm = document.getElementById('branchSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    
    document.querySelectorAll('.branch-row').forEach(row => {
        const name = row.cells[1].textContent.toLowerCase();
        const contact = row.cells[2].textContent.toLowerCase();
        const address = row.cells[3].textContent.toLowerCase();
        const status = row.dataset.status;
        
        let show = true;
        
        // Search term filter
        if (searchTerm && !name.includes(searchTerm) && !contact.includes(searchTerm) && !address.includes(searchTerm)) {
            show = false;
        }
        
        // Status filter
        if (statusFilter && status !== statusFilter) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function filterBranches() {
    searchBranches(); // Reuse search function for filtering
}

function resetBranchFilters() {
    document.getElementById('branchSearch').value = '';
    document.getElementById('statusFilter').value = '';
    document.querySelectorAll('.branch-row').forEach(row => {
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

function branchShowSuccess(msg) {
    document.getElementById('branchToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('branchSuccessToast')).show();
}

function branchShowError(msg) {
    document.getElementById('branchErrorToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('branchErrorToast')).show();
}

/* =====================================================
   DATATABLE INIT
===================================================== */
document.addEventListener('DOMContentLoaded', () => {
    if ($.fn.DataTable.isDataTable('#branchesTable')) {
        $('#branchesTable').DataTable().destroy();
    }

    $('#branchesTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [6] }],
        responsive: true,
        language: {
            search: "Search branches:",
            lengthMenu: "Show _MENU_ branches per page"
        }
    });
});
</script>