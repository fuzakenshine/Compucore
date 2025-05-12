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

// Get customer information
$customer_sql = "SELECT * FROM customer WHERE PK_CUSTOMER_ID = ?";
$customer_stmt = $conn->prepare($customer_sql);
$customer_stmt->bind_param("i", $_SESSION['customer_id']);
$customer_stmt->execute();
$customer = $customer_stmt->get_result()->fetch_assoc();
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
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
            padding: 18px 24px;
            border-bottom: 1px solid #eee;
        }
        .order-header .store {
            font-weight: bold;
            color: #d32f2f;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .order-header .status {
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9em;
        }
        .order-header .status.approved {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .order-header .status.rejected {
            background: #ffebee;
            color: #c62828;
        }
        .order-header .status.pending {
            background: #fff3e0;
            color: #ef6c00;
        }
        .order-info {
            display: flex;
            justify-content: space-between;
            padding: 15px 24px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            font-size: 0.9em;
            color: #666;
        }
        .order-info-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .order-info-item i {
            color: #888;
            font-size: 0.9em;
        }
        .order-products {
            padding: 0 24px;
        }
        .supplier-group {
            background: #fff;
            border-radius: 8px;
            margin: 15px 0;
            overflow: hidden;
            border: 1px solid #eee;
        }
        .supplier-header {
            background: #f8f9fa;
            color: #333;
            padding: 10px 15px;
            font-size: 0.95em;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .supplier-header i {
            color: #666;
            font-size: 0.9em;
        }
        .product-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s ease;
            position: relative;
        }
        .product-item:hover {
            background-color: #fafafa;
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 15px;
            background: #f9f9f9;
        }
        .product-info {
            flex: 1;
            margin-right: 15px;
        }
        .product-info h4 {
            margin: 0 0 4px 0;
            font-size: 1em;
            color: #333;
        }
        .product-info .qty {
            color: #666;
            font-size: 0.85em;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .product-info .price {
            color: #d32f2f;
            font-weight: bold;
            font-size: 1em;
            margin-top: 4px;
        }
        .order-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 15px 24px;
            border-top: 1px solid #eee;
        }
        .action-btn {
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            border: none;
        }
        .print-btn {
            background-color: #34495e;
            color: white;
        }
        .print-btn:hover {
            background-color: #2c3e50;
        }
        .review-btn {
            background-color: #d32f2f;
            color: white;
            text-decoration: none;
        }
        .review-btn:hover {
            background-color: #b71c1c;
        }
        .review-btn.disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .order-summary {
            padding: 15px 24px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .order-summary .total {
            font-weight: bold;
            color: #d32f2f;
            font-size: 1.1em;
        }
        .order-summary .items-count {
            color: #666;
            font-size: 0.9em;
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
        .print-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 22px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            font-weight: bold;
            margin-left: 10px;
        }
        .print-btn:hover {
            background-color: #45a049;
        }
        .supplier-info {
            color: #666;
            font-size: 0.95em;
            margin-top: 5px;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .receipt, .receipt * {
                visibility: visible;
            }
            .receipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none;
            }
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            font-size: 11px;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .receipt-logo {
            width: 80px;
            height: auto;
            margin-bottom: 5px;
        }
        .receipt-title {
            font-size: 16px;
            color: #d32f2f;
            margin: 5px 0;
        }
        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            font-size: 10px;
        }
        .receipt-order-info, .receipt-customer-info {
            flex: 1;
        }
        .receipt-customer-info h3 {
            font-size: 12px;
            margin: 0 0 5px 0;
            color: #333;
        }
        .receipt-customer-info p {
            margin: 2px 0;
        }
        .receipt-supplier-group {
            margin-bottom: 15px;
        }
        .receipt-supplier-header {
            background: #d32f2f;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            margin-bottom: 8px;
            font-size: 10px;
        }
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }
        .receipt-table th, .receipt-table td {
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .receipt-table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .receipt-total {
            margin-top: 15px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: bold;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 10px;
        }
        .receipt-note {
            color: #666;
            font-size: 9px;
            margin-top: 5px;
        }
        @media print {
            .receipt-container {
                padding: 15px;
            }
            .receipt-table th {
                background: #f5f5f5 !important;
                -webkit-print-color-adjust: exact;
            }
            .receipt-supplier-header {
                background: #d32f2f !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
            }
            .receipt-info, .receipt-total {
                background: #f9f9f9 !important;
                -webkit-print-color-adjust: exact;
            }
        }
        .product-review {
            margin-left: auto;
            padding-left: 15px;
        }
        .review-btn {
            background-color: #d32f2f;
            color: white;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85em;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s ease;
        }
        .review-btn:hover {
            background-color: #b71c1c;
            transform: translateY(-1px);
        }
        .review-btn i {
            font-size: 0.85em;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="order-details">
        <h2 style="color:#d32f2f; margin-bottom: 30px;">My Orders</h2>
        <?php if ($orders->num_rows > 0): ?>
            <?php while($order = $orders->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="store">
                            <i class="fas fa-store"></i>
                            <span>CompuCore</span>
                        </div>
                        <span class="status <?= strtolower($order['STATUS']) ?>">
                            <?= htmlspecialchars($order['STATUS']) ?>
                        </span>
                    </div>

                    <div class="order-info">
                        <div class="order-info-item">
                            <i class="fas fa-hashtag"></i>
                            <span>Order #<?= $order['PK_ORDER_ID'] ?></span>
                        </div>
                        <div class="order-info-item">
                            <i class="fas fa-calendar"></i>
                            <span><?= date("M d, Y H:i", strtotime($order['ORDER_DATE'])) ?></span>
                        </div>
                    </div>

                    <div class="order-products">
                        <?php
                        // First, get all items grouped by supplier
                        $item_sql = "SELECT od.QTY, od.PRICE, p.PK_PRODUCT_ID, p.PROD_NAME, p.IMAGE, 
                                   s.COMPANY_NAME, s.PK_SUPPLIER_ID,
                                   CASE WHEN r.PK_REVIEW_ID IS NULL AND o.STATUS = 'Approved' THEN 1 ELSE 0 END as can_review
                                   FROM order_detail od 
                                   JOIN products p ON od.FK1_PRODUCT_ID = p.PK_PRODUCT_ID 
                                   JOIN supplier s ON p.FK2_SUPPLIER_ID = s.PK_SUPPLIER_ID 
                                   JOIN orders o ON od.FK2_ORDER_ID = o.PK_ORDER_ID
                                   LEFT JOIN reviews r ON r.FK3_ORDER_ID = o.PK_ORDER_ID 
                                       AND r.FK1_CUSTOMER_ID = ? 
                                       AND r.FK2_PRODUCT_ID = p.PK_PRODUCT_ID
                                   WHERE od.FK2_ORDER_ID = ?
                                   ORDER BY s.COMPANY_NAME, p.PROD_NAME";
                        $item_stmt = $conn->prepare($item_sql);
                        $item_stmt->bind_param("ii", $_SESSION['customer_id'], $order['PK_ORDER_ID']);
                        $item_stmt->execute();
                        $items = $item_stmt->get_result();
                        
                        $supplier_items = [];
                        $total_items = 0;
                        while($item = $items->fetch_assoc()) {
                            $supplier_id = $item['PK_SUPPLIER_ID'];
                            if (!isset($supplier_items[$supplier_id])) {
                                $supplier_items[$supplier_id] = [
                                    'company_name' => $item['COMPANY_NAME'],
                                    'items' => []
                                ];
                            }
                            $supplier_items[$supplier_id]['items'][] = $item;
                            $total_items += $item['QTY'];
                        }

                        // Display items grouped by supplier
                        foreach($supplier_items as $supplier_id => $supplier_data): ?>
                            <div class="supplier-group">
                                <div class="supplier-header">
                                    <i class="fas fa-truck"></i>
                                    <span><?= htmlspecialchars($supplier_data['company_name']) ?></span>
                                </div>
                                <?php foreach($supplier_data['items'] as $item): ?>
                                    <div class="product-item">
                                        <img src="uploads/<?= htmlspecialchars($item['IMAGE']) ?>" 
                                             class="product-image" 
                                             alt="<?= htmlspecialchars($item['PROD_NAME']) ?>">
                                        <div class="product-info">
                                            <h4><?= htmlspecialchars($item['PROD_NAME']) ?></h4>
                                            <div class="qty">
                                                <i class="fas fa-box"></i>
                                                <span>x<?= $item['QTY'] ?></span>
                                            </div>
                                            <div class="price">₱<?= number_format($item['PRICE'], 2) ?></div>
                                        </div>
                                        <?php if ($order['STATUS'] === 'Approved' && $item['can_review']): ?>
                                            <div class="product-review">
                                                <a href="write_review.php?order_id=<?= $order['PK_ORDER_ID'] ?>&product_id=<?= $item['PK_PRODUCT_ID'] ?>" 
                                                   class="review-btn">
                                                    <i class="fas fa-star"></i>
                                                    Write Review
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-summary">
                        <div class="items-count">
                            <i class="fas fa-boxes"></i>
                            <span><?= $total_items ?> items</span>
                        </div>
                        <div class="total">
                            Total: ₱<?= number_format($order['TOTAL_PRICE'], 2) ?>
                        </div>
                    </div>

                    <div class="order-actions">
                        <button onclick="printReceipt(<?= $order['PK_ORDER_ID'] ?>)" class="action-btn print-btn">
                            <i class="fas fa-print"></i>
                            Print Receipt
                        </button>
                    </div>
                </div>

                <!-- Receipt Template (Hidden) -->
                <div id="receipt-<?= $order['PK_ORDER_ID'] ?>" class="receipt" style="display: none;">
                    <div class="receipt-container">
                        <div class="receipt-header">
                            <img src="uploads/LOGO.PNG" alt="CompuCore Logo" class="receipt-logo">
                            <h1>CompuCore</h1>
                            <p class="receipt-title">Official Receipt</p>
                        </div>
                        
                        <div class="receipt-info">
                            <div class="receipt-order-info">
                                <p><strong>Order #:</strong> <?= $order['PK_ORDER_ID'] ?></p>
                                <p><strong>Date:</strong> <?= date("F d, Y h:i A", strtotime($order['ORDER_DATE'])) ?></p>
                                <p><strong>Status:</strong> <?= htmlspecialchars($order['STATUS']) ?></p>
                            </div>
                            
                            <div class="receipt-customer-info">
                                <h3>Customer Information</h3>
                                <p><strong>Name:</strong> <?= htmlspecialchars($customer['F_NAME'] . ' ' . $customer['L_NAME']) ?></p>
                                <p><strong>Address:</strong> <?= htmlspecialchars($customer['CUSTOMER_ADDRESS']) ?></p>
                                <p><strong>Phone:</strong> <?= htmlspecialchars($customer['PHONE_NUM']) ?></p>
                            </div>
                        </div>

                        <div class="receipt-items">
                            <h3>Order Details</h3>
                            <?php foreach($supplier_items as $supplier_id => $supplier_data): ?>
                                <div class="receipt-supplier-group">
                                    <div class="receipt-supplier-header">
                                        <i class="fas fa-truck"></i> <?= htmlspecialchars($supplier_data['company_name']) ?>
                                    </div>
                                    <table class="receipt-table">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Qty</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($supplier_data['items'] as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['PROD_NAME']) ?></td>
                                                    <td><?= $item['QTY'] ?></td>
                                                    <td>₱<?= number_format($item['PRICE'], 2) ?></td>
                                                    <td>₱<?= number_format($item['PRICE'] * $item['QTY'], 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="receipt-total">
                            <div class="total-row">
                                <span>Total Amount:</span>
                                <span>₱<?= number_format($order['TOTAL_PRICE'], 2) ?></span>
                            </div>
                        </div>

                        <div class="receipt-footer">
                            <p>Thank you for shopping with us!</p>
                            <p class="receipt-note">This is a computer-generated receipt and does not require a signature.</p>
                        </div>
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

    <script>
        function printReceipt(orderId) {
            const receipt = document.getElementById('receipt-' + orderId);
            const printWindow = window.open('', '_blank');
            
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Receipt - Order #${orderId}</title>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                margin: 0;
                                padding: 15px;
                                background: white;
                                font-size: 11px;
                            }
                            .receipt-container {
                                max-width: 800px;
                                margin: 0 auto;
                                padding: 20px;
                                background: white;
                            }
                            .receipt-header {
                                text-align: center;
                                margin-bottom: 15px;
                                padding-bottom: 10px;
                                border-bottom: 1px solid #eee;
                            }
                            .receipt-logo {
                                width: 80px;
                                height: auto;
                                margin-bottom: 5px;
                            }
                            .receipt-title {
                                font-size: 16px;
                                color: #d32f2f;
                                margin: 5px 0;
                            }
                            .receipt-info {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 15px;
                                padding: 10px;
                                background: #f9f9f9;
                                border-radius: 4px;
                                font-size: 10px;
                            }
                            .receipt-order-info, .receipt-customer-info {
                                flex: 1;
                            }
                            .receipt-customer-info h3 {
                                font-size: 12px;
                                margin: 0 0 5px 0;
                                color: #333;
                            }
                            .receipt-customer-info p {
                                margin: 2px 0;
                            }
                            .receipt-supplier-group {
                                margin-bottom: 15px;
                            }
                            .receipt-supplier-header {
                                background: #d32f2f;
                                color: white;
                                padding: 5px 10px;
                                border-radius: 3px;
                                margin-bottom: 8px;
                                font-size: 10px;
                            }
                            .receipt-table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-bottom: 10px;
                                font-size: 10px;
                            }
                            .receipt-table th, .receipt-table td {
                                padding: 6px 8px;
                                text-align: left;
                                border-bottom: 1px solid #eee;
                            }
                            .receipt-table th {
                                background: #f5f5f5;
                                font-weight: bold;
                            }
                            .receipt-total {
                                margin-top: 15px;
                                padding: 10px;
                                background: #f9f9f9;
                                border-radius: 4px;
                            }
                            .total-row {
                                display: flex;
                                justify-content: space-between;
                                font-size: 12px;
                                font-weight: bold;
                            }
                            .receipt-footer {
                                text-align: center;
                                margin-top: 15px;
                                padding-top: 10px;
                                border-top: 1px solid #eee;
                                font-size: 10px;
                            }
                            .receipt-note {
                                color: #666;
                                font-size: 9px;
                                margin-top: 5px;
                            }
                            @media print {
                                .receipt-table th {
                                    background: #f5f5f5 !important;
                                    -webkit-print-color-adjust: exact;
                                }
                                .receipt-supplier-header {
                                    background: #d32f2f !important;
                                    color: white !important;
                                    -webkit-print-color-adjust: exact;
                                }
                                .receipt-info, .receipt-total {
                                    background: #f9f9f9 !important;
                                    -webkit-print-color-adjust: exact;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        ${receipt.innerHTML}
                    </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.print();
        }
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>