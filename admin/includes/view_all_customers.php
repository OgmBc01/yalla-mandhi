<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all customers
$sql = "SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC";
$result = $connection->query($sql);

// Check for errors
if (!$result) {
    die("Query failed: " . $connection->error);
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_customers,
    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_customers,
    COUNT(CASE WHEN last_order_date IS NOT NULL THEN 1 END) as ordering_customers,
    COALESCE(SUM(loyalty_points), 0) as total_points
    FROM users WHERE role = 'customer'";
$stats_result = $connection->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Customer Management</h1>
            <div>
                <a href="customers.php?source=add_customer" class="btn btn-primary me-2">
                    <i class="bi bi-person-plus"></i> Add New Customer
                </a>
                <button class="btn btn-secondary" onclick="exportCustomers()">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Customers</h6>
                                <h2 class="mb-0"><?php echo $stats['total_customers'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-people display-4 opacity-50"></i>
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
                                <h2 class="mb-0"><?php echo $stats['active_customers'] ?? 0; ?></h2>
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
                                <h6 class="card-title">Ordering</h6>
                                <h2 class="mb-0"><?php echo $stats['ordering_customers'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-cart display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Points</h6>
                                <h2 class="mb-0"><?php echo number_format($stats['total_points'] ?? 0); ?></h2>
                            </div>
                            <i class="bi bi-gem display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="customerSearch" 
                               placeholder="Search by name, email, or phone..." 
                               onkeyup="searchCustomers()">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter" onchange="filterCustomers()">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="loyaltyFilter" onchange="filterCustomers()">
                            <option value="">All Loyalty</option>
                            <option value="high">High (500+ points)</option>
                            <option value="medium">Medium (100-500 points)</option>
                            <option value="low">Low (0-100 points)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>All Customers</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="customersTable">
                        <thead>
                            <tr class="table-dark">
                                <th width="50">ID</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Loyalty</th>
                                <th>Last Order</th>
                                <th>Registered</th>
                                <th width="180" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($customer = $result->fetch_assoc()): ?>
                                    <tr id="customer-row-<?php echo $customer['id']; ?>" 
                                        class="customer-row"
                                        data-status="<?php echo $customer['is_active'] ? 'active' : 'inactive'; ?>"
                                        data-points="<?php echo $customer['loyalty_points']; ?>">
                                        <td class="fw-bold">#<?php echo $customer['id']; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($customer['full_name'] ?: 'N/A'); ?></div>
                                            <small class="text-muted">@<?php echo htmlspecialchars($customer['username']); ?></small>
                                        </td>
                                        <td>
                                            <div><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($customer['email']); ?></div>
                                            <?php if ($customer['phone']): ?>
                                                <small><i class="bi bi-phone me-1"></i> <?php echo htmlspecialchars($customer['phone']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($customer['is_active']): ?>
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
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-gem text-primary me-1"></i>
                                                <span class="fw-bold"><?php echo number_format($customer['loyalty_points']); ?></span>
                                                <div class="progress ms-2" style="width: 80px; height: 6px;">
                                                    <?php
                                                    $progress = min($customer['loyalty_points'], 1000);
                                                    $percent = ($progress / 1000) * 100;
                                                    ?>
                                                    <div class="progress-bar bg-warning" style="width: <?php echo $percent; ?>%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($customer['last_order_date']): ?>
                                                <span class="text-success"><?php echo date('M d, Y', strtotime($customer['last_order_date'])); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">No orders yet</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-info view-customer-btn" 
                                                        onclick="customerShowViewModal(<?php echo $customer['id']; ?>)" 
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                              
                                                <a href='customers.php?source=edit_customer&id=<?php echo $customer['id']; ?>'
                                                   class="btn btn-outline-warning"
                                                   title="Edit Customer">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-outline-danger delete-customer-btn" 
                                                        onclick="customerShowDeleteConfirm(
                                                        <?php echo (int)$customer['id']; ?>,
                                                        '<?php echo htmlspecialchars(addslashes($customer['full_name'] ?: $customer['username']), ENT_QUOTES); ?>')"
                                                        title="Delete Customer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                
                                                <?php if ($customer['is_active']): ?>
                                                    <button type="button" class="btn btn-outline-secondary" 
                                                            onclick="toggleCustomerStatus(<?php echo $customer['id']; ?>, 0)"
                                                            title="Deactivate">
                                                        <i class="bi bi-toggle-on"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline-success" 
                                                            onclick="toggleCustomerStatus(<?php echo $customer['id']; ?>, 1)"
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
                                            <i class="bi bi-people display-4 d-block mb-2"></i>
                                            <h5>No customers found</h5>
                                            <p>Get started by adding your first customer.</p>
                                            <a href="customers.php?source=add_customer" class="btn btn-primary mt-2">
                                                <i class="bi bi-person-plus"></i> Add Customer
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

<!-- View Customer Modal -->

<div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content reservation-modal-theme">
            <div class="modal-header reservation-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person display-6 text-theme"></i>
                    <h5 class="modal-title mb-0">Customer Details</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body reservation-modal-body" id="customerDetails">
                <!-- Customer details will be loaded here via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-theme" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer reservation-modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editCustomerBtn" class="btn btn-theme">Edit Customer</a>
                <button type="button" class="btn btn-warning" id="resetPasswordBtn">Reset Password</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Customer Modal -->
<div class="modal fade" id="deleteCustomerConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteCustomerName"></strong>? This action cannot be undone.
                <div class="alert alert-danger mt-2">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This will permanently remove the customer account and all associated data.
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmDeleteCheckbox">
                    <label class="form-check-label" for="confirmDeleteCheckbox">
                        I understand this action is irreversible
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteCustomerBtn" 
                        onclick="customerDeleteItem()" disabled>
                    Delete Customer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    A password reset link will be sent to the customer's email.
                </div>
                <div class="mb-3">
                    <label for="customerEmail" class="form-label">Customer Email</label>
                    <input type="email" class="form-control" id="customerEmail" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendPasswordReset()">Send Reset Link</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="customerSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="customerToastMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="customerErrorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="customerErrorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
/* =====================================================
   GLOBAL STATE (CUSTOMER PAGE ONLY)
===================================================== */
let customerDeleteItemId = null;
let customerDeleteModalInstance = null;
let resetPasswordCustomerId = null;

/* =====================================================
   VIEW CUSTOMER DETAILS
===================================================== */
function customerShowViewModal(customerId) {
    if (!customerId) {
        customerShowError('Invalid customer ID');
        return;
    }

    const detailsEl = document.getElementById('customerDetails');
    detailsEl.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading customer details...</p>
        </div>
    `;

    const viewModal = new bootstrap.Modal(
        document.getElementById('viewCustomerModal')
    );
    viewModal.show();

    fetch('includes/get_customer_details.php?id=' + customerId)
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.customer) {
                detailsEl.innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load customer details
                    </div>`;
                return;
            }

            const customer = data.customer;
            const statusBadge = customer.is_active 
                ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>'
                : '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Inactive</span>';

            const lastLogin = customer.last_login 
                ? formatDateTime(customer.last_login)
                : 'Never logged in';

            const lastOrder = customer.last_order_date 
                ? formatDate(customer.last_order_date)
                : 'No orders yet';

            detailsEl.innerHTML = `
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 120px; height: 120px;">
                            <i class="bi bi-person display-3 text-muted"></i>
                        </div>
                        <h4>${escapeHtml(customer.full_name || customer.username)}</h4>
                        <p class="text-muted">@${escapeHtml(customer.username)}</p>
                        ${statusBadge}
                    </div>
                    <div class="col-md-8">
                        <div class="row mb-3">
                            <div class="col-6">
                                <h6><i class="bi bi-envelope me-2"></i> Email</h6>
                                <p>${escapeHtml(customer.email)}</p>
                            </div>
                            <div class="col-6">
                                <h6><i class="bi bi-phone me-2"></i> Phone</h6>
                                <p>${customer.phone ? escapeHtml(customer.phone) : 'Not provided'}</p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <h6><i class="bi bi-gem me-2"></i> Loyalty Points</h6>
                                <h4 class="text-primary">${parseInt(customer.loyalty_points).toLocaleString()}</h4>
                            </div>
                            <div class="col-6">
                                <h6><i class="bi bi-calendar me-2"></i> Last Order</h6>
                                <p>${lastOrder}</p>
                            </div>
                        </div>
                        
                        ${customer.address ? `
                            <div class="mb-3">
                                <h6><i class="bi bi-geo-alt me-2"></i> Address</h6>
                                <p class="text-muted">${escapeHtml(customer.address).replace(/\n/g,'<br>')}</p>
                            </div>
                        ` : ''}
                        
                        <div class="row">
                            <div class="col-6">
                                <h6><i class="bi bi-clock me-2"></i> Last Login</h6>
                                <p>${lastLogin}</p>
                            </div>
                            <div class="col-6">
                                <h6><i class="bi bi-calendar-plus me-2"></i> Registered</h6>
                                <p>${formatDate(customer.created_at)}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Set edit button URL
            document.getElementById('editCustomerBtn').href =
                'customers.php?source=edit_customer&id=' + customerId;
                
            // Set reset password button
            document.getElementById('resetPasswordBtn').onclick = function() {
                showResetPasswordModal(customerId, customer.email);
            };
        })
        .catch(() => customerShowError('Error loading customer details'));
}

/* =====================================================
   DELETE CUSTOMER FUNCTIONS
===================================================== */
function customerShowDeleteConfirm(customerId, customerName) {
    if (!customerId) {
        customerShowError('Invalid customer ID');
        return;
    }

    customerDeleteItemId = customerId;

    const nameEl = document.getElementById('deleteCustomerName');
    if (!nameEl) {
        console.error('deleteCustomerName not found');
        return;
    }

    nameEl.textContent = customerName;

    if (!customerDeleteModalInstance) {
        customerDeleteModalInstance = new bootstrap.Modal(
            document.getElementById('deleteCustomerConfirmModal')
        );
    }

    // Reset checkbox
    document.getElementById('confirmDeleteCheckbox').checked = false;
    document.getElementById('confirmDeleteCustomerBtn').disabled = true;
    
    // Add checkbox listener
    document.getElementById('confirmDeleteCheckbox').onchange = function() {
        document.getElementById('confirmDeleteCustomerBtn').disabled = !this.checked;
    };

    customerDeleteModalInstance.show();
}

function customerDeleteItem() {
    if (!customerDeleteItemId) {
        customerShowError('No customer selected');
        return;
    }

    const btn = document.getElementById('confirmDeleteCustomerBtn');
    const originalHTML = btn.innerHTML;

    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    btn.disabled = true;

    // Send the request with proper headers
    fetch('includes/delete_customer.php?id=' + customerDeleteItemId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'confirm=1' // You can remove this if you don't want confirmation
    })
        .then(res => {
            if (!res.ok) {
                throw new Error('Network response was not ok');
            }
            return res.json();
        })
        .then(data => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;

            if (!data.success) {
                customerShowError(data.message || 'Delete failed');
                return;
            }

            customerDeleteModalInstance.hide();

            const row = document.getElementById('customer-row-' + customerDeleteItemId);
            if (row) {
                if ($.fn.DataTable.isDataTable('#customersTable')) {
                    $('#customersTable').DataTable().row(row).remove().draw();
                } else {
                    row.remove();
                }
            }

            customerShowSuccess(data.message || 'Customer deleted successfully');
            customerDeleteItemId = null;
        })
        .catch((error) => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            console.error('Delete error:', error);
            customerShowError('Server error while deleting: ' + error.message);
        });
}

/* =====================================================
   TOGGLE CUSTOMER STATUS
===================================================== */
function toggleCustomerStatus(customerId, newStatus) {
    if (!customerId) {
        customerShowError('Invalid customer ID');
        return;
    }

    fetch('includes/toggle_customer_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + customerId + '&status=' + newStatus
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                customerShowError(data.message || 'Status update failed');
                return;
            }

            // Update button in table
            const row = document.getElementById('customer-row-' + customerId);
            if (row) {
                const btnGroup = row.querySelector('.btn-group');
                if (btnGroup) {
                    if (newStatus == 0) {
                        // Change to activate button
                        btnGroup.innerHTML = btnGroup.innerHTML.replace(
                            'bi-toggle-on">',
                            'bi-toggle-off"></i>'
                        ).replace(
                            'onclick="toggleCustomerStatus(' + customerId + ', 0)"',
                            'onclick="toggleCustomerStatus(' + customerId + ', 1)"'
                        ).replace(
                            'class="btn btn-outline-secondary"',
                            'class="btn btn-outline-success"'
                        ).replace(
                            'Deactivate',
                            'Activate'
                        );
                        
                        // Update status badge
                        const statusCell = row.cells[3];
                        statusCell.innerHTML = '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Inactive</span>';
                    } else {
                        // Change to deactivate button
                        btnGroup.innerHTML = btnGroup.innerHTML.replace(
                            'bi-toggle-off">',
                            'bi-toggle-on"></i>'
                        ).replace(
                            'onclick="toggleCustomerStatus(' + customerId + ', 1)"',
                            'onclick="toggleCustomerStatus(' + customerId + ', 0)"'
                        ).replace(
                            'class="btn btn-outline-success"',
                            'class="btn btn-outline-secondary"'
                        ).replace(
                            'Activate',
                            'Deactivate'
                        );
                        
                        // Update status badge
                        const statusCell = row.cells[3];
                        statusCell.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>';
                    }
                }
            }

            customerShowSuccess(data.message || 'Status updated successfully');
        })
        .catch(() => customerShowError('Server error'));
}

/* =====================================================
   RESET PASSWORD FUNCTIONS
===================================================== */
function showResetPasswordModal(customerId, customerEmail) {
    resetPasswordCustomerId = customerId;
    document.getElementById('customerEmail').value = customerEmail;
    
    const resetModal = new bootstrap.Modal(
        document.getElementById('resetPasswordModal')
    );
    resetModal.show();
}

function sendPasswordReset() {
    if (!resetPasswordCustomerId) {
        customerShowError('No customer selected');
        return;
    }

    const btn = document.getElementById('resetPasswordModal').querySelector('.btn-primary');
    const originalHTML = btn.innerHTML;

    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
    btn.disabled = true;

    fetch('includes/send_password_reset.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + resetPasswordCustomerId
    })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;

            if (!data.success) {
                customerShowError(data.message || 'Failed to send reset link');
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')).hide();
            customerShowSuccess('Password reset link sent successfully');
            resetPasswordCustomerId = null;
        })
        .catch(() => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            customerShowError('Server error');
        });
}

/* =====================================================
   SEARCH AND FILTER FUNCTIONS
===================================================== */
function searchCustomers() {
    const searchTerm = document.getElementById('customerSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const loyaltyFilter = document.getElementById('loyaltyFilter').value;
    
    document.querySelectorAll('.customer-row').forEach(row => {
        const name = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const status = row.dataset.status;
        const points = parseInt(row.dataset.points);
        
        let show = true;
        
        // Search term filter
        if (searchTerm && !name.includes(searchTerm) && !email.includes(searchTerm)) {
            show = false;
        }
        
        // Status filter
        if (statusFilter && status !== statusFilter) {
            show = false;
        }
        
        // Loyalty filter
        if (loyaltyFilter) {
            if (loyaltyFilter === 'high' && points < 500) show = false;
            if (loyaltyFilter === 'medium' && (points < 100 || points >= 500)) show = false;
            if (loyaltyFilter === 'low' && points >= 100) show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function filterCustomers() {
    searchCustomers(); // Reuse search function for filtering
}

function resetFilters() {
    document.getElementById('customerSearch').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('loyaltyFilter').value = '';
    document.querySelectorAll('.customer-row').forEach(row => {
        row.style.display = '';
    });
}

function exportCustomers() {
    window.location.href = 'includes/export_customers.php';
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

function customerShowSuccess(msg) {
    document.getElementById('customerToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('customerSuccessToast')).show();
}

function customerShowError(msg) {
    document.getElementById('customerErrorToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('customerErrorToast')).show();
}

/* =====================================================
   DATATABLE INIT
===================================================== */
document.addEventListener('DOMContentLoaded', () => {
    if ($.fn.DataTable.isDataTable('#customersTable')) {
        $('#customersTable').DataTable().destroy();
    }

    $('#customersTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [7] }],
        responsive: true,
        language: {
            search: "Search customers:",
            lengthMenu: "Show _MENU_ customers per page"
        }
    });
});
</script>