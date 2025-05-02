<?php
require_once 'includes/auth.php';
checkAdminAccess();
include 'db_connect.php'; 

$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminName = $adminQuery->get_result()->fetch_assoc()['full_name'];

$sql = "SELECT o.PK_ORDER_ID, c.F_NAME, c.L_NAME, o.STATUS, o.TOTAL_PRICE, o.ORDER_DATE 
        FROM orders o
        JOIN customer c ON o.FK1_CUSTOMER_ID = c.PK_CUSTOMER_ID
        ORDER BY o.ORDER_DATE DESC";
$result = $conn->query($sql);

// Function to get product names for an order
function getProductNames($conn, $order_id) {
    $sql = "SELECT p.PROD_NAME 
            FROM order_detail od 
            JOIN products p ON od.FK1_PRODUCT_ID = p.PK_PRODUCT_ID 
            WHERE od.FK2_ORDER_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $product_names = [];
    while ($row = $result->fetch_assoc()) {
        $product_names[] = $row['PROD_NAME'];
    }
    
    return implode(", ", $product_names);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action'];
    $new_status = '';
    if ($action === 'approve') {
        $new_status = 'Approved';
    } elseif ($action === 'reject') {
        $new_status = 'Rejected';
    }
    if ($new_status) {
        // First get the customer ID from the order
        $customer_sql = "SELECT FK1_CUSTOMER_ID FROM orders WHERE PK_ORDER_ID = ?";
        $stmt = $conn->prepare($customer_sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $customer_result = $stmt->get_result();
        $customer_id = $customer_result->fetch_assoc()['FK1_CUSTOMER_ID'];
        
        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET STATUS = ? WHERE PK_ORDER_ID = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        
        // Create notification for the customer
        $message = $new_status === 'Approved' 
            ? "Your order for " . getProductNames($conn, $order_id) . " has been approved and is being processed."
            : "Your order for " . getProductNames($conn, $order_id) . " has been rejected. Please check the details.";
        $notification_sql = "INSERT INTO notifications (FK_CUSTOMER_ID, MESSAGE, TYPE) 
                            VALUES (?, ?, 'order')";
        $stmt = $conn->prepare($notification_sql);
        $stmt->bind_param("is", $customer_id, $message);
        $stmt->execute();
        
        $stmt->close();
        // Optional: Refresh to show updated status
        header("Location: Admin_orders.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders</title>
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

        .orders-section {
            margin-left: 250px;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            color: #333;
        }

        .header input {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 200px;
        }

        table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        <a href="Admin_orders.php" class="active">
            <i class="fas fa-shopping-cart"></i> Orders
        </a>
        <a href="Admin_suppliers.php">
            <i class="fas fa-truck"></i> Suppliers
        </a>
        <a href="Admin_product.php">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="Admin_customers.php">
            <i class="fas fa-users"></i> Customers
        </a>
        <a href="Admin_reports.php">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Keep existing orders-section content -->
    <div class="orders-section">
        <div class="header">
            <h2>Manage Orders</h2>
            <input type="text" placeholder="Search orders...">
        </div>
        <!-- Keep existing table structure -->
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Total Price</th>
                    <th>Date/Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['PK_ORDER_ID']) ?></td>
                    <td><?= htmlspecialchars($row['F_NAME'] . ' ' . $row['L_NAME']) ?></td>
                    <td style="font-weight:bold;color:<?= $row['STATUS'] === 'Approved' ? '#388e3c' : ($row['STATUS'] === 'Rejected' ? '#d32f2f' : '#333') ?>;">
                        <?= htmlspecialchars($row['STATUS']) ?>
                    </td>
                    <td>₱<?= number_format($row['TOTAL_PRICE'], 2) ?></td>
                    <td><?= date("m/d/Y H:i", strtotime($row['ORDER_DATE'])) ?></td>
                    <td>
                        <?php if ($row['STATUS'] !== 'Approved' && $row['STATUS'] !== 'Rejected'): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="order_id" value="<?= htmlspecialchars($row['PK_ORDER_ID']) ?>">
                                <button type="submit" name="action" value="approve" style="background:#388e3c;color:white;border:none;padding:5px 10px;border-radius:3px;cursor:pointer;">Approve</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="order_id" value="<?= htmlspecialchars($row['PK_ORDER_ID']) ?>">
                                <button type="submit" name="action" value="reject" style="background:#d32f2f;color:white;border:none;padding:5px 10px;border-radius:3px;cursor:pointer;">Reject</button>
                            </form>
                        <?php else: ?>
                            <span style="color:<?= $row['STATUS'] === 'Approved' ? '#388e3c' : '#d32f2f' ?>;font-weight:bold;">
                                <?= htmlspecialchars($row['STATUS']) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>