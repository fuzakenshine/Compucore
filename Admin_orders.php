<?php
include 'db_connect.php'; 

if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminResult = $adminQuery->get_result();
$adminName = $adminResult->num_rows > 0 ? $adminResult->fetch_assoc()['full_name'] : 'Test Admin';

// Pagination settings
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Handle search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build the SQL query with filters
$sql = "SELECT o.PK_ORDER_ID, c.F_NAME, c.L_NAME, o.STATUS, o.TOTAL_PRICE, o.ORDER_DATE,
        GROUP_CONCAT(CONCAT(p.PROD_NAME, ' (', od.QTY, ')') SEPARATOR ', ') as products
        FROM orders o
        JOIN customer c ON o.FK1_CUSTOMER_ID = c.PK_CUSTOMER_ID
        JOIN order_detail od ON o.PK_ORDER_ID = od.FK2_ORDER_ID
        JOIN products p ON od.FK1_PRODUCT_ID = p.PK_PRODUCT_ID
        WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (c.F_NAME LIKE ? OR c.L_NAME LIKE ? OR o.PK_ORDER_ID LIKE ?)";
}

if ($status_filter !== 'all') {
    $sql .= " AND o.STATUS = ?";
}

if (!empty($date_from)) {
    $sql .= " AND DATE(o.ORDER_DATE) >= ?";
}

if (!empty($date_to)) {
    $sql .= " AND DATE(o.ORDER_DATE) <= ?";
}

$sql .= " GROUP BY o.PK_ORDER_ID, c.F_NAME, c.L_NAME, o.STATUS, o.TOTAL_PRICE, o.ORDER_DATE";

// Get total count for pagination
$count_sql = str_replace("SELECT o.PK_ORDER_ID, c.F_NAME, c.L_NAME, o.STATUS, o.TOTAL_PRICE, o.ORDER_DATE,
        GROUP_CONCAT(CONCAT(p.PROD_NAME, ' (', od.QTY, ')') SEPARATOR ', ') as products", 
        "SELECT COUNT(DISTINCT o.PK_ORDER_ID) as total", $sql);
$count_stmt = $conn->prepare($count_sql);

// Add pagination to main query
$sql .= " ORDER BY o.ORDER_DATE DESC LIMIT ? OFFSET ?";

// Prepare and execute the query with filters
$stmt = $conn->prepare($sql);

// Build the parameter binding
$types = "";
$params = array();

