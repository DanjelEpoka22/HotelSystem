<?php
session_start();

// Include database connection
require_once 'database.php';

// Global configurations
define('SITE_NAME', 'Villa Adrian');
define('SITE_URL', 'http://localhost/villa-adrian');
define('CANCELLATION_DAYS', 15);
define('CHECK_IN_TIME', '14:00');
define('CHECK_OUT_TIME', '11:00');

// Booking.com API configurations
define('BOOKING_API_KEY', 'your_booking_api_key');
define('BOOKING_API_URL', 'https://api.bookings.com/v1');

// Create database instance
$database = new Database();
$db = $database->getConnection();

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}
?>