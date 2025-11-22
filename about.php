<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rreth Ksamilit & Villa Adrian - <?php echo SITE_NAME; ?></title>
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
            line-height: 1.6;
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
            transition: all 0.3s ease;
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
            text-decoration: none;
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
            height: 70vh;
            background: linear-gradient(rgba(44, 95, 124, 0.7), rgba(38, 70, 83, 0.6)), 
                        url('assets/images/ksamil/ksamil-beach.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-top: 80px;
            position: relative;
        }

        .page-hero::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: linear-gradient(to top, rgba(248,249,250,1), transparent);
        }

        .page-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4.5rem;
            margin-bottom: 1rem;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
            animation: fadeInDown 1s ease;
        }

        .page-hero p {
            font-size: 1.6rem;
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

        /* About Ksamil Section */
        .about-section {
            padding: 6rem 2rem;
            background: var(--light-color);
        }

        .about-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            margin-bottom: 4rem;
        }

        .about-text h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .about-text p {
            color: #666;
            line-height: 1.8;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .about-image {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .about-image img {
            width: 100%;
            height: 500px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .about-image:hover img {
            transform: scale(1.1);
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-top: 4px solid var(--secondary-color);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.7;
        }

        /* Hotel Story Section */
        .hotel-story {
            padding: 6rem 2rem;
            background: white;
        }

        .story-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .story-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .story-image {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .story-image img {
            width: 100%;
            height: 500px;
            object-fit: cover;
        }

        .story-text h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .story-text > p {
            color: #666;
            line-height: 1.8;
            font-size: 1.1rem;
            margin-bottom: 2.5rem;
        }

        .story-features {
            display: grid;
            gap: 1.5rem;
        }

        .story-feature {
            background: var(--light-color);
            padding: 1.5rem;
            border-radius: 15px;
            border-left: 4px solid var(--secondary-color);
            transition: all 0.3s ease;
        }

        .story-feature:hover {
            transform: translateX(10px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .story-feature h4 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .story-feature p {
            color: #666;
            line-height: 1.6;
        }

        /* Activities Section */
        .activities-section {
            padding: 6rem 2rem;
            background: var(--light-color);
        }

        .activities-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .activities-container h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 4rem;
        }

        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
        }

        .activity-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
        }

        .activity-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }

        .activity-image {
            height: 250px;
            overflow: hidden;
            position: relative;
        }

        .activity-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .activity-card:hover .activity-image img {
            transform: scale(1.15);
        }

        .activity-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.3));
        }

        .activity-info {
            padding: 2rem;
        }

        .activity-info h3 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
            font-size: 1.6rem;
            margin-bottom: 1rem;
        }

        .activity-info p {
            color: #666;
            line-height: 1.7;
        }

        /* Testimonials Section */
        .testimonials-section {
            padding: 6rem 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            color: white;
        }

        .testimonials-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .testimonials-container h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 4rem;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
        }

        .testimonial-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            background: rgba(255,255,255,0.15);
        }

        .testimonial-text {
            margin-bottom: 2rem;
            position: relative;
        }

        .testimonial-text::before {
            content: '"';
            font-size: 4rem;
            font-family: 'Playfair Display', serif;
            position: absolute;
            top: -20px;
            left: -10px;
            opacity: 0.3;
        }

        .testimonial-text p {
            font-size: 1.1rem;
            line-height: 1.8;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .testimonial-author strong {
            font-size: 1.2rem;
            color: var(--secondary-color);
        }

        .testimonial-author span {
            opacity: 0.8;
        }

        /* CTA Section */
        .cta-section {
            padding: 6rem 2rem;
            background: white;
            text-align: center;
        }

        .cta-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .cta-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .cta-content p {
            font-size: 1.3rem;
            color: #666;
            margin-bottom: 2.5rem;
        }

        .btn {
            padding: 1.2rem 3rem;
            border: none;
            border-radius: 50px;
            font-size: 1.2rem;
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

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .social-link:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
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
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }

            .nav-menu.active {
                left: 0;
            }

            .page-hero h1 {
                font-size: 2.5rem;
            }

            .page-hero p {
                font-size: 1.2rem;
            }

            .about-grid,
            .story-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .story-grid .story-image {
                order: -1;
            }

            .activities-grid,
            .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .about-text h2,
            .story-text h2,
            .activities-container h2,
            .testimonials-container h2,
            .cta-content h2 {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding: 0 1rem;
            }

            .page-hero {
                height: 60vh;
                margin-top: 70px;
            }

            .about-section,
            .hotel-story,
            .activities-section,
            .testimonials-section,
            .cta-section {
                padding: 4rem 1rem;
            }

            .feature-card,
            .testimonial-card {
                padding: 1.5rem;
            }

            .btn {
                padding: 1rem 2rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="nav-brand">
                <img src="assets/images/logo.png" alt="Villa Adrian Logo" class="logo" onerror="this.style.display='none'">
                <span>Villa Adrian</span>
            </a>
            <button class="mobile-toggle" id="mobileToggle">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="rooms.php" class="nav-link">Dhomat</a></li>
                <li><a href="about.php" class="nav-link active">Rreth Ksamilit</a></li>
                <li><a href="contact.php" class="nav-link">Kontakt</a></li>
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
            <h1>Zbuloni Ksamilin</h1>
            <p>Perla e Rivierës Shqiptare & Villa Adrian</p>
        </div>
    </section>

    <!-- About Ksamil Section -->
    <section class="about-section">
        <div class="about-container">
            <div class="about-grid">
                <div class="about-text" data-aos="fade-right">
                    <h2>Ksamili - Parajsa e Detit Jonit</h2>
                    <p>Ksamili është një nga destinacionet më të bukura dhe më të kërkuara turistike në Shqipëri, i vendosur në zemër të Rivierës Shqiptare. I njohur për plazhet e tij mahnitëse, ujërat kristal turqez dhe ishujt piktoresk, Ksamili ofron një përzierje të përsosur të bukurisë natyrore dhe komoditeteve moderne.</p>
                    
                    <p>Me klimë mesdhetare dhe mbi 300 ditë diell në vit, Ksamili është destinacioni ideal për pushime në çdo stinë. Uji i pastër dhe i ngrohtë, rëra e bardhë dhe pamjet spektakolare e bëjnë këtë vend një nga vendet më të veçanta në Mesdhe.</p>
                </div>
                
                <div class="about-image" data-aos="fade-left">
                    <img src="assets/images/ksamil.jpg" alt="Plazhi i Ksamilit">
                
                </div>
            </div>

            <div class="features-grid">
                <div class="feature-card" data-aos="zoom-in">
                    <div class="feature-icon">🏖️</div>
                    <h3>Plazhe të Pastra</h3>
                    <p>Plazhe me rërë të bardhë dhe disa nga ujërat më të pastra në Mesdhe, ideale për not dhe relaksim</p>
                </div>
                
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="100">
                    <div class="feature-icon">🏝️</div>
                    <h3>Ishujt e Ksamilit</h3>
                    <p>Katër ishuj të vegjël vetëm disa minuta nga bregu, të përsosur për ekskursione ditore dhe not</p>
                </div>
                
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="200">
                    <div class="feature-icon">🍽️</div>
                    <h3>Ushqim i Freskët Deti</h3>
                    <p>Ushqim i freskët deti dhe pjata tradicionale shqiptare në restorante në bregdet me pamje mahnitëse</p>
                </div>
                
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="300">
                    <div class="feature-icon">🌅</div>
                    <h3>Perëndimet Magjike</h3>
                    <p>Pamje spektakolare të perëndimit të diellit mbi Detin Jon, një përvojë e paharrueshme</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Villa Adrian Story -->
    <section class="hotel-story">
        <div class="story-container">
            <div class="story-grid">
                <div class="story-image" data-aos="fade-right">
                    <img src="assets/images/hotel.jpg" >
                </div>
                
                <div class="story-text" data-aos="fade-left">
                    <h2>Mirë se vini në Villa Adrian</h2>
                    <p>Villa Adrian është një hotel luksoz i vendosur në zemër të Ksamilit, duke ofruar pamje mahnitëse të Detit Jon dhe akses të drejtpërdrejtë në disa nga plazhet më të bukura në Shqipëri. Hoteli ynë kombinon komoditetin modern me mikpritjen tradicionale shqiptare për të krijuar një përvojë të paharrueshme për mysafirët tanë.</p>
                    
                    <div class="story-features">
                        <div class="story-feature">
                            <h4>🏆 Akomodim Luksoz</h4>
                            <p>Dhoma dhe suita të hapura me pamje deti, komoditete moderne dhe mobilim të rehatshëm për një qëndrim të përkryer</p>
                        </div>
                        
                        <div class="story-feature">
                            <h4>📍 Lokacion i Përsosur</h4>
                            <p>Vetëm disa hapa larg nga plazhi dhe në largësi këmbimi nga restorante dhe dyqane lokale</p>
                        </div>
                        
                        <div class="story-feature">
                            <h4>🍹 Terracë në Çati</h4>
                            <p>Pamje panoramike mahnitëse të Ksamilit dhe ishujve nga bar-i ynë në tarracë me atmosferë unike</p>
                        </div>
                        
                        <div class="story-feature">
                            <h4>👨‍🍳 Kuzhinë Autentike</h4>
                            <p>Pjata tradicionale shqiptare dhe mesdhetare të përgatitura me përbërës lokalë të freskët</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Activities Section -->
    <section class="activities-section">
        <div class="activities-container">
            <h2 data-aos="fade-up">Gjëra për të Bërë në Ksamil</h2>
            <div class="activities-grid">
                <div class="activity-card" data-aos="flip-left">
                    <div class="activity-image">
                        <img src="assets/images/ksamil/boat-tour.jpg" >
                    </div>
                    <div class="activity-info">
                        <h3>Ture me Varkë</h3>
                        <p>Eksploroni Ishujt e Ksamilit dhe gjiret e fshehura me ture lokale me varkë. Zbuloni plazhe sekrete dhe ujëra kristal.</p>
                    </div>
                </div>
                
                <div class="activity-card" data-aos="flip-left" data-aos-delay="100">
                    <div class="activity-image">
                        <img src="assets/images/ksamil/butrint.jpg" >
                    </div>
                    <div class="activity-info">
                        <h3>Parku Kombëtar i Butrintit</h3>
                        <p>Vizitoni sitin e Trashëgimisë Botërore të UNESCO-s me rrënoja antike vetëm 10 minuta larg nga Ksamili.</p>
                    </div>
                </div>
                
                <div class="activity-card" data-aos="flip-left" data-aos-delay="200">
                    <div class="activity-image">
                        <img src="assets/images/ksamil/diving.jpg" >
                    </div>
                    <div class="activity-info">
                        <h3>Zhytje me Tub</h3>
                        <p>Zbuloni shpella nënujore dhe jetën detare në ujëra kristal të pastra me ekskursione zhytjeje.</p>
                    </div>
                </div>
                
                <div class="activity-card" data-aos="flip-left" data-aos-delay="300">
                    <div class="activity-image">
                        <img src="assets/images/ksamil/saranda.jpg" >
                    </div>
                    <div class="activity-info">
                        <h3>Ekskursion në Sarandë</h3>
                        <p>Vizitoni qytetin e gjallë të Sarandës, vetëm 20 minuta larg, me dyqane, restorante dhe jetë nate.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="testimonials-container">
            <h2 data-aos="fade-up">Çfarë Thonë Mysafirët Tanë</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card" data-aos="fade-up">
                    <div class="testimonial-text">
                        <p>Villa Adrian tejkaloi të gjitha pritjet tona. Pamja nga dhoma jonë ishte mahnitëse dhe stafi bëri gjithçka për ta bërë qëndrimin tonë të përsosur. Do të kthehemi patjetër!</p>
                    </div>
                    <div class="testimonial-author">
                        <strong>Maria & Giovanni</strong>
                        <span>🇮🇹 Itali</span>
                    </div>
                </div>
                
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-text">
                        <p>Lokacioni është i përsosur - mes plazhit dhe qytetit. Dhomat janë moderne dhe të pastra, dhe mëngjesi ishte i shijshëm çdo mëngjes. Shërbim 5 yje!</p>
                    </div>
                    <div class="testimonial-author">
                        <strong>Thomas Schmidt</strong>
                        <span>🇩🇪 Gjermani</span>
                    </div>
                </div>
                
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-text">
                        <p>Na pëlqeu qëndrimi ynë në Villa Adrian! Terraca në çati ka pamjet më të mira të perëndimit të diellit në Ksamil. Do të jemi patjetër sërish vitin e ardhshëm!</p>
                    </div>
                    <div class="testimonial-author">
                        <strong>Sarah Johnson</strong>
                        <span>🇬🇧 Mbretëria e Bashkuar</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content" data-aos="zoom-in">
            <h2>Përjetoni Ksamilin Me Ne</h2>
            <p>Rezervoni qëndrimin tuaj në Villa Adrian dhe zbuloni magjinë e Rivierës Shqiptare</p>
            <?php if (isLoggedIn()): ?>
                <a href="user/book.php" class="btn btn-primary">
                    <i class="fas fa-calendar-check"></i> Rezervo Qëndrimin Tënd
                </a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Krijo Llogari për të Rezervuar
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section" data-aos="fade-up">
                <h3>Villa Adrian</h3>
                <p>Hoteli më i bukur në Ksamil, me pamje të mrekullueshme të detit Jonit dhe shërbim ekskluziv.</p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-tripadvisor"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-booking"></i></a>
                </div>
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
                <p><i class="fas fa-map-marker-alt"></i> Ksamil Beach Road, Sarandë, Albania</p>
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
            <p>&copy; <?php echo date('Y'); ?> Villa Adrian. Të gjitha të drejtat e rezervuara. | Dizajnuar me ❤️ për Ksamilin</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Initialize AOS animations
        AOS.init({ 
            duration: 1000, 
            once: true,
            offset: 100
        });

        // Mobile menu toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const navMenu = document.getElementById('navMenu');
        
        mobileToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            const icon = mobileToggle.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                mobileToggle.querySelector('i').classList.add('fa-bars');
                mobileToggle.querySelector('i').classList.remove('fa-times');
            });
        });

        // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.style.padding = '1rem 0';
                header.style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                header.style.padding = '1.5rem 0';
                header.style.background = 'rgba(255, 255, 255, 0.95)';
            }
        });
    </script>
</body>
</html>