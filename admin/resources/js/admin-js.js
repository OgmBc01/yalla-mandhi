// ADMIN JS

document.addEventListener('DOMContentLoaded', function() {

    // Hourly Reservations Chart
    const hourlyChartElem = document.getElementById('hourlyReservationsChart');
    if (hourlyChartElem && typeof Chart !== 'undefined') {
        const hourlyCtx = hourlyChartElem.getContext('2d');
        const hourlyData = window.dashboardHourlyReservations || [];
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: Array.from({length: 24}, (_, i) => i + ':00'),
                datasets: [{
                    label: 'Reservations',
                    data: hourlyData,
                    backgroundColor: 'rgba(138, 134, 53, 0.7)',
                    borderColor: 'rgba(138, 134, 53, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        ticks: {
                            maxTicksLimit: 12
                        }
                    }
                }
            }
    // ===== Pending Reservations Management (view_pending_reservations.php) =====
});

// Pending Reservations Management (outside DOMContentLoaded for global access)
window.confirmReservation = function(reservationId) {
    if (!reservationId) {
        showError('Invalid reservation ID');
        return;
    }
    if (!confirm('Are you sure you want to confirm this reservation?')) {
        return;
    }
    fetch('includes/update_reservation_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + reservationId + '&status=confirmed'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove row from table
            const row = document.getElementById('reservation-row-' + reservationId);
            if (row) {
                const table = $('#pendingReservationsTable').DataTable();
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
            showSuccess('Reservation confirmed successfully!');
        } else {
            showError(data.message || 'Failed to confirm reservation');
        }
    })
    .catch(error => {
        showError('Error confirming reservation: ' + error.message);
        console.error('Error:', error);
    });
};

window.cancelReservation = function(reservationId) {
    if (!reservationId) {
        showError('Invalid reservation ID');
        return;
    }
    if (!confirm('Are you sure you want to cancel this reservation?')) {
        return;
    }
    fetch('includes/update_reservation_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + reservationId + '&status=cancelled'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove row from table
            const row = document.getElementById('reservation-row-' + reservationId);
            if (row) {
                const table = $('#pendingReservationsTable').DataTable();
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
            showSuccess('Reservation cancelled successfully!');
        } else {
            showError(data.message || 'Failed to cancel reservation');
        }
    })
    .catch(error => {
        showError('Error cancelling reservation: ' + error.message);
        console.error('Error:', error);
    });
};

