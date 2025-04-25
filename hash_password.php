<?php
include 'db_connect.php';

$admin_password = "admin123";
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET 
        PASSWORD_HASH = ?, 
        IS_ADMIN = 1 
        WHERE EMAIL = 'admin@compucore.com'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashed_password);

if ($stmt->execute()) {
    echo "Admin password updated successfully!";
} else {
    echo "Error updating admin password: " . $conn->error;
}
?>