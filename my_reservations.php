<?php
// customer/my_reservations.php
require_once '../includes/functions.php';
requireCustomerLogin();

$page_title = "My Reservations";
$message = '';

$conn = getConnection();
$customer_id = $_SESSION['customer_id'];

// Handle reservation cancellation
if (isset($_GET['cancel'])) {
    $reservation_id = intval($_GET['cancel']);
    
    // Check if reservation belongs to this customer
    $check_sql = "SELECT * FROM reservations WHERE id = $reservation_id AND customer_id = $customer_id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) == 1) {
        $reservation = mysqli_fetch_assoc($check_result);
        
        // Check if reservation can be cancelled (only pending or confirmed)
        if (in_array($reservation['status'], ['pending', 'confirmed'])) {
            // Update reservation status
            $update_sql = "UPDATE reservations SET status = 'cancelled' WHERE id = $reservation_id";
            
            if (mysqli_query($conn, $update_sql)) {
                // Update car status if it was confirmed
                if ($reservation['status'] == 'confirmed') {
                    $car_update = "UPDATE cars SET status = 'available' WHERE id = {$reservation['car_id']}";
                    mysqli_query($conn, $car_update);
                }
                
                $message = '<div class="alert alert-success">Reservation cancelled successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error cancelling reservation: ' . mysqli_error($conn) . '</div>';
            }
        } else {
            $message = '<div class="alert alert-warning">This reservation cannot be cancelled.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Reservation not found or you do not have permission to cancel it.</div>';
    }
}

// Get all reservations for this customer
$sql = "SELECT r.*, 
               c.brand, c.model, c.license_plate, c.color, c.image_url,
               CONCAT(c.brand, ' ', c.model) as car_name
        FROM reservations r
        JOIN cars c ON r.car_id = c.id
        WHERE r.customer_id = $customer_id
        ORDER BY r.created_at DESC";
$result = mysqli_query($conn, $sql);

// Get customer information
$customer_sql = "SELECT first_name, last_name, email, phone FROM customers WHERE id = $customer_id";
$customer_result = mysqli_query($conn, $customer_sql);
$customer_info = mysqli_fetch_assoc($customer_result);

require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">My Reservations</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="browse_cars.php" class="btn btn-primary">
            <i class="fas fa-car"></i> Rent Another Car
        </a>
    </div>
</div>

<?php echo $message; ?>

<!-- Customer Info -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5>Customer Information</h5>
                        <p class="mb-1"><strong>Name:</strong> <?php echo $customer_info['first_name'] . ' ' . $customer_info['last_name']; ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo $customer_info['email']; ?></p>
                        <p class="mb-1"><strong>Phone:</strong> <?php echo $customer_info['phone'] ?: 'Not provided'; ?></p>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="#" class="btn btn-outline-primary" data-toggle="modal" data-target="#editProfileModal">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reservations List -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Reservations</h5>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Reservation ID</th>
                                    <th>Car Details</th>
                                    <th>Rental Period</th>
                                    <th>Total Days</th>
                                    <th>Total Cost</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reservation = mysqli_fetch_assoc($result)): 
                                    $is_cancellable = in_array($reservation['status'], ['pending', 'confirmed']);
                                    $is_upcoming = strtotime($reservation['pickup_date']) > time();
                                ?>
                                <tr>
                                    <td>#<?php echo $reservation['id']; ?></td>
                                    <td>
                                        <strong><?php echo $reservation['car_name']; ?></strong><br>
                                        <small class="text-muted">
                                            License: <?php echo $reservation['license_plate']; ?><br>
                                            Color: <?php echo $reservation['color']; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong>Pickup:</strong> <?php echo formatDate($reservation['pickup_date']); ?><br>
                                        <strong>Return:</strong> <?php echo formatDate($reservation['return_date']); ?>
                                    </td>
                                    <td><?php echo $reservation['total_days']; ?> days</td>
                                    <td>$<?php echo number_format($reservation['total_cost'], 2); ?></td>
                                    <td><?php echo getReservationStatusBadge($reservation['status']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info view-reservation-details" 
                                                data-id="<?php echo $reservation['id']; ?>"
                                                data-toggle="modal" 
                                                data-target="#reservationDetailsModal">
                                            <i class="fas fa-eye"></i> Details
                                        </button>
                                        
                                        <?php if ($is_cancellable && $is_upcoming): ?>
                                            <a href="?cancel=<?php echo $reservation['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to cancel this reservation?\n\nA cancellation fee may apply.')">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($reservation['status'] == 'completed'): ?>
                                            <button class="btn btn-sm btn-warning btn-rate" 
                                                    data-id="<?php echo $reservation['id']; ?>"
                                                    data-car="<?php echo $reservation['car_name']; ?>">
                                                <i class="fas fa-star"></i> Rate
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h4>No Reservations Yet</h4>
                        <p class="text-muted">You haven't made any reservations yet.</p>
                        <a href="browse_cars.php" class="btn btn-primary">
                            <i class="fas fa-car"></i> Browse Available Cars
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Reservation Summary -->
<?php if (mysqli_num_rows($result) > 0): 
    // Reset pointer
    mysqli_data_seek($result, 0);
    
    $total_reservations = 0;
    $total_spent = 0;
    $active_reservations = 0;
    $completed_reservations = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        $total_reservations++;
        $total_spent += $row['total_cost'];
        
        if ($row['status'] == 'active') {
            $active_reservations++;
        } elseif ($row['status'] == 'completed') {
            $completed_reservations++;
        }
    }
