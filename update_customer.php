<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['customer_id', 'f_name', 'l_name', 'email', 'phone_num', 'address'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                throw new Exception("$field is required");
            }
        }

        // Sanitize inputs
        $customer_id = filter_var($_POST['customer_id'], FILTER_SANITIZE_NUMBER_INT);
        $f_name = filter_var($_POST['f_name'], FILTER_SANITIZE_STRING);
        $l_name = filter_var($_POST['l_name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone_num = filter_var($_POST['phone_num'], FILTER_SANITIZE_STRING);
        $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);

        // Start transaction
        $conn->begin_transaction();

        // Update customer information
        $stmt = $conn->prepare("UPDATE customer SET 
            F_NAME = ?, 
            L_NAME = ?, 
            EMAIL = ?, 
            PHONE_NUM = ?, 
            CUSTOMER_ADDRESS = ?,
            UPDATE_AT = CURRENT_TIMESTAMP 
            WHERE PK_CUSTOMER_ID = ?");
        
        $stmt->bind_param("sssssi", 
            $f_name, 
            $l_name, 
            $email, 
            $phone_num, 
            $address, 
            $customer_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to update customer: " . $conn->error);
        }

        // Check if any rows were affected
        if ($stmt->affected_rows === 0) {
            throw new Exception("No changes were made or customer not found");
        }

        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Customer updated successfully'
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close();