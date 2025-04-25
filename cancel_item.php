<?php
session_start();
include 'db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['loggedin'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id'];
    $sql = "DELETE FROM cart WHERE cart_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->bind_param("i", $cart_id) && $stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item cancelled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error cancelling item']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
