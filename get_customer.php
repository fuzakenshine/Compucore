<?php
require_once 'db_connect.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Customer ID is required'
    ]);
    exit;
}

$customer_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM customer WHERE PK_CUSTOMER_ID = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $customer_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();

    if ($customer) {
        echo json_encode([
            'success' => true,
            'data' => $customer
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Customer not found'
        ]);
    }
} catch (Exception $e) {
    error_log("Error in get_customer.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching customer data: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>