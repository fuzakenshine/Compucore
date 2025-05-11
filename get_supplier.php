<?php
require_once 'db_connect.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Supplier ID is required'
    ]);
    exit;
}

$supplier_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM supplier WHERE PK_SUPPLIER_ID = ?");
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
            'data' => $supplier
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Supplier not found'
        ]);
    }
} catch (Exception $e) {
    error_log("Error in get_supplier.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching supplier data: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?> 