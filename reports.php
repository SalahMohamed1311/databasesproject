<?php
// admin/reports.php
require_once '../includes/functions.php';
requireAdminLogin();

$page_title = "Reports & Analytics";
$conn = getConnection();

// Default date range (last 30 days)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Report type
$report_type = isset($_GET['type']) ? $_GET['type'] : 'overview';

// Export functionality
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    if ($export_type == 'csv') {
        exportReportCSV();
    }
}

// Get report data based on type
switch($report_type) {
    case 'reservations':
        $report_title = "Reservations Report";
        $data = getReservationsReport($conn, $start_date, $end_date);
        break;
    case 'revenue':
        $report_title = "Revenue Report";
        $data = getRevenueReport($conn, $start_date, $end_date);
        break;
    case 'customers':
        $report_title = "Customers Report";
        $data = getCustomersReport($conn, $start_date, $end_date);
        break;
    case 'cars':
        $report_title = "Cars Report";
        $data = getCarsReport($conn);
        break;
    default:
        $report_title = "Overview Report";
        $data = getOverviewReport($conn, $start_date, $end_date);
        break;
}

function getOverviewReport($conn, $start_date, $end_date) {
    $report = [];
    
    // Total reservations
    $sql = "SELECT COUNT(*) as total, 
                   SUM(total_cost) as revenue,
                   AVG(total_cost) as avg_revenue
            FROM reservations 
            WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
    $result = mysqli_query($conn, $sql);
    $report['reservations'] = mysqli_fetch_assoc($result);
    
    // Reservations by status
    $sql = "SELECT status, COUNT(*) as count 
            FROM reservations 
            WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
            GROUP BY status";
    $result = mysqli_query($conn, $sql);
    $report['by_status'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $report['by_status'][$row['status']] = $row['count'];
    }
    
    // Revenue by month
    $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                   SUM(total_cost) as revenue
            FROM reservations 
            WHERE status IN ('completed', 'active')
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 6";
    $result = mysqli_query($conn, $sql);
    $report['monthly_revenue'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $report['monthly_revenue'][$row['month']] = $row['revenue'];
    }
    
    // Top cars
    $sql = "SELECT c.brand, c.model, COUNT(r.id) as rentals, SUM(r.total_cost) as revenue
            FROM reservations r
            JOIN cars c ON r.car_id = c.id
            WHERE DATE(r.created_at) BETWEEN '$start_date' AND '$end_date'
            GROUP BY c.id
            ORDER BY rentals DESC
            LIMIT 5";
    $result = mysqli_query($conn, $sql);
    $report['top_cars'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $report['top_cars'][] = $row;
    }
    
    return $report;
}

function getReservationsReport($conn, $start_date, $end_date) {
    $sql = "SELECT r.*, 
                   CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                   CONCAT(car.brand, ' ', car.model) as car_name,
                   car.license_plate
            FROM reservations r
            JOIN customers c ON r.customer_id = c.id
            JOIN cars car ON r.car_id = car.id
            WHERE DATE(r.created_at) BETWEEN '$start_date' AND '$end_date'
            ORDER BY r.created_at DESC";
    $result = mysqli_query($conn, $sql);
    
    $report = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $report[] = $row;
    }
    
    return $report;
}

function getRevenueReport($conn, $start_date, $end_date) {
    $sql = "SELECT 
                DATE(r.created_at) as date,
                COUNT(r.id) as reservations,
                SUM(r.total_cost) as revenue,
                AVG(r.total_cost) as avg_per_reservation
            FROM reservations r
            WHERE DATE(r.created_at) BETWEEN '$start_date' AND '$end_date'
                AND r.status IN ('completed', 'active')
            GROUP BY DATE(r.created_at)
            ORDER BY date DESC";
    $result = mysqli_query($conn, $sql);
    
    $report = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $report[] = $row;
    }
    
    return $report;
}

function getCustomersReport($conn, $start_date, $end_date) {
    $sql = "SELECT c.*, 
                   COUNT(r.id) as total_reservations,
                   SUM(r.total_cost) as total_spent,
                   MAX(r.created_at) as last_reservation
            FROM customers c
            LEFT JOIN reservations r ON c.id = r.customer_id
                AND DATE(r.created_at) BETWEEN '$start_date' AND '$end_date'
            GROUP BY c.id
            ORDER BY total_spent DESC";
    $result = mysqli_query($conn, $sql);
    
    $report = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $report[] = $row;
    }
    
    return $report;
}

