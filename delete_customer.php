<?php
include 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$customer_id = $data['customer_id'];

$stmt = $conn->prepare("DELETE FROM customer WHERE PK_CUSTOMER_ID = ?");
$stmt->bind_param("i", $customer_id);

$response = ['success' => $stmt->execute()];
echo json_encode($response);