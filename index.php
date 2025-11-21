<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
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

<!-- Featured Rooms Section - Replace in your index.php -->
<section class="featured-rooms">
    <div class="container">
        <h2>Our Rooms</h2>
        <div class="rooms-grid">
            <?php
            $query = "SELECT * FROM rooms WHERE is_available = 1 LIMIT 3";
            $stmt = $db->prepare($query);
            $stmt->execute();
            
            while ($room = $stmt->fetch(PDO::FETCH_ASSOC)):
                $photos = getRoomPhotos($room['id']);
            ?>
            <div class="room-card">
                <?php if (!empty($photos)): ?>
                <div class="room-slider">
                    <div class="slider-container">
                        <?php foreach ($photos as $index => $photo): ?>
                        <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="assets/images/rooms/uploads/<?php echo $photo['photo_filename']; ?>" 
                                 alt="<?php echo getRoomTypeName($room['room_type']); ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($photos) > 1): ?>
                    <button class="slider-btn prev" onclick="moveSlide(this, -1)">❮</button>
                    <button class="slider-btn next" onclick="moveSlide(this, 1)">❯</button>
                    
                    <div class="slider-dots">
                        <?php foreach ($photos as $index => $photo): ?>
                        <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>" 
                              onclick="goToSlide(this, <?php echo $index; ?>)"></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="room-image">
                    <img src="assets/images/rooms/default.jpg" alt="Room">
                </div>
                <?php endif; ?>
                
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

<style>
/* Photo Slider Styles */
.room-slider {
    position: relative;
    height: 250px;
    overflow: hidden;
    background: #000;
}

.slider-container {
    position: relative;
    height: 100%;
}

.slide {
    display: none;
    height: 100%;
}

.slide.active {
    display: block;
}

.slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.slider-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    font-size: 18px;
    transition: background 0.3s;
    z-index: 10;
}

.slider-btn:hover {
    background: rgba(0,0,0,0.8);
}

.slider-btn.prev {
    left: 10px;
}

.slider-btn.next {
    right: 10px;
}

.slider-dots {
    position: absolute;
    bottom: 15px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 8px;
    z-index: 10;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: background 0.3s;
}

.dot.active,
.dot:hover {
    background: rgba(255,255,255,1);
}
</style>

<script>
// Photo Slider JavaScript
function moveSlide(btn, direction) {
    const slider = btn.closest('.room-slider');
    const slides = slider.querySelectorAll('.slide');
    const dots = slider.querySelectorAll('.dot');
    let currentIndex = Array.from(slides).findIndex(slide => slide.classList.contains('active'));
    
    slides[currentIndex].classList.remove('active');
    dots[currentIndex].classList.remove('active');
    
    currentIndex += direction;
    if (currentIndex < 0) currentIndex = slides.length - 1;
    if (currentIndex >= slides.length) currentIndex = 0;
    
    slides[currentIndex].classList.add('active');
    dots[currentIndex].classList.add('active');
}

function goToSlide(dot, index) {
    const slider = dot.closest('.room-slider');
    const slides = slider.querySelectorAll('.slide');
    const dots = slider.querySelectorAll('.dot');
    
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(d => d.classList.remove('active'));
    
    slides[index].classList.add('active');
    dots[index].classList.add('active');
}

// Auto-slide every 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.room-slider').forEach(slider => {
        if (slider.querySelectorAll('.slide').length > 1) {
            setInterval(() => {
                const nextBtn = slider.querySelector('.next');
                if (nextBtn) moveSlide(nextBtn, 1);
            }, 5000);
        }
    });
});
</script>