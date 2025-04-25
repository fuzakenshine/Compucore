<?php
session_start();
include 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Show real SQL errors


// Ensure the user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

// Fetch cart items for the logged-in user
$user_id = $_SESSION['customer_id'];
$sql = "SELECT c.*, p.IMAGE, p.PROD_NAME 
        FROM cart c 
        JOIN products p ON c.product_id = p.PK_PRODUCT_ID 
        WHERE c.customer_id = ? 
        ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <title>My Cart</title>
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
    width: 100px; /* Adjust width as needed */
    height: 30px; /* Adjust height as needed */
    font-weight: bold;
}
        .header .logo img {
            height: 50px;
            width: 80px;
            display: block;
            margin: 0 auto;
            transition: transform 0.3s ease;
        }
        .header .logo img:hover {
            transform: scale(1.1);
        }
        .header .logo a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 24px;
        }
        .header .search-bar {
            display: flex;
            align-items: center;
            position: relative;
            margin-left: 450px;
        }

        .header .search-bar input {
            padding: 8px 35px 8px 12px;
            border: none;
            border-radius: 30px;
            width: 200px;
            outline: none;
        }

        .header .search-bar button {
            position: absolute;
            right: 5px;
            background: none;
            border: none;
            color: #555;
            cursor: pointer;
            font-size: 16px;
        }

        .header .icons {
            display: flex;
            align-items: right;
            margin-left: 400px;
        }
        .header .icons i {
            margin: 0 10px;
            cursor: pointer;
            font-size: 20px;
            color: white;
        }
        .header .burger-menu {
            position: relative;
            display: inline-block;
        }
        .header .burger-menu button {
            background-color: transparent;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        .header .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .header .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .header .dropdown-content a:hover {
            background-color: #ddd;
        }
        .header .burger-menu:hover .dropdown-content {
            display: block;
        }
        .header .cart-buttons button {
            background-color: white;
            color: #d32f2f;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin-left: 10px;
        }
        .cart-container {
            max-width: 1200px;
            margin: 80px auto 40px;
            padding: 0 20px;
        }

        .cart-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            background-color: white;
            border-radius: 12px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .cart-item-details {
            flex: 1;
            padding: 0 20px;
        }

        .cart-item-details h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            margin: 10px 0;
            background: #f5f5f5;
            border-radius: 25px;
            padding: 5px;
            width: fit-content;
        }

        .quantity-controls button {
            background: #fff;
            border: 1px solid #ddd;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .quantity-controls button:hover {
            background: #e0e0e0;
            transform: scale(1.1);
        }

        .quantity-controls input {
            width: 40px;
            text-align: center;
            border: none;
            background: transparent;
            font-size: 16px;
            font-weight: 500;
            padding: 0;
            -moz-appearance: textfield;
        }

        .quantity-controls input::-webkit-outer-spin-button,
        .quantity-controls input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .price-details {
            margin-top: 10px;
            font-size: 16px;
        }

        .item-total {
            color: #d32f2f;
            font-weight: bold;
            font-size: 18px;
        }

        .cart-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .summary-row.total {
            border-bottom: none;
            font-size: 20px;
            font-weight: bold;
            color: #d32f2f;
        }

        .checkout-button {
            background: #d32f2f;
            color: white;
            border: none;
            width: 100%;
            padding: 15px;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 20px;
        }

        .checkout-button:hover {
            background: #b71c1c;
        }
        .popup {
            position: fixed;
            top: 80px;
            right: 20px;
            background-color: #d32f2f;
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 1000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }

        .popup.show {
            opacity: 1;
            transform: translateX(0);
        }

        .cart-item-link {
            display: block;
            transition: transform 0.2s ease;
        }

        .cart-item-link:hover {
            transform: scale(1.05);
        }

        .cart-item-link img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .cart-item-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-left: 400px;
        }

        .cart-item-actions button {
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            border: none;
            transition: all 0.3s ease;
            min-width: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .cart-item-actions button:first-child {
            background-color: #4CAF50;
            color: white;
        }

        .cart-item-actions button:first-child:hover {
            background-color: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .cart-item-actions .cancel {
            background-color: #ff5252;
            color: white;
        }

        .cart-item-actions .cancel:hover {
            background-color: #ff1744;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin: 40px auto;
            max-width: 400px;
        }

        .empty-cart i {
            font-size: 80px;
            color: #d32f2f;
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .empty-cart p {
            font-size: 24px;
            color: #333;
            margin-bottom: 30px;
        }

        .continue-shopping {
            background: #d32f2f;
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .continue-shopping:hover {
            background:rgb(172, 47, 47);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        </style>
</head>
<body>
<header class="header">
<!-- Replace the empty div with an img tag -->
<div class="logo">
    <a href="index.php">
        <img src="uploads/LOGOW.PNG" alt="Compucore Logo" height="30">
    </a>
</div>
        <div class="search-bar">
            <input type="text" placeholder="Search">
            <button><i class="fas fa-search"></i></button>
        </div>
        <div class="icons">
        <a href="cart.php">
            <i class="fas fa-shopping-cart"></i>
        </a>
            <i class="fas fa-money-bill"></i> 
        </div>
        <div class="burger-menu">
            <button><i class="fas fa-bars"></i></button>
            <div class="dropdown-content">
                <a href="profile.php">Profile</a>
                <a href="landing.php">Logout</a>
            </div>
        </div>
    </header>    
    <div class="cart-container">
        <h1 class="cart-title">🛒 My Cart</h1>

        <?php if ($result->num_rows > 0): ?>
            <?php 
            $grand_total = 0;
            $total_items = 0;
            ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    $total = $row['product_price'] * $row['quantity'];
                    $grand_total += $total;
                    $total_items += $row['quantity'];
                ?>
                <div class="cart-item">
                    <a href="viewdetail.php?product_id=<?php echo htmlspecialchars($row['product_id']); ?>" class="cart-item-link">
                        <img src="uploads/<?php echo htmlspecialchars($row['IMAGE']); ?>" 
                             alt="<?php echo htmlspecialchars($row['PROD_NAME']); ?>">
                    </a>
                    <div class="cart-item-details">
                        <h3><?php echo htmlspecialchars($row['PROD_NAME']); ?></h3>
                        <div class="price-details">
                            <p>Price: ₱<?php echo number_format($row['product_price'], 2); ?></p>
                            <p class="item-total">Total: ₱<?php echo number_format($total, 2); ?></p>
                        </div>
                    </div>
                    
                    <div class="quantity-controls">
                        <button onclick="updateQuantity(<?php echo $row['cart_id']; ?>, 'decrease')">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" value="<?php echo $row['quantity']; ?>" 
                               min="1" max="99" readonly
                               onchange="updateQuantity(<?php echo $row['cart_id']; ?>, 'set', this.value)">
                        <button onclick="updateQuantity(<?php echo $row['cart_id']; ?>, 'increase')">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    
                    <div class="cart-item-actions">
                        <button onclick="orderNow(<?php echo $row['cart_id']; ?>)">
                            <i class="fas fa-shopping-bag"></i> Order Now
                        </button>
                        <button class="cancel" onclick="cancelItem(<?php echo $row['cart_id']; ?>)">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Total Items:</span>
                    <span><?php echo $total_items; ?> items</span>
                </div>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>₱<?php echo number_format($grand_total, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>₱<?php echo number_format($grand_total, 2); ?></span>
                </div>
                <button class="checkout-button" onclick="checkout()">
                    Proceed to Checkout
                </button>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Your shopping cart is empty</p>
                <p style="font-size: 16px; color: #666; margin-bottom: 30px;">
                    Looks like you haven't made your choice yet...
                </p>
                <a href="index.php" class="continue-shopping">
                    <i></i> Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>

</body>
<script>
    function confirmCancel() {
        return confirm("Are you sure you want to cancel this item?");
    }

    function showPopup(message) {
        const popup = document.createElement('div');
        popup.className = 'popup';
        popup.innerHTML = `
            <i class="fas fa-info-circle"></i> 
            ${message}
        `;
        document.body.appendChild(popup);

        // Force reflow
        popup.offsetHeight;

        // Add show class
        popup.classList.add('show');

        // Remove after 3 seconds
        setTimeout(() => {
            popup.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(popup);
            }, 300);
        }, 3000);
    }

    window.onload = function() {
        <?php if (isset($_GET['message'])): ?>
            showPopup("<?php echo htmlspecialchars($_GET['message']); ?>");
        <?php endif; ?>
    };

    function updateQuantity(cartId, action, value = null) {
        let formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('action', action);
        if (value) formData.append('value', value);

        fetch('update_cart_quantity.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showPopup(data.message || 'Error updating quantity');
            }
        })
        .catch(error => {
            showPopup('Error updating quantity');
        });
    }

    function checkout() {
        window.location.href = 'checkout.php';
    }

    function cancelItem(cartId) {
        if (confirm("Are you sure you want to cancel this item?")) {
            const formData = new FormData();
            formData.append('cart_id', cartId);

            fetch('cancel_item.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showPopup('Item cancelled successfully');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showPopup('Error cancelling item');
                }
            })
            .catch(error => {
                showPopup('Error cancelling item');
            });
        }
    }
    function orderNow(cartId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'ordernow.php';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'cart_id';
    input.value = cartId;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
</script>

</html>