<?php
include 'db_connect.php';

$sql = "INSERT INTO users (PK_USER_ID,
    F_NAME, L_NAME, EMAIL, PASSWORD_HASH, ADDRESS, PHONE_NUM, IS_ADMIN, UPDATE_AT
) VALUES (
    2,
    'Super',
    'Admin',
    'Admin2@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'CompuCore Main Office',
    '09876543210',
    1,
    CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "New admin created successfully!<br>";
    echo "Email: superadmin@compucore.com<br>";
    echo "Password: Admin@123";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>