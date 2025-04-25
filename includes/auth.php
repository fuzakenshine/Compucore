<?php
require_once 'session_timeout.php';

function checkAuthentication() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    checkSessionTimeout();
    
    if (!isset($_SESSION['loggedin'])) {
        error_log("Unauthorized access attempt - IP: " . $_SERVER['REMOTE_ADDR']);
        session_unset();
        session_destroy();
        header("Location: ../login.php?error=unauthorized");
        exit();
    }
}

function checkAdminAccess() {
    checkAuthentication();
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        error_log("Unauthorized admin access attempt - User: " . ($_SESSION['email'] ?? 'unknown'));
        header("Location: ../index.php?error=unauthorized");
        exit();
    }
}

function checkCustomerAccess() {
    checkAuthentication();
    if (!isset($_SESSION['customer_id'])) {
        header("Location: ../login.php?error=unauthorized");
        exit();
    }
}