<?php
require_once 'includes/auth.php';
checkAdminAccess();
include 'db_connect.php';

// Fetch admin name
$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminName = $adminQuery->get_result()->fetch_assoc()['full_name'];

// Supplier query
$sql = "SELECT S_FNAME, S_LNAME, EMAIL, CREATE_AT, COMPANY_NAME, SUPPLIER_IMAGE FROM supplier ORDER BY CREATE_AT DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Suppliers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .sidebar {
            width: 200px;
            background-color: #d32f2f;
            height: 100vh;
            position: fixed;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .admin-profile {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
        }

        .admin-profile h3 {
            margin: 0;
            font-size: 1.2em;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .sidebar a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .sidebar a.active {
            background: rgba(255,255,255,0.2);
        }

        .supplier-section {
            margin-left: 250px;
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
            color: #333;
        }

        .header button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 14px;
        }

        .header button:hover {
            background-color: #b71c1c;
        }

        table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        table th,
        table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
        }

        table tr:hover {
            background-color: #f5f5f5;
        }

        /* Keep your existing table styles */
        .supplier-section table td img {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="admin-profile">
            <h3><?= htmlspecialchars($adminName) ?></h3>
        </div>
        <a href="Admin_home.php">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="Admin_orders.php">
            <i class="fas fa-shopping-cart"></i> Orders
        </a>
        <a href="Admin_suppliers.php" class="active">
            <i class="fas fa-truck"></i> Suppliers
        </a>
        <a href="Admin_product.php">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="Admin_customers.php">
            <i class="fas fa-users"></i> Customers
        </a>
        <a href="logout.php" style="margin-top: auto;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Keep your existing supplier section content -->
    <div class="supplier-section">
        <div class="header">
            <h2>Suppliers</h2>
            <button onclick="window.location.href='Admin_add_supplier.php'">Add Supplier</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Date Created</th>
                    <th>Company</th>
                </tr>
            </thead>
            <tbody>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                        <?php
                            $photo = !empty($row['SUPPLIER_IMAGE']) ? 'uploads/' . htmlspecialchars($row['SUPPLIER_IMAGE']) : 'assets/default-profile.png';
                        ?>
                        <img src="<?= $photo ?>" alt="Avatar" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">                            
                            <div>
                                <strong><?= htmlspecialchars($row['S_FNAME'] . ' ' . $row['S_LNAME']) ?></strong><br>
                                <small><?= htmlspecialchars($row['EMAIL']) ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?= date("m/d/Y", strtotime($row['CREATE_AT'])) ?></td>
                    <td><?= htmlspecialchars($row['COMPANY_NAME']) ?></td>
                </tr>
                <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
