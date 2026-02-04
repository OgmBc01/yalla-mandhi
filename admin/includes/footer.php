<!-- Admin Footer -->
 
<footer class="admin-footer mt-auto py-3 bg-light">
    <div class="container text-center">
        <span class="text-muted">&copy; <?php echo date('Y'); ?> Yalla Al Mandi. All rights reserved.</span>
    </div>
</footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom Admin JS -->
    <script src="resources/js/admin-js.js"></script>
    
    <?php if (basename($_SERVER['PHP_SELF']) == 'dashboard.php'): ?>
    <!-- Chart.js for dashboard only -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
    
    <script>
        // Auto-update page title with active menu
        document.addEventListener('DOMContentLoaded', function() {
            const activeLink = document.querySelector('.nav-link.active');
            if (activeLink) {
                const pageTitle = activeLink.querySelector('.nav-text')?.textContent || 'Admin';
                document.title = pageTitle + ' | Yalla Al Mandi Admin';
            }
        });
    </script>
</body>
</html>
<?php
// Close database connection if open
if (isset($connection) && $connection instanceof mysqli) {
    $connection->close();
}
?>