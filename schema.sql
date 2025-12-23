-- Create database
CREATE DATABASE IF NOT EXISTS car_rental_db;
USE car_rental_db;

-- Admin users table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Customers table
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    driver_license VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cars table
CREATE TABLE cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    color VARCHAR(30),
    license_plate VARCHAR(20) UNIQUE NOT NULL,
    daily_rate DECIMAL(10,2) NOT NULL,
    status ENUM('available', 'rented', 'maintenance') DEFAULT 'available',
    fuel_type ENUM('petrol', 'diesel', 'electric', 'hybrid') DEFAULT 'petrol',
    transmission ENUM('automatic', 'manual') DEFAULT 'automatic',
    seats INT DEFAULT 5,
    image_url VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reservations table
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    car_id INT NOT NULL,
    pickup_date DATE NOT NULL,
    return_date DATE NOT NULL,
    total_days INT NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'credit_card', 'debit_card', 'online') DEFAULT 'credit_card',
    payment_status ENIMAL('pending', 'completed', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE
);

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admins (username, password, full_name, email) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'System Administrator', 'admin@carrental.com');

-- Insert some sample cars
INSERT INTO cars (brand, model, year, color, license_plate, daily_rate, status, fuel_type, transmission, seats, description) VALUES
('Toyota', 'Camry', 2022, 'White', 'ABC-1234', 50.00, 'available', 'petrol', 'automatic', 5, 'Comfortable sedan with great fuel economy'),
('Honda', 'Civic', 2023, 'Black', 'XYZ-5678', 45.00, 'available', 'petrol', 'automatic', 5, 'Reliable compact car with modern features'),
('Ford', 'Explorer', 2021, 'Blue', 'DEF-9012', 80.00, 'available', 'diesel', 'automatic', 7, 'Spacious SUV for family trips'),
('Tesla', 'Model 3', 2023, 'Red', 'TES-1234', 120.00, 'available', 'electric', 'automatic', 5, 'Electric car with autopilot features'),
('BMW', 'X5', 2022, 'Silver', 'BMW-7890', 150.00, 'rented', 'petrol', 'automatic', 5, 'Luxury SUV with premium features'),
('Hyundai', 'Elantra', 2022, 'Gray', 'HYN-3456', 40.00, 'maintenance', 'petrol', 'automatic', 5, 'Economical car for daily commute');