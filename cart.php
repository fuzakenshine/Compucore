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
            padding: 20px;
            margin-top: 50px; /* Adjust for fixed header */
            margin-left: 40px;
        }
        .cart-title {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .cart-item {
            display: flex;
            align-items: center;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 15px;
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
        }
        .cart-item-details {
            flex: 1;
        }
        .cart-item-details h3 {
            margin: 0;
            font-size: 18px;
        }
        .cart-item-details p {
            margin: 5px 0;
            color: #555;
        }
        .cart-item-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .cart-item-actions button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin-bottom: 5px;
            border-radius: 5px;
        }
        .cart-item-actions button.cancel {
            background-color: #ccc;
            color: black;
        }
        .cart-item-actions button:hover {
            opacity: 0.9;
        }
        .cta-banner {
            text-align: center;
            background-color: #d32f2f;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .cta-banner h2 {
            margin: 0;
            font-size: 24px;
        }
        .cta-banner p {
            margin: 10px 0;
        }
        .cta-banner button {
            background-color: white;
            color: #d32f2f;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        .cta-banner button:hover {
            background-color: #f4f4f4;
        }
        .popup {
            position: fixed;
            top: 60px;
            right: 20px;
            background-color:rgb(194, 40, 40);
            color: white;
            padding: 15px;
            border-radius: 5px;
            opacity: 0;
            transition: opacity 0.5s ease;
            z-index: 1000;
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
            <?php $grand_total = 0; ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    $total = $row['product_price'] * $row['quantity'];
                    $grand_total += $total;
                ?>
                <div class="cart-item">
                    <img src="uploads/<?php echo htmlspecialchars($row['IMAGE']); ?>" alt="<?php echo htmlspecialchars($row['PROD_NAME']); ?>">
                    <div class="cart-item-details">
                        <h3><?php echo htmlspecialchars($row['PROD_NAME']); ?></h3>
                        <p>Qty: <?php echo $row['quantity']; ?></p>
                        <p>₱<?php echo number_format($row['product_price'], 2); ?></p>
                    </div>
                    <div class="cart-item-actions">
                        <form method="POST" action="order_now.php">
                            <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($row['cart_id']); ?>">
                            <button type="submit">Order Now</button>
                        </form>
                        <form method="POST" action="cancel_item.php" onsubmit="return confirmCancel();">
                            <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($row['cart_id']); ?>">
                            <button type="submit" class="cancel">Cancel</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
            <div class="cta-banner">
                <h2>Enhance your PC performance!</h2>
                <p>Enhance your PC performance with the perfect upgrade – we help you find the tools and components you need to unlock your system's full potential.</p>
                <button>Place Order (₱<?php echo number_format($grand_total, 2); ?>)</button>
            </div>
        <?php else: ?>
            <p>Your cart is empty.</p>
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
        popup.innerText = message;
        document.body.appendChild(popup);

        setTimeout(() => {
            popup.style.opacity = '1';
        }, 100);
        
        setTimeout(() => {
            popup.style.opacity = '0';
        }, 3000);
        
        setTimeout(() => {
            document.body.removeChild(popup);
        }, 3500);
    }

    window.onload = function() {
        <?php if (isset($_GET['message'])): ?>
            showPopup("<?php echo htmlspecialchars($_GET['message']); ?>");
        <?php endif; ?>
    };
</script>

</html>