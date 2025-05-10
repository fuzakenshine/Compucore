<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch all orders for the customer with review status
$sql = "SELECT o.PK_ORDER_ID, o.STATUS, o.TOTAL_PRICE, o.ORDER_DATE,
        CASE WHEN r.PK_REVIEW_ID IS NULL AND o.STATUS = 'Approved' THEN 1 ELSE 0 END as can_review
        FROM orders o
        LEFT JOIN reviews r ON r.FK3_ORDER_ID = o.PK_ORDER_ID 
            AND r.FK1_CUSTOMER_ID = ?
        WHERE o.FK1_CUSTOMER_ID = ?
        ORDER BY o.ORDER_DATE DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $_SESSION['customer_id'], $_SESSION['customer_id']);
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding-top: 70px;
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
        .review-btn {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 8px 22px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            font-weight: bold;
        }
        .review-btn.disabled {
            background-color: #ccc;
            cursor: not-allowed;
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
        .order-footer {
            padding: 0 24px;
            color: #888;
            font-size: 0.95em;
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin: 40px auto;
            max-width: 400px;
            margin-bottom: 250px;
        }

        .empty-orders i {
            font-size: 80px;
            color: #d32f2f;
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .empty-orders p {
            font-size: 24px;
            color: #333;
            margin-bottom: 30px;
        }

        .empty-orders .subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .empty-orders .start-shopping {
            background: #d32f2f;
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .empty-orders .start-shopping:hover {
            background: rgb(172, 47, 47);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="order-details">
        <h2 style="color:#d32f2f;">My Orders</h2>
        <?php if ($orders->num_rows > 0): ?>
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
                        $order_id = $order['PK_ORDER_ID'];
                        $item_sql = "SELECT od.QTY, od.PRICE, p.PK_PRODUCT_ID, p.PROD_NAME, p.IMAGE 
                                   FROM order_detail od 
                                   JOIN products p ON od.FK1_PRODUCT_ID = p.PK_PRODUCT_ID 
                                   WHERE od.FK2_ORDER_ID = ?";
                        $item_stmt = $conn->prepare($item_sql);
                        $item_stmt->bind_param("i", $order_id);
                        $item_stmt->execute();
                        $items = $item_stmt->get_result();
                        while($item = $items->fetch_assoc()): ?>
                            <div class="product-item">
                                <img src="uploads/<?= htmlspecialchars($item['IMAGE']) ?>" 
                                     class="product-image" 
                                     alt="<?= htmlspecialchars($item['PROD_NAME']) ?>">
                                <div class="product-info">
                                    <h4><?= htmlspecialchars($item['PROD_NAME']) ?></h4>
                                    <div class="qty">x<?= $item['QTY'] ?></div>
                                    <div class="price">₱<?= number_format($item['PRICE'], 2) ?></div>
                                    <?php if ($order['STATUS'] === 'Approved' && $order['can_review']): ?>
                                        <a href="write_review.php?order_id=<?= $order['PK_ORDER_ID'] ?>&product_id=<?= $item['PK_PRODUCT_ID'] ?>" 
                                           class="review-btn">
                                            Write Review
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="order-summary">
                        Total: <span class="total">₱<?= number_format($order['TOTAL_PRICE'], 2) ?></span>
                    </div>
                    <div class="order-footer">
                        <span>Order Date: <?= date("M d, Y H:i", strtotime($order['ORDER_DATE'])) ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-orders">
                <i class="fas fa-shopping-bag"></i>
                <p>No orders found</p>
                <p class="subtitle">Looks like you haven't made any orders yet...</p>
                <a href="index.php" class="start-shopping">
                    Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>