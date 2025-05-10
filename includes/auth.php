<?php
require_once 'session_timeout.php';

// Move session_start() to the top of the file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAuthentication() {
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

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

function getUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}