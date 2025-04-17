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
    $prod_name = $_POST['prod_name'];
    $prod_desc = $_POST['prod_desc'];
    $price = $_POST['price'];
    $qty = $_POST['qty'];
    $image = $_POST['image'];

    $sql = "INSERT INTO products (PROD_NAME, PROD_DESC, PRICE, QTY, IMAGE, FK1_CATEGORY_ID, FK2_SUPPLIER_ID, UPDATED_AT) VALUES (?, ?, ?, ?, ?, 1, 1, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdis", $prod_name, $prod_desc, $price, $qty, $image);
    $stmt->execute();

    echo "<p>Product added successfully!</p>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Add Product</title>
</head>
<body>
    <h1>Add New Product</h1>
    <form method="POST" action="">
        <label for="prod_name">Product Name:</label><br>
        <input type="text" id="prod_name" name="prod_name" required><br>

        <label for="prod_desc">Product Description:</label><br>
        <input type="text" id="prod_desc" name="prod_desc" required><br>

        <label for="price">Price:</label><br>
        <input type="number" step="0.01" id="price" name="price" required><br>

        <label for="qty">Quantity:</label><br>
        <input type="number" id="qty" name="qty" required><br>

        <label for="image">Image Filename:</label><br>
        <input type="text" id="image" name="image" required><br>

        <input type="submit" value="Add Product">
    </form>
</body>
</html> 