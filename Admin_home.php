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

// --- Fetch pending orders ---
$ordersQuery = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE STATUS = 'Pending'");
$pendingOrders = $ordersQuery->fetch_assoc()['total'];

// --- Fetch total customers ---
$customersQuery = $conn->query("SELECT COUNT(*) AS total FROM customer");
$totalCustomers = $customersQuery->fetch_assoc()['total'];

// --- Monthly Revenue Trend (from orders) ---
$salesThisYear = [];
$salesLastYear = [];

for ($month = 1; $month <= 12; $month++) {
    // Format with leading zero
    $m = str_pad($month, 2, "0", STR_PAD_LEFT);

    // This year
    $q1 = $conn->query("SELECT SUM(TOTAL_PRICE) AS total FROM orders 
        WHERE MONTH(ORDER_DATE) = $month AND YEAR(ORDER_DATE) = YEAR(CURDATE())");
    $salesThisYear[] = floatval($q1->fetch_assoc()['total'] ?? 0);

    // Last year
    $q2 = $conn->query("SELECT SUM(TOTAL_PRICE) AS total FROM orders 
        WHERE MONTH(ORDER_DATE) = $month AND YEAR(ORDER_DATE) = YEAR(CURDATE()) - 1");
    $salesLastYear[] = floatval($q2->fetch_assoc()['total'] ?? 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .admin-profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 10px;
            border: 3px solid white;
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

        .main {
            margin-left: 250px;
            padding: 20px;
        }

        .cards {
            display: flex;
            gap: 20px;
        }

        .card {
            flex: 1;
            padding: 20px;
            border-radius: 10px;
            color: white;
        }

        .card.green { background-color: #4caf50; }
        .card.yellow { background-color: #fbc02d; }

        .card h2 {
            margin: 0;
        }

        .chart-container {
            margin-top: 40px;
            background-color: #ffcdd2;
            padding: 20px;
            border-radius: 15px;
        }
        .card:hover {
            transform: scale(1.03);
        }
        .card {
            cursor: pointer;
            transition: transform 0.2s;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="admin-profile">
            <h3><?= htmlspecialchars($adminName) ?></h3>
        </div>
        <a href="Admin_home.php" class="active">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="Admin_orders.php">
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
        <a href="logout.php" style="margin-top: auto;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="main">
        <h1>Dashboard</h1>

        <div class="cards">
    <a href="Admin_orders.php" style="text-decoration: none; width: 50%;">
        <div class="card green">
            <i class="fas fa-shopping-cart fa-2x"></i>
            <h2><?= $pendingOrders ?></h2>
            <p>Pending Orders</p>
        </div>
    </a>

    <a href="Admin_customers.php" style="text-decoration: none; width: 50%;">
        <div class="card yellow">
            <i class="fas fa-users fa-2x"></i>
            <h2><?= number_format($totalCustomers) ?></h2>
            <p>Users</p>
        </div>
    </a>
</div>


        <div class="chart-container">
            <h2>Sales Trend</h2>
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [{
                    label: 'This Year',
                    data: <?= json_encode($salesThisYear) ?>,
                    borderColor: '#ffffff',
                    backgroundColor: 'transparent',
                    borderWidth: 2
                }, {
                    label: 'Last Year',
                    data: <?= json_encode($salesLastYear) ?>,
                    borderColor: '#000000',
                    backgroundColor: 'transparent',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1000
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
