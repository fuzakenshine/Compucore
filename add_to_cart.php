<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "compucore";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $quantity = 1; // Default quantity

    // Check if the product is already in the cart
    $check_sql = "SELECT * FROM cart WHERE product_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If product exists, update the quantity
        $update_sql = "UPDATE cart SET quantity = quantity + 1 WHERE product_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $product_id);
        $update_stmt->execute();
    } else {
        // If product does not exist, insert it
        $insert_sql = "INSERT INTO cart (product_id, product_name, product_price, quantity) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isdi", $product_id, $product_name, $product_price, $quantity);
        $insert_stmt->execute();
    }

    // Redirect back to the index page
    header('Location: index.php?message=Product+successfully+added+to+cart#product-' . $product_id);
    exit();
    
}

$conn->close();
?>