<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    
    $errors = [];
    
    if (empty($name)) $errors[] = "Emri është i detyrueshëm";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email-i valid është i detyrueshëm";
    if (empty($subject)) $errors[] = "Subjekti është i detyrueshëm";
    if (empty($message)) $errors[] = "Mesazhi është i detyrueshëm";
    
    if (empty($errors)) {
        $success = "Faleminderit për mesazhin tuaj! Do t'ju përgjigjemi brenda 24 orëve.";
        $_POST = [];
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontakt - Villa Adrian</title>
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
            color: var(--dark-color);
        }

        /* Header */
        .header {
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            padding: 1.5rem 0;
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
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .logo {
            height: 50px;
            margin-right: 1rem;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--secondary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-color);
            cursor: pointer;
        }

        /* Page Hero */
        .page-hero {
            height: 60vh;
            background: linear-gradient(rgba(44, 95, 124, 0.8), rgba(38, 70, 83, 0.7)), 
                        url('assets/images/ksamil.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-top: 80px;
        }

        .page-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            margin-bottom: 1rem;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
            animation: fadeInDown 1s ease;
        }

        .page-hero p {
            font-size: 1.5rem;
            animation: fadeInUp 1s ease 0.3s both;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Contact Section */
        .contact-section {
            padding: 6rem 2rem;
            background: var(--light-color);
        }

        .contact-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: start;
        }

        .contact-info h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .contact-info > p {
            color: #666;
            margin-bottom: 3rem;
            line-height: 1.8;
        }

        .contact-details {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .contact-item {
            display: flex;
            gap: 1.5rem;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .contact-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }

        .contact-icon {
            font-size: 2.5rem;
            color: var(--secondary-color);
        }

        .contact-text h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .contact-text p {
            color: #666;
            line-height: 1.6;
        }

        .social-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .social-section h3 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }

        .social-icons {
            display: flex;
            gap: 1rem;
        }

        .social-link {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 1.3rem;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        /* Contact Form */
        .contact-form-wrapper {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .contact-form-wrapper h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 2rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
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

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(244, 162, 97, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 150px;
        }

        .btn {
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            color: white;
            box-shadow: 0 4px 15px rgba(244, 162, 97, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(231, 111, 81, 0.5);
        }

        /* FAQ Section */
        .faq-section {
            padding: 6rem 2rem;
            background: white;
        }

        .faq-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .faq-container h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 4rem;
        }

        .faq-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .faq-item {
            padding: 2rem;
            background: var(--light-color);
            border-radius: 15px;
            border-left: 4px solid var(--secondary-color);
            transition: all 0.3s ease;
        }

        .faq-item:hover {
            transform: translateX(10px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .faq-item h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .faq-item p {
            color: #666;
            line-height: 1.7;
        }

        /* Map Section */
        .map-section {
            padding: 6rem 2rem;
            background: var(--light-color);
        }

        .map-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .map-container h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 3rem;
        }

        .map-placeholder {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            color: white;
            padding: 4rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .map-content h3 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .map-content > p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .map-directions {
            background: rgba(255,255,255,0.1);
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
        }

        .map-directions p {
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--dark-color), var(--primary-color));
            color: var(--white);
            padding: 4rem 2rem 2rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-section h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .footer-section p,
        .footer-section a {
            color: rgba(255,255,255,0.8);
            line-height: 1.8;
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: var(--secondary-color);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }

            .nav-menu {
                position: fixed;
                left: -100%;
                top: 80px;
                flex-direction: column;
                background: rgba(255,255,255,0.98);
                width: 100%;
                padding: 2rem;
                transition: left 0.3s;
            }

            .nav-menu.active {
                left: 0;
            }

            .page-hero h1 {
                font-size: 2.5rem;
            }

            .contact-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .faq-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <img src="assets/images/logo.png" alt="Villa Adrian Logo" class="logo" onerror="this.style.display='none'">
                <span>Villa Adrian</span>
            </div>
            <button class="mobile-toggle" id="mobileToggle">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="rooms.php" class="nav-link">Dhomat</a></li>
                <li><a href="about.php" class="nav-link">Rreth Ksamilit</a></li>
                <li><a href="contact.php" class="nav-link active">Kontakt</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="user/index.php" class="nav-link"><i class="fas fa-user"></i> Dashboard</a></li>
                    <li><a href="user/logout.php" class="nav-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="register.php" class="nav-link btn-primary" style="padding: 0.5rem 1.5rem;">Regjistrohu</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Page Hero -->
    <section class="page-hero">
        <div data-aos="fade-up">
            <h1>Kontaktoni me Ne</h1>
            <p>Jemi këtu për t'ju ndihmuar - Dërgoni një mesazh</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="contact-container">
            <div class="contact-grid">
                <div class="contact-info" data-aos="fade-right">
                    <h2>Na Kontaktoni</h2>
                    <p>Do të donim të dëgjonim prej jush. Dërgoni një mesazh dhe do t'ju përgjigjemi sa më shpejt të jetë e mundur.</p>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Adresa</h3>
                                <p>Rruga e Plazhit të Ksamilit<br>Ksamil, Sarandë<br>Shqipëri</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Telefon</h3>
                                <p>+355 69 123 4567</p>
                                <p>+355 69 987 6543</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Email</h3>
                                <p>info@villaadrian.com</p>
                                <p>booking@villaadrian.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Orari i Punës</h3>
                                <p>Reception: 24/7</p>
                                <p>Restaurant: 07:00 - 23:00</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-section">
                        <h3>Na Ndiqni në Social Media</h3>
                        <div class="social-icons">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-tripadvisor"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form-wrapper" data-aos="fade-left">
                    <h2>Dërgoni një Mesazh</h2>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-user"></i> Emri Juaj *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo $_POST['name'] ?? ''; ?>" 
                                       placeholder="Shkruani emrin tuaj" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-envelope"></i> Email-i Juaj *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo $_POST['email'] ?? ''; ?>" 
                                       placeholder="emaili@example.com" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-tag"></i> Subjekti *</label>
                            <input type="text" name="subject" class="form-control" 
                                   value="<?php echo $_POST['subject'] ?? ''; ?>" 
                                   placeholder="Si mund t'ju ndihmojmë?" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-comment"></i> Mesazhi Juaj *</label>
                            <textarea name="message" class="form-control" 
                                      placeholder="Shkruani mesazhin tuaj këtu..." 
                                      required><?php echo $_POST['message'] ?? ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Dërgo Mesazhin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="faq-container">
            <h2 data-aos="fade-up">Pyetje të Shpeshta</h2>
            <div class="faq-grid">
                <div class="faq-item" data-aos="fade-up">
                    <h3><i class="fas fa-question-circle"></i> Cila është ora e check-in dhe check-out?</h3>
                    <p>Check-in është nga ora 14:00 dhe check-out deri në ora 11:00. Check-in i hershëm dhe check-out i vonë mund të jenë të disponueshëm me kërkesë.</p>
                </div>
                
                <div class="faq-item" data-aos="fade-up" data-aos-delay="100">
                    <h3><i class="fas fa-question-circle"></i> A ofroni transfer nga aeroporti?</h3>
                    <p>Po, mund të organizojmë transfer nga Aeroporti Ndërkombëtar i Tiranës ose Aeroporti i Korfuzit. Ju lutemi na kontaktoni paraprakisht për të rregulluar këtë shërbim.</p>
                </div>
                
                <div class="faq-item" data-aos="fade-up" data-aos-delay="200">
                    <h3><i class="fas fa-question-circle"></i> A ka parking të disponueshëm?</h3>
                    <p>Po, ofrojmë parking privat falas për të gjithë mysafirët tanë. Zona e parkimit është e sigurt dhe e monitoruar 24/7.</p>
                </div>
                
                <div class="faq-item" data-aos="fade-up">
                    <h3><i class="fas fa-question-circle"></i> A keni restorant?</h3>
                    <p>Po, kemi një restorant në hotel që shërben gatim tradicional shqiptar dhe mesdhetar. Kemi gjithashtu një bar në tarracë me pamje panoramike të detit.</p>
                </div>
                
                <div class="faq-item" data-aos="fade-up" data-aos-delay="100">
                    <h3><i class="fas fa-question-circle"></i> A lejohen kafshët shtëpiake?</h3>
                    <p>Lejojmë kafshë të vogla shtëpiake në disa dhoma me rregullim paraprak. Mund të aplikohen tarifa shtesë. Ju lutemi na kontaktoni për më shumë informacion.</p>
                </div>
                
                <div class="faq-item" data-aos="fade-up" data-aos-delay="200">
                    <h3><i class="fas fa-question-circle"></i> Cila është politika e anulimit?</h3>
                    <p>Mund të anuloni rezervimin tuaj pa pagesë deri në 15 ditë para datës së check-in. Pas kësaj date, mund të aplikohen tarifa anulimi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="map-container">
            <h2 data-aos="fade-up">Na Gjeni Lehtë</h2>
            <div class="map-placeholder" data-aos="zoom-in">
                <div class="map-content">
                    <h3><i class="fas fa-map-marker-alt"></i> Lokacioni i Villa Adrian</h3>
                    <p>Rruga e Plazhit të Ksamilit, Ksamil, Shqipëri</p>
                    <div class="map-directions">
                        <p><strong><i class="fas fa-car"></i> Nga Saranda:</strong> 20 minuta me makinë (15 km)</p>
                        <p><strong><i class="fas fa-landmark"></i> Nga Butrinti:</strong> 10 minuta me makinë (7 km)</p>
                        <p><strong><i class="fas fa-plane"></i> Nga Aeroporti i Tiranës:</strong> 4 orë me makinë (280 km)</p>
                    </div>
                    <a href="https://maps.google.com" target="_blank" class="btn btn-primary">
                        <i class="fas fa-map"></i> Hap në Google Maps
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section" data-aos="fade-up">
                <h3>Villa Adrian</h3>
                <p>Hoteli më i bukur në Ksamil, me pamje të mrekullueshme të detit Jonit dhe shërbim ekskluziv.</p>
            </div>
            
            <div class="footer-section" data-aos="fade-up" data-aos-delay="100">
                <h3>Navigim i Shpejtë</h3>
                <a href="index.php">Home</a>
                <a href="rooms.php">Dhomat</a>
                <a href="about.php">Rreth Nesh</a>
                <a href="contact.php">Kontakt</a>
            </div>
            
            <div class="footer-section" data-aos="fade-up" data-aos-delay="200">
                <h3>Kontakti</h3>
                <p><i class="fas fa-map-marker-alt"></i> Ksamil Beach Road, Sarandë</p>
                <p><i class="fas fa-phone"></i> +355 69 123 4567</p>
                <p><i class="fas fa-envelope"></i> info@villaadrian.com</p>
            </div>
            
            <div class="footer-section" data-aos="fade-up" data-aos-delay="300">
                <h3>Orari</h3>
                <p>Check-in: 14:00</p>
                <p>Check-out: 11:00</p>
                <p>Reception: 24/7</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Villa Adrian. Të gjitha të drejtat e rezervuara. | Dizenjuar me ❤️ për Ksamilin</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });

        const mobileToggle = document.getElementById('mobileToggle');
        const navMenu = document.getElementById('navMenu');
        
        mobileToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            const icon = mobileToggle.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
    </script>
</body>
</html>