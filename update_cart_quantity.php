<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = $_POST['cart_id'];
    $action = $_POST['action'];
    
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE cart_id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current = $result->fetch_assoc();
    
    if ($action === 'increase') {
        $new_quantity = $current['quantity'] + 1;
    } elseif ($action === 'decrease') {
        $new_quantity = max(1, $current['quantity'] - 1);
    } else {
        $new_quantity = max(1, min(99, intval($_POST['value'])));
    }
    
    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
    $update->bind_param("ii", $new_quantity, $cart_id);
    
    if ($update->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
}