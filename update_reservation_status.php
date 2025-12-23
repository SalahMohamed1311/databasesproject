<?php
// admin/ajax/update_reservation_status.php
require_once '../../includes/functions.php';
requireAdminLogin();

header('Content-Type: application/json');

if (!isset($_POST['id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

$reservation_id = intval($_POST['id']);
$new_status = sanitize($_POST['status']);
$conn = getConnection();

// Get current reservation details
$sql = "SELECT * FROM reservations WHERE id = $reservation_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found']);
    mysqli_close($conn);
    exit();
}

$reservation = mysqli_fetch_assoc($result);

// Validate status transition
$allowed_transitions = [
    'pending' => ['confirmed', 'cancelled'],
    'confirmed' => ['active', 'cancelled'],
    'active' => ['completed'],
    'completed' => [],
    'cancelled' => []
];

if (!in_array($new_status, $allowed_transitions[$reservation['status']])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status transition']);
    mysqli_close($conn);
    exit();
}

// Update reservation status
$update_sql = "UPDATE reservations SET status = '$new_status' WHERE id = $reservation_id";

if (mysqli_query($conn, $update_sql)) {
    // Update car status if needed
    if ($new_status == 'cancelled' && in_array($reservation['status'], ['confirmed', 'active'])) {
        $car_update = "UPDATE cars SET status = 'available' WHERE id = {$reservation['car_id']}";
        mysqli_query($conn, $car_update);
    } elseif ($new_status == 'confirmed' || $new_status == 'active') {
        $car_update = "UPDATE cars SET status = 'rented' WHERE id = {$reservation['car_id']}";
        mysqli_query($conn, $car_update);
    } elseif ($new_status == 'completed') {
        $car_update = "UPDATE cars SET status = 'available' WHERE id = {$reservation['car_id']}";
        mysqli_query($conn, $car_update);
    }
    
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>