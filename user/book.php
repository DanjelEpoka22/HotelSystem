<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/user.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <img src="../assets/images/logo.png" alt="Villa Adrian Logo" class="logo">
                <span><?php echo SITE_NAME; ?></span>
            </div>
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link">Home</a></li>
                <li><a href="../rooms.php" class="nav-link">Rooms</a></li>
                <li><a href="index.php" class="nav-link">Dashboard</a></li>
                <li><a href="book.php" class="nav-link active">Book Room</a></li>
                <li><a href="my_reservations.php" class="nav-link">My Reservations</a></li>
                <li><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="user-container">
        <div class="user-content">
            <div class="user-header">
                <h1>Book Your Stay</h1>
                <p>Find the perfect room for your vacation in Ksamil</p>
            </div>

            <!-- Search Form -->
            <div class="dashboard-section">
                <form id="availabilityForm" class="booking-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Check-in Date</label>
                            <input type="date" id="check_in" name="check_in" class="form-control" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Check-out Date</label>
                            <input type="date" id="check_out" name="check_out" class="form-control" required 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Number of Guests</label>
                            <select id="guests" name="guests" class="form-control" required>
                                <option value="1">1 Guest</option>
                                <option value="2" selected>2 Guests</option>
                                <option value="3">3 Guests</option>
                                <option value="4">4 Guests</option>
                                <option value="5">5 Guests</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Room Type</label>
                            <select id="room_type" name="room_type" class="form-control">
                                <option value="">Any Room Type</option>
                                <option value="one_bedroom_apartment">One-Bedroom Apartment</option>
                                <option value="deluxe_double">Deluxe Double Room</option>
                                <option value="deluxe_triple">Deluxe Triple Room</option>
                                <option value="deluxe_quadruple">Deluxe Quadruple Room</option>
                                <option value="suite">Suite</option>
                                <option value="deluxe_studio">Deluxe Studio</option>
                                <option value="family_studio">Family Studio</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Check Availability</button>
                </form>
            </div>

            <!-- Results -->
            <div id="resultsSection" class="dashboard-section" style="display: none;">
                <h2>Available Rooms</h2>
                <div id="availableRooms" class="rooms-grid"></div>
            </div>

            <!-- Booking Modal -->
            <div id="bookingModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Complete Your Booking</h2>
                    <form id="bookingForm">
                        <input type="hidden" id="selected_room_id" name="room_id">
                        <input type="hidden" id="selected_check_in" name="check_in">
                        <input type="hidden" id="selected_check_out" name="check_out">
                        <input type="hidden" id="selected_guests" name="guests">
                        
                        <div class="booking-summary" id="bookingSummary"></div>
                        
                        <div class="form-group">
                            <label class="form-label">Special Requests</label>
                            <textarea id="special_requests" name="special_requests" class="form-control" rows="3" 
                                      placeholder="Any special requests or preferences..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Payment Method</label>
                            <div class="payment-options">
                                <label>
                                    <input type="radio" name="payment_method" value="card" required> 
                                    Pay with Credit Card
                                </label>
                                <label>
                                    <input type="radio" name="payment_method" value="cash" checked> 
                                    Pay at Hotel
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeBookingModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Confirm Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/booking.js"></script>
    <script>
        // Set user ID for booking
        window.currentUserId = <?php echo $_SESSION['user_id']; ?>;
        
        // Initialize booking functionality
        document.addEventListener('DOMContentLoaded', function() {
            window.bookingManager = new BookingManager();
        });
    </script>
</body>
</html>