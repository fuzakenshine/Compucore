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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
        .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
        padding: 10px 20px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background-color: #d32f2f;
        height: 30px;
    }
    .header .logo {
    font-size: 24px;
    width: 100px; /* Adjust width as needed */
    height: 30px; /* Adjust height as needed */
    font-weight: bold;
}
        .header .logo img {
            height: 50px;
            width: 80px;
            display: block;
            margin: 0 auto;
            transition: transform 0.3s ease;
        }
        .header .logo img:hover {
            transform: scale(1.1);
        }
        .header .logo a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 24px;
        }
        .header .search-bar {
            display: flex;
            align-items: center;
            position: relative;
            margin-left: 450px;
        }

        .header .search-bar input {
            padding: 8px 35px 8px 12px;
            border: none;
            border-radius: 30px;
            width: 200px;
            outline: none;
        }

        .header .search-bar button {
            position: absolute;
            right: 5px;
            background: none;
            border: none;
            color: #555;
            cursor: pointer;
            font-size: 16px;
        }

        .header .icons {
            display: flex;
            align-items: right;
            margin-left: 400px;
        }
        .header .icons i {
            margin: 0 10px;
            cursor: pointer;
            font-size: 20px;
            color: white;
        }
        .header .burger-menu {
            position: relative;
            display: inline-block;
        }
        .header .burger-menu button {
            background-color: transparent;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        .header .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .header .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .header .dropdown-content a:hover {
            background-color: #ddd;
        }
        .header .burger-menu:hover .dropdown-content {
            display: block;
        }
    </style>
</head>
<body>
<header class="header">
<!-- Replace the empty div with an img tag -->
<div class="logo">
    <a href="index.php">
        <img src="uploads/LOGOW.PNG" alt="Compucore Logo" height="30">
    </a>
</div>
        <div class="search-bar">
            <input type="text" placeholder="Search">
            <button><i class="fas fa-search"></i></button>
        </div>
        <div class="icons">
        <a href="cart.php">
            <i class="fas fa-shopping-cart"></i>
        </a>
            <i class="fas fa-money-bill"></i> 
        </div>
        <div class="burger-menu">
            <button><i class="fas fa-bars"></i></button>
            <div class="dropdown-content">
                <a href="profile.php">Profile</a>
                <a href="landing.php">Logout</a>
            </div>
        </div>
    </header>    
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