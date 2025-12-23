<?php
// admin/ajax/get_customer_details.php
require_once '../../includes/functions.php';
requireAdminLogin();

if (!isset($_GET['id'])) {
    die('<div class="alert alert-danger">Customer ID is required</div>');
}

$customer_id = intval($_GET['id']);
$conn = getConnection();

$sql = "SELECT c.*, 
               COUNT(r.id) as total_reservations,
               SUM(r.total_cost) as total_spent,
               MAX(r.created_at) as last_reservation
        FROM customers c
        LEFT JOIN reservations r ON c.id = r.customer_id
        WHERE c.id = $customer_id
        GROUP BY c.id";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {
    $customer = mysqli_fetch_assoc($sql);
    ?>
    <div data-customer-id="<?php echo $customer['id']; ?>">
        <div class="row">
            <div class="col-md-4">
                <div class="text-center mb-3">
                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                </div>
                <h5 class="text-center"><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></h5>
            </div>
            <div class="col-md-8">
                <table class="table table-sm">
                    <tr>
                        <th width="30%">Email:</th>
                        <td><?php echo $customer['email']; ?></td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><?php echo $customer['phone'] ?: 'N/A'; ?></td>
                    </tr>
                    <tr>
                        <th>Driver License:</th>
                        <td><?php echo $customer['driver_license'] ?: 'N/A'; ?></td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td><?php echo nl2br($customer['address']) ?: 'N/A'; ?></td>
                    </tr>
                    <tr>
                        <th>Member Since:</th>
                        <td><?php echo date('F d, Y', strtotime($customer['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <th>Total Reservations:</th>
                        <td><span class="badge badge-info"><?php echo $customer['total_reservations']; ?></span></td>
                    </tr>
                    <tr>
                        <th>Total Spent:</th>
                        <td><span class="badge badge-success">$<?php echo number_format($customer['total_spent'] ?? 0, 2); ?></span></td>
                    </tr>
                    <tr>
                        <th>Last Reservation:</th>
                        <td>
                            <?php if ($customer['last_reservation']): ?>
                                <?php echo date('M d, Y', strtotime($customer['last_reservation'])); ?>
                            <?php else: ?>
                                Never
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php if ($customer['total_reservations'] > 0): ?>
        <hr>
        <h6>Recent Reservations</h6>
        <?php
        $res_sql = "SELECT r.*, CONCAT(car.brand, ' ', car.model) as car_name 
                   FROM reservations r
                   JOIN cars car ON r.car_id = car.id
                   WHERE r.customer_id = $customer_id
                   ORDER BY r.created_at DESC
                   LIMIT 5";
        $res_result = mysqli_query($conn, $res_sql);
        ?>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Car</th>
                        <th>Pickup Date</th>
                        <th>Status</th>
                        <th>Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($res = mysqli_fetch_assoc($res_result)): ?>
                    <tr>
                        <td>#<?php echo $res['id']; ?></td>
                        <td><?php echo $res['car_name']; ?></td>
                        <td><?php echo formatDate($res['pickup_date']); ?></td>
                        <td><?php echo getReservationStatusBadge($res['status']); ?></td>
                        <td>$<?php echo number_format($res['total_cost'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php
} else {
    echo '<div class="alert alert-danger">Customer not found</div>';
}

mysqli_close($conn);
?>