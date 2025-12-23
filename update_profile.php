<?php
// customer/update_profile.php
require_once '../includes/functions.php';
requireCustomerLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_SESSION['customer_id'];
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $phone = sanitize($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $conn = getConnection();
    $errors = [];
    
    // Validate required fields
    if (empty($first_name) || empty($last_name)) {
        $errors[] = 'First name and last name are required.';
    }
    
    // Check if changing password
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = 'Current password is required to change password.';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters long.';
        } else {
            // Verify current password
            $check_sql = "SELECT password FROM customers WHERE id = $customer_id";
            $check_result = mysqli_query($conn, $check_sql);
            $customer = mysqli_fetch_assoc($check_result);
            
            if (!verifyPassword($current_password, $customer['password'])) {
                $errors[] = 'Current password is incorrect.';
            }
        }
    }
    
    if (empty($errors)) {
        // Build update query
        $update_fields = "first_name = '$first_name', last_name = '$last_name', phone = '$phone'";
        
        if (!empty($new_password)) {
            $hashed_password = hashPassword($new_password);
            $update_fields .= ", password = '$hashed_password'";
        }
        
        $sql = "UPDATE customers SET $update_fields WHERE id = $customer_id";
        
        if (mysqli_query($conn, $sql)) {
            // Update session name
            $_SESSION['customer_name'] = $first_name . ' ' . $last_name;
            
            $_SESSION['success_message'] = 'Profile updated successfully!';
            header("Location: my_reservations.php");
            exit();
        } else {
            $_SESSION['error_message'] = 'Error updating profile: ' . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
    
    mysqli_close($conn);
    header("Location: my_reservations.php");
    exit();
} else {
    header("Location: my_reservations.php");
    exit();
}
?>