function getCarsReport($conn) {
    $sql = "SELECT c.*,
                   COUNT(r.id) as times_rented,
                   SUM(r.total_cost) as revenue_generated,
                   AVG(r.total_cost) as avg_revenue_per_rental
            FROM cars c
            LEFT JOIN reservations r ON c.id = r.car_id
            GROUP BY c.id
            ORDER BY revenue_generated DESC";
    $result = mysqli_query($conn, $sql);
    
    $report = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $report[] = $row;
    }
    
    return $report;
}

function exportReportCSV() {
    // This would generate and download a CSV file
    // Implementation depends on specific requirements
    header("Location: export_csv.php?type=" . $_GET['type']);
    exit();
}

require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Reports & Analytics</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="?type=<?php echo $report_type; ?>&export=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
               class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-export"></i> Export
            </a>
        </div>
    </div>
</div>

<!-- Date Range Filter -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Reports</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="form-inline">
            <input type="hidden" name="type" value="<?php echo $report_type; ?>">
            
            <div class="form-group mr-2">
                <label for="start_date" class="mr-2">From:</label>
                <input type="date" class="form-control" id="start_date" name="start_date" 
                       value="<?php echo $start_date; ?>" required>
            </div>
            
            <div class="form-group mr-2">
                <label for="end_date" class="mr-2">To:</label>
                <input type="date" class="form-control" id="end_date" name="end_date" 
                       value="<?php echo $end_date; ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary mr-2">
                <i class="fas fa-filter"></i> Apply Filter
            </button>
            
            <a href="reports.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

<!-- Report Type Navigation -->
<div class="row mb-4">
    <div class="col-12">
        <div class="btn-group" role="group">
            <a href="?type=overview&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
               class="btn btn-<?php echo ($report_type == 'overview') ? 'primary' : 'outline-primary'; ?>">
                <i class="fas fa-chart-line"></i> Overview
            </a>
            <a href="?type=reservations&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
               class="btn btn-<?php echo ($report_type == 'reservations') ? 'primary' : 'outline-primary'; ?>">
                <i class="fas fa-calendar-check"></i> Reservations
            </a>
            <a href="?type=revenue&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
               class="btn btn-<?php echo ($report_type == 'revenue') ? 'primary' : 'outline-primary'; ?>">
                <i class="fas fa-dollar-sign"></i> Revenue
            </a>
            <a href="?type=customers&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
               class="btn btn-<?php echo ($report_type == 'customers') ? 'primary' : 'outline-primary'; ?>">
                <i class="fas fa-users"></i> Customers
            </a>
            <a href="?type=cars&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
               class="btn btn-<?php echo ($report_type == 'cars') ? 'primary' : 'outline-primary'; ?>">
                <i class="fas fa-car"></i> Cars
            </a>
        </div>
    </div>
</div>

