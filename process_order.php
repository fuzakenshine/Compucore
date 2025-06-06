<?php
session_start();
include 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['loggedin'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['customer_id'];
$shipping_method = $_POST['shipping_method'];
$payment_method = $_POST['payment_method'];

// Calculate shipping cost
$shipping_cost = 0;
switch($shipping_method) {
    case 'express':
        $shipping_cost = 150;
        break;
    case 'same-day':
        $shipping_cost = 250;
        break;
    default:
        $shipping_cost = 0;
}

// Start transaction
$conn->begin_transaction();

try {
    // Get cart items
    $cart_sql = "SELECT c.*, p.PROD_NAME, p.PRICE as product_price 
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
    
    // Calculate total
    $total = 0;
    $cart_items = [];
    while($item = $cart_result->fetch_assoc()) {
        $total += $item['product_price'] * $item['quantity'];
        $cart_items[] = $item;
    }
    $total += $shipping_cost;

    // Insert order using stored procedure
    $stmt = $conn->prepare("CALL populateORDERS(?, ?, ?)");
    $stmt->bind_param("idi", $user_id, $total, $payment_method);
    $stmt->execute();
    $result = $stmt->get_result();
    $order_row = $result->fetch_assoc();
    $order_id = $order_row['order_id'];
    $stmt->close();
    $conn->next_result(); // Clear results for next queries

    // Create notification for the order
    $notification_sql = "INSERT INTO notifications (FK_CUSTOMER_ID, MESSAGE, TYPE) 
                        VALUES (?, ?, 'order')";
    $product_names = array_map(function($item) { return $item['PROD_NAME']; }, $cart_items);
    $notification_message = "Your order for " . implode(", ", $product_names) . " has been placed and is awaiting approval.";
    $stmt = $conn->prepare($notification_sql);
    $stmt->bind_param("is", $user_id, $notification_message);
    $stmt->execute();

    // Insert order items
    $stmt = $conn->prepare("CALL populateORDERDETAILS(?, ?, ?, ?)");
    
    foreach($cart_items as $item) {
        $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['product_price']);
        $stmt->execute();
    }
    $stmt->close();
    $conn->next_result(); // Clear results for next queries

    // Clear cart items
    if (isset($_GET['selected_items'])) {
        $delete_sql = "DELETE FROM cart WHERE customer_id = ? AND cart_id IN ($placeholders)";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param($types, ...$params);
    } else {
        $delete_sql = "DELETE FROM cart WHERE customer_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Order placed successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error processing order: ' . $e->getMessage()]);
}

$conn->close();
?> 