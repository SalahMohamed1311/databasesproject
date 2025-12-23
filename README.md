# Car Rental System

A complete web-based Car Rental System built with PHP, MySQL, and Bootstrap.

## Features

### Customer Features:
- User registration and login
- Browse available cars with filters
- View car details
- Make reservations
- View reservation history
- Manage account

### Admin Features:
- Admin login
- Dashboard with statistics
- Manage cars (Add, Edit, Delete)
- Manage customers
- View all reservations
- Generate reports

## Installation Instructions

### 1. Requirements:
- XAMPP (Apache, MySQL, PHP)
- Web browser

### 2. Setup Steps:

1. **Install XAMPP:**
   - Download and install XAMPP from https://www.apachefriends.org/
   - Start Apache and MySQL from XAMPP Control Panel

2. **Extract Project:**
   - Extract the project folder to: `C:\xampp\htdocs\car-rental-system\`

3. **Create Database:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the database schema from: `database/schema.sql`
   - This will create the database and tables with sample data

4. **Configure Database:**
   - Edit `config/database.php` if your MySQL credentials are different
   - Default credentials: username: `root`, password: `` (empty)

5. **Access the Application:**
   - Open browser and go to: `http://localhost/car-rental-system/`

## Default Login Credentials

### Admin:
- Username: `admin`
- Password: `admin123`

### Customer:
- Register as a new customer from the registration page

## Project Structure
