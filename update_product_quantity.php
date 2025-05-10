<?php
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

function updateProductQuantity($order_id, $conn) {
    // Get all products in the order with correct column names
    $sql = "SELECT od.FK1_PRODUCT_ID, od.QTY, p.QTY as STOCK_QTY, p.PROD_NAME 
            FROM order_detail od
            JOIN products p ON od.FK1_PRODUCT_ID = p.PK_PRODUCT_ID
            WHERE od.FK2_ORDER_ID = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Check if enough stock
        if ($row['STOCK_QTY'] < $row['QTY']) {
            error_log("Insufficient stock for product: " . $row['PROD_NAME'] . 
                     " (Available: " . $row['STOCK_QTY'] . ", Ordered: " . $row['QTY'] . ")");
            return false;
        }
        
        // Update product quantity
        $update_sql = "UPDATE products 
                      SET QTY = QTY - ? 
                      WHERE PK_PRODUCT_ID = ? AND QTY >= ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iii", 
            $row['QTY'],
            $row['FK1_PRODUCT_ID'],
            $row['QTY']
        );
        
        if (!$update_stmt->execute()) {
            error_log("Failed to update quantity for product ID: " . $row['FK1_PRODUCT_ID']);
            return false;
        }
    }
    
    return true;
}
?>