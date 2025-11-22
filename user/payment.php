<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAuth();

$reservation_id = $_GET['id'] ?? '';
if (empty($reservation_id)) {
    header('Location: my_reservations.php');
    exit;
}

$query = "SELECT r.*, rm.room_number, rm.room_type, rm.price_per_night 
         FROM reservations r 
         JOIN rooms rm ON r.room_id = rm.id 
         WHERE r.id = :reservation_id AND r.user_id = :user_id AND r.payment_status = 'pending'";
$stmt = $db->prepare($query);
$stmt->bindParam(':reservation_id', $reservation_id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    header('Location: my_reservations.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = 'card';
    $card_number = str_replace(' ', '', $_POST['card_number']);
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];
    $card_holder = $_POST['card_holder'];
    
    $errors = [];
    
    if (strlen($card_number) !== 16 || !is_numeric($card_number)) {
        $errors[] = "Numri i kartës është i pavlefshëm";
    }
    
    if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $expiry_date)) {
        $errors[] = "Formati i datës (MM/YY) është i pavlefshëm";
    }
    
    if (strlen($cvv) !== 3 || !is_numeric($cvv)) {
        $errors[] = "CVV është i pavlefshëm";
    }
    
    if (empty($card_holder)) {
        $errors[] = "Emri i mbajtësit të kartës është i detyrueshëm";
    }
    
    if (empty($errors)) {
        $transaction_id = 'TXN_' . uniqid() . '_' . time();
        
        $db->beginTransaction();
        
        try {
            $updateQuery = "UPDATE reservations SET payment_status = 'paid' WHERE id = :reservation_id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':reservation_id', $reservation_id);
            $updateStmt->execute();
            
            $paymentQuery = "INSERT INTO payments (reservation_id, amount, payment_method, transaction_id, status, payment_date) 
                           VALUES (:reservation_id, :amount, :payment_method, :transaction_id, 'completed', NOW())";
            $paymentStmt = $db->prepare($paymentQuery);
            $paymentStmt->bindParam(':reservation_id', $reservation_id);
            $paymentStmt->bindParam(':amount', $reservation['total_price']);
            $paymentStmt->bindParam(':payment_method', $payment_method);
            $paymentStmt->bindParam(':transaction_id', $transaction_id);
            $paymentStmt->execute();
            
            $db->commit();
            
            $success = "Pagesa u përpunua me sukses!";
            $payment_complete = true;
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Përpunimi i pagesës dështoi: " . $e->getMessage();
        }
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
    <title>Pagesa - Villa Adrian</title>
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

        .nav-link:hover {
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

        .alert i {
            font-size: 1.5rem;
        }

        .payment-success {
            background: white;
            padding: 3rem;
            border-radius: 25px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .payment-success h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: #28a745;
            margin-bottom: 1.5rem;
        }

        .success-details {
            background: var(--light-color);
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem auto;
            max-width: 500px;
            text-align: left;
        }

        .success-details p {
            margin: 1rem 0;
            display: flex;
            justify-content: space-between;
        }

        .success-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .payment-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .payment-summary,
        .payment-form-section {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .payment-summary h2,
        .payment-form-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .summary-card {
            background: var(--light-color);
            padding: 2rem;
            border-radius: 15px;
            border-left: 5px solid var(--secondary-color);
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #ddd;
        }

        .summary-item.total {
            border: none;
            font-size: 1.3rem;
            font-weight: 700;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 3px solid var(--secondary-color);
            color: var(--primary-color);
        }

        .amount {
            color: #28a745;
            font-size: 1.5rem;
            font-weight: 700;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .security-notice {
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            margin: 1.5rem 0;
        }

        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #28a745;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .btn {
            padding: 1rem 2rem;
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

        .btn-secondary {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-lg {
            padding: 1.2rem 3rem;
            font-size: 1.1rem;
            width: 100%;
            justify-content: center;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .payment-methods {
            display: flex;
            gap: 1.5rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .payment-method {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
        }

        .method-icon {
            font-size: 1.5rem;
        }

        .security-info {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
        }

        .security-info h3 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .security-info ul {
            list-style: none;
            padding: 0;
        }

        .security-info li {
            padding: 0.75rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        @media (max-width: 768px) {
            .user-container {
                margin: 80px auto 1rem;
                padding: 0 1rem;
            }

            .payment-container {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .success-actions {
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
                <li><a href="my_reservations.php" class="nav-link"><i class="fas fa-calendar"></i> Rezervimet</a></li>
                <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="user-container">
        <div class="user-header" data-aos="fade-down">
            <h1>Përfundoni Pagesën</h1>
            <p>Pagesë e sigurt për rezervimin tuaj</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success" data-aos="fade-up">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $success; ?></span>
            </div>
            
            <?php if ($payment_complete): ?>
            <div class="payment-success" data-aos="zoom-in">
                <h2><i class="fas fa-check-circle"></i> Pagesa u Krye me Sukses!</h2>
                <p>Rezervimi juaj është konfirmuar dhe pagesa është përpunuar.</p>
                <div class="success-details">
                    <p><strong>ID e Rezervimit:</strong> <span>#<?php echo $reservation_id; ?></span></p>
                    <p><strong>ID e Transaksionit:</strong> <span><?php echo $transaction_id; ?></span></p>
                    <p><strong>Shuma e Paguar:</strong> <span class="amount">€<?php echo $reservation['total_price']; ?></span></p>
                    <p><strong>Data e Pagesës:</strong> <span><?php echo date('d/m/Y H:i'); ?></span></p>
                </div>
                <div class="success-actions">
                    <a href="my_reservations.php" class="btn btn-primary">
                        <i class="fas fa-calendar"></i> Shiko Rezervimet
                    </a>
                    <a href="../index.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Kthehu në Home
                    </a>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($error) && !isset($success)): ?>
            <div class="alert alert-error" data-aos="fade-up">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <?php if (!isset($payment_complete)): ?>
        <div class="payment-container" data-aos="fade-up">
            <div class="payment-summary">
                <h2><i class="fas fa-file-invoice"></i> Përmbledhje</h2>
                <div class="summary-card">
                    <div class="summary-item">
                        <strong>Dhoma:</strong>
                        <span><?php echo getRoomTypeName($reservation['room_type']); ?> (<?php echo $reservation['room_number']; ?>)</span>
                    </div>
                    <div class="summary-item">
                        <strong>Check-in:</strong>
                        <span><?php echo formatDate($reservation['check_in']); ?></span>
                    </div>
                    <div class="summary-item">
                        <strong>Check-out:</strong>
                        <span><?php echo formatDate($reservation['check_out']); ?></span>
                    </div>
                    <div class="summary-item">
                        <strong>Mysafirë:</strong>
                        <span><?php echo $reservation['guests']; ?></span>
                    </div>
                    <div class="summary-item total">
                        <strong>Totali:</strong>
                        <span class="amount">€<?php echo $reservation['total_price']; ?></span>
                    </div>
                </div>
            </div>

            <div class="payment-form-section">
                <h2><i class="fas fa-credit-card"></i> Detajet e Pagesës</h2>
                <form method="POST" id="paymentForm">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user"></i> Mbajtësi i Kartës</label>
                        <input type="text" name="card_holder" class="form-control" 
                               placeholder="Emri i plotë" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-credit-card"></i> Numri i Kartës</label>
                        <input type="text" name="card_number" class="form-control" 
                               placeholder="1234 5678 9012 3456" 
                               maxlength="19" 
                               oninput="formatCardNumber(this)" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-calendar"></i> Data e Skadimit</label>
                            <input type="text" name="expiry_date" class="form-control" 
                                   placeholder="MM/YY" 
                                   maxlength="5" 
                                   oninput="formatExpiryDate(this)" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-lock"></i> CVV</label>
                            <input type="text" name="cvv" class="form-control" 
                                   placeholder="123" 
                                   maxlength="3" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        </div>
                    </div>

                    <div class="security-notice">
                        <div class="secure-badge">
                            <i class="fas fa-lock"></i> Pagesë e Sigurt
                        </div>
                        <p>Informacioni juaj i pagesës është i enkriptuar dhe i sigurt.</p>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check-circle"></i> Paguaj €<?php echo $reservation['total_price']; ?>
                        </button>
                        <a href="my_reservations.php" class="btn btn-secondary" style="flex: 1;">
                            <i class="fas fa-times"></i> Anulo
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="dashboard-section" style="background: white; padding: 2.5rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); margin-top: 2rem;" data-aos="fade-up">
            <h2 style="font-family: 'Playfair Display', serif; color: var(--primary-color); margin-bottom: 1.5rem;">
                <i class="fas fa-shield-alt"></i> Mënyrat e Pagesës të Pranuara
            </h2>
            <div class="payment-methods">
                <div class="payment-method">
                    <span class="method-icon">💳</span>
                    <span>Visa</span>
                </div>
                <div class="payment-method">
                    <span class="method-icon">💳</span>
                    <span>MasterCard</span>
                </div>
                <div class="payment-method">
                    <span class="method-icon">💳</span>
                    <span>American Express</span>
                </div>
            </div>
            
            <div class="security-info">
                <h3>Siguria & Privatësia</h3>
                <ul>
                    <li><i class="fas fa-check-circle" style="color: #28a745;"></i> Lidhje e enkriptuar SSL</li>
                    <li><i class="fas fa-check-circle" style="color: #28a745;"></i> PCI DSS compliant</li>
                    <li><i class="fas fa-check-circle" style="color: #28a745;"></i> Autentifikim 3D Secure</li>
                    <li><i class="fas fa-check-circle" style="color: #28a745;"></i> Mbrojtje nga mashtrimi</li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        function formatCardNumber(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            input.value = value.substring(0, 19);
        }

        function formatExpiryDate(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            input.value = value.substring(0, 5);
        }

        document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
            const cardNumber = document.querySelector('input[name="card_number"]').value.replace(/\s/g, '');
            const expiryDate = document.querySelector('input[name="expiry_date"]').value;
            const cvv = document.querySelector('input[name="cvv"]').value;
            
            if (cardNumber.length !== 16) {
                e.preventDefault();
                alert('Ju lutemi vendosni një numër të vlefshëm karte 16-shifror.');
                return;
            }
            
            if (!/^(0[1-9]|1[0-2])\/[0-9]{2}$/.test(expiryDate)) {
                e.preventDefault();
                alert('Ju lutemi vendosni një datë të vlefshme në formatin MM/YY.');
                return;
            }
            
            const [month, year] = expiryDate.split('/');
            const expiry = new Date(2000 + parseInt(year), parseInt(month) - 1);
            const today = new Date();
            
            if (expiry < today) {
                e.preventDefault();
                alert('Kjo kartë ka skaduar. Ju lutemi përdorni një kartë të vlefshme.');
                return;
            }
            
            if (cvv.length !== 3) {
                e.preventDefault();
                alert('Ju lutemi vendosni një CVV të vlefshëm 3-shifror.');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Duke Përpunuar...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>