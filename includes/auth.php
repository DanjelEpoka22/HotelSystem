<?php
// Authentication functions

function login($username, $password) {
    global $db;
    
    $query = "SELECT id, username, password, role, first_name, last_name, is_active 
              FROM users 
              WHERE username = :username OR email = :username";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($password, $user['password']) && $user['is_active']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            
            return true;
        }
    }
    
    return false;
}

function register($user_data) {
    global $db;
    
    $query = "INSERT INTO users (username, email, password, first_name, last_name, phone, role) 
              VALUES (:username, :email, :password, :first_name, :last_name, :phone, 'user')";
    
    $stmt = $db->prepare($query);
    
    $password_hash = password_hash($user_data['password'], PASSWORD_DEFAULT);
    
    $stmt->bindParam(':username', $user_data['username']);
    $stmt->bindParam(':email', $user_data['email']);
    $stmt->bindParam(':password', $password_hash);
    $stmt->bindParam(':first_name', $user_data['first_name']);
    $stmt->bindParam(':last_name', $user_data['last_name']);
    $stmt->bindParam(':phone', $user_data['phone']);
    
    return $stmt->execute();
}

function logout() {
    session_destroy();
    redirect('../login.php');
}

function requireAuth() {
    if (!isLoggedIn()) {
        redirect('../login.php');
    }
}

function requireRole($role) {
    requireAuth();
    if (!hasRole($role)) {
        http_response_code(403);
        die("Access denied. You don't have permission to access this page.");
    }
}
?>