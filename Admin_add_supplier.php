<?php
include 'db_connect.php'; 
session_start();

// Get logged in admin ID
$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminName = $adminQuery->get_result()->fetch_assoc()['full_name'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Use the logged-in admin's ID instead of hard-coded value
    $admin_user_id = $_SESSION['user_id']; 

    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $company = $_POST['company'];
    $address = $_POST['address'];

    // Handle image upload
    $image = $_FILES['photo']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
        // Use prepared statement to prevent SQL injection
        $sql = "INSERT INTO supplier (FK_USER_ID, S_FNAME, S_LNAME, PHONE_NUM, COMPANY_NAME, EMAIL, SUPPLIER_ADDRESS, SUPPLIER_IMAGE, UPDATE_AT) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssss", 
            $admin_user_id, 
            $fname, 
            $lname, 
            $phone, 
            $company, 
            $email, 
            $address, 
            $image
        );

        if ($stmt->execute()) {
            echo "<script>alert('Supplier added successfully!'); window.location.href='Admin_suppliers.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "<script>alert('Failed to upload image.');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Supplier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .sidebar {
            width: 200px;
            background-color: #d32f2f;
            height: 100vh;
            position: fixed;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .admin-profile {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
        }

        .admin-profile h3 {
            margin: 0;
            font-size: 1.2em;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .sidebar a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .sidebar a.active {
            background: rgba(255,255,255,0.2);
        }

        .main-content {
            margin-left: 250px;
            padding: 40px;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }

        form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            max-width: 100%;
        }

        .form-group {
            flex: 1 1 calc(50% - 10px);
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input, textarea {
            width: 90%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #b71c1c;
        }
    </style>
</head>
<body>
    <div class="sidebar">
    <div class="admin-profile">
            <h3><?= htmlspecialchars($adminName) ?></h3>
        </div>
        <a href="Admin_home.php">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="Admin_orders.php">
            <i class="fas fa-shopping-cart"></i> Orders
        </a>
        <a href="Admin_suppliers.php" class="active">
            <i class="fas fa-truck"></i> Suppliers
        </a>
        <a href="Admin_product.php">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="Admin_customers.php">
            <i class="fas fa-users"></i> Customers
        </a>
        <a href="logout.php" style="margin-top: auto;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Add Supplier</h1>
        </div>
        <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>First Name: <input type="text" name="fname" required></label>
        </div>
        <div class="form-group">
            <label>Last Name: <input type="text" name="lname" required></label>
        </div>
        <div class="form-group">
            <label>Email: <input type="email" name="email" required></label>
        </div>
        <div class="form-group">
            <label>Phone Number: <input type="text" name="phone" required></label>
        </div>
        <div class="form-group">
            <label>Company Name: <input type="text" name="company" required></label>
        </div>
        <div class="form-group">
            <label>Address: <input type="text" name="address" required></label>
        </div>
        <div class="form-group" style="flex: 1 1 90%;">
            <label>Photo:</label>
            <div style="display: flex; align-items: center; width: 97%;">
                <input type="file" name="photo" accept="image/*" required style="flex: 1; width: 80%;">
            </div>
        </div>
        <button type="submit">Add Supplier</button>
    </form>

    <script>
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            const fileName = e.target.files.length > 0 ? e.target.files[0].name : 'No file chosen';
            document.getElementById('placeholderText').textContent = fileName;
        });
    </script>
    </div>
</body>
</html>