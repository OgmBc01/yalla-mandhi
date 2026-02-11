<?php
// Get month and year from URL
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Validate month and year
if ($month < 1) $month = 1;
if ($month > 12) $month = 12;
if ($year < 2020) $year = date('Y');
if ($year > 2030) $year = date('Y');

// Get filter employee
$filter_employee = isset($_GET['employee']) ? intval($_GET['employee']) : 0;

// Calculate calendar dates
$first_day = mktime(0,0,0,$month,1,$year);
$days_in_month = date('t', $first_day);
$day_of_week = date('w', $first_day); // 0 = Sunday, 1 = Monday, etc.
$today = date('Y-m-d');

// Get all active employees for filter
$employees_query = "SELECT id, full_name, employee_id, position, department 
                   FROM users 
                   WHERE role IN ('employee', 'admin') AND is_active = 1 
                   ORDER BY full_name";
$employees_result = $connection->query($employees_query);

// Build shift data for the month
$shift_query = "SELECT s.shift_date, s.employee_id, s.shift_type, s.start_time, s.end_time, 
                       s.location, s.is_active, s.id, u.full_name, u.position
                FROM shifts s
                JOIN users u ON s.employee_id = u.id
                WHERE s.shift_date BETWEEN ? AND ? 
                AND s.is_active = 1";

$params = [date('Y-m-01', $first_day), date('Y-m-t', $first_day)];
$types = "ss";

if ($filter_employee > 0) {
    $shift_query .= " AND s.employee_id = ?";
    $params[] = $filter_employee;
    $types .= "i";
}

$shift_query .= " ORDER BY s.shift_date, s.start_time";

$stmt = $connection->prepare($shift_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$shifts_result = $stmt->get_result();

// Organize shifts by date
$shifts_by_date = [];
while ($shift = $shifts_result->fetch_assoc()) {
    $date = $shift['shift_date'];
    if (!isset($shifts_by_date[$date])) {
        $shifts_by_date[$date] = [];
    }
    $shifts_by_date[$date][] = $shift;
}
$stmt->close();

// Previous and next month navigation
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year = $year - 1;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year = $year + 1;
}

// Month names
$month_names = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

