<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if (isset($_GET['id'])) {
    try {
        $supplier_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
        
        // Prepare and execute the query
        $stmt = $conn->prepare("SELECT PK_SUPPLIER_ID, S_FNAME, S_LNAME, EMAIL, COMPANY_NAME, SUPPLIER_IMAGE 
                               FROM supplier 
                               WHERE PK_SUPPLIER_ID = ? AND STATUS = 'Active'");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $supplier_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $supplier = $result->fetch_assoc();
        
        if ($supplier) {
            echo json_encode([
                'success' => true,
                'supplier' => $supplier
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Supplier not found'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No supplier ID provided'
    ]);
}

$conn->close();
?> 