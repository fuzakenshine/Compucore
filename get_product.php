<?php
include 'db_connect.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

$product_id = intval($_GET['id']);

$sql = "SELECT p.*, c.CAT_NAME, s.COMPANY_NAME 
        FROM PRODUCTS p 
        LEFT JOIN categories c ON p.FK1_CATEGORY_ID = c.PK_CATEGORY_ID 
        LEFT JOIN supplier s ON p.FK2_SUPPLIER_ID = s.PK_SUPPLIER_ID 
        WHERE p.PK_PRODUCT_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    echo json_encode($product);
} else {
    echo json_encode(['error' => 'Product not found']);
}

$stmt->close();
$conn->close();
?> 