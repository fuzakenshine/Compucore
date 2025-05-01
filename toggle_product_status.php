<?php
session_start();
include 'db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $current_status = $_POST['current_status'];
    
    // Toggle status
    $new_status = $current_status === 'available' ? 'unavailable' : 'available';
    
    $stmt = $conn->prepare("UPDATE PRODUCTS SET STATUS = ? WHERE PK_PROD_ID = ?");
    $stmt->bind_param("si", $new_status, $product_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 