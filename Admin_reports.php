<?php
session_start();
include 'db_connect.php';

$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminResult = $adminQuery->get_result();
$adminName = $adminResult->num_rows > 0 ? $adminResult->fetch_assoc()['full_name'] : 'Test Admin';

// Get report type from URL parameter
$reportType = isset($_GET['type']) ? $_GET['type'] : 'summary';

// Fetch data based on report type
switch($reportType) {
    case 'detailed':
        // Redirect to Detail_reports.php
        header('Location: Detail_reports.php');
        exit;
        break;

    case 'summary':
        // Summary Report
        $summaryData = [
            'total_orders' => $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'],
            'total_revenue' => $conn->query("SELECT SUM(TOTAL_PRICE) as total FROM orders")->fetch_assoc()['total'],
            'avg_order_value' => $conn->query("SELECT AVG(TOTAL_PRICE) as avg FROM orders")->fetch_assoc()['avg'],
            'pending_orders' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE STATUS = 'Pending'")->fetch_assoc()['count'],
            'completed_orders' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE STATUS = 'Completed'")->fetch_assoc()['count']
        ];
        break;

    case 'statistical':
        // Statistical Report Data
        $monthlySales = [];
        $monthlyOrders = [];
        $customerGrowth = [];
        
        for ($i = 0; $i < 12; $i++) {
            $month = date('Y-m', strtotime("-$i months"));
            $sales = $conn->query("
                SELECT SUM(TOTAL_PRICE) as total 
                FROM orders 
                WHERE DATE_FORMAT(ORDER_DATE, '%Y-%m') = '$month'
            ")->fetch_assoc()['total'];
            
            $orders = $conn->query("
                SELECT COUNT(*) as count 
                FROM orders 
                WHERE DATE_FORMAT(ORDER_DATE, '%Y-%m') = '$month'
            ")->fetch_assoc()['count'];
            
            $customers = $conn->query("
                SELECT COUNT(*) as count 
                FROM customer 
                WHERE DATE_FORMAT(CREATED_AT, '%Y-%m') = '$month'
            ")->fetch_assoc()['count'];
            
            $monthlySales[] = $sales ?: 0;
            $monthlyOrders[] = $orders;
            $customerGrowth[] = $customers;
        }
        
        $statisticalData = [
            'monthly_sales' => array_reverse($monthlySales),
            'monthly_orders' => array_reverse($monthlyOrders),
            'customer_growth' => array_reverse($customerGrowth),
            'months' => array_reverse(array_map(function($i) {
                return date('M Y', strtotime("-$i months"));
            }, range(0, 11)))
        ];
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Reports</title>
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

        .report-type-selector {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .report-type-selector a {
            padding: 10px 20px;
            background-color: #d32f2f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .report-type-selector a.active {
            background-color: #9a0007;
        }

        .report-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f5f5f5;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .summary-card {
            background-color: #d32f2f;
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .chart-container {
            margin-top: 20px;
            height: 400px;
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
        <a href="Admin_suppliers.php">
            <i class="fas fa-truck"></i> Suppliers
        </a>
        <a href="Admin_product.php">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="Admin_customers.php">
            <i class="fas fa-users"></i> Customers
        </a>
        <a href="Admin_reports.php"  class="active">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="main">
        <h1>Reports</h1>
        
        <div class="report-type-selector">
            <a href="?type=summary" class="<?= $reportType === 'summary' ? 'active' : '' ?>">Summary Report</a>
            <a href="Detail_reports.php" class="<?= $reportType === 'detailed' ? 'active' : '' ?>">Detailed Report</a>
            <a href="?type=statistical" class="<?= $reportType === 'statistical' ? 'active' : '' ?>">Statistical Report</a>
        </div>

        <div class="report-container">
            <?php if ($reportType === 'summary'): ?>
                <h2>Summary Report</h2>
                <div class="summary-cards">
                    <div class="summary-card">
                        <h3>Total Orders</h3>
                        <p><?= number_format($summaryData['total_orders']) ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Total Revenue</h3>
                        <p>$<?= number_format($summaryData['total_revenue'], 2) ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Average Order Value</h3>
                        <p>$<?= number_format($summaryData['avg_order_value'], 2) ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Pending Orders</h3>
                        <p><?= number_format($summaryData['pending_orders']) ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Completed Orders</h3>
                        <p><?= number_format($summaryData['completed_orders']) ?></p>
                    </div>
                </div>
            <?php elseif ($reportType === 'statistical'): ?>
                <h2>Statistical Report</h2>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="ordersChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="customersChart"></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>

    <?php if ($reportType === 'statistical'): ?>
    <script>
        // Sales Chart
        new Chart(document.getElementById('salesChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($statisticalData['months']) ?>,
                datasets: [{
                    label: 'Monthly Sales',
                    data: <?= json_encode($statisticalData['monthly_sales']) ?>,
                    borderColor: '#d32f2f',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Orders Chart
        new Chart(document.getElementById('ordersChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($statisticalData['months']) ?>,
                datasets: [{
                    label: 'Monthly Orders',
                    data: <?= json_encode($statisticalData['monthly_orders']) ?>,
                    backgroundColor: '#d32f2f'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Customers Chart
        new Chart(document.getElementById('customersChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($statisticalData['months']) ?>,
                datasets: [{
                    label: 'Customer Growth',
                    data: <?= json_encode($statisticalData['customer_growth']) ?>,
                    borderColor: '#d32f2f',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
    <?php endif; ?>
</body>
</html> 