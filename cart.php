<?php
session_start();
include 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Show real SQL errors


// Ensure the user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

// Function to check product stock
function checkProductStock($conn, $product_id, $requested_qty) {
    $sql = "SELECT QTY FROM products WHERE PK_PRODUCT_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['QTY'] >= $requested_qty;
}

// Fetch cart items for the logged-in user
$user_id = $_SESSION['customer_id'];
$sql = "SELECT c.*, p.IMAGE, p.PROD_NAME, p.QTY as available_stock, s.COMPANY_NAME as supplier_name 
        FROM cart c 
        JOIN products p ON c.product_id = p.PK_PRODUCT_ID 
        LEFT JOIN supplier s ON p.FK2_SUPPLIER_ID = s.PK_SUPPLIER_ID
        WHERE c.customer_id = ? 
        ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle AJAX quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_quantity') {
    $response = array('success' => false, 'message' => '');
    
    try {
        $cart_id = intval($_POST['cart_id']);
        $new_qty = intval($_POST['quantity']);
        
        // Get current cart item
        $check_sql = "SELECT c.*, p.QTY as available_stock 
                     FROM cart c 
                     JOIN products p ON c.product_id = p.PK_PRODUCT_ID 
                     WHERE c.cart_id = ? AND c.customer_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $cart_id, $user_id);
        $check_stmt->execute();
        $cart_item = $check_stmt->get_result()->fetch_assoc();
        
        if (!$cart_item) {
            throw new Exception("Cart item not found");
        }
        
        // Check if requested quantity exceeds available stock
        if ($new_qty > $cart_item['available_stock']) {
            throw new Exception("Order quantity exceeds available stock. Only " . $cart_item['available_stock'] . " items available.");
        }
        
        // Update quantity
        $update_sql = "UPDATE cart SET quantity = ? WHERE cart_id = ? AND customer_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iii", $new_qty, $cart_id, $user_id);
        
        if ($update_stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Quantity updated successfully";
        } else {
            throw new Exception("Failed to update quantity");
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
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
            display: grid;
            grid-template-columns: auto 1fr auto auto;
            gap: 20px;
            align-items: center;
            background-color: white;
            border-radius: 12px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
        }

        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background-color: #fafafa;
        }

        .cart-item.selected {
            background-color: #fff5f5;
            border: 1px solid #ffcdd2;
        }

        .cart-item-checkbox {
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .cart-item-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .cart-item-content {
            display: flex;
            align-items: center;
            gap: 20px;
            pointer-events: none;
            flex: 1;
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

        .supplier-info {
            color: #666;
            font-size: 13px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .supplier-info i {
            color: #888;
            font-size: 12px;
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

        .cart-item-controls {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center;
            z-index: 2;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            background: #f5f5f5;
            border-radius: 25px;
            padding: 5px;
            width: fit-content;
        }

        .cart-item-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 2;
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

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin: 40px auto;
            max-width: 400px;
            margin-bottom: 250px;
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

        .stock-info {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }

        .stock-warning {
            color: #d32f2f;
            font-weight: bold;
        }

        .popup.error {
            background-color: #d32f2f;
        }

        .quantity-controls input[type="number"] {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 4px;
        }

        .quantity-controls input[type="number"]:invalid {
            border-color: #d32f2f;
        }
        </style>
</head>
<body>
<?php include 'header.php'; ?>
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
                <div class="cart-item" onclick="toggleItemSelection(this)">
                    <div class="cart-item-checkbox">
                        <input type="checkbox" class="item-checkbox" name="selected_items[]" value="<?php echo $row['cart_id']; ?>" onclick="event.stopPropagation();">
                    </div>
                    <div class="cart-item-content">
                        <img src="uploads/<?php echo htmlspecialchars($row['IMAGE']); ?>" 
                             alt="<?php echo htmlspecialchars($row['PROD_NAME']); ?>"
                             class="cart-item-image">
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($row['PROD_NAME']); ?></h3>
                            <div class="supplier-info">
                                <i class="fas fa-truck"></i>
                                <span><?php echo htmlspecialchars($row['supplier_name']); ?></span>
                            </div>
                            <div class="price-details">
                                <p>Price: ₱<?php echo number_format($row['product_price'], 2); ?></p>
                                <p class="item-total">Total: ₱<?php echo number_format($total, 2); ?></p>
                
                            </div>
                        </div>
                    </div>
                    <div class="cart-item-controls">
                        <div class="quantity-controls">
                            <button onclick="event.stopPropagation(); updateQuantity(<?php echo $row['cart_id']; ?>, 'decrease')">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" value="<?php echo $row['quantity']; ?>" 
                                   min="1" max="<?php echo $row['available_stock']; ?>" 
                                   data-max="<?php echo $row['available_stock']; ?>"
                                   onchange="event.stopPropagation(); updateQuantity(<?php echo $row['cart_id']; ?>, 'set', this.value)">
                            <button onclick="event.stopPropagation(); updateQuantity(<?php echo $row['cart_id']; ?>, 'increase')">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="cart-item-actions">
                        <button class="cancel" onclick="event.stopPropagation(); cancelItem(<?php echo $row['cart_id']; ?>)" style="background-color: red; color: white;">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Selected Items:</span>
                    <span id="selected-items-count">0 items</span>
                </div>
                <div class="summary-row">
                    <span>Total Items:</span>
                    <span><?php echo $total_items; ?> items</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span id="selected-total">₱0.00</span>
                </div>
                <button class="checkout-button" onclick="checkout()" id="checkout-button" disabled>
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
    <?php include 'footer.php'; ?>
</body>
<script>
    function confirmCancel() {
        return confirm("Are you sure you want to remove this item?");
    }

    function showPopup(message, isError = false) {
        const popup = document.createElement('div');
        popup.className = 'popup ' + (isError ? 'error' : '');
        popup.innerHTML = `
            <i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i> 
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
        const input = document.querySelector(`input[data-max]`);
        const maxStock = parseInt(input.dataset.max);
        let newQty;

        if (action === 'set') {
            newQty = parseInt(value);
        } else {
            const currentQty = parseInt(input.value);
            newQty = action === 'increase' ? currentQty + 1 : currentQty - 1;
        }

        if (newQty > maxStock) {
            showPopup(`Order quantity exceeds available stock. Only ${maxStock} items available.`, true);
            return;
        }

        if (newQty < 1) {
            showPopup('Quantity must be at least 1', true);
            return;
        }

        let formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('action', 'update_quantity');
        formData.append('quantity', newQty);

        fetch('cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showPopup(data.message || 'Error updating quantity', true);
            }
        })
        .catch(error => {
            showPopup('Error updating quantity', true);
        });
    }

    function updateSelectedItems() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const selectedCount = document.querySelectorAll('.item-checkbox:checked').length;
        const selectedItemsCount = document.getElementById('selected-items-count');
        const checkoutButton = document.getElementById('checkout-button');
        const selectedTotal = document.getElementById('selected-total');
        
        let total = 0;
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const cartItem = checkbox.closest('.cart-item');
                const priceText = cartItem.querySelector('.price-details p:first-child').textContent;
                const quantity = parseInt(cartItem.querySelector('.quantity-controls input').value);
                
                // Extract price from "Price: ₱X,XXX.XX" format
                const price = parseFloat(priceText.replace('Price: ₱', '').replace(/,/g, ''));
                
                if (!isNaN(price) && !isNaN(quantity)) {
                    total += price * quantity;
                }
            }
        });

        selectedItemsCount.textContent = `${selectedCount} items`;
        selectedTotal.textContent = `₱${total.toFixed(2)}`;
        checkoutButton.disabled = selectedCount === 0;
    }

    function toggleItemSelection(cartItem) {
        const checkbox = cartItem.querySelector('.item-checkbox');
        checkbox.checked = !checkbox.checked;
        cartItem.classList.toggle('selected', checkbox.checked);
        updateSelectedItems();
    }

    // Update the DOMContentLoaded event listener
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const cartItem = this.closest('.cart-item');
                cartItem.classList.toggle('selected', this.checked);
                updateSelectedItems();
            });
        });

        // Restore checked items after page load
        const checkedItems = JSON.parse(sessionStorage.getItem('checkedItems') || '[]');
        checkedItems.forEach(cartId => {
            const checkbox = document.querySelector(`.item-checkbox[value="${cartId}"]`);
            if (checkbox) {
                checkbox.checked = true;
                checkbox.closest('.cart-item').classList.add('selected');
            }
        });
        
        // Clear the stored items
        sessionStorage.removeItem('checkedItems');
        
        // Update the totals for restored checkboxes
        updateSelectedItems();
    });

    function checkout() {
        const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'))
            .map(checkbox => checkbox.value);
        
        if (selectedItems.length === 0) {
            showPopup('Please select at least one item to checkout');
            return;
        }

        // Pass selected items to checkout page
        window.location.href = 'checkout.php?selected_items=' + selectedItems.join(',');
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