if (!empty($search)) {
    $search_param = "%$search%";
    $types .= "sss";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($status_filter !== 'all') {
    $types .= "s";
    $params[] = $status_filter;
}

if (!empty($date_from)) {
    $types .= "s";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $types .= "s";
    $params[] = $date_to;
}

// Add pagination parameters
$types .= "ii";
$params[] = $items_per_page;
$params[] = $offset;

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Get total count
$count_types = substr($types, 0, -2); // Remove pagination parameters
$count_params = array_slice($params, 0, -2);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$total_items = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

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

// Function to update product quantities
function updateProductQuantity($order_id, $conn) {
    try {
        // Get order details with product quantities
        $sql = "SELECT od.FK1_PRODUCT_ID, od.QTY, p.QTY as current_stock 
                FROM order_detail od 
                JOIN products p ON od.FK1_PRODUCT_ID = p.PK_PRODUCT_ID 
                WHERE od.FK2_ORDER_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if we have enough stock for all products
        while ($row = $result->fetch_assoc()) {
            if ($row['current_stock'] < $row['QTY']) {
                return false; // Not enough stock
            }
        }

        // Reset result pointer
        $result->data_seek(0);

        // Update quantities
        while ($row = $result->fetch_assoc()) {
            $new_qty = $row['current_stock'] - $row['QTY'];
            $update_sql = "UPDATE products SET QTY = ? WHERE PK_PRODUCT_ID = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $new_qty, $row['FK1_PRODUCT_ID']);
            $update_stmt->execute();
        }

        return true;
    } catch (Exception $e) {
        error_log("Error updating product quantities: " . $e->getMessage());
        return false;
    }
}

// Handle order status updates
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
        try {
            $conn->begin_transaction();
            
            $check_sql = "SELECT STATUS FROM orders WHERE PK_ORDER_ID = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $order_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                throw new Exception("Order not found");
            }
            
            $order_status = $check_result->fetch_assoc()['STATUS'];
            if ($order_status !== 'Pending') {
                throw new Exception("Order is no longer pending");
            }
            
            $customer_sql = "SELECT FK1_CUSTOMER_ID FROM orders WHERE PK_ORDER_ID = ?";
            $stmt = $conn->prepare($customer_sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $customer_result = $stmt->get_result();
            $customer_id = $customer_result->fetch_assoc()['FK1_CUSTOMER_ID'];
            
            if ($new_status === 'Approved') {
                if (!updateProductQuantity($order_id, $conn)) {
                    throw new Exception("Insufficient stock for one or more products");
                }
            }
            
            $update_sql = "UPDATE orders SET STATUS = ?, UPDATE_DATE = CURRENT_TIMESTAMP WHERE PK_ORDER_ID = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_status, $order_id);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update order status");
            }
            
            if ($new_status === 'Approved') {
                $product_names = getProductNames($conn, $order_id);
                $message = "Your order for $product_names has been approved and is being processed.";
            } else {
                $message = "Your order #$order_id has been rejected.";
            }
                
            $notification_sql = "INSERT INTO notifications (FK_CUSTOMER_ID, MESSAGE, TYPE, CREATED_AT) 
                               VALUES (?, ?, 'order', CURRENT_TIMESTAMP)";
            $notify_stmt = $conn->prepare($notification_sql);
            $notify_stmt->bind_param("is", $customer_id, $message);
            
            if (!$notify_stmt->execute()) {
                throw new Exception("Failed to create notification");
            }
            
            $conn->commit();
            header("Location: Admin_orders.php?success=true&action=" . urlencode($new_status));
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Order processing error: " . $e->getMessage());
            header("Location: Admin_orders.php?error=" . urlencode($e->getMessage()));
            exit();
        }
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .search-filters {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .search-filters form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            align-items: end;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-size: 0.9em;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .search-btn {
            background: #d32f2f;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 0.9em;
        }

        .search-btn:hover {
            background: #b71c1c;
        }

        .orders-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }

        .orders-table th, .orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .orders-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .orders-table tr:hover {
            background: #f5f5f5;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-approved {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-rejected {
            background: #ffebee;
            color: #c62828;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85em;
            font-weight: 500;
            transition: opacity 0.3s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-approve {
            background: #4caf50;
            color: white;
        }

        .btn-reject {
            background: #f44336;
            color: white;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            font-size: 0.9em;
        }

        .pagination a:hover {
            background: #f5f5f5;
        }

        .pagination .active {
            background: #d32f2f;
            color: white;
            border-color: #d32f2f;
        }

        .pagination .disabled {
            color: #999;
            cursor: not-allowed;
        }

        .products-cell {
            max-width: 300px;
            white-space: normal;
            word-wrap: break-word;
            line-height: 1.4;
            font-size: 0.9em;
            color: #555;
        }

        .orders-table td {
            vertical-align: middle;
        }

        @media (max-width: 1200px) {
            .products-cell {
                max-width: 200px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }

            .sidebar span {
                display: none;
            }

            .orders-section {
                margin-left: 80px;
            }

            .orders-table {
                display: block;
                overflow-x: auto;
            }

            .products-cell {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="admin-profile">
            <h3><?= htmlspecialchars($adminName) ?></h3>
        </div>
        <a href="Admin_home.php" ><i class="fas fa-home"></i> Dashboard</a>
        <a href="Admin_orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a>
        <a href="Admin_suppliers.php"><i class="fas fa-truck"></i> Suppliers</a>
        <a href="Admin_product.php"><i class="fas fa-box"></i> Products</a>
        <a href="Admin_customers.php"><i class="fas fa-users"></i> Customers</a>
        <a href="Admin_reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="orders-section">
        <h1>Order Management</h1>

        <div class="search-filters">
            <form method="GET" action="">
                <div class="form-group">
                    <label>Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Order ID or customer name">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="form-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($row['PK_ORDER_ID']); ?></td>
                    <td><?php echo htmlspecialchars($row['F_NAME'] . ' ' . $row['L_NAME']); ?></td>
                    <td class="products-cell"><?php echo htmlspecialchars($row['products']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($row['STATUS']); ?>">
                            <?php echo htmlspecialchars($row['STATUS']); ?>
                        </span>
                    </td>
                    <td>₱<?php echo number_format($row['TOTAL_PRICE'], 2); ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($row['ORDER_DATE'])); ?></td>
                    <td>
                        <?php if ($row['STATUS'] === 'Pending'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="order_id" value="<?php echo $row['PK_ORDER_ID']; ?>">
                            <button type="submit" name="action" value="approve" class="btn btn-approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-reject">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" 
               class="<?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        <?php if (isset($_GET['success']) && $_GET['success'] === 'true'): ?>
        Swal.fire({
            title: 'Success!',
            text: 'Order has been <?php echo strtolower($_GET['action']); ?>.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        Swal.fire({
            title: 'Error!',
            text: '<?php echo htmlspecialchars($_GET['error']); ?>',
            icon: 'error'
        });
        <?php endif; ?>
    </script>
</body>
</html>