<?php
session_start();
include 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['customer_id'];

// Fetch user's address
$address_sql = "SELECT * FROM customer WHERE PK_CUSTOMER_ID = ?";
$stmt = $conn->prepare($address_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Fetch cart items
$cart_sql = "SELECT c.*, p.IMAGE, p.PROD_NAME
             FROM cart c 
             JOIN products p ON c.product_id = p.PK_PRODUCT_ID 
             WHERE c.customer_id = ?";
if (isset($_GET['selected_items'])) {
    $selected_items = explode(',', $_GET['selected_items']);
    $placeholders = str_repeat('?,', count($selected_items) - 1) . '?';
    $cart_sql .= " AND c.cart_id IN ($placeholders)";
    $stmt = $conn->prepare($cart_sql);
    $types = "i" . str_repeat("i", count($selected_items));
    $params = array_merge([$user_id], $selected_items);
    $stmt->bind_param($types, ...$params);
} else {
    $stmt = $conn->prepare($cart_sql);
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$cart_result = $stmt->get_result();

// Calculate totals
$subtotal = 0;
$protection_fee = 132; // Electronic Protection fee from image
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            color: #333;
            min-height: 100vh;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        .checkout-content {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .checkout-section {
            background: white;
            padding: 25px;
            margin-bottom: 0;
            border-bottom: 1px solid #eee;
            transition: transform 0.2s ease;
        }

        .checkout-section:last-child {
            border-bottom: none;
        }

        .checkout-section:hover {
            transform: translateY(-2px);
        }

        .order-summary {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 25px;
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .order-summary h2 {
            color: #333;
            font-size: 1.5em;
            margin: 0 0 20px 0;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: #666;
        }

        .summary-item.total {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            padding-top: 15px;
            border-top: 2px solid #f0f0f0;
            margin-top: 15px;
        }

        .section-title {
            font-size: 1.3em;
            color: #333;
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #d32f2f;
        }

        .address-section {
            display: flex;
            align-items: start;
            gap: 20px;
            padding: 25px;
            background: #fff5f5;
            border-radius: 12px;
            border-left: 4px solid #d32f2f;
            position: relative;
            overflow: hidden;
        }

        .address-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background: linear-gradient(45deg, transparent, rgba(211, 47, 47, 0.05));
            border-radius: 0 0 0 150px;
        }

        .address-icon {
            color: #d32f2f;
            font-size: 28px;
            margin-top: 5px;
            background: white;
            padding: 12px;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(211, 47, 47, 0.1);
        }

        .address-content {
            flex: 1;
        }

        .address-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .address-name {
            font-size: 1.3em;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .address-phone {
            color: #666;
            font-size: 1.1em;
            margin: 5px 0;
        }

        .address-details {
            color: #555;
            font-size: 1.1em;
            line-height: 1.5;
            margin: 0;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .address-label {
            display: inline-block;
            background: #d32f2f;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .store-section {
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .store-section img {
            border: 2px solid #d32f2f;
            border-radius: 50%;
            padding: 3px;
            transition: transform 0.3s ease;
        }

        .store-section img:hover {
            transform: scale(1.05);
        }

        .store-section h3 {
            color: #333;
            font-size: 1.5em;
            margin: 0;
        }

        .product-item {
            display: flex;
            align-items: center;
            padding: 20px 0;
            gap: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .product-details {
            flex: 1;
        }

        .product-details h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.1em;
        }

        .product-details p {
            margin: 5px 0;
            color: #666;
        }

        .shipping-option {
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            transition: all 0.3s ease;
            background: white;
        }

        .shipping-option:hover {
            border-color: #d32f2f;
            box-shadow: 0 2px 12px rgba(211, 47, 47, 0.1);
        }

        .shipping-details strong {
            color: #333;
            font-size: 1.1em;
        }

        .shipping-details p {
            color: #666;
            margin: 8px 0;
        }

        .shipping-price {
            color: #d32f2f;
            font-weight: bold;
            font-size: 1.1em;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin: 10px 0;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: #d32f2f;
            background: #fff5f5;
        }

        .payment-method label {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
        }

        .payment-method i {
            font-size: 1.2em;
            color: #666;
        }

        .payment-method input[type="radio"]:checked + label {
            color: #d32f2f;
        }

        .payment-method input[type="radio"]:checked + label i {
            color: #d32f2f;
        }

        .place-order-btn {
            background: #d32f2f;
            color: white;
            border: none;
            padding: 18px 30px;
            border-radius: 8px;
            width: 100%;
            font-size: 1.1em;
            cursor: pointer;
            margin-top: 25px;
            transition: all 0.3s ease;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .place-order-btn:hover {
            background: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(211, 47, 47, 0.2);
        }

        .back-button {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 15px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
        }

        .back-button:hover {
            transform: translateX(-5px);
            background: rgba(255, 255, 255, 0.2);
        }

        .back-button i {
            font-size: 1.1em;
        }

        /* Add smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Custom radio button styling */
        input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #d32f2f;
        }

        /* Add new topnav styles */
        .topnav {
            background-color: #d32f2f;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .topnav-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .topnav-title {
            color: white;
            font-size: 1.5em;
            font-weight: bold;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="topnav">
        <div class="topnav-left">
            <a href="cart.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h1 class="topnav-title">Checkout</h1>
        </div>
    </div>

    <div class="main-container">
        <div class="checkout-content">
            <div class="checkout-section">
                <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Delivery Information</h2>
                <div class="address-section">
                    <i class="fas fa-map-marker-alt address-icon"></i>
                    <div class="address-content">
                        <div class="address-header">
                            <div>
                                <span class="address-label">Delivery Address</span>
                                <h3 class="address-name"><?php echo htmlspecialchars($user_data['F_NAME'] . ' ' . $user_data['L_NAME']); ?></h3>
                                <p class="address-phone">
                                    <i class="fas fa-phone-alt" style="margin-right: 8px;"></i>
                                    <?php echo htmlspecialchars($user_data['PHONE_NUM']); ?>
                                </p>
                            </div>
                        </div>
                        <p class="address-details">
                            <i class="fas fa-home" style="margin-right: 8px; color: #d32f2f;"></i>
                            <?php echo htmlspecialchars($user_data['CUSTOMER_ADDRESS']); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="checkout-section">
                <h2 class="section-title"><i class="fas fa-store"></i> Order Details</h2>
                <div class="store-section">
                    <img src="uploads/LOGO.PNG" alt="Store Logo" style="width: 65px; height: 65px;">
                    <h3>CompuCore</h3>
                </div>
                <?php while($cart_item = $cart_result->fetch_assoc()): 
                    $subtotal += $cart_item['product_price'] * $cart_item['quantity'];
                ?>
                <div class="product-item">
                    <img src="uploads/<?php echo htmlspecialchars($cart_item['IMAGE']); ?>" alt="Product" class="product-image">
                    <div class="product-details">
                        <h4><?php echo htmlspecialchars($cart_item['PROD_NAME']); ?></h4>
                        <p>₱<?php echo number_format($cart_item['product_price'], 2); ?></p>
                        <p>Quantity: <?php echo $cart_item['quantity']; ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="checkout-section">
                <h2 class="section-title"><i class="fas fa-truck"></i> Shipping Options</h2>
                <form id="shipping-form" class="shipping-form">
                    <div class="shipping-option">
                        <input type="radio" id="standard" name="shipping_method" value="standard" checked>
                        <label for="standard">
                            <div class="shipping-details">
                                <strong>Standard Local</strong>
                                <p>Guaranteed to get by 5 - 8 May</p>
                                <span class="shipping-price">Free</span>
                            </div>
                        </label>
                    </div>
                    <div class="shipping-option">
                        <input type="radio" id="express" name="shipping_method" value="express">
                        <label for="express">
                            <div class="shipping-details">
                                <strong>Express Delivery</strong>
                                <p>Guaranteed to get by 3 - 4 May</p>
                                <span class="shipping-price">₱150.00</span>
                            </div>
                        </label>
                    </div>
                    <div class="shipping-option">
                        <input type="radio" id="same-day" name="shipping_method" value="same-day">
                        <label for="same-day">
                            <div class="shipping-details">
                                <strong>Same Day Delivery</strong>
                                <p>Guaranteed delivery within 24 hours</p>
                                <span class="shipping-price">₱250.00</span>
                            </div>
                        </label>
                    </div>
                </form>
            </div>

            <div class="checkout-section">
                <h2 class="section-title"><i class="fas fa-credit-card"></i> Payment Method</h2>
                <form id="payment-form" class="payment-form">
                    <div class="payment-method">
                        <input type="radio" id="cod" name="payment_method" value="cod" checked>
                        <label for="cod">
                            <i class="fas fa-money-bill"></i>
                            <span>Cash on Delivery</span>
                        </label>
                    </div>
                    <div class="payment-method">
                        <input type="radio" id="card" name="payment_method" value="card">
                        <label for="card">
                            <i class="fas fa-credit-card"></i>
                            <span>Credit/Debit Card</span>
                        </label>
                    </div>
                    <div class="payment-method">
                        <input type="radio" id="ewallet" name="payment_method" value="ewallet">
                        <label for="ewallet">
                            <i class="fas fa-mobile-alt"></i>
                            <span>E-Wallet</span>
                        </label>
                    </div>
                </form>
            </div>
        </div>

        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="summary-item">
                <span>Subtotal</span>
                <span>₱<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="summary-item">
                <span>Shipping Fee</span>
                <span id="shipping-fee">Free</span>
            </div>
            <div class="summary-item total">
                <span>Total</span>
                <span id="total-amount">₱<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <button class="place-order-btn">Place Order</button>
        </div>
    </div>

    <script>
        // Handle shipping fee updates
        const shippingForm = document.getElementById('shipping-form');
        const shippingFeeElement = document.getElementById('shipping-fee');
        const totalAmountElement = document.getElementById('total-amount');
        const subtotal = <?php echo $subtotal; ?>;

        function updateTotal(shippingCost) {
            const total = subtotal + shippingCost;
            totalAmountElement.textContent = '₱' + total.toFixed(2);
        }

        shippingForm.addEventListener('change', function(e) {
            if (e.target.name === 'shipping_method') {
                let shippingCost = 0;
                let shippingText = 'Free';

                switch(e.target.value) {
                    case 'express':
                        shippingCost = 150;
                        shippingText = '₱150.00';
                        break;
                    case 'same-day':
                        shippingCost = 250;
                        shippingText = '₱250.00';
                        break;
                    default:
                        shippingCost = 0;
                        shippingText = 'Free';
                }

                shippingFeeElement.textContent = shippingText;
                updateTotal(shippingCost);
            }
        });

        document.querySelector('.place-order-btn').addEventListener('click', function() {
            // Get selected shipping and payment methods
            const shippingMethod = document.querySelector('input[name="shipping_method"]:checked').value;
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            // Create form data
            const formData = new FormData();
            formData.append('shipping_method', shippingMethod);
            formData.append('payment_method', paymentMethod);
            
            // Get selected items from URL if present
            const urlParams = new URLSearchParams(window.location.search);
            const selectedItems = urlParams.get('selected_items');
            
            // Send order to server
            fetch('process_order.php' + (selectedItems ? '?selected_items=' + selectedItems : ''), {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order placed successfully! Waiting for admin approval.');
                    window.location.href = 'index.php';
                } else {
                    alert('Error placing order: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error placing order. Please try again.');
            });
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html> 