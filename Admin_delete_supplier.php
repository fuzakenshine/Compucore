<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

try {
    // Get POST data
    $raw_data = file_get_contents('php://input');
    if (empty($raw_data)) {
        throw new Exception('No data received');
    }

    // Decode JSON data
    $data = json_decode($raw_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    if (!isset($data['supplier_id'])) {
        throw new Exception('Supplier ID is required');
    }

    // Sanitize and validate supplier ID
    $supplier_id = filter_var($data['supplier_id'], FILTER_VALIDATE_INT);
    if ($supplier_id === false) {
        throw new Exception('Invalid supplier ID format');
    }

    // Update supplier status
    $stmt = $conn->prepare("UPDATE supplier SET STATUS = 'Inactive', UPDATE_AT = CURRENT_TIMESTAMP WHERE PK_SUPPLIER_ID = ?");
    $stmt->bind_param("i", $supplier_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('Supplier not found');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Supplier deactivated successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>