// DataTable and popover initialization for pendingReservationsTable
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        if ($('#pendingReservationsTable').length) {
            // Destroy existing DataTable instance if it exists
            if ($.fn.DataTable.isDataTable('#pendingReservationsTable')) {
                $('#pendingReservationsTable').DataTable().destroy();
            }
            $('#pendingReservationsTable').DataTable({
                pageLength: 25,
                order: [[2, 'asc']], // Sort by date (column index 2)
                columnDefs: [
                    { orderable: false, targets: [7] }, // Make Actions column non-orderable (8th column, index 7)
                    { width: "50px", targets: [0] }, // ID column width
                    { width: "180px", targets: [7] } // Actions column width (8th column, index 7)
                ],
                responsive: true,
                language: {
                    search: "Search pending reservations:",
                    lengthMenu: "Show _MENU_ reservations per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ pending reservations",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    zeroRecords: "No matching reservations found",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        }
    }
    // Popover initialization for pendingReservationsTable
    const popoverTriggerList = document.querySelectorAll('#pendingReservationsTable [data-bs-toggle="popover"]');
    [...popoverTriggerList].forEach(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
});

            // Popover initialization for pendingReservationsTable
            const popoverTriggerList = document.querySelectorAll('#pendingReservationsTable [data-bs-toggle="popover"]');
            [...popoverTriggerList].forEach(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    }
    // Sidebar toggle functionality
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.getElementById('mainContent');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            
            // Update toggle button icon
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.className = 'bi bi-list';
            } else {
                icon.className = 'bi bi-x';
            }
        });
    }
    
    // Menu toggle functionality
    const menuToggleBtns = document.querySelectorAll('.menu-toggle-btn');
    
    menuToggleBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const menuName = this.getAttribute('data-menu');
            const subMenu = document.getElementById(menuName + '-menu');
            
            if (subMenu) {
                // Close other submenus if needed
                if (!this.classList.contains('active')) {
                    closeAllSubMenus();
                }
                
                // Toggle current submenu
                this.classList.toggle('active');
                subMenu.classList.toggle('show');
            }
        });
    });
    
    function closeAllSubMenus() {
        document.querySelectorAll('.sub-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
        document.querySelectorAll('.menu-toggle-btn.active').forEach(btn => {
            btn.classList.remove('active');
        });
    }
    
    // Auto-close submenus when clicking outside on mobile
    if (window.innerWidth <= 768) {
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.sidebar')) {
                closeAllSubMenus();
            }
        });
    }
    
    // Update active menu based on URL
    function updateActiveMenu() {
        const currentUrl = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentUrl.split('/').pop()) {
                link.classList.add('active');
                
                // Also activate parent menu if it's a submenu
                const parentMenu = link.closest('.sub-menu');
                if (parentMenu) {
                    parentMenu.classList.add('show');
                    const menuToggle = document.querySelector(`[data-menu="${parentMenu.id.replace('-menu', '')}"]`);
                    if (menuToggle) {
                        menuToggle.classList.add('active');
                    }
                }
            }
        });
    }
    
    updateActiveMenu();
    
    // DataTables initialization for tables with class 'data-table'
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.data-table').DataTable({
            responsive: true,
            pageLength: 25,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
                lengthMenu: "_MENU_ records per page",
                zeroRecords: "No matching records found",
                info: "Showing _START_ to _END_ of _TOTAL_ records",
                infoEmpty: "Showing 0 to 0 of 0 records",
                infoFiltered: "(filtered from _MAX_ total records)"
            }
        });
    }
    
    // Confirm delete actions
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // Status update buttons
    document.querySelectorAll('.status-update').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const type = this.getAttribute('data-type');
            const newStatus = this.getAttribute('data-status');
            
            if (confirm(`Change status to ${newStatus}?`)) {
                updateStatus(id, type, newStatus);
            }
        });
    });
    
    function updateStatus(id, type, status) {
        fetch('includes/ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_status&id=${id}&type=${type}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating status');
        });
    }
    
    // Real-time notifications check (every 60 seconds)
    setInterval(checkNotifications, 60000);
    
    function checkNotifications() {
        fetch('includes/ajax.php?action=check_notifications')
            .then(response => response.json())
            .then(data => {
                if (data.count > 0) {
                    updateNotificationBadge(data.count);
                }
            });
    }
    
    function updateNotificationBadge(count) {
        let badge = document.querySelector('.notification-badge');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'notification-badge badge bg-danger';
            document.querySelector('.user-profile').appendChild(badge);
        }
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline-block' : 'none';
    }
    
    // Initialize tooltips
    if (typeof $.fn.tooltip !== 'undefined') {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }
    
    // Initialize popovers
    if (typeof $.fn.popover !== 'undefined') {
        $('[data-bs-toggle="popover"]').popover();
    }

    // ===== Dashboard Charts (Chart.js) =====
    // Revenue Chart
    const revenueChartElem = document.getElementById('revenueChart');
    if (revenueChartElem && typeof Chart !== 'undefined') {
        const revenueCtx = revenueChartElem.getContext('2d');
        const revenueData = window.dashboardMonthlyRevenue || [];
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue (AED)',
                    data: revenueData,
                    backgroundColor: 'rgba(196, 30, 58, 0.1)',
                    borderColor: 'rgba(196, 30, 58, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'AED ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Reservation Form: Set minimum date and default time
    const reservationDateInput = document.getElementById('reservation_date');
    if (reservationDateInput) {
        const today = new Date().toISOString().split('T')[0];
        reservationDateInput.min = today;

        // Set default time to next available slot
        const now = new Date();
        const currentHour = now.getHours();
        // If current time is after 10 PM, set date to tomorrow
        if (currentHour >= 22) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            reservationDateInput.value = tomorrow.toISOString().split('T')[0];
        }
    }
    // ===== Reservation Management (view_all_reservations.php) =====
    let currentDeleteReservationId = null;

    window.viewReservation = function(reservationId) {
        if (!reservationId) {
            showError('Invalid reservation ID');
            return;
        }
        // Show loading in modal
        document.getElementById('reservationDetails').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading reservation details...</p>
            </div>
        `;
        // Show modal
        const viewModal = new bootstrap.Modal(document.getElementById('viewReservationModal'));
        viewModal.show();
        // Fetch reservation details
        fetch('includes/get_reservation_details.php?id=' + reservationId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.reservation) {
                    const reservation = data.reservation;
                    // Determine status badge class
                    let statusClass = '';
                    switch(reservation.status) {
                        case 'pending': statusClass = 'status-pending'; break;
                        case 'confirmed': statusClass = 'status-confirmed'; break;
                        case 'cancelled': statusClass = 'status-cancelled'; break;
                        case 'completed': statusClass = 'status-completed'; break;
                    }
                    const detailsHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">CUSTOMER INFORMATION</h6>
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Name:</strong></p>
                                    <p class="fs-5">${escapeHtml(reservation.customer_name)}</p>
                                </div>
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Email:</strong></p>
                                    <p><a href="mailto:${escapeHtml(reservation.customer_email)}">${escapeHtml(reservation.customer_email)}</a></p>
                                </div>
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Phone:</strong></p>
                                    <p><a href="tel:${escapeHtml(reservation.customer_phone)}">${escapeHtml(reservation.customer_phone)}</a></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">RESERVATION DETAILS</h6>
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Status:</strong></p>
                                    <span class="status-badge ${statusClass}">${escapeHtml(reservation.status.charAt(0).toUpperCase() + reservation.status.slice(1))}</span>
                                </div>
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Date & Time:</strong></p>
                                    <p class="fs-5">${escapeHtml(formatDate(reservation.reservation_date))} at ${escapeHtml(formatTime(reservation.reservation_time))}</p>
                                </div>
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Number of Guests:</strong></p>
                                    <p class="fs-5">${escapeHtml(reservation.number_of_guests)}</p>
                                </div>
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Branch:</strong></p>
                                    <p>${escapeHtml(reservation.name || 'Main Branch')}</p>
                                </div>
                            </div>
                        </div>
                        ${reservation.special_requests ? `
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted mb-2">SPECIAL REQUESTS</h6>
                                <div class="bg-light p-3 rounded">
                                    ${escapeHtml(reservation.special_requests).replace(/\n/g, '<br>')}
                                </div>
                            </div>
                        </div>` : ''}
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1 text-muted"><small>Reservation ID:</small></p>
                                <p class="mb-0"><strong>#${reservation.id}</strong></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 text-muted"><small>Created:</small></p>
                                <p class="mb-0">${escapeHtml(formatDateTime(reservation.created_at))}</p>
                            </div>
                        </div>`;
                    document.getElementById('reservationDetails').innerHTML = detailsHtml;
                    document.getElementById('editReservationBtn').href = 'reservation.php?source=edit_reservation&id=' + reservationId;
                } else {
                    document.getElementById('reservationDetails').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            ${data.message || 'Failed to load reservation details'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('reservationDetails').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Error loading reservation details. Please try again.
                    </div>
                `;
                console.error('Error:', error);
            });
    };


    // ===== Menu Item Deletion (view_all_menu_items.php) =====
    let currentDeleteMenuItemId = null;
    window.showDeleteMenuItemConfirmation = function(menuItemId, menuItemInfo) {
        if (!menuItemId) {
            showError('Invalid menu item ID');
            return;
        }
        currentDeleteMenuItemId = menuItemId;
        var infoElem = document.getElementById('deleteMenuItemInfo');
        if (infoElem) {
            infoElem.textContent = menuItemInfo;
        }
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteMenuItemModal'));
        deleteModal.show();
    };

    window.deleteMenuItem = function() {
        if (!currentDeleteMenuItemId) {
            showError('No menu item selected for deletion');
            return;
        }
        const deleteBtn = document.getElementById('confirmDeleteMenuItemBtn');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
        deleteBtn.disabled = true;
        fetch('includes/delete_menu_item.php?id=' + currentDeleteMenuItemId)
            .then(response => response.json())
            .then(data => {
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteMenuItemModal'));
                    modal.hide();
                    // Remove row from table
                    const row = document.getElementById('menu-item-row-' + currentDeleteMenuItemId);
                    if (row) {
                        row.style.opacity = '0';
                        row.style.transition = 'opacity 0.4s';
                        setTimeout(() => {
                            row.remove();
                            // If using DataTables, redraw the table
                            if (typeof $.fn.DataTable !== 'undefined' && $('#menuItemsTable').DataTable()) {
                                $('#menuItemsTable').DataTable().clear().draw();
                            }
                        }, 400);
                    }
                    showSuccess(data.message || 'Menu item deleted successfully!');
                    currentDeleteMenuItemId = null;
                } else {
                    showError(data.message || 'Failed to delete menu item');
                }
            })
            .catch(error => {
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
                showError('Error deleting menu item: ' + error.message);
                console.error('Error:', error);
            });
    };

    window.deleteReservation = function() {
        if (!currentDeleteReservationId) {
            showError('No reservation selected for deletion');
            return;
        }
        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
        deleteBtn.disabled = true;
        fetch('includes/delete_reservation.php?id=' + currentDeleteReservationId)
            .then(response => response.json())
            .then(data => {
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                    modal.hide();
                    // Remove row from table
                    const row = document.getElementById('reservation-row-' + currentDeleteReservationId);
                    if (row) {
                        row.style.opacity = '0';
                        row.style.transition = 'opacity 0.4s';
                        setTimeout(() => {
                            row.remove();
                            // If using DataTables, redraw the table
                            if (typeof $.fn.DataTable !== 'undefined' && $('#reservationsTable').DataTable()) {
                                $('#reservationsTable').DataTable().clear().draw();
                            }
                        }, 400);
                    }
                    // Show success message
                    showSuccess(data.message || 'Reservation deleted successfully!');
                    currentDeleteReservationId = null;
                } else {
                    showError(data.message || 'Failed to delete reservation');
                }
            })
            .catch(error => {
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
                showError('Error deleting reservation: ' + error.message);
                console.error('Error:', error);
            });
    };

    // Helper functions for reservation management
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }
    function formatTime(timeString) {
        const [hours, minutes] = timeString.split(':');
        const date = new Date();
        date.setHours(hours, minutes);
        return date.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
    function formatDateTime(datetimeString) {
        const date = new Date(datetimeString);
        return date.toLocaleString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    function showSuccess(message) {
        document.getElementById('toastMessage').textContent = message;
        const toast = new bootstrap.Toast(document.getElementById('successToast'));
        toast.show();
        setTimeout(() => toast.hide(), 5000);
    }
    function showError(message) {
        document.getElementById('errorToastMessage').textContent = message;
        const toast = new bootstrap.Toast(document.getElementById('errorToast'));
        toast.show();
        setTimeout(() => toast.hide(), 5000);
    }

    // DataTable initialization for reservationsTable
    if (typeof jQuery !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        if ($('#reservationsTable').length) {
            $('#reservationsTable').DataTable({
                pageLength: 25,
                order: [[0, 'desc']],
                responsive: true,
                language: {
                    search: "Search reservations:",
                    lengthMenu: "Show _MENU_ reservations per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ reservations",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        }
    }

});