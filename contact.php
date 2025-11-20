<?php
require_once 'config/config.php';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    
    // Basic validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    if (empty($errors)) {
        // In a real application, this would send an email
        // For now, we'll just show a success message
        $success = "Thank you for your message! We'll get back to you within 24 hours.";
        
        // Clear form
        $_POST = [];
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
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
                <li><a href="about.php" class="nav-link">About Ksamil</a></li>
                <li><a href="contact.php" class="nav-link active">Contact</a></li>
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

    <!-- Contact Header -->
    <section class="page-header">
        <div class="container">
            <h1>Contact Us</h1>
            <p>Get in touch with Villa Adrian - we're here to help</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-content">
                <div class="contact-info">
                    <h2>Get in Touch</h2>
                    <p>We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">📍</div>
                            <div class="contact-text">
                                <h3>Address</h3>
                                <p>Ksamil Beach Road<br>Ksamil, Sarandë<br>Albania</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">📞</div>
                            <div class="contact-text">
                                <h3>Phone</h3>
                                <p>+355 69 123 4567</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">✉️</div>
                            <div class="contact-text">
                                <h3>Email</h3>
                                <p>info@villaadrian.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">🌐</div>
                            <div class="contact-text">
                                <h3>Website</h3>
                                <p>www.villaadrian.com</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <h3>Follow Us</h3>
                        <div class="social-icons">
                            <a href="#" class="social-link">📘 Facebook</a>
                            <a href="#" class="social-link">📷 Instagram</a>
                            <a href="#" class="social-link">🐦 Twitter</a>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form-container">
                    <h2>Send us a Message</h2>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" class="contact-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Your Name *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo $_POST['name'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Your Email *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo $_POST['email'] ?? ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Subject *</label>
                            <input type="text" name="subject" class="form-control" 
                                   value="<?php echo $_POST['subject'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Message *</label>
                            <textarea name="message" class="form-control" rows="6" 
                                      placeholder="How can we help you?" required><?php echo $_POST['message'] ?? ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-grid">
                <div class="faq-item">
                    <h3>What time is check-in and check-out?</h3>
                    <p>Check-in is from 2:00 PM and check-out is until 11:00 AM. Early check-in and late check-out may be available upon request and subject to availability.</p>
                </div>
                
                <div class="faq-item">
                    <h3>Do you offer airport transfers?</h3>
                    <p>Yes, we can arrange airport transfers from Tirana International Airport or Corfu Airport. Please contact us in advance to arrange this service.</p>
                </div>
                
                <div class="faq-item">
                    <h3>Is parking available?</h3>
                    <p>Yes, we offer free private parking for all our guests. The parking area is secure and monitored.</p>
                </div>
                
                <div class="faq-item">
                    <h3>Do you have a restaurant?</h3>
                    <p>Yes, we have an on-site restaurant serving traditional Albanian and Mediterranean cuisine. We also have a rooftop bar with panoramic sea views.</p>
                </div>
                
                <div class="faq-item">
                    <h3>Are pets allowed?</h3>
                    <p>We allow small pets in certain rooms with prior arrangement. Additional charges may apply. Please contact us for more information.</p>
                </div>
                
                <div class="faq-item">
                    <h3>What is your cancellation policy?</h3>
                    <p>You can cancel your reservation free of charge up to 15 days before your check-in date. After that, cancellation fees may apply.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <h2>Find Us</h2>
            <div class="map-container">
                <!-- In a real application, this would be an embedded Google Map -->
                <div class="map-placeholder">
                    <div class="map-content">
                        <h3>📍 Villa Adrian Location</h3>
                        <p>Ksamil Beach Road, Ksamil, Albania</p>
                        <div class="map-directions">
                            <p><strong>From Sarandë:</strong> 20 minutes by car</p>
                            <p><strong>From Butrint:</strong> 10 minutes by car</p>
                            <p><strong>From Tirana Airport:</strong> 4 hours by car</p>
                        </div>
                        <a href="https://maps.google.com" target="_blank" class="btn btn-primary">Open in Google Maps</a>
                    </div>
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