?>
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3><?php echo $total_reservations; ?></h3>
                <p class="mb-0">Total Reservations</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3>$<?php echo number_format($total_spent, 2); ?></h3>
                <p class="mb-0">Total Spent</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3><?php echo $active_reservations; ?></h3>
                <p class="mb-0">Active Reservations</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h3><?php echo $completed_reservations; ?></h3>
                <p class="mb-0">Completed Trips</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Reservation Details Modal -->
<div class="modal fade" id="reservationDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reservation Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="reservationDetailsContent">
                Loading reservation details...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printReceiptBtn">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="update_profile.php">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" 
                               value="<?php echo $customer_info['first_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" 
                               value="<?php echo $customer_info['last_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo $customer_info['phone']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="current_password">Current Password (for verification)</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                        <small class="text-muted">Leave blank if not changing password</small>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rate Your Experience</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="ratingForm">
                    <input type="hidden" id="rating_reservation_id" name="reservation_id">
                    <div class="form-group">
                        <label>Car Condition</label>
                        <div class="star-rating">
                            <span class="fa fa-star" data-rating="1"></span>
                            <span class="fa fa-star" data-rating="2"></span>
                            <span class="fa fa-star" data-rating="3"></span>
                            <span class="fa fa-star" data-rating="4"></span>
                            <span class="fa fa-star" data-rating="5"></span>
                            <input type="hidden" name="car_condition" id="car_condition" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Service Quality</label>
                        <div class="star-rating">
                            <span class="fa fa-star" data-rating="1"></span>
                            <span class="fa fa-star" data-rating="2"></span>
                            <span class="fa fa-star" data-rating="3"></span>
                            <span class="fa fa-star" data-rating="4"></span>
                            <span class="fa fa-star" data-rating="5"></span>
                            <input type="hidden" name="service_quality" id="service_quality" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="comments">Comments</label>
                        <textarea class="form-control" id="comments" name="comments" rows="3" 
                                  placeholder="Tell us about your experience..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitRatingBtn">Submit Rating</button>
            </div>
        </div>
    </div>
</div>

<style>
.star-rating {
    direction: rtl;
    display: inline-block;
    padding: 20px;
}

.star-rating span {
    font-size: 24px;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.star-rating span:hover,
.star-rating span:hover ~ span,
.star-rating span.selected {
    color: #ffc107;
}

.receipt {
    font-family: 'Courier New', monospace;
    background: white;
    padding: 20px;
    border: 1px solid #ddd;
}
</style>

<script>
$(document).ready(function() {
    // View reservation details
    $('.view-reservation-details').click(function() {
        var reservationId = $(this).data('id');
        
        $.ajax({
            url: '../ajax/get_customer_reservation.php',
            type: 'GET',
            data: { id: reservationId },
            success: function(response) {
                $('#reservationDetailsContent').html(response);
            },
            error: function() {
                $('#reservationDetailsContent').html('<div class="alert alert-danger">Error loading reservation details</div>');
            }
        });
    });

    // Print receipt
    $('#printReceiptBtn').click(function() {
        var printContent = $('#reservationDetailsContent').html();
        var originalContent = $('body').html();
        
        $('body').html('<div class="receipt">' + printContent + '</div>');
        window.print();
        $('body').html(originalContent);
        location.reload();
    });

    // Rating system
    $('.btn-rate').click(function() {
        var reservationId = $(this).data('id');
        var carName = $(this).data('car');
        
        $('#rating_reservation_id').val(reservationId);
        $('#ratingModal .modal-title').text('Rate Your Experience: ' + carName);
        $('#ratingModal').modal('show');
    });

    // Star rating
    $('.star-rating span').click(function() {
        var rating = $(this).data('rating');
        var inputId = $(this).closest('.form-group').find('input[type="hidden"]').attr('id');
        
        $('#' + inputId).val(rating);
        
        // Highlight selected stars
        $(this).siblings('span').removeClass('selected');
        $(this).addClass('selected');
        $(this).prevAll('span').addClass('selected');
    });

    // Submit rating
    $('#submitRatingBtn').click(function() {
        var formData = $('#ratingForm').serialize();
        
        $.ajax({
            url: '../ajax/submit_rating.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    alert('Thank you for your feedback!');
                    $('#ratingModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function() {
                alert('Error submitting rating');
            }
        });
    });

    // Form validation for edit profile
    $('form').submit(function() {
        var newPassword = $('#new_password').val();
        var confirmPassword = $('#confirm_password').val();
        
        if (newPassword !== confirmPassword) {
            alert('New passwords do not match!');
            return false;
        }
        
        if (newPassword && newPassword.length < 6) {
            alert('Password must be at least 6 characters long');
            return false;
        }
        
        return true;
    });
});
</script>

<?php 
mysqli_close($conn);
require_once '../includes/footer.php'; 
?>