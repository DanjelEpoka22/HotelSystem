<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Ksamil & Villa Adrian - <?php echo SITE_NAME; ?></title>
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
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="rooms.php" class="nav-link">Rooms</a></li>
                <li><a href="about.php" class="nav-link active">About Ksamil</a></li>
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

    <!-- About Header -->
    <section class="page-header">
        <div class="container">
            <h1>About Ksamil & Villa Adrian</h1>
            <p>Discover the beauty of Albania's Riviera and our luxury hotel</p>
        </div>
    </section>

    <!-- About Ksamil -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Discover Ksamil</h2>
                    <p>Ksamil is one of the most beautiful and sought-after tourist destinations in Albania, located in the heart of the Albanian Riviera. Known for its stunning beaches, crystal-clear turquoise waters, and picturesque islands, Ksamil offers a perfect blend of natural beauty and modern amenities.</p>
                    
                    <div class="features-grid">
                        <div class="feature-item">
                            <div class="feature-icon">🏖️</div>
                            <h3>Pristine Beaches</h3>
                            <p>White sandy beaches with some of the clearest waters in the Mediterranean</p>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">🏝️</div>
                            <h3>Island Hopping</h3>
                            <p>Four small islands just minutes from shore, perfect for day trips and swimming</p>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">🍽️</div>
                            <h3>Mediterranean Cuisine</h3>
                            <p>Fresh seafood and traditional Albanian dishes in beachfront restaurants</p>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">🌅</div>
                            <h3>Breathtaking Sunsets</h3>
                            <p>Spectacular sunset views over the Ionian Sea</p>
                        </div>
                    </div>
                </div>
                
                <div class="about-image">
                    <img src="assets/images/ksamil/ksamil-beach.jpg" alt="Ksamil Beach">
                </div>
            </div>
        </div>
    </section>

    <!-- Villa Adrian Story -->
    <section class="hotel-story">
        <div class="container">
            <div class="story-content">
                <div class="story-image">
                    <img src="assets/images/hotel/hotel-exterior.jpg" alt="Villa Adrian">
                </div>
                
                <div class="story-text">
                    <h2>Welcome to Villa Adrian</h2>
                    <p>Villa Adrian is a luxury hotel located in the heart of Ksamil, offering breathtaking views of the Ionian Sea and direct access to some of the most beautiful beaches in Albania. Our hotel combines modern comfort with traditional Albanian hospitality to create an unforgettable experience for our guests.</p>
                    
                    <div class="story-features">
                        <div class="story-feature">
                            <h4>🏆 Luxury Accommodation</h4>
                            <p>Spacious rooms and suites with sea views, modern amenities, and comfortable furnishings</p>
                        </div>
                        
                        <div class="story-feature">
                            <h4>📍 Prime Location</h4>
                            <p>Just steps away from the beach and within walking distance of restaurants and shops</p>
                        </div>
                        
                        <div class="story-feature">
                            <h4>🍹 Rooftop Terrace</h4>
                            <p>Stunning panoramic views of Ksamil and the islands from our rooftop bar</p>
                        </div>
                        
                        <div class="story-feature">
                            <h4>👨‍🍳 Authentic Cuisine</h4>
                            <p>Traditional Albanian and Mediterranean dishes made with local ingredients</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Things to Do -->
    <section class="activities-section">
        <div class="container">
            <h2>Things to Do in Ksamil</h2>
            <div class="activities-grid">
                <div class="activity-card">
                    <img src="assets/images/ksamil/boat-tour.jpg" alt="Boat Tours">
                    <div class="activity-info">
                        <h3>Boat Tours</h3>
                        <p>Explore the Ksamil Islands and hidden coves with local boat tours</p>
                    </div>
                </div>
                
                <div class="activity-card">
                    <img src="assets/images/ksamil/butrint.jpg" alt="Butrint National Park">
                    <div class="activity-info">
                        <h3>Butrint National Park</h3>
                        <p>Visit the UNESCO World Heritage site with ancient ruins</p>
                    </div>
                </div>
                
                <div class="activity-card">
                    <img src="assets/images/ksamil/diving.jpg" alt="Scuba Diving">
                    <div class="activity-info">
                        <h3>Scuba Diving</h3>
                        <p>Discover underwater caves and marine life in crystal-clear waters</p>
                    </div>
                </div>
                
                <div class="activity-card">
                    <img src="assets/images/ksamil/saranda.jpg" alt="Saranda Day Trip">
                    <div class="activity-info">
                        <h3>Saranda Day Trip</h3>
                        <p>Visit the vibrant city of Saranda, just 20 minutes away</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials-section">
        <div class="container">
            <h2>What Our Guests Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        <p>"Villa Adrian exceeded all our expectations. The view from our room was breathtaking, and the staff went above and beyond to make our stay perfect."</p>
                    </div>
                    <div class="testimonial-author">
                        <strong>Maria & Giovanni</strong>
                        <span>Italy</span>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        <p>"The location is perfect - right between the beach and the town. The rooms are modern and clean, and the breakfast was delicious every morning."</p>
                    </div>
                    <div class="testimonial-author">
                        <strong>Thomas Schmidt</strong>
                        <span>Germany</span>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        <p>"We loved our stay at Villa Adrian! The rooftop terrace has the best sunset views in Ksamil. We'll definitely be back next year!"</p>
                    </div>
                    <div class="testimonial-author">
                        <strong>Sarah Johnson</strong>
                        <span>United Kingdom</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Experience Ksamil with Us</h2>
                <p>Book your stay at Villa Adrian and discover the magic of the Albanian Riviera</p>
                <?php if (isLoggedIn()): ?>
                    <a href="user/book.php" class="btn btn-primary btn-large">Book Your Stay</a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary btn-large">Create Account</a>
                <?php endif; ?>
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