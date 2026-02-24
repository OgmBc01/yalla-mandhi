<?php

// Determine active page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar" id="sidebar" style="overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none;">
    <div class="logo-container">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
            <i class="bi bi-list"></i>
        </button>

        <div class="logo">
            <i class="bi bi-restaurant logo-icon"></i>
            <span class="logo-text">AdminPanel</span>
        </div>
    </div>
    
    <style>

    </style>
    <ul class="nav flex-column">
        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-speedometer nav-icon"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>

        <!-- POS / Orders Menu - ENHANCED -->
        <?php
        // Determine if any POS & Orders submenu is active
        $orders_sources = ['add_order','order_list','online_orders'];
        $orders_active = ($current_page == 'orders.php') && ((isset($_GET['source']) && in_array($_GET['source'], $orders_sources)) || (!isset($_GET['source']) || $_GET['source'] == 'pos_view' || $_GET['source'] == 'pos'));
        ?>
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn <?php echo $orders_active ? 'active' : ''; ?>" href="#" data-menu="orders">
                <i class="bi bi-cart nav-icon"></i>
                <span class="nav-text">POS & Orders</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu<?php echo $orders_active ? ' show' : ''; ?>" id="orders-menu">
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'add_order') ? 'active' : ''; ?>" href="orders.php?source=add_order">
                        <i class="bi bi-plus-circle nav-icon"></i>
                        <span class="nav-text">New Order</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'order_list') ? 'active' : ''; ?>" href="orders.php?source=order_list">
                        <i class="bi bi-card-checklist nav-icon"></i>
                        <span class="nav-text">All Orders</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'online_orders') ? 'active' : ''; ?>" href="orders.php?source=online_orders">
                        <i class="bi bi-globe nav-icon"></i>
                        <span class="nav-text">Online Orders</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php?status=pending">
                        <i class="bi bi-clock-history nav-icon"></i>
                        <span class="nav-text">Pending Orders</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Employee Management -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn" href="#" data-menu="employees">
                <i class="bi bi-person-badge nav-icon"></i>
                <span class="nav-text">Employee Management</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="employees-menu">
                <li class="nav-item">
                    <a class="nav-link" href="employees.php?source=add_employee">
                        <i class="bi bi-person-plus nav-icon"></i>
                        <span class="nav-text">Add Employee</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="employees.php">
                        <i class="bi bi-person-lines-fill nav-icon"></i>
                        <span class="nav-text">View All Employees</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Shift Management -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn" href="#" data-menu="shifts">
                <i class="bi bi-clock-history nav-icon"></i>
                <span class="nav-text">Shift Management</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="shifts-menu">
                <li class="nav-item">
                    <a class="nav-link" href="shifts.php?source=add_shift">
                        <i class="bi bi-plus-circle nav-icon"></i>
                        <span class="nav-text">Add Shift</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="shifts.php">
                        <i class="bi bi-card-checklist nav-icon"></i>
                        <span class="nav-text">View All Shifts</span>
                    </a>
                </li>
                <!-- In sidebar.php -->
                <li class="nav-item">
                    <a class="nav-link" href="shifts.php?source=attendance">
                        <i class="bi bi-calendar-check nav-icon"></i>
                        <span class="nav-text">Attendance</span>
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Reports Menu -->
        <?php
        // Determine if any Reports & Analytics submenu is active
        $reports_sources = ['dashboard','daily','monthly','items','payment','vendor','staff','tax','export'];
        $reports_active = ($current_page == 'reports.php') && (isset($_GET['source']) && in_array($_GET['source'], $reports_sources));
        ?>
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn <?php echo ($current_page == 'reports.php' && (isset($_GET['source']) && in_array($_GET['source'], $reports_sources))) ? 'active' : ''; ?>" href="#" data-menu="reports">
                <i class="bi bi-graph-up nav-icon"></i>
                <span class="nav-text">Reports & Analytics</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu<?php echo $reports_active ? ' show' : ''; ?>" id="reports-menu">
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'dashboard') ? 'active' : ''; ?>" href="reports.php?source=dashboard">
                        <i class="bi bi-speedometer2 nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'daily') ? 'active' : ''; ?>" href="reports.php?source=daily">
                        <i class="bi bi-calendar-day nav-icon"></i>
                        <span class="nav-text">Daily Sales</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'monthly') ? 'active' : ''; ?>" href="reports.php?source=monthly">
                        <i class="bi bi-calendar-month nav-icon"></i>
                        <span class="nav-text">Monthly Summary</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'items') ? 'active' : ''; ?>" href="reports.php?source=items">
                        <i class="bi bi-box-seam nav-icon"></i>
                        <span class="nav-text">Item Performance</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'payment') ? 'active' : ''; ?>" href="reports.php?source=payment">
                        <i class="bi bi-credit-card nav-icon"></i>
                        <span class="nav-text">Payment Methods</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'vendor') ? 'active' : ''; ?>" href="reports.php?source=vendor">
                        <i class="bi bi-truck nav-icon"></i>
                        <span class="nav-text">Vendor Reconciliation</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'staff') ? 'active' : ''; ?>" href="reports.php?source=staff">
                        <i class="bi bi-person-badge nav-icon"></i>
                        <span class="nav-text">Staff Performance</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'tax') ? 'active' : ''; ?>" href="reports.php?source=tax">
                        <i class="bi bi-percent nav-icon"></i>
                        <span class="nav-text">Tax Report</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'export') ? 'active' : ''; ?>" href="reports.php?source=export">
                        <i class="bi bi-download nav-icon"></i>
                        <span class="nav-text">Export Reports</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Kitchen Display - NEW -->
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'kitchen_display') ? 'active' : ''; ?>" href="orders.php?source=kitchen_display">
                <i class="bi bi-tv nav-icon"></i>
                <span class="nav-text">Kitchen Display</span>
            </a>
        </li>

        <!-- Inventory Management - NEW SECTION (commented out)
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn" href="#" data-menu="inventory">
                <i class="bi bi-boxes nav-icon"></i>
                <span class="nav-text">Inventory</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="inventory-menu">
                <li class="nav-item">
                    <a class="nav-link" href="inventory.php?source=add_stock">
                        <i class="bi bi-plus-circle nav-icon"></i>
                        <span class="nav-text">Add Stock</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="inventory.php">
                        <i class="bi bi-card-checklist nav-icon"></i>
                        <span class="nav-text">View Inventory</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="inventory.php?source=stock_history">
                        <i class="bi bi-clock-history nav-icon"></i>
                        <span class="nav-text">Stock History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="inventory.php?source=low_stock">
                        <i class="bi bi-exclamation-triangle nav-icon"></i>
                        <span class="nav-text">Low Stock Alert</span>
                    </a>
                </li>
            </ul>
        </li>
        -->

        <!-- Customers Menu -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn" href="#" data-menu="customers">
                <i class="bi bi-people nav-icon"></i>
                <span class="nav-text">Customers</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="customers-menu">
                <li class="nav-item">
                    <a class="nav-link" href="customers.php?source=add_customer">
                        <i class="bi bi-person-plus nav-icon"></i>
                        <span class="nav-text">Add Customer</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="customers.php">
                        <i class="bi bi-person-lines-fill nav-icon"></i>
                        <span class="nav-text">View All</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="loyalty.php">
                        <i class="bi bi-star-fill nav-icon"></i>
                        <span class="nav-text">Loyalty Program</span>
                    </a>
                </li>
            </ul>
        </li>

        
        <!-- Reservations Menu -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn" href="#" data-menu="reservations">
                <i class="bi bi-calendar-check nav-icon"></i>
                <span class="nav-text">Reservations</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="reservations-menu">
                <li class="nav-item">
                    <a class="nav-link" href="reservation.php?source=add_reservation">
                        <i class="bi bi-plus-circle nav-icon"></i>
                        <span class="nav-text">Add Reservation</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservation.php">
                        <i class="bi bi-card-checklist nav-icon"></i>
                        <span class="nav-text">View All</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservation.php?source=view_pending">
                        <i class="bi bi-clock-history nav-icon"></i>
                        <span class="nav-text">Pending</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Menu Management -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn" href="#" data-menu="menu">
                <i class="bi bi-menu-button-wide nav-icon"></i>
                <span class="nav-text">Menu Management</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="menu-menu">
                <li class="nav-item">
                    <a class="nav-link" href="menu_items.php?source=add_item">
                        <i class="bi bi-plus-circle nav-icon"></i>
                        <span class="nav-text">Add Menu Item</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="menu_items.php">
                        <i class="bi bi-card-checklist nav-icon"></i>
                        <span class="nav-text">View All Items</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="categories.php">
                        <i class="bi bi-tags nav-icon"></i>
                        <span class="nav-text">Categories</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="categories.php?source=add_category">
                        <i class="bi bi-plus-circle nav-icon"></i>
                        <span class="nav-text">Add Category</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Branches Menu -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn" href="#" data-menu="branches">
                <i class="bi bi-shop nav-icon"></i>
                <span class="nav-text">Branches</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="branches-menu">
                <li class="nav-item">
                    <a class="nav-link" href="branches.php?source=add_branch">
                        <i class="bi bi-plus-circle nav-icon"></i>
                        <span class="nav-text">Add Branch</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="branches.php">
                        <i class="bi bi-card-checklist nav-icon"></i>
                        <span class="nav-text">View All Branches</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Catering & Events
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn" href="#" data-menu="catering">
                <i class="bi bi-truck nav-icon"></i>
                <span class="nav-text">Catering & Events</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="catering-menu">
                <li class="nav-item">
                    <a class="nav-link" href="catering_inquiries.php">
                        <i class="bi bi-envelope nav-icon"></i>
                        <span class="nav-text">Inquiries</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="catering_events.php">
                        <i class="bi bi-calendar-event nav-icon"></i>
                        <span class="nav-text">Events Calendar</span>
                    </a>
                </li>
            </ul>
        </li> -->

        <!-- Testimonials Menu -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn" href="#" data-menu="testimonials">
                <i class="bi bi-chat-left-quote nav-icon"></i>
                <span class="nav-text">Testimonials</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="testimonials-menu">
                <li class="nav-item">
                    <a class="nav-link" href="testimonials.php?source=add_testimonial">
                        <i class="bi bi-plus-circle nav-icon"></i>
                        <span class="nav-text">Add Testimonial</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="testimonials.php">
                        <i class="bi bi-card-checklist nav-icon"></i>
                        <span class="nav-text">View All Testimonials</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Offers & Promotions Menu -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn" href="#" data-menu="promotions">
                <i class="bi bi-percent nav-icon"></i>
                <span class="nav-text">Offers & Promotions</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="promotions-menu">
                <li class="nav-item">
                    <a class="nav-link" href="promotions.php?source=add_promotion">
                        <i class="bi bi-plus-circle nav-icon"></i>
                        <span class="nav-text">Add Promotion</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="promotions.php">
                        <i class="bi bi-card-checklist nav-icon"></i>
                        <span class="nav-text">View All Promotions</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Shift Schedule Dropdown -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#shiftMenu" data-bs-toggle="collapse" data-bs-target="#shiftMenu">
                <i class="bi bi-clock-history nav-icon"></i>
                <span class="nav-text">Shift Schedule</span>
                <i class="bi bi-chevron-right ms-auto"></i>
            </a>
            <div class="collapse" id="shiftMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a class="nav-link" href="shifts.php">
                            <i class="bi bi-table"></i> All Shifts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shifts.php?source=calendar_view">
                            <i class="bi bi-calendar-week"></i> Calendar View
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shifts.php?source=add_shift">
                            <i class="bi bi-plus-circle"></i> Add Single Shift
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shifts.php?source=bulk_assign">
                            <i class="bi bi-people-fill"></i> Bulk Assign
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Settings -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn" href="#" data-menu="settings">
                <i class="bi bi-gear nav-icon"></i>
                <span class="nav-text">Settings</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="settings-menu">
                <li class="nav-item">
                    <a class="nav-link" href="settings.php">
                        <i class="bi bi-sliders nav-icon"></i>
                        <span class="nav-text">General Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="bi bi-people-fill nav-icon"></i>
                        <span class="nav-text">Users & Permissions</span>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</div>