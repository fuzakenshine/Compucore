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
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .checkout-flex-max, .main-container, .main-content {
            flex: 1 0 auto;
        }
        footer {
            flex-shrink: 0;
            width: 100%;
            background: #d32f2f;
            color: white;
            text-align: center;
            padding: 16px 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-container {
            max-width: 1800px;
            margin: 0 auto;
            padding: 10px 10px;
            display: grid;
            grid-template-columns: 2.2fr 0.9fr;
            gap: 30px;
            min-height: calc(100vh - 60px);
        }

        .checkout-content {
            background: white;
            border-radius: 12px;
            margin-top: 60px;
            margin-bottom: 20px;
            padding: 30px 32px 30px 32px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow-y: auto;
            height: calc(95vh - 90px);
            position: relative;
        }

        .checkout-content::-webkit-scrollbar {
            width: 8px;
        }

        .checkout-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 8px;
        }

        .checkout-content::-webkit-scrollbar-thumb {
            background: #d32f2f;
            border-radius: 8px;
        }

        .checkout-content::-webkit-scrollbar-thumb:hover {
            background: #b71c1c;
        }

        .checkout-section {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
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
            border-radius: 12px;
            margin-top: 50px;
            padding: 20px 18px;
            position: sticky;
            top: 60px;
            height: fit-content;
            max-width: 370px;
            min-width: 270px;
            max-height: calc(95vh - 100px); /* Prevent overflow */
            overflow-y: auto;
        }

        .order-summary h2 {
            font-size: 1.2em;
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95em;
            color: #666;
        }

        .summary-item.total {
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
            padding-top: 10px;
            border-top: 2px solid #f0f0f0;
            margin-top: 10px;
        }

        .section-title {
            font-size: 1.1em;
            margin: 0 0 15px 0;
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
            gap: 15px;
            padding: 15px;
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
            font-size: 22px;
            padding: 10px;
            color: #d32f2f;
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
            font-size: 1.1em;
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
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .store-section img {
            width: 50px;
            height: 50px;
            border: 2px solid #d32f2f;
            border-radius: 50%;
            padding: 3px;
            transition: transform 0.3s ease;
        }

        .store-section img:hover {
            transform: scale(1.05);
        }

        .store-section h3 {
            font-size: 1.2em;
            color: #333;
            margin: 0;
        }

        .product-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            gap: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .product-image {
            width: 80px;
            height: 80px;
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
            padding: 15px;
            margin: 10px 0;
            transition: all 0.3s ease;
            background: white;
        }

        .shipping-option:hover {
            border-color: #d32f2f;
            box-shadow: 0 2px 12px rgba(211, 47, 47, 0.1);
        }

        .shipping-details strong {
            font-size: 1em;
            color: #333;
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
            padding: 12px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin: 8px 0;
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
            padding: 15px 25px;
            border-radius: 8px;
            width: 100%;
            font-size: 1em;
            cursor: pointer;
            margin-top: 15px;
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

        footer {
            margin-top: auto;
        }

        /* Add these new styles for better spacing */
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-row > * {
            flex: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .main-container {
                grid-template-columns: 1fr 320px;
                max-width: 100vw;
                padding: 0 5px;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                grid-template-columns: 1fr;
                max-width: 100vw;
                padding: 0 2px;
            }
            .order-summary {
                position: relative;
                top: 0;
                margin-top: 0;
                max-width: 100%;
                min-width: unset;
            }
            .checkout-content {
                margin-top: 60px;
                height: auto;
                overflow-y: visible;
                padding: 16px 4px;
            }
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            padding: 10px 0;
        }

        .section-header i.fa-chevron-down {
            transition: transform 0.3s ease;
        }

        .section-header.active i.fa-chevron-down {
            transform: rotate(180deg);
        }

        .section-content {
            display: none;
            padding-top: 15px;
        }

        .section-content.active {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .checkout-flex-max {
            display: flex;
            flex-direction: row;
            margin-left: 25px;  
            width: 95vw;
            min-height: calc(100vh - 60px);
            padding: 0 24px;
            box-sizing: border-box;
            gap: 32px;
            background: #f0f2f5;
        }
        .checkout-content {
            flex: 2.5;
            min-width: 0;
        }
        .order-summary {
            flex: 1;
            min-width: 270px;
            max-width: 400px;
        }
        @media (max-width: 1024px) {
            .checkout-flex-max {
                flex-direction: column;
                padding: 0 6px;
            }
            .order-summary {
                max-width: 100%;
                min-width: unset;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

    <div class="checkout-flex-max">
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
                <div class="section-header" onclick="toggleSection(this)">
                    <h2 class="section-title"><i class="fas fa-truck"></i> Shipping Options</h2>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="section-content">
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
            </div>

            <div class="checkout-section">
                <div class="section-header" onclick="toggleSection(this)">
                    <h2 class="section-title"><i class="fas fa-credit-card"></i> Payment Method</h2>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="section-content">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            // Show processing popup
            Swal.fire({
                title: 'Processing Order',
                text: 'Please wait while we process your order...',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

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
                    Swal.fire({
                        title: 'Success!',
                        text: 'Your order has been placed successfully! Waiting for admin approval.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Error placing order: ' + data.message,
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Error placing order. Please try again.',
                    icon: 'error'
                });
            });
        });

        function toggleSection(header) {
            const content = header.nextElementSibling;
            const isActive = header.classList.contains('active');
            
            // Close all sections first
            document.querySelectorAll('.section-header').forEach(h => {
                h.classList.remove('active');
                h.nextElementSibling.classList.remove('active');
            });
            
            // Toggle clicked section
            if (!isActive) {
                header.classList.add('active');
                content.classList.add('active');
            }
        }

        // Open first section by default on page load
        document.addEventListener('DOMContentLoaded', function() {
            const firstSection = document.querySelector('.section-header');
            if (firstSection) {
                toggleSection(firstSection);
            }
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>