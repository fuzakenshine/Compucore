<?php
session_start();
include 'db_connect.php'; // Include the database connector

// Fetch products from the database
$sql = "SELECT * FROM PRODUCTS";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .sidebar {
            width: 200px;
            background-color: #d32f2f;
            color: white;
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            margin-bottom: 20px;
            font-size: 18px;
        }
        .sidebar a:hover {
            text-decoration: underline;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
        }
        .header button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        .header button:hover {
            background-color: #b71c1c;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .product-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .product-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .product-card h3 {
            font-size: 16px;
            margin: 0 0 10px;
        }
        .product-card p {
            margin: 5px 0;
            color: #555;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin: 0 5px;
            border-radius: 5px;
        }
        .pagination button:hover {
            background-color: #b71c1c;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="Admin_home.php">Home</a>
        <a href="Admin_suppliers.php">Suppliers</a>
        <a href="Admin_product.php">Products</a>
        <a href="Admin_logout.php">Logout</a>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Products</h1>
            <button onclick="window.location.href='Admin_add_product.php'">Add Product</button>
        </div>
        <div class="product-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="uploads/<?php echo htmlspecialchars($row['IMAGE']); ?>" alt="<?php echo htmlspecialchars($row['PROD_NAME']); ?>">
                        <h3><?php echo htmlspecialchars($row['PROD_NAME']); ?></h3>
                        <p>₱<?php echo number_format($row['PRICE'], 2); ?></p>
                        <p>Qty: <?php echo $row['QTY']; ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
        <div class="pagination">
            <button>&laquo;</button>
            <button>1</button>
            <button>2</button>
            <button>&raquo;</button>
        </div>
    </div>
</body>
</html>