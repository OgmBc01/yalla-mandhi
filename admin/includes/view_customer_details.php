<?php
// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get customer ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: customers.php");
    exit();
}

$customer_id = (int)$_GET['id'];

// Fetch customer data with additional statistics
$customer_query = "SELECT * FROM users WHERE id = ? AND role = 'customer'";
$customer_stmt = $connection->prepare($customer_query);
$customer_stmt->bind_param("i", $customer_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();

if ($customer_result->num_rows === 0) {
    header("Location: customers.php");
    exit();
}

$customer = $customer_result->fetch_assoc();
$customer_stmt->close();

// Get customer statistics
$stats_query = "SELECT 
    COUNT(DISTINCT o.id) as total_orders,
    COALESCE(SUM(o.total_amount), 0) as total_spent,
    MAX(o.order_date) as last_order_date,
    COUNT(DISTINCT DATE(o.order_date)) as order_days
    FROM orders o 
    WHERE o.customer_id = ?";
$stats_stmt = $connection->prepare($stats_query);
$stats_stmt->bind_param("i", $customer_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stats_stmt->close();

// Get recent orders (last 5)
$orders_query = "SELECT 
    o.id, o.order_number, o.total_amount, o.status, 
    o.order_date, o.payment_method,
    COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.customer_id = ?
    GROUP BY o.id
    ORDER BY o.order_date DESC
    LIMIT 5";
$orders_stmt = $connection->prepare($orders_query);
$orders_stmt->bind_param("i", $customer_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Customer Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="customers.php">Customers</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($customer['full_name'] ?: $customer['username']); ?></li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                <a href="customers.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <a href="customers.php?source=edit_customer&id=<?php echo $customer_id; ?>" 
                   class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <button type="button" class="btn btn-danger" 
                        onclick="customerShowDeleteConfirm(
                            <?php echo $customer_id; ?>,
                            '<?php echo htmlspecialchars(addslashes($customer['full_name'] ?: $customer['username']), ENT_QUOTES); ?>'
                        )">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
        </div>

        <!-- Customer Profile Card -->
        <div class="row mb-4">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 120px; height: 120px;">
                            <i class="bi bi-person display-3 text-muted"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($customer['full_name'] ?: 'N/A'); ?></h3>
                        <p class="text-muted">@<?php echo htmlspecialchars($customer['username']); ?></p>
                        
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <?php if ($customer['is_active']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Active
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> Inactive
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($customer['loyalty_points'] > 500): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-star-fill"></i> Premium
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-start">
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
                            <?php if ($customer['phone']): ?>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($customer['address']): ?>
                                <p><strong>Address:</strong><br>
                                <span class="text-muted"><?php echo nl2br(htmlspecialchars($customer['address'])); ?></span></p>
                            <?php endif; ?>
                            
                            <p><strong>Member Since:</strong> <?php echo date('F d, Y', strtotime($customer['created_at'])); ?></p>
                            <?php if ($customer['last_login']): ?>
                                <p><strong>Last Login:</strong> <?php echo date('M d, Y H:i', strtotime($customer['last_login'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Orders</h6>
                                        <h2 class="mb-0"><?php echo $stats['total_orders'] ?? 0; ?></h2>
                                    </div>
                                    <i class="bi bi-cart display-4 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Spent</h6>
                                        <h2 class="mb-0">$<?php echo number_format($stats['total_spent'] ?? 0, 2); ?></h2>
                                    </div>
                                    <i class="bi bi-currency-dollar display-4 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Loyalty Points</h6>
                                        <h2 class="mb-0"><?php echo number_format($customer['loyalty_points']); ?></h2>
                                    </div>
                                    <i class="bi bi-gem display-4 opacity-50"></i>
                                </div>
                                <div class="progress bg-dark bg-opacity-25 mt-2" style="height: 5px;">
                                    <?php
                                    $progress = min($customer['loyalty_points'], 1000);
                                    $percent = ($progress / 1000) * 100;
                                    ?>
                                    <div class="progress-bar bg-warning" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Avg/Order</h6>
                                        <h2 class="mb-0">
                                            <?php
                                            $avg_order = ($stats['total_orders'] > 0) 
                                                ? $stats['total_spent'] / $stats['total_orders'] 
                                                : 0;
                                            echo '$' . number_format($avg_order, 2);
                                            ?>
                                        </h2>
                                    </div>
                                    <i class="bi bi-graph-up display-4 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <button class="btn btn-outline-primary w-100" 
                                        onclick="showResetPasswordModal(<?php echo $customer_id; ?>, '<?php echo htmlspecialchars($customer['email']); ?>')">
                                    <i class="bi bi-key me-1"></i> Reset Password
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="orders.php?customer_id=<?php echo $customer_id; ?>" 
                                   class="btn btn-outline-success w-100">
                                    <i class="bi bi-cart me-1"></i> View Orders
                                </a>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-info w-100" 
                                        onclick="addLoyaltyPoints(<?php echo $customer_id; ?>)">
                                    <i class="bi bi-plus-circle me-1"></i> Add Points
                                </button>
                            </div>
                            <div class="col-md-3">
                                <?php if ($customer['is_active']): ?>
                                    <button class="btn btn-outline-secondary w-100" 
                                            onclick="toggleCustomerStatus(<?php echo $customer_id; ?>, 0)">
                                        <i class="bi bi-toggle-on me-1"></i> Deactivate
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-success w-100" 
                                            onclick="toggleCustomerStatus(<?php echo $customer_id; ?>, 1)">
                                        <i class="bi bi-toggle-off me-1"></i> Activate
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Orders</h5>
                        <a href="orders.php?customer_id=<?php echo $customer_id; ?>" class="btn btn-sm btn-outline-primary">
                            View All Orders
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if ($orders_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                                            <tr>
                                                <td class="fw-bold">#<?php echo $order['order_number']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                                <td><?php echo $order['item_count']; ?> items</td>
                                                <td class="fw-bold">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <?php
                                                    $status_badge = [
                                                        'pending' => 'warning',
                                                        'processing' => 'info',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $badge_color = $status_badge[$order['status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_color; ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo ucfirst($order['payment_method']); ?></td>
                                                <td>
                                                    <a href="orders.php?source=view_order&id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-cart-x display-4 text-muted d-block mb-2"></i>
                                <h5>No orders yet</h5>
                                <p class="text-muted">This customer hasn't placed any orders.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Customer Notes (Optional) -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-sticky me-2"></i>Customer Notes</h5>
            </div>
            <div class="card-body">
                <div id="notesContainer">
                    <!-- Notes will be loaded via AJAX -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Loading notes...</p>
                    </div>
                </div>
                <div class="mt-3">
                    <form id="addNoteForm">
                        <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                        <div class="input-group">
                            <input type="text" class="form-control" name="note" 
                                   placeholder="Add a note about this customer..." required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Note
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Loyalty Points Modal -->
<div class="modal fade" id="addLoyaltyPointsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Loyalty Points</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="pointsAmount" class="form-label">Points to Add</label>
                    <input type="number" class="form-control" id="pointsAmount" min="1" max="1000" value="10">
                </div>
                <div class="mb-3">
                    <label for="pointsReason" class="form-label">Reason (Optional)</label>
                    <textarea class="form-control" id="pointsReason" rows="2" 
                              placeholder="e.g., Referral bonus, Special promotion"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveLoyaltyPoints()">Add Points</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentCustomerId = <?php echo $customer_id; ?>;
let loyaltyPointsModal = null;

// Load customer notes
function loadCustomerNotes() {
    fetch('includes/get_customer_notes.php?id=' + currentCustomerId)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('notesContainer');
            if (!data.success || !data.notes) {
                container.innerHTML = '<div class="alert alert-info">No notes yet</div>';
                return;
            }
            
            if (data.notes.length === 0) {
                container.innerHTML = '<div class="alert alert-info">No notes yet</div>';
                return;
            }
            
            let html = '';
            data.notes.forEach(note => {
                html += `
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <p class="mb-1">${escapeHtml(note.note)}</p>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteNote(${note.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                Added by ${note.added_by} on ${formatDateTime(note.created_at)}
                            </small>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        })
        .catch(() => {
            document.getElementById('notesContainer').innerHTML = 
                '<div class="alert alert-danger">Error loading notes</div>';
        });
}

// Add note
document.getElementById('addNoteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';
    btn.disabled = true;
    
    fetch('includes/add_customer_note.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            
            if (!data.success) {
                customerShowError(data.message || 'Failed to add note');
                return;
            }
            
            this.reset();
            loadCustomerNotes();
            customerShowSuccess('Note added successfully');
        })
        .catch(() => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            customerShowError('Server error');
        });
});

// Delete note
function deleteNote(noteId) {
    if (!confirm('Delete this note?')) return;
    
    fetch('includes/delete_customer_note.php?id=' + noteId, {
        method: 'DELETE'
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                customerShowError(data.message || 'Failed to delete note');
                return;
            }
            
            loadCustomerNotes();
            customerShowSuccess('Note deleted');
        })
        .catch(() => customerShowError('Server error'));
}

// Add loyalty points
function addLoyaltyPoints() {
    if (!loyaltyPointsModal) {
        loyaltyPointsModal = new bootstrap.Modal(
            document.getElementById('addLoyaltyPointsModal')
        );
    }
    
    document.getElementById('pointsAmount').value = 10;
    document.getElementById('pointsReason').value = '';
    loyaltyPointsModal.show();
}

function saveLoyaltyPoints() {
    const points = document.getElementById('pointsAmount').value;
    const reason = document.getElementById('pointsReason').value;
    
    if (!points || points < 1) {
        alert('Please enter valid points amount');
        return;
    }
    
    const btn = document.getElementById('addLoyaltyPointsModal').querySelector('.btn-primary');
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';
    btn.disabled = true;
    
    fetch('includes/add_loyalty_points.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `customer_id=${currentCustomerId}&points=${points}&reason=${encodeURIComponent(reason)}`
    })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            
            if (!data.success) {
                customerShowError(data.message || 'Failed to add points');
                return;
            }
            
            loyaltyPointsModal.hide();
            customerShowSuccess('Loyalty points added successfully');
            
            // Refresh page after 1 second to show updated points
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        })
        .catch(() => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            customerShowError('Server error');
        });
}

// Load notes on page load
document.addEventListener('DOMContentLoaded', () => {
    loadCustomerNotes();
});
</script>