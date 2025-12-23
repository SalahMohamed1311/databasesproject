<?php
// index.php

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_folder = 'car-rental-system';
define('BASE_URL', $protocol . "://" . $host . "/" . $project_folder . "/");

$page_title = "Car Rental System - Home";
require_once 'includes/header.php';

// Include functions
require_once 'includes/functions.php';
?>

<div class="hero-section text-center">
    <div class="container">
        <h1 class="display-4">Find Your Perfect Ride</h1>
        <p class="lead">Rent high-quality cars at affordable prices. Flexible plans for all your needs.</p>
        <?php if (!isset($_SESSION['customer_id'])): ?>
            <a href="<?php echo BASE_URL; ?>customer/register.php" class="btn btn-primary btn-lg">Get Started</a>
            <a href="<?php echo BASE_URL; ?>customer/browse_cars.php" class="btn btn-outline-light btn-lg">Browse Cars</a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>customer/browse_cars.php" class="btn btn-primary btn-lg">Rent a Car Now</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <div class="row mb-5">
        <div class="col-md-12 text-center">
            <h2>Why Choose Our Service?</h2>
            <p class="text-muted">We provide the best car rental experience</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-center p-4">
                <div class="card-body">
                    <i class="fas fa-car fa-3x text-primary mb-3"></i>
                    <h4>Wide Selection</h4>
                    <p>Choose from a variety of cars - economy, luxury, SUVs, and more.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card text-center p-4">
                <div class="card-body">
                    <i class="fas fa-dollar-sign fa-3x text-success mb-3"></i>
                    <h4>Best Prices</h4>
                    <p>Competitive rates with no hidden fees. Price match guarantee.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card text-center p-4">
                <div class="card-body">
                    <i class="fas fa-headset fa-3x text-info mb-3"></i>
                    <h4>24/7 Support</h4>
                    <p>Our customer service team is available round the clock to assist you.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-12 text-center">
            <h2>Popular Cars</h2>
            <p class="text-muted">Check out our most rented vehicles</p>
        </div>
    </div>
    
    <div class="row">
        <?php
        $conn = getConnection();
        $sql = "SELECT * FROM cars WHERE status = 'available' LIMIT 3";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['brand'] . ' ' . $row['model']; ?></h5>
                            <p class="card-text">
                                <strong>Year:</strong> <?php echo $row['year']; ?><br>
                                <strong>Daily Rate:</strong> $<?php echo $row['daily_rate']; ?><br>
                                <strong>Status:</strong> <?php echo getCarStatusBadge($row['status']); ?>
                            </p>
                            <a href="<?php echo BASE_URL; ?>customer/browse_cars.php" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-md-12"><div class="alert alert-info">No cars available at the moment.</div></div>';
        }
        mysqli_close($conn);
        ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>