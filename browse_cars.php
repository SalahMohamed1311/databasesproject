<?php
require_once '../includes/functions.php';
requireCustomerLogin();

$page_title = "Browse Available Cars";

$conn = getConnection();

// Filter variables
$brand_filter = isset($_GET['brand']) ? sanitize($_GET['brand']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 500;
$fuel_type = isset($_GET['fuel_type']) ? sanitize($_GET['fuel_type']) : '';

// Build query
$sql = "SELECT * FROM cars WHERE status = 'available'";
$params = [];

if (!empty($brand_filter)) {
    $sql .= " AND brand LIKE '%$brand_filter%'";
}
if ($min_price > 0) {
    $sql .= " AND daily_rate >= $min_price";
}
if ($max_price > 0) {
    $sql .= " AND daily_rate <= $max_price";
}
if (!empty($fuel_type)) {
    $sql .= " AND fuel_type = '$fuel_type'";
}

$sql .= " ORDER BY daily_rate ASC";
$result = mysqli_query($conn, $sql);

// Get distinct brands for filter
$brands_query = "SELECT DISTINCT brand FROM cars WHERE status = 'available' ORDER BY brand";
$brands_result = mysqli_query($conn, $brands_query);

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h2>Available Cars for Rent</h2>
        <p>Choose from our wide selection of vehicles</p>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Filter Cars</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="brand">Brand</label>
                                <select class="form-control" id="brand" name="brand">
                                    <option value="">All Brands</option>
                                    <?php while ($brand = mysqli_fetch_assoc($brands_result)): ?>
                                        <option value="<?php echo $brand['brand']; ?>" 
                                            <?php echo ($brand_filter == $brand['brand']) ? 'selected' : ''; ?>>
                                            <?php echo $brand['brand']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fuel_type">Fuel Type</label>
                                <select class="form-control" id="fuel_type" name="fuel_type">
                                    <option value="">All Types</option>
                                    <option value="petrol" <?php echo ($fuel_type == 'petrol') ? 'selected' : ''; ?>>Petrol</option>
                                    <option value="diesel" <?php echo ($fuel_type == 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                                    <option value="electric" <?php echo ($fuel_type == 'electric') ? 'selected' : ''; ?>>Electric</option>
                                    <option value="hybrid" <?php echo ($fuel_type == 'hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="min_price">Min Price ($)</label>
                                <input type="number" class="form-control" id="min_price" name="min_price" 
                                       min="0" max="1000" value="<?php echo $min_price; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="max_price">Max Price ($)</label>
                                <input type="number" class="form-control" id="max_price" name="max_price" 
                                       min="0" max="1000" value="<?php echo $max_price; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="browse_cars.php" class="btn btn-secondary">Clear Filters</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Cars Grid -->
<div class="row">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($car = mysqli_fetch_assoc($result)): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $car['brand'] . ' ' . $car['model']; ?> (<?php echo $car['year']; ?>)</h5>
                    <p class="card-text">
                        <strong>Color:</strong> <?php echo $car['color']; ?><br>
                        <strong>License Plate:</strong> <?php echo $car['license_plate']; ?><br>
                        <strong>Fuel Type:</strong> <?php echo ucfirst($car['fuel_type']); ?><br>
                        <strong>Transmission:</strong> <?php echo ucfirst($car['transmission']); ?><br>
                        <strong>Seats:</strong> <?php echo $car['seats']; ?><br>
                        <strong>Status:</strong> <?php echo getCarStatusBadge($car['status']); ?>
                    </p>
                    <p class="lead">
                        <strong>Daily Rate: $<?php echo $car['daily_rate']; ?></strong>
                    </p>
                    <?php if (!empty($car['description'])): ?>
                        <p class="card-text"><small><?php echo $car['description']; ?></small></p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="reserve_car.php?car_id=<?php echo $car['id']; ?>" class="btn btn-primary btn-block">
                        <i class="fas fa-calendar-check"></i> Reserve Now
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-md-12">
            <div class="alert alert-info">
                No cars available matching your criteria. Please try different filters.
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
mysqli_close($conn);
require_once '../includes/footer.php'; 
?>