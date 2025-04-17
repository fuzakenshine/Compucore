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
$sql = "SELECT c.*, p.IMAGE, p.PROD_NAME FROM cart c JOIN products p ON c.product_id = p.PK_PRODUCT_ID WHERE c.customer_id = ?";
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
    <title>My Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .header {
            background-color: #d32f2f;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header .logo {
            font-size: 24px;
            font-weight: bold;
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
        </style>
</head>
<body>
    <header class="header">
        <div class="logo">CompuCore Parts</div>
        <div class="cart-buttons">
            <button>My Cart</button>
            <button>My Purchase</button>
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
    <?php if (isset($_GET['message'])): ?>
    <div class="alert" style="padding: 10px; background-color: #d32f2f; color: white; margin: 20px 0; border-radius: 5px;">
        <?php echo htmlspecialchars($_GET['message']); ?>
    </div>
<?php endif; ?>

</body>
<script>
    function confirmCancel() {
        return confirm("Are you sure you want to cancel this item?");
    }
</script>

</html>