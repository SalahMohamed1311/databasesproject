<?php
// ajax/submit_rating.php
require_once '../includes/functions.php';
requireCustomerLogin();

header('Content-Type: application/json');

if (!isset($_POST['reservation_id']) || !isset($_POST['car_condition']) || !isset($_POST['service_quality'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$reservation_id = intval($_POST['reservation_id']);
$car_condition = intval($_POST['car_condition']);
$service_quality = intval($_POST['service_quality']);
$comments = isset($_POST['comments']) ? sanitize($_POST['comments']) : '';
$customer_id = $_SESSION['customer_id'];

// Validate rating values
if ($car_condition < 1 || $car_condition > 5 || $service_quality < 1 || $service_quality > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating values']);
    exit();
}

$conn = getConnection();

// Verify reservation belongs to customer and is completed
$check_sql = "SELECT * FROM reservations 
              WHERE id = $reservation_id 
              AND customer_id = $customer_id 
              AND status = 'completed'";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) != 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid reservation or rating not allowed']);
    mysqli_close($conn);
    exit();
}

// Check if rating already exists
$check_rating_sql = "SELECT id FROM ratings WHERE reservation_id = $reservation_id";
$check_rating_result = mysqli_query($conn, $check_rating_sql);

if (mysqli_num_rows($check_rating_result) > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already rated this reservation']);
    mysqli_close($conn);
    exit();
}

// First, we need to create the ratings table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    car_condition INT NOT NULL CHECK (car_condition BETWEEN 1 AND 5),
    service_quality INT NOT NULL CHECK (service_quality BETWEEN 1 AND 5),
    overall_rating DECIMAL(3,2) GENERATED ALWAYS AS ((car_condition + service_quality) / 2) STORED,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE
)";

mysqli_query($conn, $create_table_sql);

// Insert rating
$insert_sql = "INSERT INTO ratings (reservation_id, car_condition, service_quality, comments) 
               VALUES ($reservation_id, $car_condition, $service_quality, '$comments')";

if (mysqli_query($conn, $insert_sql)) {
    echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>