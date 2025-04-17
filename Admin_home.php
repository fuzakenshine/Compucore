<?php
session_start();
include 'db_connect.php'; 

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

        .sidebar a {
            color: white;
            text-decoration: none;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .sidebar a:hover {
            text-decoration: underline;
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
        <h3>Sabnock</h3>
        <a href="Admin_home.php">Home</a>
        <a href="Admin_suppliers.php">Suppliers</a>
        <a href="Admin_product.php">Products</a>
        <a href="Admin_logout.php">Logout</a>
    </div>

    <div class="main">
        <h1>Dashboard</h1>

        <div class="cards">
    <a href="Admin_orders.php" style="text-decoration: none; width: 50%;">
        <div class="card green">
            <h2><?= $pendingOrders ?></h2>
            <p>Pending Orders</p>
        </div>
    </a>

    <a href="Admin_customers.php" style="text-decoration: none; width: 50%;">
        <div class="card yellow">
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
