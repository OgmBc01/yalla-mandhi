<?php
// Get employee ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: employees.php");
    exit();
}

$employee_id = (int)$_GET['id'];

// Fetch employee data
$stmt = $connection->prepare("SELECT * FROM users WHERE id = ? AND role IN ('employee', 'admin', 'super-admin')");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: employees.php");
    exit();
}

$employee = $result->fetch_assoc();
$stmt->close();

// Get employee activity stats
$activity_query = "SELECT 
    COUNT(*) as total_orders,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders
    FROM orders WHERE employee_id = ?";
    
$activity_stmt = $connection->prepare($activity_query);
$activity_stmt->bind_param("i", $employee_id);
$activity_stmt->execute();
$activity_result = $activity_stmt->get_result();
$activity = $activity_result->fetch_assoc();
$activity_stmt->close();
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Employee Details</h1>
            <a href="employees.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Employees
            </a>
        </div>
        
        <div class="row">
            <!-- Employee Profile Card -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 150px; height: 150px;">
                            <i class="bi bi-person-badge display-2 text-muted"></i>
                        </div>
                        
                        <h3><?php echo htmlspecialchars($employee['full_name'] ?: $employee['username']); ?></h3>
                        <p class="text-muted">@<?php echo htmlspecialchars($employee['username']); ?></p>
                        
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
                        <span class="badge <?php echo $role_badge; ?> mb-2">
                            <?php echo ucfirst(str_replace('-', ' ', $employee['role'])); ?>
                        </span>
                        
                        <?php if ($employee['is_active']): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Active
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Inactive
                            </span>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <div class="text-start">
                            <h6><i class="bi bi-envelope me-2"></i> Email</h6>
                            <p class="text-muted"><?php echo htmlspecialchars($employee['email']); ?></p>
                            
                            <?php if ($employee['phone']): ?>
                                <h6><i class="bi bi-phone me-2"></i> Phone</h6>
                                <p class="text-muted"><?php echo htmlspecialchars($employee['phone']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($employee['employee_id']): ?>
                                <h6><i class="bi bi-card-text me-2"></i> Employee ID</h6>
                                <p class="text-muted"><?php echo htmlspecialchars($employee['employee_id']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="employees.php?source=edit_employee&id=<?php echo $employee_id; ?>" 
                               class="btn btn-warning">
                                <i class="bi bi-pencil me-2"></i> Edit Employee
                            </a>
                            
                            <?php if ($employee['is_active']): ?>
                                <button class="btn btn-outline-danger" 
                                        onclick="toggleEmployeeStatus(<?php echo $employee_id; ?>, 0)">
                                    <i class="bi bi-toggle-on me-2"></i> Deactivate
                                </button>
                            <?php else: ?>
                                <button class="btn btn-outline-success" 
                                        onclick="toggleEmployeeStatus(<?php echo $employee_id; ?>, 1)">
                                    <i class="bi bi-toggle-off me-2"></i> Activate
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($_SESSION['role'] === 'super-admin' && $employee_id != $_SESSION['user_id']): ?>
                                <button class="btn btn-danger" 
                                        onclick="showDeleteConfirm(
                                            <?php echo $employee_id; ?>,
                                            '<?php echo htmlspecialchars(addslashes($employee['full_name'] ?: $employee['username']), ENT_QUOTES); ?>')">
                                    <i class="bi bi-trash me-2"></i> Delete Employee
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Employee Details -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Employee Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-briefcase me-2"></i> Position</h6>
                                <p><?php echo $employee['position'] ? htmlspecialchars($employee['position']) : '<span class="text-muted">Not specified</span>'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-building me-2"></i> Department</h6>
                                <p><?php echo $employee['department'] ? htmlspecialchars($employee['department']) : '<span class="text-muted">Not assigned</span>'; ?></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-cash-coin me-2"></i> Salary</h6>
                                <h4 class="text-success"><?php echo number_format($employee['salary'], 2); ?> AED</h4>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-calendar me-2"></i> Hire Date</h6>
                                <p><?php echo $employee['hire_date'] ? date('F d, Y', strtotime($employee['hire_date'])) : '<span class="text-muted">Not set</span>'; ?></p>
                            </div>
                        </div>
                        
                        <?php if ($employee['address']): ?>
                            <h6><i class="bi bi-geo-alt me-2"></i> Address</h6>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($employee['address'])); ?></p>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-clock me-2"></i> Last Login</h6>
                                <p><?php echo $employee['last_login'] ? date('F d, Y H:i', strtotime($employee['last_login'])) : '<span class="text-muted">Never logged in</span>'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-calendar-plus me-2"></i> Registered</h6>
                                <p><?php echo date('F d, Y', strtotime($employee['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Stats -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Activity Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h1 class="mb-0"><?php echo $activity['total_orders'] ?? 0; ?></h1>
                                        <p class="mb-0">Total Orders</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h1 class="mb-0"><?php echo $activity['completed_orders'] ?? 0; ?></h1>
                                        <p class="mb-0">Completed</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body text-center">
                                        <h1 class="mb-0"><?php echo $activity['pending_orders'] ?? 0; ?></h1>
                                        <p class="mb-0">Pending</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Employee Modal (Same as in view_all_employees.php) -->
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

<script>
// Reuse the functions from view_all_employees.php
let deleteEmployeeId = null;
let deleteModalInstance = null;

function showDeleteConfirm(empId, empName) {
    deleteEmployeeId = empId;
    document.getElementById('deleteEmployeeName').textContent = empName;
    
    if (!deleteModalInstance) {
        deleteModalInstance = new bootstrap.Modal(
            document.getElementById('deleteEmployeeConfirmModal')
        );
    }
    
    document.getElementById('confirmDeleteCheckbox').checked = false;
    document.getElementById('confirmDeleteEmployeeBtn').disabled = true;
    
    document.getElementById('confirmDeleteCheckbox').onchange = function() {
        document.getElementById('confirmDeleteEmployeeBtn').disabled = !this.checked;
    };
    
    deleteModalInstance.show();
}

function deleteEmployee() {
    if (!deleteEmployeeId) return;
    
    const btn = document.getElementById('confirmDeleteEmployeeBtn');
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    btn.disabled = true;
    
    fetch('includes/delete_employee.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + deleteEmployeeId
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        
        if (!data.success) {
            alert(data.message || 'Delete failed');
            return;
        }
        
        deleteModalInstance.hide();
        window.location.href = 'employees.php';
    })
    .catch(() => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        alert('Server error');
    });
}

function toggleEmployeeStatus(empId, newStatus) {
    if (!empId) return;
    
    fetch('includes/toggle_employee_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + empId + '&status=' + newStatus
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Status update failed');
            return;
        }
        
        // Reload page to show updated status
        window.location.reload();
    })
    .catch(() => alert('Server error'));
}
</script>