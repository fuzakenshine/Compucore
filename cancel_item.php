<?php
session_start();
include 'db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the cart_id to be deleted
    $cart_id = $_POST['cart_id'];

    // Prepare the SQL to delete the item
    $sql = "DELETE FROM cart WHERE cart_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $cart_id);
        if ($stmt->execute()) {
            // Redirect to cart page with a success message
            header('Location: cart.php?message=Item+successfully+canceled');
            exit();
        } else {
            // Handle error
            echo "Error: " . $stmt->error;
        }
    }
}
?>
