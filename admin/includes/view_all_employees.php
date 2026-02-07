<?php
// Fetch only employees (role = 'employee')
$sql = "SELECT * FROM users WHERE role = 'employee' ORDER BY created_at DESC";
$result = $connection->query($sql);

// Check for errors
if (!$result) {
    die("Query failed: " . $connection->error);
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_employees,
    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_employees,
    COALESCE(AVG(salary), 0) as avg_salary
    FROM users WHERE role = 'employee'";
$stats_result = $connection->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Employee Management</h1>
            <div>
                <a href="employees.php?source=add_employee" class="btn btn-primary me-2">
                    <i class="bi bi-person-plus"></i> Add New Employee
                </a>
                <button class="btn btn-secondary" onclick="exportEmployees()">
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
                                <h6 class="card-title">Total Employees</h6>
                                <h2 class="mb-0"><?php echo $stats['total_employees'] ?? 0; ?></h2>
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
                                <h2 class="mb-0"><?php echo $stats['active_employees'] ?? 0; ?></h2>
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
                                <h6 class="card-title">Admins</h6>
                                <h2 class="mb-0"><?php echo $stats['admin_count'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-shield-check display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Avg Salary</h6>
                                <h2 class="mb-0"><?php echo number_format($stats['avg_salary'] ?? 0, 2); ?> AED</h2>
                            </div>
                            <i class="bi bi-currency-exchange display-4 opacity-50"></i>
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
                        <input type="text" class="form-control" id="employeeSearch" 
                               placeholder="Search by name, email, or position..." 
                               onkeyup="searchEmployees()">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter" onchange="filterEmployees()">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="roleFilter" onchange="filterEmployees()">
                            <option value="">All Roles</option>
                            <option value="employee">Employee</option>
                            <option value="admin">Admin</option>
                            <option value="super-admin">Super Admin</option>
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

        <!-- Employees Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>All Employees</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="employeesTable">
                        <thead>
                            <tr class="table-dark">
                                <th width="50">ID</th>
                                <th>Employee</th>
                                <th>Contact</th>
                                <th>Position</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Salary</th>
                                <th width="180" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($employee = $result->fetch_assoc()): ?>
                                    <tr id="employee-row-<?php echo $employee['id']; ?>" 
                                        class="employee-row"
                                        data-status="<?php echo $employee['is_active'] ? 'active' : 'inactive'; ?>"
                                        data-role="<?php echo $employee['role']; ?>">
                                        <td class="fw-bold">#<?php echo $employee['id']; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($employee['full_name'] ?: 'N/A'); ?></div>
                                            <small class="text-muted">@<?php echo htmlspecialchars($employee['username']); ?></small>
                                            <?php if (!empty($employee['employee_id'])): ?>
                                                <div><small class="text-info">ID: <?php echo htmlspecialchars($employee['employee_id']); ?></small></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($employee['email']); ?></div>
                                            <?php if ($employee['phone']): ?>
                                                <small><i class="bi bi-phone me-1"></i> <?php echo htmlspecialchars($employee['phone']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($employee['position']): ?>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($employee['position']); ?></span>
                                            <?php endif; ?>
                                            <?php if ($employee['department']): ?>
                                                <div><small class="text-muted"><?php echo htmlspecialchars($employee['department']); ?></small></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $role_badge = '';
                                            switch($employee['role']) {
                                                case 'super-admin':
                                                    $role_badge = 'bg-danger';
                                                    break;
                                                case 'admin':
                                                    $role_badge = 'bg-warning text-dark';
                                                    break;
                                                default:
                                                    $role_badge = 'bg-primary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $role_badge; ?>">
                                                <?php echo ucfirst(str_replace('-', ' ', $employee['role'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($employee['is_active']): ?>
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
                                            <span class="fw-bold"><?php echo number_format($employee['salary'] ?? 0, 2); ?> AED</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-info view-employee-btn" 
                                                        onclick="showViewModal(<?php echo $employee['id']; ?>)" 
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                              
                                                <a href='employees.php?source=edit_employee&id=<?php echo $employee['id']; ?>'
                                                   class="btn btn-outline-warning"
                                                   title="Edit Employee">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <?php 
                                                // Only allow deleting if not current user and not super-admin (or allow super-admin to delete)
                                                $can_delete = ($employee['id'] != $_SESSION['user_id'] && 
                                                              $_SESSION['role'] == 'super-admin');
                                                if ($can_delete): 
                                                ?>
                                                <button type="button" class="btn btn-outline-danger delete-employee-btn" 
                                                        onclick="showDeleteConfirm(
                                                        <?php echo (int)$employee['id']; ?>,
                                                        '<?php echo htmlspecialchars(addslashes($employee['full_name'] ?: $employee['username']), ENT_QUOTES); ?>')"
                                                        title="Delete Employee">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($employee['is_active']): ?>
                                                    <button type="button" class="btn btn-outline-secondary toggle-status-btn" 
                                                            data-id="<?php echo $employee['id']; ?>"
                                                            data-status="1"
                                                            onclick="toggleEmployeeStatus(<?php echo $employee['id']; ?>, 0)"
                                                            title="Deactivate">
                                                        <i class="bi bi-toggle-on"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline-success toggle-status-btn" 
                                                            data-id="<?php echo $employee['id']; ?>"
                                                            data-status="0"
                                                            onclick="toggleEmployeeStatus(<?php echo $employee['id']; ?>, 1)"
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
                                            <h5>No employees found</h5>
                                            <p>Get started by adding your first employee.</p>
                                            <a href="employees.php?source=add_employee" class="btn btn-primary mt-2">
                                                <i class="bi bi-person-plus"></i> Add Employee
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

<!-- View Employee Modal (Consistent with Customer Modal) -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content reservation-modal-theme">
            <div class="modal-header reservation-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-badge display-6 text-theme"></i>
                    <h5 class="modal-title mb-0">Employee Details</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="employeeDetails">
                <!-- Employee details will be loaded here via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editEmployeeBtn" class="btn btn-primary">Edit Employee</a>
                <button type="button" class="btn btn-warning" id="resetPasswordBtn">Reset Password</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Employee Modal -->
<div class="modal fade" id="deleteEmployeeConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteEmployeeName"></strong>? This action cannot be undone.
                <div class="alert alert-danger mt-2">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This will permanently remove the employee account and all associated data.
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
                <button type="button" class="btn btn-danger" id="confirmDeleteEmployeeBtn" 
                        onclick="deleteEmployee()" disabled>
                    Delete Employee
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="employeeSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="employeeToastMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="employeeErrorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="employeeErrorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
/* =====================================================
   GLOBAL STATE (EMPLOYEE PAGE ONLY)
===================================================== */
let deleteEmployeeId = null;
let deleteModalInstance = null;

/* =====================================================
   VIEW EMPLOYEE DETAILS
===================================================== */
function showViewModal(employeeId) {
    if (!employeeId) {
        showError('Invalid employee ID');
        return;
    }

    const detailsEl = document.getElementById('employeeDetails');
    detailsEl.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading employee details...</p>
        </div>
    `;

    const viewModal = new bootstrap.Modal(
        document.getElementById('viewEmployeeModal')
    );
    viewModal.show();

    fetch('includes/get_employee_details.php?id=' + employeeId)
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.employee) {
                detailsEl.innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load employee details
                    </div>`;
                return;
            }

            const employee = data.employee;
            
            // Role badge
            let roleBadge = '';
            switch(employee.role) {
                case 'super-admin':
                    roleBadge = '<span class="badge bg-danger">Super Admin</span>';
                    break;
                case 'admin':
                    roleBadge = '<span class="badge bg-warning text-dark">Admin</span>';
                    break;
                default:
                    roleBadge = '<span class="badge bg-primary">Employee</span>';
            }
            
            // Status badge
            const statusBadge = employee.is_active 
                ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>'
                : '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Inactive</span>';
            
            // Format dates
            const hireDate = employee.hire_date 
                ? formatDate(employee.hire_date)
                : 'Not set';
                
            const lastLogin = employee.last_login 
                ? formatDateTime(employee.last_login)
                : 'Never logged in';

            detailsEl.innerHTML = `
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 120px; height: 120px;">
                            <i class="bi bi-person-badge display-3 text-muted"></i>
                        </div>
                        <h4>${escapeHtml(employee.full_name || employee.username)}</h4>
                        <p class="text-muted">@${escapeHtml(employee.username)}</p>
                        ${statusBadge}
                        <div class="mt-2">${roleBadge}</div>
                    </div>
                    <div class="col-md-8">
                        <div class="row mb-3">
                            <div class="col-6">
                                <h6><i class="bi bi-envelope me-2"></i> Email</h6>
                                <p>${escapeHtml(employee.email)}</p>
                            </div>
                            <div class="col-6">
                                <h6><i class="bi bi-phone me-2"></i> Phone</h6>
                                <p>${employee.phone ? escapeHtml(employee.phone) : 'Not provided'}</p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <h6><i class="bi bi-briefcase me-2"></i> Position</h6>
                                <p>${employee.position ? escapeHtml(employee.position) : 'Not specified'}</p>
                            </div>
                            <div class="col-6">
                                <h6><i class="bi bi-building me-2"></i> Department</h6>
                                <p>${employee.department ? escapeHtml(employee.department) : 'Not assigned'}</p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <h6><i class="bi bi-cash-coin me-2"></i> Salary</h6>
                                <h4 class="text-success">${parseFloat(employee.salary).toLocaleString('en-US', {minimumFractionDigits: 2})} AED</h4>
                            </div>
                            <div class="col-6">
                                <h6><i class="bi bi-calendar me-2"></i> Hire Date</h6>
                                <p>${hireDate}</p>
                            </div>
                        </div>
                        
                        ${employee.employee_id ? `
                            <div class="mb-3">
                                <h6><i class="bi bi-card-text me-2"></i> Employee ID</h6>
                                <p class="text-muted">${escapeHtml(employee.employee_id)}</p>
                            </div>
                        ` : ''}
                        
                        ${employee.address ? `
                            <div class="mb-3">
                                <h6><i class="bi bi-geo-alt me-2"></i> Address</h6>
                                <p class="text-muted">${escapeHtml(employee.address).replace(/\n/g,'<br>')}</p>
                            </div>
                        ` : ''}
                        
                        <div class="row">
                            <div class="col-6">
                                <h6><i class="bi bi-clock me-2"></i> Last Login</h6>
                                <p>${lastLogin}</p>
                            </div>
                            <div class="col-6">
                                <h6><i class="bi bi-calendar-plus me-2"></i> Registered</h6>
                                <p>${formatDate(employee.created_at)}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Set edit button URL
            document.getElementById('editEmployeeBtn').href =
                'employees.php?source=edit_employee&id=' + employeeId;
                
            // Set reset password button
            document.getElementById('resetPasswordBtn').onclick = function() {
                showResetPasswordModal(employeeId, employee.email);
            };
        })
        .catch(() => showError('Error loading employee details'));
}

/* =====================================================
   DELETE EMPLOYEE FUNCTIONS
===================================================== */
function showDeleteConfirm(employeeId, employeeName) {
    if (!employeeId) {
        showError('Invalid employee ID');
        return;
    }

    deleteEmployeeId = employeeId;

    const nameEl = document.getElementById('deleteEmployeeName');
    if (!nameEl) {
        console.error('deleteEmployeeName not found');
        return;
    }

    nameEl.textContent = employeeName;

    if (!deleteModalInstance) {
        deleteModalInstance = new bootstrap.Modal(
            document.getElementById('deleteEmployeeConfirmModal')
        );
    }

    // Reset checkbox
    document.getElementById('confirmDeleteCheckbox').checked = false;
    document.getElementById('confirmDeleteEmployeeBtn').disabled = true;
    
    // Add checkbox listener
    document.getElementById('confirmDeleteCheckbox').onchange = function() {
        document.getElementById('confirmDeleteEmployeeBtn').disabled = !this.checked;
    };

    deleteModalInstance.show();
}

function deleteEmployee() {
    if (!deleteEmployeeId) {
        showError('No employee selected');
        return;
    }

    const btn = document.getElementById('confirmDeleteEmployeeBtn');
    const originalHTML = btn.innerHTML;

    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    btn.disabled = true;

    fetch('includes/delete_employee.php?id=' + deleteEmployeeId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'confirm=1'
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
                showError(data.message || 'Delete failed');
                return;
            }

            deleteModalInstance.hide();

            const row = document.getElementById('employee-row-' + deleteEmployeeId);
            if (row) {
                if ($.fn.DataTable.isDataTable('#employeesTable')) {
                    $('#employeesTable').DataTable().row(row).remove().draw();
                } else {
                    row.remove();
                }
            }

            showSuccess(data.message || 'Employee deleted successfully');
            deleteEmployeeId = null;
        })
        .catch((error) => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            console.error('Delete error:', error);
            showError('Server error while deleting: ' + error.message);
        });
}

/* =====================================================
   TOGGLE EMPLOYEE STATUS
===================================================== */
function toggleEmployeeStatus(employeeId, newStatus) {
    if (!employeeId) {
        showError('Invalid employee ID');
        return;
    }

    fetch('includes/toggle_employee_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + employeeId + '&status=' + newStatus
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                showError(data.message || 'Status update failed');
                return;
            }

            // Update button in table
            const row = document.getElementById('employee-row-' + employeeId);
            if (row) {
                const btnGroup = row.querySelector('.btn-group');
                if (btnGroup) {
                    const toggleBtn = btnGroup.querySelector('.toggle-status-btn');
                    if (toggleBtn) {
                        if (newStatus == 0) {
                            // Change to activate button
                            toggleBtn.innerHTML = '<i class="bi bi-toggle-off"></i>';
                            toggleBtn.className = 'btn btn-outline-success toggle-status-btn';
                            toggleBtn.setAttribute('data-status', '0');
                            toggleBtn.onclick = function() { toggleEmployeeStatus(employeeId, 1); };
                            toggleBtn.title = 'Activate';
                            
                            // Update status badge
                            const statusCell = row.cells[5];
                            statusCell.innerHTML = '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Inactive</span>';
                        } else {
                            // Change to deactivate button
                            toggleBtn.innerHTML = '<i class="bi bi-toggle-on"></i>';
                            toggleBtn.className = 'btn btn-outline-secondary toggle-status-btn';
                            toggleBtn.setAttribute('data-status', '1');
                            toggleBtn.onclick = function() { toggleEmployeeStatus(employeeId, 0); };
                            toggleBtn.title = 'Deactivate';
                            
                            // Update status badge
                            const statusCell = row.cells[5];
                            statusCell.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>';
                        }
                    }
                }
            }

            showSuccess(data.message || 'Status updated successfully');
        })
        .catch(() => showError('Server error'));
}

/* =====================================================
   SEARCH AND FILTER FUNCTIONS
===================================================== */
function searchEmployees() {
    const searchTerm = document.getElementById('employeeSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const roleFilter = document.getElementById('roleFilter').value;
    
    document.querySelectorAll('.employee-row').forEach(row => {
        const name = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const position = row.cells[3].textContent.toLowerCase();
        const status = row.dataset.status;
        const role = row.dataset.role;
        
        let show = true;
        
        // Search term filter
        if (searchTerm && !name.includes(searchTerm) && !email.includes(searchTerm) && !position.includes(searchTerm)) {
            show = false;
        }
        
        // Status filter
        if (statusFilter && status !== statusFilter) {
            show = false;
        }
        
        // Role filter
        if (roleFilter && role !== roleFilter) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function filterEmployees() {
    searchEmployees(); // Reuse search function for filtering
}

function resetFilters() {
    document.getElementById('employeeSearch').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('roleFilter').value = '';
    document.querySelectorAll('.employee-row').forEach(row => {
        row.style.display = '';
    });
}

function exportEmployees() {
    window.location.href = 'includes/export_employees.php';
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

function showSuccess(msg) {
    document.getElementById('employeeToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('employeeSuccessToast')).show();
}

function showError(msg) {
    document.getElementById('employeeErrorToastMessage').textContent = msg;
    new bootstrap.Toast(document.getElementById('employeeErrorToast')).show();
}

/* =====================================================
   DATATABLE INIT
===================================================== */
document.addEventListener('DOMContentLoaded', () => {
    if ($.fn.DataTable.isDataTable('#employeesTable')) {
        $('#employeesTable').DataTable().destroy();
    }

    $('#employeesTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [7] }],
        responsive: true,
        language: {
            search: "Search employees:",
            lengthMenu: "Show _MENU_ employees per page"
        }
    });
});
</script>