<?php
include 'db_connect.php';

// Check if the SQL file exists
if (!file_exists('admin_setup.sql')) {
    die("Error: admin_setup.sql file not found!");
}

// Read and execute the SQL file
$sql = file_get_contents('admin_setup.sql');
if (empty($sql)) {
    die("Error: admin_setup.sql is empty!");
}

if ($conn->multi_query($sql)) {
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<h2 style='color: #4CAF50;'>Admin Setup Complete!</h2>";
    echo "<p><strong>Admin account created successfully:</strong></p>";
    echo "<ul>";
    echo "<li>Email: Admin@gmail.com</li>";
    echo "<li>Password: Admin@123</li>";
    echo "</ul>";
    echo "<p style='color: #f44336;'><strong>Important:</strong> Please delete both files after use:</p>";
    echo "<ul>";
    echo "<li>setup_admin.php</li>";
    echo "<li>admin_setup.sql</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "Error creating admin account: " . $conn->error;
}

$conn->close();