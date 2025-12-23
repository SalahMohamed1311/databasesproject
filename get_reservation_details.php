<?php
// admin/ajax/get_reservation_details.php
require_once '../../includes/functions.php';
requireAdminLogin();

if (!isset($_GET['id'])) {
    die('<div class="alert alert-danger">Reservation ID is required</div>');
}

$reservation_id = intval($_GET['id']);
$conn = getConnection();

$sql = "SELECT r.*, 
               CONCAT(c.first_name, ' ', c.last_name) as customer_name,
               c.email as customer_email,
               c.phone as customer_phone,
               CONCAT(car.brand, ' ', car.model) as car_name,
               car.license_plate,
               car.color,
               car.daily_rate
        FROM reservations r
        JOIN customers c ON r.customer_id = c.id
        JOIN cars car ON r.car_id = car.id
        WHERE r.id = $reservation_id";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {
    $reservation = mysqli_fetch_assoc($result);
    ?>
    <div class="row">
        <div class="col-md-6">
            <h5>Reservation Information</h5>
            <table class="table table-sm">
                <tr>
                    <th width="40%">Reservation ID:</th>
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
                <tr>
                    <th>Total Cost:</th>
                    <td><strong>$<?php echo number_format($reservation['total_cost'], 2); ?></strong></td>
                </tr>
            </table>
        </div>
        
        <div class="col-md-6">
            <h5>Customer Information</h5>
            <table class="table table-sm">
                <tr>
                    <th width="40%">Name:</th>
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
            </table>
            
            <h5 class="mt-3">Car Information</h5>
            <table class="table table-sm">
                <tr>
                    <th width="40%">Car:</th>
                    <td><?php echo $reservation['car_name']; ?></td>
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
                    <th>Daily Rate:</th>
                    <td>$<?php echo number_format($reservation['daily_rate'], 2); ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <!-- Payment Information -->
    <?php
    $payment_sql = "SELECT * FROM payments WHERE reservation_id = $reservation_id";
    $payment_result = mysqli_query($conn, $payment_sql);
    
    if (mysqli_num_rows($payment_result) > 0) {
        $payment = mysqli_fetch_assoc($payment_result);
        ?>
        <hr>
        <h5>Payment Information</h5>
        <table class="table table-sm">
            <tr>
                <th width="40%">Payment Status:</th>
                <td>
                    <?php if ($payment['payment_status'] == 'completed'): ?>
                        <span class="badge badge-success">Completed</span>
                    <?php elseif ($payment['payment_status'] == 'pending'): ?>
                        <span class="badge badge-warning">Pending</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Failed</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Amount:</th>
                <td>$<?php echo number_format($payment['amount'], 2); ?></td>
            </tr>
            <tr>
                <th>Payment Method:</th>
                <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
            </tr>
            <tr>
                <th>Transaction ID:</th>
                <td><?php echo $payment['transaction_id'] ?: 'N/A'; ?></td>
            </tr>
            <?php if ($payment['payment_date']): ?>
            <tr>
                <th>Payment Date:</th>
                <td><?php echo date('F d, Y H:i', strtotime($payment['payment_date'])); ?></td>
            </tr>
            <?php endif; ?>
        </table>
        <?php
    } else {
        echo '<div class="alert alert-warning">No payment information found for this reservation.</div>';
    }
    ?>
    
    <!-- Action Buttons -->
    <hr>
    <div class="text-right">
        <?php if ($reservation['status'] == 'pending'): ?>
            <button class="btn btn-success update-status" 
                    data-id="<?php echo $reservation['id']; ?>" 
                    data-status="confirmed">
                <i class="fas fa-check"></i> Confirm Reservation
            </button>
        <?php endif; ?>
        
        <?php if (in_array($reservation['status'], ['pending', 'confirmed'])): ?>
            <button class="btn btn-danger update-status" 
                    data-id="<?php echo $reservation['id']; ?>" 
                    data-status="cancelled">
                <i class="fas fa-times"></i> Cancel Reservation
            </button>
        <?php endif; ?>
        
        <?php if ($reservation['status'] == 'active'): ?>
            <button class="btn btn-primary update-status" 
                    data-id="<?php echo $reservation['id']; ?>" 
                    data-status="completed">
                <i class="fas fa-flag-checkered"></i> Mark as Completed
            </button>
        <?php endif; ?>
    </div>
    <?php
} else {
    echo '<div class="alert alert-danger">Reservation not found</div>';
}

mysqli_close($conn);
?>