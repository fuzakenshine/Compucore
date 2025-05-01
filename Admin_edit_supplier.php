<?php
require_once 'includes/auth.php';
checkAdminAccess();
include 'db_connect.php';

header('Content-Type: application/json');

// Get supplier ID from POST data
$supplier_id = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0;

if ($supplier_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid supplier ID']);
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $company = $_POST['company'];
    
    // Handle image upload
    $image = $_FILES['image'];
    $image_path = '';
    
    if ($image['size'] > 0) {
        $target_dir = "uploads/";
        $file_extension = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($image["tmp_name"], $target_file)) {
            $image_path = $new_filename;
        }
    }
    
    // Update supplier information
    $sql = "UPDATE supplier SET 
            S_FNAME = ?, 
            S_LNAME = ?, 
            EMAIL = ?, 
            COMPANY_NAME = ?";
    
    $params = [$fname, $lname, $email, $company];
    $types = "ssss";
    
    if ($image_path) {
        $sql .= ", SUPPLIER_IMAGE = ?";
        $params[] = $image_path;
        $types .= "s";
    }
    
    $sql .= " WHERE PK_SUPPLIER_ID = ?";
    $params[] = $supplier_id;
    $types .= "i";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit();
}

// If we get here, it's an invalid request
echo json_encode(['success' => false, 'message' => 'Invalid request method']);
exit(); 