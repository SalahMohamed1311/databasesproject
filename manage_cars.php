<?php
require_once '../includes/functions.php';
requireAdminLogin();

$page_title = "Manage Cars";
$message = '';

$conn = getConnection();

// Handle delete car
if (isset($_GET['delete'])) {
    $car_id = intval($_GET['delete']);
    $delete_sql = "DELETE FROM cars WHERE id = $car_id";
    
    if (mysqli_query($conn, $delete_sql)) {
        $message = '<div class="alert alert-success">Car deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting car: ' . mysqli_error($conn) . '</div>';
    }
}

// Handle add/edit car
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $brand = sanitize($_POST['brand']);
    $model = sanitize($_POST['model']);
    $year = intval($_POST['year']);
    $color = sanitize($_POST['color']);
    $license_plate = sanitize($_POST['license_plate']);
    $daily_rate = floatval($_POST['daily_rate']);
    $status = sanitize($_POST['status']);
    $fuel_type = sanitize($_POST['fuel_type']);
    $transmission = sanitize($_POST['transmission']);
    $seats = intval($_POST['seats']);
    $description = sanitize($_POST['description']);
    
    if ($id > 0) {
        // Update existing car
        $sql = "UPDATE cars SET 
                brand = '$brand',
                model = '$model',
                year = $year,
                color = '$color',
                license_plate = '$license_plate',
                daily_rate = $daily_rate,
                status = '$status',
                fuel_type = '$fuel_type',
                transmission = '$transmission',
                seats = $seats,
                description = '$description'
                WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            $message = '<div class="alert alert-success">Car updated successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error updating car: ' . mysqli_error($conn) . '</div>';
        }
    } else {
        // Insert new car
        $sql = "INSERT INTO cars (brand, model, year, color, license_plate, daily_rate, status, fuel_type, transmission, seats, description) 
                VALUES ('$brand', '$model', $year, '$color', '$license_plate', $daily_rate, '$status', '$fuel_type', '$transmission', $seats, '$description')";
        
        if (mysqli_query($conn, $sql)) {
            $message = '<div class="alert alert-success">New car added successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error adding car: ' . mysqli_error($conn) . '</div>';
        }
    }
}

// Get all cars
$sql = "SELECT * FROM cars ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Cars</h1>
    <button class="btn btn-primary" data-toggle="modal" data-target="#addCarModal">
        <i class="fas fa-plus"></i> Add New Car
    </button>
</div>

<?php echo $message; ?>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Year</th>
                <th>License Plate</th>
                <th>Daily Rate</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['brand']; ?></td>
                <td><?php echo $row['model']; ?></td>
                <td><?php echo $row['year']; ?></td>
                <td><?php echo $row['license_plate']; ?></td>
                <td>$<?php echo $row['daily_rate']; ?></td>
                <td><?php echo getCarStatusBadge($row['status']); ?></td>
                <td>
                    <button class="btn btn-sm btn-info edit-car" 
                            data-id="<?php echo $row['id']; ?>"
                            data-brand="<?php echo $row['brand']; ?>"
                            data-model="<?php echo $row['model']; ?>"
                            data-year="<?php echo $row['year']; ?>"
                            data-color="<?php echo $row['color']; ?>"
                            data-license_plate="<?php echo $row['license_plate']; ?>"
                            data-daily_rate="<?php echo $row['daily_rate']; ?>"
                            data-status="<?php echo $row['status']; ?>"
                            data-fuel_type="<?php echo $row['fuel_type']; ?>"
                            data-transmission="<?php echo $row['transmission']; ?>"
                            data-seats="<?php echo $row['seats']; ?>"
                            data-description="<?php echo $row['description']; ?>">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <a href="?delete=<?php echo $row['id']; ?>" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this car?')">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Car Modal -->
<div class="modal fade" id="addCarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Car</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="car_id" name="id" value="0">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="brand">Brand *</label>
                                <input type="text" class="form-control" id="brand" name="brand" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="model">Model *</label>
                                <input type="text" class="form-control" id="model" name="model" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="year">Year *</label>
                                <input type="number" class="form-control" id="year" name="year" min="2000" max="2030" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="color">Color</label>
                                <input type="text" class="form-control" id="color" name="color">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="license_plate">License Plate *</label>
                                <input type="text" class="form-control" id="license_plate" name="license_plate" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="daily_rate">Daily Rate ($) *</label>
                                <input type="number" step="0.01" class="form-control" id="daily_rate" name="daily_rate" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="available">Available</option>
                                    <option value="rented">Rented</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="seats">Seats</label>
                                <input type="number" class="form-control" id="seats" name="seats" min="1" max="20" value="5">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fuel_type">Fuel Type</label>
                                <select class="form-control" id="fuel_type" name="fuel_type">
                                    <option value="petrol">Petrol</option>
                                    <option value="diesel">Diesel</option>
                                    <option value="electric">Electric</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transmission">Transmission</label>
                                <select class="form-control" id="transmission" name="transmission">
                                    <option value="automatic">Automatic</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Car</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle edit button click
    $('.edit-car').click(function() {
        var carId = $(this).data('id');
        var brand = $(this).data('brand');
        var model = $(this).data('model');
        var year = $(this).data('year');
        var color = $(this).data('color');
        var license_plate = $(this).data('license_plate');
        var daily_rate = $(this).data('daily_rate');
        var status = $(this).data('status');
        var fuel_type = $(this).data('fuel_type');
        var transmission = $(this).data('transmission');
        var seats = $(this).data('seats');
        var description = $(this).data('description');
        
        // Fill modal with data
        $('#car_id').val(carId);
        $('#brand').val(brand);
        $('#model').val(model);
        $('#year').val(year);
        $('#color').val(color);
        $('#license_plate').val(license_plate);
        $('#daily_rate').val(daily_rate);
        $('#status').val(status);
        $('#fuel_type').val(fuel_type);
        $('#transmission').val(transmission);
        $('#seats').val(seats);
        $('#description').val(description);
        
        // Change modal title
        $('.modal-title').text('Edit Car');
        
        // Show modal
        $('#addCarModal').modal('show');
    });
    
    // Reset modal when closed
    $('#addCarModal').on('hidden.bs.modal', function() {
        $('.modal-title').text('Add New Car');
        $('#car_id').val('0');
        document.getElementById("addCarModal").reset();
    });
});
</script>

<?php 
mysqli_close($conn);
require_once '../includes/footer.php'; 
?>