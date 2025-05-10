<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

if (isset($_GET['id'])) {
    $customer_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    
    $stmt = $conn->prepare("SELECT PK_CUSTOMER_ID, F_NAME, L_NAME, EMAIL, PHONE_NUM, 
                           CUSTOMER_ADDRESS, PROFILE_PIC 
                           FROM customer WHERE PK_CUSTOMER_ID = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($customer = $result->fetch_assoc()) {
        // Add default avatar if no profile picture
        if (empty($customer['PROFILE_PIC'])) {
            $customer['PROFILE_PIC'] = 'default-avatar.png';
        }
        echo json_encode($customer);
    } else {
        echo json_encode(['error' => 'Customer not found']);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}