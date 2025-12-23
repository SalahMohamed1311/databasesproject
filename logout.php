<?php
require_once '../includes/functions.php';

// Destroy customer session
if (isset($_SESSION['customer_id'])) {
    unset($_SESSION['customer_id']);
    unset($_SESSION['customer_name']);
    unset($_SESSION['customer_email']);
    session_destroy();
}

// Redirect to home page
header("Location: ../index.php");
exit();
?>