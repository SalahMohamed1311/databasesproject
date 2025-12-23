<?php
// ajax/get_customer_reservation.php
require_once '../includes/functions.php';
requireCustomerLogin();

if (!isset($_GET['id'])) {
    die('<div class="alert alert-danger">Reservation ID is required</div>');
}

$reservation_id = intval($_GET['id']);
$customer_id = $_SESSION['customer_id'];
$conn = getConnection();

// Verify reservation belongs to customer
$sql = "SELECT r.*, 
               CONCAT(c.first_name, ' ', c.last_name) as customer_name,
               c.email as customer_email,
               c.phone as customer_phone,
               c.driver_license,
               CONCAT(car.brand, ' ', car.model) as car_name,
               car.license_plate,
               car.color,
               car.year,
               car.fuel_type,
               car.transmission,
               car.seats,
               car.daily_rate
        FROM reservations r
        JOIN customers c ON r.customer_id = c.id
        JOIN cars car ON r.car_id = car.id
        WHERE r.id = $reservation_id AND r.customer_id = $customer_id";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {
    $reservation = mysqli_fetch_assoc($result);
    ?>
    <div class="receipt">
        <div class="text-center mb-4">
            <h3>Car Rental Receipt</h3>
            <p class="text-muted">Reservation #<?php echo $reservation['id']; ?></p>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <h5>Rental Details</h5>
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="50%">Reservation ID:</th>
                        <td>#<?php echo $reservation['id']; ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td><?php echo getReservationStatusBadge($reservation['status']); ?></td>
                    </tr>
                    <tr>
                        <th>Created On:</th>
                        <td><?php echo date('F d, Y H:i', strtotime($reservation['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <th>Pickup Date:</th>
                        <td><?php echo formatDate($reservation['pickup_date']); ?></td>
                    </tr>
                    <tr>
                        <th>Return Date:</th>
                        <td><?php echo formatDate($reservation['return_date']); ?></td>
                    </tr>
                    <tr>
                        <th>Total Days:</th>
                        <td><?php echo $reservation['total_days']; ?> days</td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <h5>Car Information</h5>
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="50%">Car:</th>
                        <td><?php echo $reservation['car_name']; ?> (<?php echo $reservation['year']; ?>)</td>
                    </tr>
                    <tr>
                        <th>License Plate:</th>
                        <td><?php echo $reservation['license_plate']; ?></td>
                    </tr>
                    <tr>
                        <th>Color:</th>
                        <td><?php echo $reservation['color']; ?></td>
                    </tr>
                    <tr>
                        <th>Fuel Type:</th>
                        <td><?php echo ucfirst($reservation['fuel_type']); ?></td>
                    </tr>
                    <tr>
                        <th>Transmission:</th>
                        <td><?php echo ucfirst($reservation['transmission']); ?></td>
                    </tr>
                    <tr>
                        <th>Seats:</th>
                        <td><?php echo $reservation['seats']; ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <hr>
        
        <!-- Cost Breakdown -->
        <h5>Cost Breakdown</h5>
        <table class="table table-sm">
            <tr>
                <th width="70%">Daily Rate (<?php echo $reservation['total_days']; ?> days × $<?php echo $reservation['daily_rate']; ?>)</th>
                <td class="text-right">$<?php echo number_format($reservation['daily_rate'] * $reservation['total_days'], 2); ?></td>
            </tr>
            <?php
            // Calculate tax (example: 8%)
            $subtotal = $reservation['total_cost'];
            $tax_rate = 0.08;
            $tax = $subtotal * $tax_rate;
            $total = $subtotal + $tax;
            ?>
            <tr>
                <th>Subtotal</th>
                <td class="text-right">$<?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <tr>
                <th>Tax (<?php echo ($tax_rate * 100); ?>%)</th>
                <td class="text-right">$<?php echo number_format($tax, 2); ?></td>
            </tr>
            <tr class="table-active font-weight-bold">
                <th>TOTAL</th>
                <td class="text-right">$<?php echo number_format($total, 2); ?></td>
            </tr>
        </table>
        
        <hr>
        
        <!-- Customer Information -->
        <h5>Customer Information</h5>
        <table class="table table-sm table-borderless">
            <tr>
                <th width="30%">Name:</th>
                <td><?php echo $reservation['customer_name']; ?></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><?php echo $reservation['customer_email']; ?></td>
            </tr>
            <tr>
                <th>Phone:</th>
                <td><?php echo $reservation['customer_phone'] ?: 'N/A'; ?></td>
            </tr>
            <tr>
                <th>Driver License:</th>
                <td><?php echo $reservation['driver_license'] ?: 'N/A'; ?></td>
            </tr>
        </table>
        
        <hr>
        
        <!-- Terms and Conditions -->
        <div class="mt-4">
            <h6>Terms & Conditions</h6>
            <small class="text-muted">
                <p>1. The vehicle must be returned in the same condition as rented.</p>
                <p>2. Late returns will incur additional charges.</p>
                <p>3. Fuel is the responsibility of the renter.</p>
                <p>4. Insurance is included in the rental price.</p>
                <p>5. All traffic violations are the responsibility of the renter.</p>
            </small>
        </div>
        
        <div class="text-center mt-4">
            <p>Thank you for choosing Car Rental System!</p>
            <p class="text-muted">For inquiries: contact@carrentalsystem.com | Phone: +1-234-567-8900</p>
        </div>
    </div>
    <?php
} else {
    echo '<div class="alert alert-danger">Reservation not found or access denied</div>';
}

mysqli_close($conn);
?>