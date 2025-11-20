<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Luxury Hotel in Ksamil</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <img src="assets/images/logo.png" alt="Villa Adrian Logo" class="logo">
                <span><?php echo SITE_NAME; ?></span>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link active">Home</a></li>
                <li><a href="rooms.php" class="nav-link">Rooms</a></li>
                <li><a href="about.php" class="nav-link">About Ksamil</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="user/index.php" class="nav-link">Dashboard</a></li>
                    <li><a href="user/logout.php" class="nav-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="register.php" class="nav-link">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Villa Adrian</h1>
            <p>Experience Luxury in the Heart of Ksamil</p>
            <a href="rooms.php" class="btn btn-primary">Book Your Stay</a>
        </div>
    </section>

    <!-- Featured Rooms -->
    <section class="featured-rooms">
        <div class="container">
            <h2>Our Rooms</h2>
            <div class="rooms-grid">
                <?php
                $query = "SELECT * FROM rooms WHERE is_available = 1 LIMIT 3";
                $stmt = $db->prepare($query);
                $stmt->execute();
                
                while ($room = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <div class="room-card">
                    <div class="room-image">
                        <img src="assets/images/rooms/<?php echo $room['id']; ?>/1.jpg" alt="<?php echo getRoomTypeName($room['room_type']); ?>">
                    </div>
                    <div class="room-info">
                        <h3><?php echo getRoomTypeName($room['room_type']); ?></h3>
                        <p class="room-price">€<?php echo $room['price_per_night']; ?>/night</p>
                        <p class="room-guests">Max guests: <?php echo $room['max_guests']; ?></p>
                        <a href="rooms.php?room=<?php echo $room['id']; ?>" class="btn btn-secondary">View Details</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- About Ksamil Section -->
    <section class="about-ksamil">
        <div class="container">
            <h2>Discover Ksamil</h2>
            <p>Ksamil is one of the most beautiful tourist destinations in Albania, known for its crystal-clear waters, stunning beaches, and picturesque islands. Located in the Albanian Riviera, Ksamil offers a perfect blend of natural beauty and modern amenities.</p>
            <div class="ksamil-features">
                <div class="feature">
                    <h3>Beautiful Beaches</h3>
                    <p>Pristine sandy beaches with turquoise waters</p>
                </div>
                <div class="feature">
                    <h3>Island Hopping</h3>
                    <p>Visit the famous Ksamil Islands just minutes from shore</p>
                </div>
                <div class="feature">
                    <h3>Mediterranean Cuisine</h3>
                    <p>Enjoy fresh seafood and local delicacies</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>