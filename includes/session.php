<?php
// Session management functions

function startSecureSession() {
    $session_name = 'villa_adrian_session';
    $secure = true; // Set to true if using HTTPS
    $httponly = true; // Prevent JavaScript access to session ID
    
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    
    if ($secure) {
        ini_set('session.cookie_secure', 1);
    }
    
    session_name($session_name);
    session_start();
    session_regenerate_id(true); // Regenerate session ID to prevent fixation
}

function destroySession() {
    $_SESSION = array();
    
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
    
    session_destroy();
}

function validateSession() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
        return false;
    }
    
    // Session timeout (30 minutes)
    $timeout_duration = 1800;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
        destroySession();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    
    // Validate IP address (optional - can be strict for security)
    if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
        destroySession();
        return false;
    }
    
    return true;
}

function initializeSessionSecurity() {
    if (!isset($_SESSION['user_ip'])) {
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
    }
    
    if (!isset($_SESSION['user_agent'])) {
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }
    
    $_SESSION['last_activity'] = time();
}

function checkSessionHijacking() {
    if (!isset($_SESSION['user_ip']) || $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
        return false;
    }
    
    if (!isset($_SESSION['user_agent']) || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        return false;
    }
    
    return true;
}
?>