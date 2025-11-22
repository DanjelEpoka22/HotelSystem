<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAuth();

$query = "SELECT * FROM users WHERE id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = sanitizeInput($_POST['first_name']);
        $last_name = sanitizeInput($_POST['last_name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        
        $updateQuery = "UPDATE users SET first_name = :first_name, last_name = :last_name, 
                       email = :email, phone = :phone, updated_at = NOW() 
                       WHERE id = :user_id";
        
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':first_name', $first_name);
        $updateStmt->bindParam(':last_name', $last_name);
        $updateStmt->bindParam(':email', $email);
        $updateStmt->bindParam(':phone', $phone);
        $updateStmt->bindParam(':user_id', $_SESSION['user_id']);
        
        if ($updateStmt->execute()) {
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            
            $success = "Profili u përditësua me sukses!";
            
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Përditësimi i profilit dështoi.";
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (!password_verify($current_password, $user['password'])) {
            $error = "Fjalëkalimi aktual është i gabuar.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Fjalëkalimet e reja nuk përputhen.";
        } elseif (strlen($new_password) < 6) {
            $error = "Fjalëkalimi i ri duhet të jetë të paktën 6 karaktere.";
        } else {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $passwordQuery = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :user_id";
            $passwordStmt = $db->prepare($passwordQuery);
            $passwordStmt->bindParam(':password', $new_password_hash);
            $passwordStmt->bindParam(':user_id', $_SESSION['user_id']);
            
            if ($passwordStmt->execute()) {
                $success = "Fjalëkalimi u ndryshua me sukses!";
            } else {
                $error = "Ndryshimi i fjalëkalimit dështoi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profili Im - Villa Adrian</title>
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
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-color);
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
        }

        .nav-link {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s;
            font-size: 0.95rem;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--secondary-color);
            color: white;
        }

        .user-container {
            max-width: 1200px;
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

        .alert {
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }

        .dashboard-section {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .dashboard-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
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
            transition: all 0.3s;
            background: var(--light-color);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            background: white;
            box-shadow: 0 0 0 4px rgba(244, 162, 97, 0.1);
        }

        .form-control:disabled {
            background: #e9ecef;
            cursor: not-allowed;
        }

        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: block;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            background: var(--light-color);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            border-top: 4px solid var(--secondary-color);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .stat-card h3 {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: #666;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 1rem;
            background: var(--light-color);
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .checkbox-label:hover {
            background: #e9ecef;
        }

        .checkbox-label input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .account-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-danger {
            background: var(--accent-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .user-container {
                margin: 80px auto 1rem;
                padding: 0 1rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .account-actions {
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
                <img src="../assets/images/logo.png" alt="Logo" class="logo" onerror="this.style.display='none'">
                <span>Villa Adrian</span>
            </div>
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="index.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="book.php" class="nav-link"><i class="fas fa-calendar-plus"></i> Rezervo</a></li>
                <li><a href="my_reservations.php" class="nav-link"><i class="fas fa-calendar"></i> Rezervimet</a></li>
                <li><a href="profile.php" class="nav-link active"><i class="fas fa-user"></i> Profili</a></li>
                <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="user-container">
        <div class="user-header" data-aos="fade-down">
            <h1>Profili Im</h1>
            <p>Menaxhoni informacionin e llogarisë tuaj</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success" data-aos="fade-up">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $success; ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error" data-aos="fade-up">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <div class="dashboard-section" data-aos="fade-up">
            <h2><i class="fas fa-user-circle"></i> Informacioni Personal</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user"></i> Emri</label>
                        <input type="text" name="first_name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user"></i> Mbiemri</label>
                        <input type="text" name="last_name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-phone"></i> Telefoni</label>
                        <input type="tel" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-at"></i> Emri i Përdoruesit</label>
                        <input type="text" class="form-control" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small class="form-text">Emri i përdoruesit nuk mund të ndryshohet</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-calendar"></i> Anëtar Që Nga</label>
                        <input type="text" class="form-control" 
                               value="<?php echo formatDate($user['created_at'], 'F j, Y'); ?>" disabled>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Ruaj Ndryshimet
                    </button>
                </div>
            </form>
        </div>

        <div class="dashboard-section" data-aos="fade-up">
            <h2><i class="fas fa-lock"></i> Ndrysho Fjalëkalimin</h2>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-key"></i> Fjalëkalimi Aktual</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-lock"></i> Fjalëkalimi i Ri</label>
                        <input type="password" name="new_password" class="form-control" required 
                               minlength="6" placeholder="Të paktën 6 karaktere">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-lock"></i> Konfirmo Fjalëkalimin</label>
                        <input type="password" name="confirm_password" class="form-control" required 
                               minlength="6">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-check"></i> Ndrysho Fjalëkalimin
                    </button>
                </div>
            </form>
        </div>

        <div class="dashboard-section" data-aos="fade-up">
            <h2><i class="fas fa-chart-line"></i> Statistikat e Llogarisë</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <h3>
                        <?php
                        $query = "SELECT COUNT(*) as total FROM reservations 
                                 WHERE user_id = :user_id 
                                 AND status IN ('confirmed', 'checked_in', 'checked_out')";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':user_id', $_SESSION['user_id']);
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo $result['total'];
                        ?>
                    </h3>
                    <p>Rezervime Totale</p>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <h3>
                        <?php
                        $query = "SELECT SUM(total_price) as total FROM reservations 
                                 WHERE user_id = :user_id 
                                 AND status IN ('confirmed', 'checked_in', 'checked_out')";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':user_id', $_SESSION['user_id']);
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo '€' . ($result['total'] ?? 0);
                        ?>
                    </h3>
                    <p>Totali i Shpenzuar</p>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🏨</div>
                    <h3>
                        <?php
                        $query = "SELECT COUNT(DISTINCT room_id) as total FROM reservations 
                                 WHERE user_id = :user_id 
                                 AND status IN ('confirmed', 'checked_in', 'checked_out')";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':user_id', $_SESSION['user_id']);
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo $result['total'];
                        ?>
                    </h3>
                    <p>Dhoma të Ndryshme</p>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <h3>
                        <?php
                        $query = "SELECT AVG(total_price) as average FROM reservations 
                                 WHERE user_id = :user_id 
                                 AND status IN ('confirmed', 'checked_in', 'checked_out')";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':user_id', $_SESSION['user_id']);
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo '€' . round($result['average'] ?? 0, 2);
                        ?>
                    </h3>
                    <p>Mesatarja e Rezervimit</p>
                </div>
            </div>
        </div>

        <div class="dashboard-section" data-aos="fade-up">
            <h2><i class="fas fa-cog"></i> Preferencat</h2>
            <form class="preferences-form">
                <label class="checkbox-label">
                    <input type="checkbox" name="email_notifications" checked>
                    <span><i class="fas fa-envelope"></i> Njoftime me email për ofertat e reja</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="sms_notifications">
                    <span><i class="fas fa-sms"></i> Njoftime SMS për konfirmimet e rezervimeve</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="special_offers" checked>
                    <span><i class="fas fa-gift"></i> Prano ofertat dhe zbritjet speciale</span>
                </label>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-primary" onclick="savePreferences()">
                        <i class="fas fa-save"></i> Ruaj Preferencat
                    </button>
                </div>
            </form>
        </div>

        <div class="dashboard-section" data-aos="fade-up">
            <h2><i class="fas fa-shield-alt"></i> Veprime të Llogarisë</h2>
            <div class="account-actions">
                <button class="btn btn-outline" onclick="exportData()">
                    <i class="fas fa-download"></i> Eksporto të Dhënat
                </button>
                <button class="btn btn-outline" onclick="requestDeletion()">
                    <i class="fas fa-user-times"></i> Kërko Fshirjen e Llogarisë
                </button>
                <button class="btn btn-danger" onclick="logoutEverywhere()">
                    <i class="fas fa-sign-out-alt"></i> Dil nga të Gjitha Paisjet
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        function savePreferences() {
            const formData = new FormData(document.querySelector('.preferences-form'));
            const preferences = {
                email_notifications: formData.get('email_notifications') === 'on',
                sms_notifications: formData.get('sms_notifications') === 'on',
                special_offers: formData.get('special_offers') === 'on'
            };
            
            localStorage.setItem('user_preferences', JSON.stringify(preferences));
            alert('Preferencat u ruajtën me sukses!');
        }

        function exportData() {
            if (confirm('Kjo do të eksportojë të gjitha të dhënat tuaja personale dhe historinë e rezervimeve. Vazhdo?')) {
                alert('Funksioni i eksportimit do të gjeneronte një skedar me të gjithë informacionin tuaj.');
            }
        }

        function requestDeletion() {
            if (confirm('Kjo do të kërkojë fshirjen e përhershme të llogarisë tuaj dhe të gjitha të dhënave shoqëruese. Ky veprim nuk mund të zhbëhet. Vazhdo?')) {
                const reason = prompt('Ju lutemi na tregoni pse dëshironi të fshini llogarinë tuaj:');
                if (reason !== null) {
                    alert('Kërkesa për fshirje të llogarisë u dërgua. Do ta përpunojmë brenda 30 ditëve.');
                }
            }
        }

        function logoutEverywhere() {
            if (confirm('Kjo do t\'ju nxjerrë nga të gjitha pajisjet. Vazhdo?')) {
                fetch('../api/logout_everywhere.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({user_id: <?php echo $_SESSION['user_id']; ?>})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('U dolt nga të gjitha pajisjet me sukses!');
                        window.location.href = 'logout.php';
                    } else {
                        alert('Gabim: ' + data.error);
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const savedPreferences = localStorage.getItem('user_preferences');
            if (savedPreferences) {
                const preferences = JSON.parse(savedPreferences);
                document.querySelector('input[name="email_notifications"]').checked = preferences.email_notifications;
                document.querySelector('input[name="sms_notifications"]').checked = preferences.sms_notifications;
                document.querySelector('input[name="special_offers"]').checked = preferences.special_offers;
            }
        });
    </script>
</body>
</html>