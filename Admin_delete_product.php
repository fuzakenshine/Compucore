<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id > 0) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First, get the product image filename
        $sql = "SELECT IMAGE FROM PRODUCTS WHERE PK_PRODUCT_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        // Delete references from cart table
        $delete_cart_sql = "DELETE FROM cart WHERE product_id = ?";
        $stmt = $conn->prepare($delete_cart_sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        // Delete the product from database
        $delete_sql = "DELETE FROM PRODUCTS WHERE PK_PRODUCT_ID = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        // If everything went well, commit the transaction
        $conn->commit();

        // If product was deleted successfully, delete the image file
        if ($product && $product['IMAGE']) {
            $image_path = 'uploads/' . $product['IMAGE'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        header('Location: Admin_product.php?deleted=1');
    } catch (Exception $e) {
        // If there was an error, rollback the transaction
        $conn->rollback();
        header('Location: Admin_product.php?error=1');
    }
} else {
    header('Location: Admin_product.php');
}
exit(); 