<?php
// customer/register.php

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_folder = 'car-rental-system';
define('BASE_URL', $protocol . "://" . $host . "/" . $project_folder . "/");

// Include functions
require_once dirname(dirname(__FILE__)) . '/includes/functions.php';

$page_title = "Customer Registration";
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $driver_license = sanitize($_POST['driver_license']);
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'Please fill all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $conn = getConnection();
        
        // Check if email already exists
        $check_email = "SELECT id FROM customers WHERE email = '$email'";
        $result = mysqli_query($conn, $check_email);
        
        if (mysqli_num_rows($result) > 0) {
            $error = 'Email already registered.';
        } else {
            // Hash password
            $hashed_password = hashPassword($password);
            
            // Insert customer
            $sql = "INSERT INTO customers (first_name, last_name, email, password, phone, address, driver_license) 
                    VALUES ('$first_name', '$last_name', '$email', '$hashed_password', '$phone', '$address', '$driver_license')";
            
            if (mysqli_query($conn, $sql)) {
                $success = 'Registration successful! You can now login.';
                // Clear form
                $first_name = $last_name = $email = $phone = $address = $driver_license = '';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        mysqli_close($conn);
    }
}

require_once dirname(dirname(__FILE__)) . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Customer Registration</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo isset($first_name) ? $first_name : ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo isset($last_name) ? $last_name : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($email) ? $email : ''; ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo isset($phone) ? $phone : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($address) ? $address : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="driver_license">Driver's License Number</label>
                        <input type="text" class="form-control" id="driver_license" name="driver_license" 
                               value="<?php echo isset($driver_license) ? $driver_license : ''; ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>
                
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="<?php echo BASE_URL; ?>customer/login.php">Login here</a></p>
                    <p><a href="<?php echo BASE_URL; ?>index.php">Back to Home</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(dirname(__FILE__)) . '/includes/footer.php'; ?>