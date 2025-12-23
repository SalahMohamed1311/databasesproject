<?php
require_once '../includes/functions.php';
requireAdminLogin();

$page_title = "Admin Dashboard";

// Get statistics
$conn = getConnection();

// Total cars
$cars_query = "SELECT COUNT(*) as total_cars, 
               SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_cars,
               SUM(CASE WHEN status = 'rented' THEN 1 ELSE 0 END) as rented_cars,
               SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_cars
               FROM cars";
$cars_result = mysqli_query($conn, $cars_query);
$cars_stats = mysqli_fetch_assoc($cars_result);

// Total customers
$customers_query = "SELECT COUNT(*) as total_customers FROM customers";
$customers_result = mysqli_query($conn, $customers_query);
$customers_stats = mysqli_fetch_assoc($customers_result);

// Total reservations
$reservations_query = "SELECT COUNT(*) as total_reservations,
                      SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reservations,
                      SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_reservations,
                      SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_reservations,
                      SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_reservations
                      FROM reservations";
$reservations_result = mysqli_query($conn, $reservations_query);
$reservations_stats = mysqli_fetch_assoc($reservations_result);

// Recent reservations
$recent_reservations_query = "SELECT r.*, c.first_name, c.last_name, car.brand, car.model 
                             FROM reservations r
                             JOIN customers c ON r.customer_id = c.id
                             JOIN cars car ON r.car_id = car.id
                             ORDER BY r.created_at DESC LIMIT 5";
$recent_reservations_result = mysqli_query($conn, $recent_reservations_query);

require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <a href="manage_cars.php" class="btn btn-sm btn-outline-secondary">Manage Cars</a>
            <a href="manage_customers.php" class="btn btn-sm btn-outline-secondary">Manage Customers</a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Cars Statistics -->
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Cars</h5>
                <h2 class="display-4"><?php echo $cars_stats['total_cars']; ?></h2>
                <p class="card-text">
                    Available: <?php echo $cars_stats['available_cars']; ?><br>
                    Rented: <?php echo $cars_stats['rented_cars']; ?><br>
                    Maintenance: <?php echo $cars_stats['maintenance_cars']; ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Customers Statistics -->
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Customers</h5>
                <h2 class="display-4"><?php echo $customers_stats['total_customers']; ?></h2>
                <p class="card-text">Registered customers in the system</p>
            </div>
        </div>
    </div>
    
    <!-- Reservations Statistics -->
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Total Reservations</h5>
                <h2 class="display-4"><?php echo $reservations_stats['total_reservations']; ?></h2>
                <p class="card-text">
                    Pending: <?php echo $reservations_stats['pending_reservations']; ?><br>
                    Active: <?php echo $reservations_stats['active_reservations']; ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Revenue Statistics -->
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Monthly Revenue</h5>
                <h2 class="display-4">$<?php 
                    $revenue_query = "SELECT SUM(total_cost) as monthly_revenue FROM reservations 
                                     WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                                     AND YEAR(created_at) = YEAR(CURRENT_DATE())
                                     AND status IN ('completed', 'active')";
                    $revenue_result = mysqli_query($conn, $revenue_query);
                    $revenue = mysqli_fetch_assoc($revenue_result);
                    echo number_format($revenue['monthly_revenue'] ?? 0, 2);
                ?></h2>
                <p class="card-text">Current month's revenue</p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reservations -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Recent Reservations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Pickup Date</th>
                                <th>Return Date</th>
                                <th>Total Cost</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($recent_reservations_result)): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                <td><?php echo $row['brand'] . ' ' . $row['model']; ?></td>
                                <td><?php echo formatDate($row['pickup_date']); ?></td>
                                <td><?php echo formatDate($row['return_date']); ?></td>
                                <td>$<?php echo $row['total_cost']; ?></td>
                                <td><?php echo getReservationStatusBadge($row['status']); ?></td>
                                         <td>
    <a href="?view_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
        <i class="fas fa-eye"></i> View
    </a>
    <!-- باقي الأزرار -->
</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
mysqli_close($conn);
require_once '../includes/footer.php'; 
// Display reservation details directly if ID is provided
if (isset($_GET['view_id'])) {
    $reservation_id = intval($_GET['view_id']);
    $conn = getConnection();
    
    $sql = "SELECT r.*, 
                   CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                   c.email as customer_email,
                   c.phone as customer_phone,
                   CONCAT(car.brand, ' ', car.model) as car_name,
                   car.license_plate
            FROM reservations r
            JOIN customers c ON r.customer_id = c.id
            JOIN cars car ON r.car_id = car.id
            WHERE r.id = $reservation_id";
    
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $reservation = mysqli_fetch_assoc($result);
        ?>
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reservation Details #<?php echo $reservation['id']; ?></h5>
                        <a href="dashboard.php" class="close">
                            <span>&times;</span>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr><th>Customer:</th><td><?php echo $reservation['customer_name']; ?></td></tr>
                                    <tr><th>Car:</th><td><?php echo $reservation['car_name']; ?></td></tr>
                                    <tr><th>License Plate:</th><td><?php echo $reservation['license_plate']; ?></td></tr>
                                    <tr><th>Pickup Date:</th><td><?php echo formatDate($reservation['pickup_date']); ?></td></tr>
                                    <tr><th>Return Date:</th><td><?php echo formatDate($reservation['return_date']); ?></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr><th>Total Days:</th><td><?php echo $reservation['total_days']; ?></td></tr>
                                    <tr><th>Total Cost:</th><td>$<?php echo $reservation['total_cost']; ?></td></tr>
                                    <tr><th>Status:</th><td><?php echo getReservationStatusBadge($reservation['status']); ?></td></tr>
                                    <tr><th>Email:</th><td><?php echo $reservation['customer_email']; ?></td></tr>
                                    <tr><th>Phone:</th><td><?php echo $reservation['customer_phone']; ?></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="dashboard.php" class="btn btn-secondary">Close</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    mysqli_close($conn);
}
?>