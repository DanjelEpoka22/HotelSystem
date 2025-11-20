<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAuth();

// Get current user data
$query = "SELECT * FROM users WHERE id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
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
            // Update session data
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            
            $success = "Profile updated successfully!";
            
            // Refresh user data
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Failed to update profile.";
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long.";
        } else {
            // Update password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $passwordQuery = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :user_id";
            $passwordStmt = $db->prepare($passwordQuery);
            $passwordStmt->bindParam(':password', $new_password_hash);
            $passwordStmt->bindParam(':user_id', $_SESSION['user_id']);
            
            if ($passwordStmt->execute()) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to change password.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo SITE_NAME; ?></title>
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
                <li><a href="profile.php" class="nav-link active">Profile</a></li>
                <li><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="user-container">
        <div class="user-content">
            <div class="user-header">
                <h1>My Profile</h1>
                <p>Manage your account information and preferences</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Profile Information -->
            <div class="dashboard-section">
                <h2>Personal Information</h2>
                <form method="POST" class="profile-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small class="form-text">Username cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Member Since</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo formatDate($user['created_at'], 'F j, Y'); ?>" disabled>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="dashboard-section">
                <h2>Change Password</h2>
                <form method="POST" class="password-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required 
                                   minlength="6" placeholder="At least 6 characters">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required 
                                   minlength="6">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>

            <!-- Account Statistics -->
            <div class="dashboard-section">
                <h2>Account Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-info">
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
                            <p>Total Bookings</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">🏨</div>
                        <div class="stat-info">
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
                            <p>Total Spent</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">⭐</div>
                        <div class="stat-info">
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
                            <p>Different Rooms</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">🎯</div>
                        <div class="stat-info">
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
                            <p>Average Booking</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preferences -->
            <div class="dashboard-section">
                <h2>Preferences</h2>
                <form class="preferences-form">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="email_notifications" checked>
                            <span class="checkmark"></span>
                            Email notifications for new offers and promotions
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="sms_notifications">
                            <span class="checkmark"></span>
                            SMS notifications for booking confirmations
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="special_offers" checked>
                            <span class="checkmark"></span>
                            Receive special offers and discounts
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-primary" onclick="savePreferences()">Save Preferences</button>
                    </div>
                </form>
            </div>

            <!-- Account Actions -->
            <div class="dashboard-section">
                <h2>Account Actions</h2>
                <div class="account-actions">
                    <button class="btn btn-outline" onclick="exportData()">Export My Data</button>
                    <button class="btn btn-outline" onclick="requestDeletion()">Request Account Deletion</button>
                    <button class="btn btn-danger" onclick="logoutEverywhere()">Logout from All Devices</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
    function savePreferences() {
        const formData = new FormData(document.querySelector('.preferences-form'));
        const preferences = {
            email_notifications: formData.get('email_notifications') === 'on',
            sms_notifications: formData.get('sms_notifications') === 'on',
            special_offers: formData.get('special_offers') === 'on'
        };
        
        // In a real application, this would save to the server
        localStorage.setItem('user_preferences', JSON.stringify(preferences));
        alert('Preferences saved successfully!');
    }

    function exportData() {
        if (confirm('This will export all your personal data and booking history. Continue?')) {
            // In a real application, this would generate and download a file
            alert('Data export feature would generate a file with all your information.');
        }
    }

    function requestDeletion() {
        if (confirm('This will request permanent deletion of your account and all associated data. This action cannot be undone. Continue?')) {
            const reason = prompt('Please tell us why you want to delete your account:');
            if (reason !== null) {
                // In a real application, this would send a deletion request
                alert('Account deletion request submitted. We will process it within 30 days.');
            }
        }
    }

    function logoutEverywhere() {
        if (confirm('This will log you out from all devices. Continue?')) {
            fetch('../api/logout_everywhere.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: <?php echo $_SESSION['user_id']; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Logged out from all devices successfully!');
                    window.location.href = 'logout.php';
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred.');
            });
        }
    }

    // Load saved preferences
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

    <style>
    .profile-form,
    .password-form,
    .preferences-form {
        max-width: 600px;
    }

    .form-text {
        color: #6c757d;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        cursor: pointer;
        margin-bottom: 1rem;
    }

    .checkbox-label input {
        margin-right: 0.5rem;
    }

    .checkmark {
        width: 20px;
        height: 20px;
        border: 2px solid #ddd;
        border-radius: 3px;
        margin-right: 0.5rem;
        position: relative;
    }

    .checkbox-label input:checked + .checkmark {
        background: #3498db;
        border-color: #3498db;
    }

    .checkbox-label input:checked + .checkmark:after {
        content: '✓';
        color: white;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 14px;
        font-weight: bold;
    }

    .account-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid #3498db;
        color: #3498db;
    }

    .btn-outline:hover {
        background: #3498db;
        color: white;
    }

    @media (max-width: 768px) {
        .account-actions {
            flex-direction: column;
        }
        
        .account-actions .btn {
            width: 100%;
        }
    }
    </style>
</body>
</html>