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
        });
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
});