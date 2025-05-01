<?php
require_once 'includes/auth.php';
checkAdminAccess();
include 'db_connect.php';

// Get supplier ID from URL
$supplier_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($supplier_id === 0) {
    header('Location: Admin_suppliers.php');
    exit();
}

// Get supplier image before deletion
$stmt = $conn->prepare("SELECT SUPPLIER_IMAGE FROM supplier WHERE PK_SUPPLIER_ID = ?");
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$result = $stmt->get_result();
$supplier = $result->fetch_assoc();

// Delete supplier from database
$stmt = $conn->prepare("DELETE FROM supplier WHERE PK_SUPPLIER_ID = ?");
$stmt->bind_param("i", $supplier_id);

if ($stmt->execute()) {
    // Delete supplier image if exists
    if ($supplier && $supplier['SUPPLIER_IMAGE']) {
        $image_path = "uploads/" . $supplier['SUPPLIER_IMAGE'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    header('Location: Admin_suppliers.php');
    exit();
} else {
    // Handle error
    header('Location: Admin_suppliers.php?error=delete_failed');
    exit();
}
?> 