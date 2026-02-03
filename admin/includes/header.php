<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';
require_once dirname(__DIR__, 1) . '/../includes/database.php';

enforce_session_timeout();
requireAdmin(); // 🔥 SINGLE AUTH CHECK
?>
<!DOCTYPE html>
<html lang="en">
<head>
...

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Yalla Al Mandhi</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="resources/css/admin-style.css">
    
    <!-- Yalla Al Mandhi Theme Colors -->
    <style>
        :root {
            --color-red: #c41e3a;
            --color-red-light: #e84c4c;
            --color-beige: #f5f1e8;
            --color-sand: #e6dfd3;
            --color-dark-brown: #2c2416;
            --color-soft-black: #1a1a1a;
            --color-olive: #8a8635;
            --color-copper: #b87333;
            --color-white: #ffffff;
            --color-light-gray: #f8f9fa;
            
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --top-nav-height: 70px;
        }
    </style>
</head>
<body></body>