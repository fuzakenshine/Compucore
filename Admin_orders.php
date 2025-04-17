<?php
session_start();
include 'db_connect.php'; 

$sql = "SELECT o.PK_ORDER_ID, c.F_NAME, c.L_NAME, o.STATUS, o.TOTAL_PRICE, o.ORDER_DATE 
        FROM orders o
        JOIN customer c ON o.FK1_CUSTOMER_ID = c.PK_CUSTOMER_ID
        ORDER BY o.ORDER_DATE DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders</title>
    <link rel="stylesheet" href="styles.css">
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
        .orders-section {
            margin-left: 250px;
            padding: 40px;
        }
        .orders-section h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .orders-section table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .orders-section table th,
        .orders-section table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .orders-section table th {
            color: #444;
            font-weight: 600;
        }
        .header button:hover {
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
    <div class="orders-section">
        <div class="header">
            <h2>Manage Orders</h2>
            <input type="text" placeholder="Search..." style="padding: 5px; border-radius: 5px;">
        </div>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Total Price</th>
                    <th>Date/Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['PK_ORDER_ID']) ?></td>
                    <td><?= htmlspecialchars($row['F_NAME'] . ' ' . $row['L_NAME']) ?></td>
                    <td><?= htmlspecialchars($row['STATUS']) ?></td>
                    <td>₱<?= number_format($row['TOTAL_PRICE'], 2) ?></td>
                    <td><?= date("m/d/Y H:i", strtotime($row['ORDER_DATE'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>