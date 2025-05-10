<?php
session_start();
include 'db_connect.php';

// Fetch admin name
// Fetch admin name
/**
$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminName = $adminQuery->get_result()->fetch_assoc()['full_name'];
**/

$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminResult = $adminQuery->get_result();
$adminName = $adminResult->num_rows > 0 ? $adminResult->fetch_assoc()['full_name'] : 'Test Admin';
// Pending Orders
$ordersQuery = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE STATUS = 'Pending'");
$pendingOrders = $ordersQuery->fetch_assoc()['total'];

// Customers
$customersQuery = $conn->query("SELECT COUNT(*) AS total FROM customer");
$totalCustomers = $customersQuery->fetch_assoc()['total'];

// Order Trend Data
$orderCountsThisYear = [];
$orderCountsLastYear = [];

for ($month = 1; $month <= 12; $month++) {
    $q1 = $conn->query("SELECT COUNT(*) AS total FROM orders 
        WHERE MONTH(ORDER_DATE) = $month AND YEAR(ORDER_DATE) = YEAR(CURDATE())");
    $orderCountsThisYear[] = intval($q1->fetch_assoc()['total'] ?? 0);

    $q2 = $conn->query("SELECT COUNT(*) AS total FROM orders 
        WHERE MONTH(ORDER_DATE) = $month AND YEAR(ORDER_DATE) = YEAR(CURDATE()) - 1");
    $orderCountsLastYear[] = intval($q2->fetch_assoc()['total'] ?? 0);
}

// Pie Chart Data - Order Status Distribution
$statusQuery = $conn->query("SELECT STATUS, COUNT(*) AS total FROM orders GROUP BY STATUS");
$orderStatuses = [];
$statusCounts = [];

while ($row = $statusQuery->fetch_assoc()) {
    $orderStatuses[] = $row['STATUS'];
    $statusCounts[] = intval($row['total']);
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
            cursor: pointer;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.03);
        }

        .card.green { background-color: #4caf50; }
        .card.yellow { background-color: #fbc02d; }

        .charts-wrapper {
            display: flex;
            gap: 20px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .chart-container {
            flex: 1;
            min-width: 300px;
            background-color: #ffcdd2;
            padding: 20px;
            border-radius: 15px;
        }

        canvas {
            max-height: 250px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="admin-profile">
            <h3><?= htmlspecialchars($adminName) ?></h3>
        </div>
        <a href="Admin_home.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
        <a href="Admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
        <a href="Admin_suppliers.php"><i class="fas fa-truck"></i> Suppliers</a>
        <a href="Admin_product.php"><i class="fas fa-box"></i> Products</a>
        <a href="Admin_customers.php"><i class="fas fa-users"></i> Customers</a>
        <a href="Admin_reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main">
        <h1>Dashboard</h1>

        <div class="cards">
            <a href="Admin_orders.php" style="text-decoration: none; width: 33%;">
                <div class="card green">
                    <i class="fas fa-shopping-cart fa-2x"></i>
                    <h2><?= $pendingOrders ?></h2>
                    <p>Pending Orders</p>
                </div>
            </a>
            <a href="Admin_customers.php" style="text-decoration: none; width: 33%;">
                <div class="card yellow">
                    <i class="fas fa-users fa-2x"></i>
                    <h2><?= number_format($totalCustomers) ?></h2>
                    <p>Users</p>
                </div>
            </a>
            <a href="Admin_reports.php" style="text-decoration: none; width: 33%;">
                <div class="card" style="background-color: #2196f3;">
                    <i class="fas fa-chart-bar fa-2x"></i>
                    <h2>Reports</h2>
                    <p>View Analytics</p>
                </div>
            </a>
        </div>

        <div class="charts-wrapper">
            <div class="chart-container">
                <h3 style="margin-bottom: 10px;">Monthly Orders</h3>
                <canvas id="salesChart"></canvas>
            </div>

            <div class="chart-container">
                <h3 style="margin-bottom: 10px;">Order Status Distribution</h3>
                <canvas id="statusPie"></canvas>
            </div>
        </div>
    </div>

    <script>
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const statusCtx = document.getElementById('statusPie').getContext('2d');

        // Line Chart - Monthly Orders
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [
                    {
                        label: 'This Year',
                        data: <?= json_encode($orderCountsThisYear) ?>,
                        borderColor: '#4caf50',
                        backgroundColor: 'transparent',
                        borderWidth: 2
                    },
                    {
                        label: 'Last Year',
                        data: <?= json_encode($orderCountsLastYear) ?>,
                        borderColor: '#f44336',
                        backgroundColor: 'transparent',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // Pie Chart - Order Status
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode($orderStatuses) ?>,
                datasets: [{
                    label: 'Order Status',
                    data: <?= json_encode($statusCounts) ?>,
                    backgroundColor: [
                        '#ff6384', '#36a2eb', '#ffce56', '#8bc34a', '#e91e63', '#009688'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>
