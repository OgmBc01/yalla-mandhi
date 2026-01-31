<?php
// logout.php
require_once 'includes/functions.php';

$result = logoutUser();

// Redirect to home page
header('Location: index.php');
exit;
?>