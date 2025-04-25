<?php
session_start();

// Store the user type for redirect
$was_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Clear all session variables
session_unset();

// Destroy the session
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Start new session for message
session_start();
$_SESSION['success'] = 'You have been successfully logged out';

header('Location: login.php');
exit();