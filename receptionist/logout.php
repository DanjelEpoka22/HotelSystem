<?php
require_once '../config/config.php';
require_once '../includes/auth.php';

// Logout user
logout();

// Redirect to login page
redirect('../login.php');
?>