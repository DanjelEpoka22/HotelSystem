<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezervo Dhomë - Villa Adrian</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #2C5F7C;
            --secondary-color: #F4A261;
            --accent-color: #E76F51;
            --dark-color: #264653;
            --light-color: #F8F9FA;
            --white: #FFFFFF;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-color);
            color: var(--dark-color);
        }

        .header {
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            padding: 1rem 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .navbar {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .logo {
            height: 40px;
            margin-right: 0.75rem;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 1rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--secondary-color);
            color: var(--white);
        }

        .user-container {
            max-width: 1400px;
            margin: 100px auto 2rem;
            padding: 0 2rem;
        }

        .user-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .user-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .user-header p {
            font-size: 1.2rem;
            color: #666;
        }

        .dashboard-section {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .booking-form {
            max-width: 900px;
            margin: 0 auto;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-label i {
            margin-right: 0.5rem;
            color: var(--secondary-color);
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: var(--light-color);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            background: white;
            box-shadow: 0 0 0 4px rgba(244, 162, 97, 0.1);
        }

        .btn {
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            color: white;
            box-shadow: 0 4px 15px rgba(244, 162, 97, 0.4);
            width: 100%;
            justify-content: center;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(231, 111, 81, 0.5);
        }

        .btn-secondary {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background: var(--primary-color);
            color: white;
        }

        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .room-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
        }

        .room-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .room-slider {
            position: relative;
            height: 300px;
            overflow: hidden;
            background: #f0f0f0;
        }

        .slide {
            display: none;
            height: 100%;
        }

        .slide.active {
            display: block;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .room-card:hover .slide img {
            transform: scale(1.1);
        }

        .room-price-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--secondary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 10;
        }

        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.9);
            color: var(--primary-color);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
        }

        .room-card:hover .slider-btn {
            opacity: 1;
        }

        .slider-btn:hover {
            background: var(--secondary-color);
            color: white;
        }

        .slider-btn.prev { left: 15px; }
        .slider-btn.next { right: 15px; }

        .slider-dots {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.3s;
        }

        .dot.active,
        .dot:hover {
            background: var(--secondary-color);
            transform: scale(1.2);
        }

        .room-info {
            padding: 2rem;
        }

        .room-info h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .room-number {
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .room-features {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            color: #666;
        }

        .feature i {
            color: var(--secondary-color);
        }

        .room-description {
            color: #666;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .room-amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .amenity-tag {
            background: linear-gradient(135deg, #f0f4f8, #e0e7ef);
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            color: var(--primary-color);
            border: 1px solid rgba(44, 95, 124, 0.1);
        }

        .room-actions {
            display: flex;
            gap: 1rem;
        }

        .no-rooms {
            text-align: center;
            padding: 4rem 2rem;
            color: #999;
        }

        .no-rooms i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 3rem;
            border-radius: 25px;
            width: 90%;
            max-width: 700px;
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: var(--accent-color);
        }

        .booking-summary {
            background: var(--light-color);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border-left: 4px solid var(--secondary-color);
        }

        .payment-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .payment-options label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--light-color);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-options label:hover {
            background: #e9ecef;
        }

        .payment-options input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .user-container {
                margin: 80px auto 1rem;
                padding: 0 1rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .rooms-grid {
                grid-template-columns: 1fr;
            }

            .room-actions {
                flex-direction: column;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <img src="../assets/images/logo.png" alt="Villa Adrian Logo" class="logo" onerror="this.style.display='none'">
                <span>Villa Adrian</span>
            </div>
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="../rooms.php" class="nav-link"><i class="fas fa-bed"></i> Dhomat</a></li>
                <li><a href="index.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="book.php" class="nav-link active"><i class="fas fa-calendar-plus"></i> Rezervo</a></li>
                <li><a href="my_reservations.php" class="nav-link"><i class="fas fa-calendar"></i> Rezervimet</a></li>
                <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="user-container">
        <div class="user-header" data-aos="fade-down">
            <h1>Rezervoni Qëndrimin Tuaj</h1>
            <p>Gjeni dhomën e përsosur për pushimet tuaja në Ksamil</p>
        </div>

        <div class="dashboard-section" data-aos="fade-up">
            <form id="availabilityForm" class="booking-form">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-calendar-check"></i> Data e Check-in</label>
                        <input type="date" id="check_in" name="check_in" class="form-control" required 
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-calendar-times"></i> Data e Check-out</label>
                        <input type="date" id="check_out" name="check_out" class="form-control" required 
                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-users"></i> Numri i Mysafirëve</label>
                        <select id="guests" name="guests" class="form-control" required>
                            <option value="1">1 Mysafir</option>
                            <option value="2" selected>2 Mysafirë</option>
                            <option value="3">3 Mysafirë</option>
                            <option value="4">4 Mysafirë</option>
                            <option value="5">5 Mysafirë</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-door-open"></i> Lloji i Dhomës</label>
                        <select id="room_type" name="room_type" class="form-control">
                            <option value="">Çdo lloj dhome</option>
                            <option value="one_bedroom_apartment">Apartament 1 dhomë gjumi</option>
                            <option value="deluxe_double">Dhomë Deluxe Dyshe</option>
                            <option value="deluxe_triple">Dhomë Deluxe Treshe</option>
                            <option value="deluxe_quadruple">Dhomë Deluxe Katërshe</option>
                            <option value="suite">Suite</option>
                            <option value="deluxe_studio">Studio Deluxe</option>
                            <option value="family_studio">Studio Familjare</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Kontrollo Disponueshmërinë
                </button>
            </form>
        </div>

        <div id="resultsSection" class="dashboard-section" style="display: none;" data-aos="fade-up">
            <h2 style="font-family: 'Playfair Display', serif; color: var(--primary-color); margin-bottom: 2rem;">
                <i class="fas fa-bed"></i> Dhoma të Disponueshme
            </h2>
            <div id="availableRooms" class="rooms-grid"></div>
        </div>

        <div id="bookingModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 style="font-family: 'Playfair Display', serif; color: var(--primary-color); margin-bottom: 2rem;">
                    <i class="fas fa-check-circle"></i> Përfundoni Rezervimin
                </h2>
                <form id="bookingForm">
                    <input type="hidden" id="selected_room_id" name="room_id">
                    <input type="hidden" id="selected_check_in" name="check_in">
                    <input type="hidden" id="selected_check_out" name="check_out">
                    <input type="hidden" id="selected_guests" name="guests">
                    
                    <div class="booking-summary" id="bookingSummary"></div>
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-comment"></i> Kërkesa të Veçanta</label>
                        <textarea id="special_requests" name="special_requests" class="form-control" rows="3" 
                                  placeholder="Shkruani çdo kërkesë ose preferencë të veçantë..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-credit-card"></i> Mënyra e Pagesës</label>
                        <div class="payment-options">
                            <label>
                                <input type="radio" name="payment_method" value="card" required>
                                <span><i class="fas fa-credit-card"></i> Paguaj me Kartë Krediti</span>
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="cash" checked>
                                <span><i class="fas fa-money-bill-wave"></i> Paguaj në Hotel</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeBookingModal()">
                            <i class="fas fa-times"></i> Anulo
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Konfirmo Rezervimin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="../assets/js/booking.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
        
        window.currentUserId = <?php echo $_SESSION['user_id']; ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            window.bookingManager = new BookingManager();
        });

        // Slider functions
        function moveSlide(btn, direction) {
            const slider = btn.closest('.room-slider');
            const slides = slider.querySelectorAll('.slide');
            const dots = slider.querySelectorAll('.dot');
            let currentIndex = Array.from(slides).findIndex(slide => slide.classList.contains('active'));
            
            slides[currentIndex].classList.remove('active');
            if (dots.length > 0) dots[currentIndex].classList.remove('active');
            
            currentIndex += direction;
            if (currentIndex < 0) currentIndex = slides.length - 1;
            if (currentIndex >= slides.length) currentIndex = 0;
            
            slides[currentIndex].classList.add('active');
            if (dots.length > 0) dots[currentIndex].classList.add('active');
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
    </script>
</body>
</html>