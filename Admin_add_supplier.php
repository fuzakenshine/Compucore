<?php
include 'db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Use a fixed user ID, like 1 (the admin user)
    $admin_user_id = 1;

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
        $sql = "INSERT INTO supplier (FK_USER_ID, S_FNAME, S_LNAME, PHONE_NUM, COMPANY_NAME, EMAIL, SUPPLIER_ADDRESS, SUPPLIER_IMAGE, UPDATE_AT) 
                VALUES ('$admin_user_id', '$fname', '$lname', '$phone', '$company', '$email', '$address', '$image', NOW())";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Supplier added successfully!'); window.location.href='Admin_suppliers.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f9;
        }

        .sidebar {
            width: 200px;
            background: linear-gradient(to bottom, #d32f2f, #b71c1c);
            color: white;
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .main-content {
            margin-left: 250px;
            padding: 50px;
        }

        .header h1 {
            margin: 0 0 20px 0;
        }

        form {
            display: flex;
            flex-wrap: wrap; /* Allows items to wrap into multiple rows */
            gap: 15px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 1000px;
        }

        .form-group {
            flex: 1 1 calc(50% - 15px); /* Two columns with space between */
        }

        input, textarea {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 16px;
            width: 95%;
        }

        textarea {
            resize: vertical;
            height: 100px;
        }

        button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            align-self: flex-start;
            margin-top: 10px; /* Space above the button */
        }

        button:hover {
            background-color: #b71c1c;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <a href="Admin_home.php">Home</a>
    <a href="Admin_suppliers.php">Suppliers</a>
    <a href="Admin_product.php">Products</a>
    <a href="#">Logout</a>
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
    <div class="form-group" style="flex: 1 1 100%;">
        <label>Photo:</label>
        <div style="display: flex; align-items: center;">
            <input type="file" name="photo" accept="image/*" required style="flex: 1;">
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

</body>
</html>