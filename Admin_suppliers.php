<?php
session_start();
include 'db_connect.php'; 

$sql = "SELECT S_FNAME, S_LNAME, EMAIL, CREATE_AT, COMPANY_NAME, SUPPLIER_IMAGE FROM supplier ORDER BY CREATE_AT DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Suppliers</title>
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
        .supplier-section {
    margin-left: 250px;
    padding: 40px;
}

.supplier-section h2 {
    font-size: 24px;
    margin-bottom: 20px;
}

.supplier-section table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.supplier-section table th,
.supplier-section table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.supplier-section table th {
    color: #444;
    font-weight: 600;
}

.supplier-section table td img {
    vertical-align: middle;
}
.header button {
    background-color: #d32f2f;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
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