<!-- Report Content -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><?php echo $report_title; ?></h5>
        <small class="text-muted"><?php echo date('F d, Y', strtotime($start_date)); ?> to <?php echo date('F d, Y', strtotime($end_date)); ?></small>
    </div>
    <div class="card-body">
        <?php switch($report_type): 
            case 'overview': ?>
                <!-- Overview Report -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Reservations</h5>
                                <h2><?php echo $data['reservations']['total'] ?? 0; ?></h2>
                                <p class="card-text">In selected period</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Revenue</h5>
                                <h2>$<?php echo number_format($data['reservations']['revenue'] ?? 0, 2); ?></h2>
                                <p class="card-text">Average: $<?php echo number_format($data['reservations']['avg_revenue'] ?? 0, 2); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Reservations by Status</h5>
                                <?php if (!empty($data['by_status'])): ?>
                                    <?php foreach($data['by_status'] as $status => $count): ?>
                                        <p class="mb-1"><?php echo ucfirst($status); ?>: <?php echo $count; ?></p>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No data</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top Cars -->
                <div class="row">
                    <div class="col-md-6">
                        <h5>Top Rented Cars</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Car</th>
                                        <th>Times Rented</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($data['top_cars'])): ?>
                                        <?php foreach($data['top_cars'] as $car): ?>
                                        <tr>
                                            <td><?php echo $car['brand'] . ' ' . $car['model']; ?></td>
                                            <td><?php echo $car['rentals']; ?></td>
                                            <td>$<?php echo number_format($car['revenue'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3">No data available</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Monthly Revenue Chart -->
                    <div class="col-md-6">
                        <h5>Monthly Revenue (Last 6 Months)</h5>
                        <canvas id="revenueChart" height="200"></canvas>
                    </div>
                </div>
                
                <script>
                // Revenue Chart
                var ctx = document.getElementById('revenueChart').getContext('2d');
                var revenueChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [<?php echo implode(',', array_map(function($month) { return "'$month'"; }, array_keys($data['monthly_revenue']))); ?>],
                        datasets: [{
                            label: 'Revenue ($)',
                            data: [<?php echo implode(',', $data['monthly_revenue']); ?>],
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                </script>
                
            <?php break; ?>
            
            <?php case 'reservations': ?>
                <!-- Reservations Report -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Pickup Date</th>
                                <th>Return Date</th>
                                <th>Total Days</th>
                                <th>Total Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data)): ?>
                                <?php foreach($data as $reservation): ?>
                                <tr>
                                    <td>#<?php echo $reservation['id']; ?></td>
                                    <td><?php echo $reservation['customer_name']; ?></td>
                                    <td><?php echo $reservation['car_name']; ?><br>
                                        <small class="text-muted"><?php echo $reservation['license_plate']; ?></small>
                                    </td>
                                    <td><?php echo formatDate($reservation['pickup_date']); ?></td>
                                    <td><?php echo formatDate($reservation['return_date']); ?></td>
                                    <td><?php echo $reservation['total_days']; ?></td>
                                    <td>$<?php echo number_format($reservation['total_cost'], 2); ?></td>
                                    <td><?php echo getReservationStatusBadge($reservation['status']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center">No reservations found in selected period</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
            <?php break; ?>
            
            <?php case 'revenue': ?>
                <!-- Revenue Report -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reservations</th>
                                <th>Revenue</th>
                                <th>Average per Reservation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data)): 
                                $total_reservations = 0;
                                $total_revenue = 0;
                            ?>
                                <?php foreach($data as $row): 
                                    $total_reservations += $row['reservations'];
                                    $total_revenue += $row['revenue'];
                                ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                    <td><?php echo $row['reservations']; ?></td>
                                    <td>$<?php echo number_format($row['revenue'], 2); ?></td>
                                    <td>$<?php echo number_format($row['avg_per_reservation'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-primary font-weight-bold">
                                    <td>TOTAL</td>
                                    <td><?php echo $total_reservations; ?></td>
                                    <td>$<?php echo number_format($total_revenue, 2); ?></td>
                                    <td>$<?php echo number_format($total_revenue / max($total_reservations, 1), 2); ?></td>
                                </tr>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">No revenue data found in selected period</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
            <?php break; ?>
            
            <?php case 'customers': ?>
                <!-- Customers Report -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Reservations</th>
                                <th>Total Spent</th>
                                <th>Last Reservation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data)): ?>
                                <?php foreach($data as $customer): ?>
                                <tr>
                                    <td><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></td>
                                    <td><?php echo $customer['email']; ?></td>
                                    <td><?php echo $customer['phone'] ?: 'N/A'; ?></td>
                                    <td><?php echo $customer['total_reservations']; ?></td>
                                    <td>$<?php echo number_format($customer['total_spent'] ?? 0, 2); ?></td>
                                    <td>
                                        <?php if ($customer['last_reservation']): ?>
                                            <?php echo date('M d, Y', strtotime($customer['last_reservation'])); ?>
                                        <?php else: ?>
                                            Never
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">No customer data found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
            <?php break; ?>
            
            <?php case 'cars': ?>
                <!-- Cars Report -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Car</th>
                                <th>License Plate</th>
                                <th>Daily Rate</th>
                                <th>Status</th>
                                <th>Times Rented</th>
                                <th>Revenue Generated</th>
                                <th>Avg per Rental</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data)): ?>
                                <?php foreach($data as $car): ?>
                                <tr>
                                    <td><?php echo $car['brand'] . ' ' . $car['model']; ?></td>
                                    <td><?php echo $car['license_plate']; ?></td>
                                    <td>$<?php echo number_format($car['daily_rate'], 2); ?></td>
                                    <td><?php echo getCarStatusBadge($car['status']); ?></td>
                                    <td><?php echo $car['times_rented']; ?></td>
                                    <td>$<?php echo number_format($car['revenue_generated'] ?? 0, 2); ?></td>
                                    <td>$<?php echo number_format($car['avg_revenue_per_rental'] ?? 0, 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">No car data found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
            <?php break; ?>
            
        <?php endswitch; ?>
    </div>
</div>

<?php 
mysqli_close($conn);
require_once '../includes/footer.php'; 
?>