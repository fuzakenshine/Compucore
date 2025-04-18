<?php
session_start();
include 'db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id'];
    $sql = "DELETE FROM cart WHERE cart_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    
    if ($stmt->execute()) {
        header('Location: cart.php?message=Item successfully cancelled');
    } else {
        header('Location: cart.php?message=Error cancelling item');
    }
    exit();
}
?>
