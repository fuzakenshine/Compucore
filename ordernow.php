<?php
session_start();
include 'db_connect.php'; // Include the database connector

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

// Get product ID from POST data
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;

if ($product_id) {
    // Fetch product details
    $sql = "SELECT * FROM products WHERE PK_PRODUCT_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();
        // Here you can add logic to process the order, e.g., save to orders table
        $message = "Order placed for " . $product['PROD_NAME'];
    } else {
        $message = "Product not found.";
    }
} else {
    $message = "Invalid product ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .message-box {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .message-box h1 {
            color: #d32f2f;
        }
        .message-box p {
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <h1>Order Confirmation</h1>
        <p><?php echo $message; ?></p>
    </div>
</body>
</html>