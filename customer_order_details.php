<?php
session_start();
include 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch all orders for the customer
$sql = "SELECT o.PK_ORDER_ID, o.STATUS, o.TOTAL_PRICE, o.ORDER_DATE
        FROM orders o
        WHERE o.FK1_CUSTOMER_ID = ?
        ORDER BY o.ORDER_DATE DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding-top: 70px; /* Added padding to account for fixed header height */
        }
        .order-details {
            margin: 20px auto;
            max-width: 900px;
        }
        .order-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            padding: 0 0 20px 0;
            overflow: hidden;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
            padding: 18px 24px 10px 24px;
        }
        .order-header .store {
            font-weight: bold;
            color: #d32f2f;
            font-size: 1.1em;
        }
        .order-header .status {
            font-weight: bold;
            color: #388e3c;
        }
        .order-header .status.rejected {
            color: #d32f2f;
        }
        .order-header .status.pending {
            color: #f9a825;
        }
        .order-products {
            padding: 0 24px;
        }
        .product-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #f0f0f0;
            padding: 18px 0;
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 18px;
            background: #f9f9f9;
        }
        .product-info {
            flex: 1;
        }
        .product-info h4 {
            margin: 0 0 6px 0;
            font-size: 1.1em;
            color: #222;
        }
        .product-info .qty {
            color: #888;
            font-size: 0.95em;
        }
        .product-info .price {
            color: #d32f2f;
            font-weight: bold;
            font-size: 1.1em;
        }
        .order-summary {
            padding: 0 24px;
            margin-top: 10px;
            color: #333;
            font-size: 1.05em;
            display: flex;
            justify-content: flex-end;
        }
        .order-summary .total {
            font-weight: bold;
            color: #d32f2f;
            margin-left: 10px;
        }
        .order-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 16px 24px 0 24px;
        }
        .order-actions button {
            border: 1px solid #d32f2f;
            background: #fff;
            color: #d32f2f;
            padding: 8px 22px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .order-actions button.rate {
            background: #d32f2f;
            color: #fff;
        }
        .order-actions button:hover {
            opacity: 0.9;
        }
        .order-footer {
            padding: 0 24px;
            color: #888;
            font-size: 0.95em;
            margin-top: 8px;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
    <div class="order-details">
        <h2 style="color:#d32f2f;">My Orders</h2>
        <?php while($order = $orders->fetch_assoc()): ?>
            <div class="order-card">
                <div class="order-header">
                    <span class="store"><i class="fas fa-store"></i> CompuCore</span>
                    <span class="status <?= strtolower($order['STATUS']) ?>">
                        <?= htmlspecialchars($order['STATUS']) ?>
                    </span>
                </div>
                <div class="order-products">
                    <?php
                    // Fetch order items for this order
                    $order_id = $order['PK_ORDER_ID'];
                    $item_sql = "SELECT od.QTY, od.PRICE, p.PROD_NAME, p.IMAGE FROM order_detail od JOIN products p ON od.FK1_PRODUCT_ID = p.PK_PRODUCT_ID WHERE od.FK2_ORDER_ID = ?";
                    $item_stmt = $conn->prepare($item_sql);
                    $item_stmt->bind_param("i", $order_id);
                    $item_stmt->execute();
                    $items = $item_stmt->get_result();
                    while($item = $items->fetch_assoc()): ?>
                        <div class="product-item">
                            <img src="uploads/<?= htmlspecialchars($item['IMAGE']) ?>" class="product-image" alt="<?= htmlspecialchars($item['PROD_NAME']) ?>">
                            <div class="product-info">
                                <h4><?= htmlspecialchars($item['PROD_NAME']) ?></h4>
                                <div class="qty">x<?= $item['QTY'] ?></div>
                                <div class="price">₱<?= number_format($item['PRICE'], 2) ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="order-summary">
                    Total: <span class="total">₱<?= number_format($order['TOTAL_PRICE'], 2) ?></span>
                </div>
                <div class="order-footer">
                    Order Date: <?= date("M d, Y H:i", strtotime($order['ORDER_DATE'])) ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>