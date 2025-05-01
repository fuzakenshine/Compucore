<?php
session_start();
include 'db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];
    
    // Handle image upload if a new image is provided
    $image_update = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image
                $stmt = $conn->prepare("SELECT IMAGE FROM PRODUCTS WHERE PK_PROD_ID = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $old_image = $result->fetch_assoc()['IMAGE'];
                
                if ($old_image && file_exists($upload_dir . $old_image)) {
                    unlink($upload_dir . $old_image);
                }
                
                $image_update = ", IMAGE = ?";
            }
        }
    }
    
    // Update product information
    $sql = "UPDATE PRODUCTS SET PROD_NAME = ?, PRICE = ?, QTY = ?, DESCRIPTION = ?" . $image_update . " WHERE PK_PROD_ID = ?";
    $stmt = $conn->prepare($sql);
    
    if ($image_update) {
        $stmt->bind_param("sdisi", $product_name, $price, $quantity, $description, $new_filename, $product_id);
    } else {
        $stmt->bind_param("sdisi", $product_name, $price, $quantity, $description, $product_id);
    }
    
    if ($stmt->execute()) {
        header('Location: Admin_product.php?success=1');
    } else {
        header('Location: Admin_product.php?error=1');
    }
} else {
    header('Location: Admin_product.php');
}
?> 