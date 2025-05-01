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
    
    // First get the image filename to delete it
    $stmt = $conn->prepare("SELECT IMAGE FROM PRODUCTS WHERE PK_PROD_ID = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    // Delete the product from database
    $stmt = $conn->prepare("DELETE FROM PRODUCTS WHERE PK_PROD_ID = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        // Delete the image file if it exists
        if ($product && $product['IMAGE']) {
            $image_path = 'uploads/' . $product['IMAGE'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 