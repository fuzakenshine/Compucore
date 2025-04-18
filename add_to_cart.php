<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $customer_id = $_SESSION['customer_id'];
    $quantity = 1; // Default quantity

    // Check if product already exists in cart
    $check_sql = "SELECT * FROM cart WHERE customer_id = ? AND product_id = ? ORDER BY created_at DESC";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $customer_id, $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity if product exists
        $sql = "UPDATE cart SET quantity = quantity + 1, created_at = CURRENT_TIMESTAMP WHERE customer_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $customer_id, $product_id);
    } else {
        // Insert new product if it doesn't exist
        $sql = "INSERT INTO cart (customer_id, product_id, product_name, product_price, quantity, created_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisdi", $customer_id, $product_id, $product_name, $product_price, $quantity);
    }

    if ($stmt->execute()) {
        $_SESSION['cart_message'] = "Product added to cart successfully!";
        header('Location: index.php?message=Product+successfully+added+to+cart#product-' . $product_id);
        exit();
    } else {
        header('Location: index.php?message=Error adding to cart');
        exit();
    }
}
$conn->close();
?>