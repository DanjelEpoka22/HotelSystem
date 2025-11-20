<?php
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php'; // Shtoni këtë rresht

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_data = [
        'username' => sanitizeInput($_POST['username']),
        'email' => sanitizeInput($_POST['email']),
        'password' => $_POST['password'],
        'first_name' => sanitizeInput($_POST['first_name']),
        'last_name' => sanitizeInput($_POST['last_name']),
        'phone' => sanitizeInput($_POST['phone'])
    ];
    
    // Validate password confirmation
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Passwords do not match";
    } else {
        if (register($user_data)) {
            // Auto login after registration
            if (login($user_data['username'], $user_data['password'])) {
                redirect('user/index.php');
            }
        } else {
            $error = "Registration failed. Username or email might be taken.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h2>Create Your Account</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>