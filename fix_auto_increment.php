<?php
include 'db_connect.php';

// Add AUTO_INCREMENT to orders table
$sql = "ALTER TABLE orders MODIFY PK_ORDER_ID int(11) NOT NULL AUTO_INCREMENT";
if ($conn->query($sql)) {
    echo "Successfully added AUTO_INCREMENT to orders table";
} else {
    echo "Error adding AUTO_INCREMENT: " . $conn->error;
}

// Add AUTO_INCREMENT to payments table
$sql = "ALTER TABLE payments MODIFY PK_PAYMENT_ID int(11) NOT NULL AUTO_INCREMENT";
if ($conn->query($sql)) {
    echo "<br>Successfully added AUTO_INCREMENT to payments table";
} else {
    echo "<br>Error adding AUTO_INCREMENT to payments: " . $conn->error;
}

// Add AUTO_INCREMENT to order_detail table
$sql = "ALTER TABLE order_detail MODIFY PK_ORDER_DETAIL_ID int(11) NOT NULL AUTO_INCREMENT";
if ($conn->query($sql)) {
    echo "<br>Successfully added AUTO_INCREMENT to order_detail table";
} else {
    echo "<br>Error adding AUTO_INCREMENT to order_detail: " . $conn->error;
}

$conn->close();
?> 