<?php
session_start();
include 'db_connect.php'; // Include the database connector

// Fetch all cart items
$sql = "SELECT * FROM cart";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Create a new order
    $order_date = date('Y-m-d H:i:s');
    $status = 'Pending';
    $conn->query("INSERT INTO orders (order_date, status) VALUES ('$order_date', '$status')");
    $order_id = $conn->insert_id;

    // Add cart items to order details
    while ($row = $result->fetch_assoc()) {
        $product_id = $row['product_id'];
        $quantity = $row['quantity'];
        $price = $row['product_price'];
        $conn->query("INSERT INTO order_detail (order_id, product_id, quantity, price) VALUES ('$order_id', '$product_id', '$quantity', '$price')");
    }

    // Clear the cart
    $conn->query("DELETE FROM cart");

    // Redirect to a success page
    header('Location: order_success.php');
    exit;
} else {
    echo "Your cart is empty.";
}
?>