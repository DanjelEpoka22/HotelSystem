<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAuth();

// Get reservation details
$reservation_id = $_GET['id'] ?? '';
if (empty($reservation_id)) {
    header('Location: my_reservations.php');
    exit;
}

// Verify reservation belongs to user
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

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = 'card'; // This page is for card payments only
    $card_number = str_replace(' ', '', $_POST['card_number']);
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];
    $card_holder = $_POST['card_holder'];
    
    // Basic validation
    $errors = [];
    
    if (strlen($card_number) !== 16 || !is_numeric($card_number)) {
        $errors[] = "Invalid card number";
    }
    
    if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $expiry_date)) {
        $errors[] = "Invalid expiry date format (MM/YY)";
    }
    
    if (strlen($cvv) !== 3 || !is_numeric($cvv)) {
        $errors[] = "Invalid CVV";
    }
    
    if (empty($card_holder)) {
        $errors[] = "Card holder name is required";
    }
    
    if (empty($errors)) {
        // Process payment (in a real application, this would integrate with a payment gateway)
        $transaction_id = 'TXN_' . uniqid() . '_' . time();
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Update reservation payment status
            $updateQuery = "UPDATE reservations SET payment_status = 'paid' WHERE id = :reservation_id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':reservation_id', $reservation_id);
            $updateStmt->execute();
            
            // Create payment record
            $paymentQuery = "INSERT INTO payments (reservation_id, amount, payment_method, transaction_id, status, payment_date) 
                           VALUES (:reservation_id, :amount, :payment_method, :transaction_id, 'completed', NOW())";
            $paymentStmt = $db->prepare($paymentQuery);
            $paymentStmt->bindParam(':reservation_id', $reservation_id);
            $paymentStmt->bindParam(':amount', $reservation['total_price']);
            $paymentStmt->bindParam(':payment_method', $payment_method);
            $paymentStmt->bindParam(':transaction_id', $transaction_id);
            $paymentStmt->execute();
            
            $db->commit();
            
            $success = "Payment processed successfully!";
            $payment_complete = true;
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Payment processing failed: " . $e->getMessage();
        }
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
    <title>Payment - <?php echo SITE_NAME; ?></title>
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
                <li><a href="book.php" class="nav-link">Book Room</a></li>
                <li><a href="my_reservations.php" class="nav-link">My Reservations</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
                <li><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="user-container">
        <div class="user-content">
            <div class="user-header">
                <h1>Complete Your Payment</h1>
                <p>Secure payment processing for your reservation</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                
                <?php if ($payment_complete): ?>
                <div class="payment-success">
                    <h2>🎉 Payment Successful!</h2>
                    <p>Your reservation has been confirmed and payment has been processed.</p>
                    <div class="success-details">
                        <p><strong>Reservation ID:</strong> #<?php echo $reservation_id; ?></p>
                        <p><strong>Transaction ID:</strong> <?php echo $transaction_id; ?></p>
                        <p><strong>Amount Paid:</strong> €<?php echo $reservation['total_price']; ?></p>
                        <p><strong>Payment Date:</strong> <?php echo date('F j, Y g:i A'); ?></p>
                    </div>
                    <div class="success-actions">
                        <a href="my_reservations.php" class="btn btn-primary">View My Reservations</a>
                        <a href="../index.php" class="btn btn-secondary">Return to Home</a>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($error) && !isset($success)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!isset($payment_complete)): ?>
            <!-- Payment Form -->
            <div class="payment-container">
                <div class="payment-summary">
                    <h2>Reservation Summary</h2>
                    <div class="summary-card">
                        <div class="summary-item">
                            <strong>Room:</strong>
                            <span><?php echo getRoomTypeName($reservation['room_type']); ?> (Room <?php echo $reservation['room_number']; ?>)</span>
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
                            <strong>Guests:</strong>
                            <span><?php echo $reservation['guests']; ?></span>
                        </div>
                        <div class="summary-item total">
                            <strong>Total Amount:</strong>
                            <span class="amount">€<?php echo $reservation['total_price']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="payment-form-section">
                    <h2>Payment Details</h2>
                    <form method="POST" class="payment-form" id="paymentForm">
                        <div class="form-group">
                            <label class="form-label">Card Holder Name</label>
                            <input type="text" name="card_holder" class="form-control" 
                                   placeholder="John Doe" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Card Number</label>
                            <input type="text" name="card_number" class="form-control" 
                                   placeholder="1234 5678 9012 3456" 
                                   maxlength="19" 
                                   oninput="formatCardNumber(this)" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Expiry Date</label>
                                <input type="text" name="expiry_date" class="form-control" 
                                       placeholder="MM/YY" 
                                       maxlength="5" 
                                       oninput="formatExpiryDate(this)" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">CVV</label>
                                <input type="text" name="cvv" class="form-control" 
                                       placeholder="123" 
                                       maxlength="3" 
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            </div>
                        </div>

                        <div class="security-notice">
                            <div class="secure-badge">
                                🔒 Secure Payment
                            </div>
                            <p>Your payment information is encrypted and secure. We do not store your card details.</p>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg">Pay €<?php echo $reservation['total_price']; ?></button>
                            <a href="my_reservations.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payment Methods Info -->
            <div class="dashboard-section">
                <h2>Accepted Payment Methods</h2>
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
                    <h3>Security & Privacy</h3>
                    <ul>
                        <li>🔒 SSL encrypted connection</li>
                        <li>💰 PCI DSS compliant</li>
                        <li>📱 3D Secure authentication</li>
                        <li>🛡️ Fraud protection</li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
    function formatCardNumber(input) {
        // Remove all non-digit characters
        let value = input.value.replace(/\D/g, '');
        
        // Add spaces every 4 digits
        value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
        
        // Update input value
        input.value = value.substring(0, 19); // Max 16 digits + 3 spaces
    }

    function formatExpiryDate(input) {
        let value = input.value.replace(/\D/g, '');
        
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        
        input.value = value.substring(0, 5);
    }

    // Form validation
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const cardNumber = document.querySelector('input[name="card_number"]').value.replace(/\s/g, '');
        const expiryDate = document.querySelector('input[name="expiry_date"]').value;
        const cvv = document.querySelector('input[name="cvv"]').value;
        
        if (cardNumber.length !== 16) {
            e.preventDefault();
            alert('Please enter a valid 16-digit card number.');
            return;
        }
        
        if (!/^(0[1-9]|1[0-2])\/[0-9]{2}$/.test(expiryDate)) {
            e.preventDefault();
            alert('Please enter a valid expiry date in MM/YY format.');
            return;
        }
        
        // Check if card is not expired
        const [month, year] = expiryDate.split('/');
        const expiry = new Date(2000 + parseInt(year), parseInt(month) - 1);
        const today = new Date();
        
        if (expiry < today) {
            e.preventDefault();
            alert('This card has expired. Please use a valid card.');
            return;
        }
        
        if (cvv.length !== 3) {
            e.preventDefault();
            alert('Please enter a valid 3-digit CVV.');
            return;
        }
        
        // Show processing state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.textContent = 'Processing...';
        submitBtn.disabled = true;
    });
    </script>

    <style>
    .payment-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .payment-summary h2,
    .payment-form-section h2 {
        color: #2c3e50;
        margin-bottom: 1rem;
    }

    .summary-card {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        border-left: 4px solid #3498db;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e9ecef;
    }

    .summary-item.total {
        border-bottom: none;
        font-size: 1.2rem;
        font-weight: bold;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 2px solid #3498db;
    }

    .amount {
        color: #27ae60;
        font-size: 1.3rem;
    }

    .payment-form {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
    }

    .security-notice {
        background: #e8f4fd;
        padding: 1rem;
        border-radius: 5px;
        margin: 1rem 0;
        text-align: center;
    }

    .secure-badge {
        display: inline-block;
        background: #27ae60;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .payment-success {
        text-align: center;
        padding: 2rem;
        background: #f8f9fa;
        border-radius: 10px;
        margin: 2rem 0;
    }

    .payment-success h2 {
        color: #27ae60;
        margin-bottom: 1rem;
    }

    .success-details {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        margin: 1.5rem 0;
        text-align: left;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }

    .success-details p {
        margin: 0.5rem 0;
    }

    .success-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .payment-methods {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .payment-method {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
        padding: 0.75rem 1rem;
        border-radius: 5px;
        border: 1px solid #e9ecef;
    }

    .method-icon {
        font-size: 1.2rem;
    }

    .security-info ul {
        list-style: none;
        padding: 0;
    }

    .security-info li {
        padding: 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    @media (max-width: 768px) {
        .payment-container {
            grid-template-columns: 1fr;
        }
        
        .success-actions {
            flex-direction: column;
        }
        
        .success-actions .btn {
            width: 100%;
        }
    }
    </style>
</body>
</html>