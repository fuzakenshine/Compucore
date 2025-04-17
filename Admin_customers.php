<?php
session_start();
include 'db_connect.php'; 

$sql = "SELECT F_NAME, L_NAME, EMAIL, CREATED_AT, PHONE_NUM FROM customer ORDER BY CREATED_AT DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Customers</title>
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
        .customer-section {
            margin-left: 250px;
            padding: 40px;
        }
        .customer-section h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .customer-section table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .customer-section table th,
        .customer-section table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .customer-section table th {
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
    <div class="customer-section">
        <div class="header">
            <h2>Customers</h2>
            <button onclick="window.location.href='Admin_add_customer.php'">Add Customer</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['F_NAME'] . ' ' . $row['L_NAME']) ?></strong></td>
                    <td><?= htmlspecialchars($row['EMAIL']) ?></td>
                    <td><?= htmlspecialchars($row['PHONE_NUM']) ?></td>
                    <td><?= date("m/d/Y", strtotime($row['CREATED_AT'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>