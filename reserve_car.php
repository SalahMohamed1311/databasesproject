<?php
require_once '../includes/functions.php';
requireCustomerLogin();

$page_title = "Reserve Car";
$error = '';
$success = '';

// Get car ID
$car_id = isset($_GET['car_id']) ? intval($_GET['car_id']) : 0;

if ($car_id == 0) {
    header("Location: browse_cars.php");
    exit();
}

$conn = getConnection();

// Get car details
$car_sql = "SELECT * FROM cars WHERE id = $car_id";
$car_result = mysqli_query($conn, $car_sql);
$car = mysqli_fetch_assoc($car_result);

if (!$car) {
    header("Location: browse_cars.php");
    exit();
}

// Handle reservation submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pickup_date = sanitize($_POST['pickup_date']);
    $return_date = sanitize($_POST['return_date']);
    $total_days = intval($_POST['total_days']);
    $total_cost = floatval($_POST['total_cost']);
    $customer_id = $_SESSION['customer_id'];
    
    // Validate dates
    if (empty($pickup_date) || empty($return_date)) {
        $error = 'Please select both pickup and return dates.';
    } elseif ($total_days <= 0) {
        $error = 'Return date must be after pickup date.';
    } elseif ($car['status'] != 'available') {
        $error = 'Sorry, this car is no longer available.';
    } else {
        // Create reservation
        $reservation_sql = "INSERT INTO reservations (customer_id, car_id, pickup_date, return_date, total_days, total_cost, status) 
                           VALUES ($customer_id, $car_id, '$pickup_date', '$return_date', $total_days, $total_cost, 'pending')";
        
        if (mysqli_query($conn, $reservation_sql)) {
            $reservation_id = mysqli_insert_id($conn);
            
            // Update car status to rented
            $update_car_sql = "UPDATE cars SET status = 'rented' WHERE id = $car_id";
            mysqli_query($conn, $update_car_sql);
            
            $success = 'Reservation submitted successfully! Your reservation ID is #' . $reservation_id;
        } else {
            $error = 'Error creating reservation: ' . mysqli_error($conn);
        }
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Reserve Car: <?php echo $car['brand'] . ' ' . $car['model']; ?></h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <!-- Car Details -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5>Car Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Brand:</strong> <?php echo $car['brand']; ?></p>
                                <p><strong>Model:</strong> <?php echo $car['model']; ?></p>
                                <p><strong>Year:</strong> <?php echo $car['year']; ?></p>
                                <p><strong>Color:</strong> <?php echo $car['color']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Fuel Type:</strong> <?php echo ucfirst($car['fuel_type']); ?></p>
                                <p><strong>Transmission:</strong> <?php echo ucfirst($car['transmission']); ?></p>
                                <p><strong>Seats:</strong> <?php echo $car['seats']; ?></p>
                                <p><strong>Daily Rate:</strong> $<?php echo $car['daily_rate']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reservation Form -->
                <form method="POST" action="" id="reservationForm">
                    <input type="hidden" id="daily_rate" value="<?php echo $car['daily_rate']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pickup_date">Pickup Date *</label>
                                <input type="date" class="form-control" id="pickup_date" name="pickup_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="return_date">Return Date *</label>
                                <input type="date" class="form-control" id="return_date" name="return_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_days">Total Days</label>
                                <input type="number" class="form-control" id="total_days" name="total_days" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_cost">Total Cost ($)</label>
                                <input type="number" step="0.01" class="form-control" id="total_cost" name="total_cost" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="button" class="btn btn-info" id="calculate_cost">
                            <i class="fas fa-calculator"></i> Calculate Cost
                        </button>
                        <span id="total_cost_display" class="ml-3 font-weight-bold"></span>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" data-toggle="modal" data-target="#termsModal">terms and conditions</a>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Submit Reservation</button>
                    <a href="browse_cars.php" class="btn btn-secondary btn-block mt-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>1. The driver must be at least 21 years old and possess a valid driver's license.</p>
                <p>2. A security deposit may be required at the time of pickup.</p>
                <p>3. The car must be returned in the same condition as it was rented.</p>
                <p>4. Late returns will incur additional charges.</p>
                <p>5. Fuel is the responsibility of the renter.</p>
                <p>6. Insurance coverage is included in the rental price.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Set minimum date to today
    var today = new Date().toISOString().split('T')[0];
    $('#pickup_date').attr('min', today);
    
    // Calculate cost function
    $('#calculate_cost').click(function() {
        var dailyRate = parseFloat($('#daily_rate').val());
        var pickupDate = new Date($('#pickup_date').val());
        var returnDate = new Date($('#return_date').val());
        
        if (isNaN(dailyRate) || !pickupDate.getTime() || !returnDate.getTime()) {
            alert('Please select both pickup and return dates');
            return;
        }
        
        // Calculate days difference
        var timeDiff = returnDate.getTime() - pickupDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
        
        if (daysDiff <= 0) {
            alert('Return date must be after pickup date');
            return;
        }
        
        var totalCost = dailyRate * daysDiff;
        $('#total_days').val(daysDiff);
        $('#total_cost').val(totalCost.toFixed(2));
        $('#total_cost_display').text('Total: $' + totalCost.toFixed(2));
    });
    
    // Auto-calculate when dates change
    $('#pickup_date, #return_date').change(function() {
        if ($('#pickup_date').val() && $('#return_date').val()) {
            $('#calculate_cost').click();
        }
    });
});
</script>

<?php 
mysqli_close($conn);
require_once '../includes/footer.php'; 
?>