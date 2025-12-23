<?php
// includes/functions.php

// Define base path
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__FILE__)));
}

// Require database configuration
require_once BASE_PATH . '/config/database.php';

/**
 * Sanitize input data
 */
function sanitize($data) {
    $conn = getConnection();
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    mysqli_close($conn);
    return $data;
}

/**
 * Check if user is logged in as admin
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Check if user is logged in as customer
 */
function isCustomerLoggedIn() {
    return isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id']);
}

/**
 * Redirect to login if not authenticated as admin
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: " . BASE_PATH . "/admin/login.php");
        exit();
    }
}

/**
 * Redirect to login if not authenticated as customer
 */
function requireCustomerLogin() {
    if (!isCustomerLoggedIn()) {
        header("Location: " . BASE_PATH . "/customer/login.php");
        exit();
    }
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Get car status badge
 */
function getCarStatusBadge($status) {
    $badges = [
        'available' => '<span class="badge badge-success">Available</span>',
        'rented' => '<span class="badge badge-danger">Rented</span>',
        'maintenance' => '<span class="badge badge-warning">Maintenance</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

/**
 * Get reservation status badge
 */
function getReservationStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-secondary">Pending</span>',
        'confirmed' => '<span class="badge badge-primary">Confirmed</span>',
        'active' => '<span class="badge badge-info">Active</span>',
        'completed' => '<span class="badge badge-success">Completed</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

/**
 * Format date
 */
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

/**
 * Calculate total days between two dates
 */
function calculateDays($pickup_date, $return_date) {
    $pickup = new DateTime($pickup_date);
    $return = new DateTime($return_date);
    $interval = $pickup->diff($return);
    return $interval->days;
}

/**
 * Send notification email
 */
function sendNotificationEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Car Rental System <noreply@carrental.com>" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>