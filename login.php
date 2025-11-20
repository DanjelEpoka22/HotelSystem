<?php
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php'; // Shtoni këtë rresht

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (login($username, $password)) {
        // Redirect based on role
        switch ($_SESSION['role']) {
            case 'admin':
                redirect('admin/index.php');
                break;
            case 'receptionist':
                redirect('receptionist/index.php');
                break;
            case 'housekeeper':
                redirect('housekeeper/index.php');
                break;
            default:
                redirect('user/index.php');
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h2>Login to Your Account</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>