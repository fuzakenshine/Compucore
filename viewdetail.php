<?php
session_start();
include 'db_connect.php'; // Include the database connector

// Get product ID from query string
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : null;

if ($product_id) {
    // Fetch product details
    $sql = "SELECT * FROM products WHERE PK_PRODUCT_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found.";
        exit;
    }
} else {
    echo "Invalid product ID.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            margin-top: 100px;
        }
        .product-detail {
            display: flex;
            gap: 20px;
        }
        .product-image {
            flex: 1;
        }
        .product-info {
            flex: 2;
        }
        .product-info h1 {
            color: #d32f2f;
        }
        .product-info p {
            font-size: 18px;
        }
        .order-button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        .order-button:hover {
            background-color: #b71c1c;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
    <div class="container">
        <div class="product-detail">
            <div class="product-image">
                <img src="uploads/<?php echo $product['IMAGE']; ?>" alt="<?php echo $product['PROD_NAME']; ?>" style="width:100%;">
            </div>
            <div class="product-info">
                <h1><?php echo $product['PROD_NAME']; ?></h1>
                <p>Price: ₱<?php echo number_format($product['PRICE'], 2); ?></p>
                <p><?php echo $product['PROD_DESC']; ?></p>
                <form method="POST" action="ordernow.php">
                    <input type="hidden" name="product_id" value="<?php echo $product['PK_PRODUCT_ID']; ?>">
                    <button type="submit" class="order-button">Order Now</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>