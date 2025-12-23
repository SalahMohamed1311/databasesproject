<?php
// config/config.php

// Define base URL and paths
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_folder = 'car-rental-system';

define('BASE_URL', $protocol . "://" . $host . "/" . $project_folder . "/");
define('BASE_PATH', dirname(dirname(__FILE__)));

// Site settings
define('SITE_NAME', 'Car Rental System');
define('SITE_EMAIL', 'info@carrental.com');
?>