<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

// Get cart_id from POST data
$cart_id = isset($_POST['cart_id']) ? $_POST['cart_id'] : null;

if ($cart_id) {
    // Fetch cart and product details
    $sql = "SELECT c.*, p.PROD_NAME, p.PRICE, p.IMAGE, p.PK_PRODUCT_ID 
            FROM cart c 
            JOIN products p ON c.product_id = p.PK_PRODUCT_ID 
            WHERE c.cart_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $item = $result->fetch_assoc();
        $subtotal = $item['quantity'] * $item['PRICE'];
        $shipping = 4;
        $tax = 0;
        $total = $subtotal + $shipping + $tax;
        $order_id = mt_rand(1000000000, 9999999999);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase'])) {
            // Begin transaction
            $conn->begin_transaction();

            try {
                // Insert into orders table
                $order_sql = "INSERT INTO orders (FK1_CUSTOMER_ID, FK2_PAYMENT_ID, FK3_USER_ID, TOTAL_PRICE, STATUS, LINE_TOTAL) 
                            VALUES (?, 1, 1, ?, 'Pending', ?)";
                $order_stmt = $conn->prepare($order_sql);
                $order_stmt->bind_param("idd", 
                    $item['customer_id'],
                    $total,
                    $subtotal
                );
                $order_stmt->execute();
                $order_id = $conn->insert_id;

                // Insert into order_detail table
                $detail_sql = "INSERT INTO order_detail (FK1_PRODUCT_ID, FK2_ORDER_ID, QTY, PRICE) 
                             VALUES (?, ?, ?, ?)";
                $detail_stmt = $conn->prepare($detail_sql);
                $detail_stmt->bind_param("iiid",
                    $item['PK_PRODUCT_ID'],
                    $order_id,
                    $item['quantity'],
                    $item['PRICE']
                );
                $detail_stmt->execute();

                // Remove item from cart
                $delete_sql = "DELETE FROM cart WHERE cart_id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $cart_id);
                $delete_stmt->execute();

                // Commit transaction
                $conn->commit();

                // Redirect to success page
                header("Location: order_success.php?order_id=" . $order_id);
                exit();

            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                $error = "Error processing order: " . $e->getMessage();
            }
        }
    } else {
        header('Location: cart.php');
        exit();
    }
} else {
    header('Location: cart.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Detail</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            padding: 10px 20px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: #d32f2f;
            height: 30px;
        }

        .header .logo {
            font-size: 24px;
            width: 100px;
            height: 30px;
            font-weight: bold;
        }

        .header .logo img {
            height: 30px;
            display: block;
            transition: transform 0.3s ease;
        }

        .header .nav-links {
            display: flex;
            gap: 20px;
        }

        .header .nav-links a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .header .nav-links a:hover {
            opacity: 0.8;
        }

        .order-container {
            max-width: 1000px;
            margin: 80px auto 40px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 45% 55%;
            gap: 30px;
        }

        .product-image {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            height: fit-content;
        }

        .product-image img {
            max-width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: contain;
        }

        .order-details {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .detail-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .detail-header h2 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .detail-row span:first-child {
            color: #666;
        }

        .summary-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .total-row {
            font-size: 20px;
            font-weight: bold;
            color: #d32f2f;
            border-bottom: none !important;
        }

        .payment-method {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            border: 1px solid #eee;
        }

        .purchase-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: #d32f2f;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .purchase-btn:hover {
            background: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.2);
        }

        .ordered-note {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .order-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">
            <a href="index.php">
                <img src="uploads/LOGOW.PNG" alt="Compucore Logo">
            </a>
        </div>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
            <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="order-container">
        <div class="product-image">
            <img src="uploads/<?php echo htmlspecialchars($item['IMAGE']); ?>" 
                 alt="<?php echo htmlspecialchars($item['PROD_NAME']); ?>">
        </div>
        
        <div class="order-details">
            <div class="detail-header">
                <h2>Order Detail</h2>
            </div>
            
            <div class="detail-row">
                <span>Order ID:</span>
                <span><?php echo $order_id; ?></span>
            </div>
            
            <div class="detail-row">
                <span>Name:</span>
                <span><?php echo htmlspecialchars($item['PROD_NAME']); ?></span>
            </div>
            
            <div class="detail-row">
                <span>Qty:</span>
                <span><?php echo $item['quantity']; ?></span>
            </div>
            
            <div class="detail-row">
                <span>Price:</span>
                <span>₱<?php echo number_format($item['PRICE'], 2); ?></span>
            </div>
            
            <div class="summary-section">
                <div class="detail-row">
                    <span>Sub Total:</span>
                    <span>₱<?php echo number_format($subtotal, 2); ?></span>
                </div>
                
                <div class="detail-row">
                    <span>Shipping Fee:</span>
                    <span>₱<?php echo number_format($shipping, 2); ?></span>
                </div>
                
                <div class="detail-row">
                    <span>Tax:</span>
                    <span>₱<?php echo number_format($tax, 2); ?></span>
                </div>
                
                <div class="detail-row total-row">
                    <span>Total:</span>
                    <span>₱<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
            
            <div class="payment-method">
                Payment Method
            </div>
            
            <form method="POST">
                <input type="hidden" name="cart_id" value="<?php echo $cart_id; ?>">
                <button type="submit" name="purchase" class="purchase-btn">
                    Purchase
                </button>
            </form>

            <div class="ordered-note">
                <small>Ordered Note: Ship all this ordered product by Friday and we will send you an email please check. Thankyou!</small>
            </div>
        </div>
    </div>
</body>
</html>