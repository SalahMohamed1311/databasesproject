<?php
require_once '../includes/functions.php';

$page_title = "Customer Login";
$error = '';

// Redirect if already logged in
if (isCustomerLoggedIn()) {
    header("Location: browse_cars.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        $conn = getConnection();
        $sql = "SELECT * FROM customers WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) == 1) {
            $customer = mysqli_fetch_assoc($result);
            
            if (verifyPassword($password, $customer['password'])) {
                // Set session variables
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
                $_SESSION['customer_email'] = $customer['email'];
                
                // Redirect to browse cars
                header("Location: browse_cars.php");
                exit();
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'No account found with this email.';
        }
        mysqli_close($conn);
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Customer Login</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
                
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                    <p><a href="../index.php">Back to Home</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>