// Build navigation URL parameters
$nav_params = '';
if ($filter_employee > 0) {
    $nav_params = '&employee=' . $filter_employee;
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Shift Calendar</h1>
            <div>
                <a href="shifts.php" class="btn btn-secondary me-2">
                    <i class="bi bi-table"></i> Table View
                </a>
                <a href="shifts.php?source=add_shift" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Shift
                </a>
            </div>
        </div>

        <!-- Calendar Navigation and Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="btn-group" role="group">
                            <a href="shifts.php?source=calendar_view&month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?><?php echo $nav_params; ?>" 
                               class="btn btn-outline-primary">
                                <i class="bi bi-chevron-left"></i> <?php echo $month_names[$prev_month]; ?>
                            </a>
                            <a href="shifts.php?source=calendar_view&month=<?php echo date('m'); ?>&year=<?php echo date('Y'); ?><?php echo $nav_params; ?>" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-calendar"></i> Today
                            </a>
                            <a href="shifts.php?source=calendar_view&month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?><?php echo $nav_params; ?>" 
                               class="btn btn-outline-primary">
                                <?php echo $month_names[$next_month]; ?> <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <h3 class="text-center mb-0"><?php echo $month_names[$month] . ' ' . $year; ?></h3>
                    </div>
                    <div class="col-md-4">
                        <form method="GET" action="" class="row g-2">
                            <input type="hidden" name="source" value="calendar_view">
                            <input type="hidden" name="month" value="<?php echo $month; ?>">
                            <input type="hidden" name="year" value="<?php echo $year; ?>">
                            
                            <div class="col-8">
                                <select class="form-select" name="employee">
                                    <option value="0">All Employees</option>
                                    <?php if ($employees_result && $employees_result->num_rows > 0): ?>
                                        <?php while ($emp = $employees_result->fetch_assoc()): ?>
                                            <option value="<?php echo $emp['id']; ?>" 
                                                    <?php echo $filter_employee == $emp['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($emp['full_name']); ?>
                                                <?php if ($emp['position']): ?>
                                                    - <?php echo htmlspecialchars($emp['position']); ?>
                                                <?php endif; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-filter"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Legend -->
        <div class="card shadow-sm mb-4">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <span class="me-2"><strong>Shift Types:</strong></span>
                    <span class="badge bg-info">Morning (6AM-2PM)</span>
                    <span class="badge bg-warning">Afternoon (2PM-10PM)</span>
                    <span class="badge bg-primary">Evening (4PM-12AM)</span>
                    <span class="badge bg-dark">Night (10PM-6AM)</span>
                    <span class="badge bg-success ms-3">Today</span>
                    <span class="badge bg-light text-dark border">No Shifts</span>
                </div>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="calendar-grid">
                    <!-- Day headers -->
                    <div class="calendar-row header">
                        <div class="calendar-cell day-header">Sunday</div>
                        <div class="calendar-cell day-header">Monday</div>
                        <div class="calendar-cell day-header">Tuesday</div>
                        <div class="calendar-cell day-header">Wednesday</div>
                        <div class="calendar-cell day-header">Thursday</div>
                        <div class="calendar-cell day-header">Friday</div>
                        <div class="calendar-cell day-header">Saturday</div>
                    </div>

                    <!-- Calendar days -->
                    <div class="calendar-row">
                        <?php
                        // Fill empty cells from previous month
                        for ($i = 0; $i < $day_of_week; $i++):
                        ?>
                            <div class="calendar-cell empty"></div>
                        <?php endfor; ?>

                        <?php
                        // Display days of the month
                        for ($day = 1; $day <= $days_in_month; $day++):
                            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                            $is_today = ($date == $today);
                            $cell_class = 'calendar-cell';
                            if ($is_today) $cell_class .= ' today';
                            
                            // Check if date has shifts
                            $has_shifts = isset($shifts_by_date[$date]);
                            $shift_count = $has_shifts ? count($shifts_by_date[$date]) : 0;
                        ?>
                            <div class="<?php echo $cell_class; ?>">
                                <div class="calendar-date <?php echo $has_shifts ? 'has-shifts' : ''; ?>">
                                    <span class="date-number"><?php echo $day; ?></span>
                                    <?php if ($has_shifts): ?>
                                        <span class="shift-count badge bg-primary">
                                            <?php echo $shift_count; ?> shift(s)
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($has_shifts): ?>
                                    <div class="calendar-shifts">
                                        <?php foreach ($shifts_by_date[$date] as $shift): ?>
                                            <?php
                                            $type_class = '';
                                            switch($shift['shift_type']) {
                                                case 'morning': $type_class = 'bg-info'; break;
                                                case 'afternoon': $type_class = 'bg-warning'; break;
                                                case 'evening': $type_class = 'bg-primary'; break;
                                                case 'night': $type_class = 'bg-dark'; break;
                                                default: $type_class = 'bg-secondary';
                                            }
                                            ?>
                                            <div class="shift-item" onclick="location.href='shifts.php?source=view_shift&id=<?php echo $shift['id']; ?>'">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <span class="badge <?php echo $type_class; ?> shift-badge">
                                                            <?php echo strtoupper(substr($shift['shift_type'], 0, 1)); ?>
                                                        </span>
                                                        <span class="shift-employee">
                                                            <?php echo htmlspecialchars($shift['full_name']); ?>
                                                        </span>
                                                    </div>
                                                    <small class="shift-time">
                                                        <?php echo date('g:i A', strtotime($shift['start_time'])); ?>
                                                    </small>
                                                </div>
                                                <small class="shift-location text-muted d-block">
                                                    <i class="bi bi-pin-map"></i> <?php echo htmlspecialchars($shift['location']); ?>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="calendar-shifts no-shifts">
                                        <a href="shifts.php?source=add_shift&date=<?php echo $date; ?>" 
                                           class="btn btn-sm btn-outline-primary add-shift-btn">
                                            <i class="bi bi-plus-circle"></i> Add
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>

                        <?php
                        // Fill remaining cells
                        $total_cells = $day_of_week + $days_in_month;
                        $remaining_cells = 7 - ($total_cells % 7);
                        if ($remaining_cells < 7):
                            for ($i = 0; $i < $remaining_cells; $i++):
                        ?>
                            <div class="calendar-cell empty"></div>
                        <?php
                            endfor;
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shift Summary -->
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Monthly Shift Summary</h5>
            </div>
            <div class="card-body">
                <?php
                $total_shifts = array_sum(array_map('count', $shifts_by_date));
                $total_days = count($shifts_by_date);
                
                $unique_employees = [];
                foreach ($shifts_by_date as $shifts) {
                    foreach ($shifts as $shift) {
                        $unique_employees[$shift['employee_id']] = true;
                    }
                }
                $employee_count = count($unique_employees);
                $avg_shifts = $total_days > 0 ? round($total_shifts / $total_days, 1) : 0;
                ?>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted">Total Shifts</h6>
                            <h3><?php echo $total_shifts; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted">Working Days</h6>
                            <h3><?php echo $total_days; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted">Employees Scheduled</h6>
                            <h3><?php echo $employee_count; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted">Avg Shifts/Day</h6>
                            <h3><?php echo $avg_shifts; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-grid {
    display: flex;
    flex-direction: column;
    width: 100%;
    border: 1px solid #dee2e6;
    background-color: #fff;
}

.calendar-row {
    display: flex;
    flex-wrap: wrap;
}

.calendar-row.header {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
}

.calendar-cell {
    flex: 0 0 calc(100% / 7);
    min-height: 150px;
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    padding: 10px;
    position: relative;
    box-sizing: border-box;
}

.calendar-cell:nth-child(7n) {
    border-right: none;
}

.calendar-cell.empty {
    background-color: #f8f9fa;
}

.calendar-cell.today {
    background-color: #fff3cd;
    border: 2px solid #ffc107;
}

.calendar-date {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.date-number {
    font-size: 1.1rem;
    font-weight: 600;
}

.has-shifts .date-number {
    color: #0d6efd;
}

.shift-count {
    font-size: 0.7rem;
    padding: 3px 6px;
}

.calendar-shifts {
    max-height: 100px;
    overflow-y: auto;
    scrollbar-width: thin;
}

.calendar-shifts::-webkit-scrollbar {
    width: 4px;
}

.calendar-shifts::-webkit-scrollbar-thumb {
    background-color: #ced4da;
    border-radius: 4px;
}

.shift-item {
    font-size: 0.75rem;
    padding: 6px 8px;
    margin-bottom: 6px;
    background-color: #f8f9fa;
    border-radius: 4px;
    border-left: 3px solid #6c757d;
    cursor: pointer;
    transition: all 0.2s ease;
}

.shift-item:hover {
    background-color: #e9ecef;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.shift-badge {
    font-size: 0.7rem;
    margin-right: 4px;
    padding: 3px 6px;
}

.shift-employee {
    font-weight: 600;
}

.shift-time {
    color: #6c757d;
    font-size: 0.7rem;
}

.shift-location {
    margin-top: 3px;
    font-size: 0.7rem;
    color: #6c757d;
}

.no-shifts {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 80px;
}

.add-shift-btn {
    font-size: 0.75rem;
    padding: 4px 12px;
}

.day-header {
    font-weight: 600;
    padding: 12px 10px;
    text-align: center;
    background-color: #e9ecef;
}

/* Responsive Design */
@media (max-width: 992px) {
    .calendar-cell {
        min-height: 120px;
        padding: 8px;
    }
    
    .shift-item {
        padding: 4px 6px;
    }
}

@media (max-width: 768px) {
    .calendar-cell {
        min-height: 100px;
        padding: 6px;
    }
    
    .date-number {
        font-size: 0.9rem;
    }
    
    .shift-item {
        font-size: 0.7rem;
    }
    
    .shift-time {
        display: none;
    }
    
    .shift-badge {
        padding: 2px 4px;
    }
    
    .add-shift-btn {
        padding: 2px 8px;
    }
}

@media (max-width: 576px) {
    .calendar-cell {
        min-height: 80px;
    }
    
    .calendar-date {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .shift-count {
        margin-top: 4px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add responsive class to calendar on window resize
    function checkCalendarResponsive() {
        const calendarCells = document.querySelectorAll('.calendar-cell');
        if (window.innerWidth < 768) {
            calendarCells.forEach(cell => {
                cell.classList.add('mobile');
            });
        } else {
            calendarCells.forEach(cell => {
                cell.classList.remove('mobile');
            });
        }
    }
    
    // Check on load
    checkCalendarResponsive();
    
    // Check on resize
    window.addEventListener('resize', checkCalendarResponsive);
    
    // Make shift items clickable with proper URL encoding
    document.querySelectorAll('.shift-item').forEach(item => {
        item.addEventListener('click', function(e) {
            const url = this.getAttribute('onclick');
            if (url) {
                const match = url.match(/href='([^']+)'/);
                if (match && match[1]) {
                    window.location.href = match[1];
                }
            }
        });
    });
});
</script>