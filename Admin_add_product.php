<?php
session_start();
include 'db_connect.php'; // Include the database connector

$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminName = $adminQuery->get_result()->fetch_assoc()['full_name'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prod_name = $_POST['prod_name'];
    $prod_desc = $_POST['prod_desc'];
    $prod_spec = $_POST['prod_spec']; // Add this line
    $price = $_POST['price'];
    $qty = $_POST['qty'];
    $category_id = $_POST['category_id'];
    $supplier_id = $_POST['supplier_id'];

    // Handle file upload
    $image = $_FILES['image']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        // Update the SQL query to include PROD_SPEC
        $sql = "INSERT INTO PRODUCTS (FK1_CATEGORY_ID, FK2_SUPPLIER_ID, PROD_NAME, PROD_DESC, PROD_SPECS    , PRICE, QTY, IMAGE, UPDATED_AT) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssdis", $category_id, $supplier_id, $prod_name, $prod_desc, $prod_spec, $price, $qty, $image);

        if ($stmt->execute()) {
            echo "<script>alert('Product added successfully!'); window.location.href='Admin_product.php';</script>";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('imagePreview');
                output.src = reader.result;
                output.style.display = 'block';
                document.getElementById('placeholderText').style.display = 'none';
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
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
            padding: 20px;
        }
        .header {
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
        }
        .form-container {
            display: flex;
            gap: 20px;
        }
        .form-container .left, .form-container .right {
            flex: 1;
            width: 50%;
        }
        .form-container .left {
            width: 50%;
        }
        .form-container .right {
            width: 50%;
            margin-right: 30px;
        }
        .form-container input, .form-container select, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-container input {
            width: 97%;
        }
        .form-container textarea {
            height: 150px;
            margin-bottom: 20px;
            resize: vertical;
            min-height: 100px;
            max-height: 300px;
        }
        .form-container .image-upload {
            display: flex;
            justify-content: center;
            align-items: center;
            border: 1px dashed #ddd;
            height: 300px;
            margin-bottom: 10px;
            cursor: pointer;
            position: relative;
        }
        .form-container .image-upload:hover {
            background-color: #f4f4f4;
        }
        .form-container button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        .form-container button:hover {
            background-color: #b71c1c;
        }
        .right {
            margin-left: 20px;
        }   
        .image-upload {
            position: relative;
            width: 100%;
            height: 150px;
            border: 1px dashed #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            overflow: hidden;
        }

        #imagePreview {
            width: 120%;
            height: 120%;
            object-fit: contain;
            transform: scale(0.85); /* This "zooms out" the image */
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.85); /* Center + scale */
            border-radius: 5px;
        }


        #placeholderText {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #aaa;
            font-size: 16px;
        }
        #imagePreview {
            border-radius: 5px;
        }
        button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
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
        <a href="Admin_suppliers.php">
            <i class="fas fa-truck"></i> Suppliers
        </a>
        <a href="Admin_product.php" class="active">
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
            <h1>Add Product</h1>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-container">
                <div class="left">
                <label class="image-upload">
                    <input type="file" name="image" accept="image/*" style="display: none;" onchange="previewImage(event)">
                    <img id="imagePreview" src="#" alt="Image Preview">
                    <span id="placeholderText">+ Add photos</span>
                </label>
                    <input type="text" name="prod_name" placeholder="Product Name" required>
                    <input type="number" step="0.01" name="price" placeholder="Price" required>
                    <input type="number" name="qty" placeholder="Quantity" required>
                    <select name="category_id" required>
                        <option value="" disabled selected>Category</option>
                        <?php
                        // Fetch categories from the database
                        $category_sql = "SELECT PK_CATEGORY_ID, CAT_NAME FROM categories";
                        $category_result = $conn->query($category_sql);
                        if (!$category_result) {
                            echo "Error fetching categories: " . $conn->error;
                        } else {
                            while ($category = $category_result->fetch_assoc()) {
                                echo "<option value='{$category['PK_CATEGORY_ID']}'>{$category['CAT_NAME']}</option>";
                            }
                        }
                        ?>
                    </select>
                    <select name="supplier_id" required>
                        <option value="" disabled selected>Supplier</option>
                        <?php
                        // Fetch suppliers from the database
                        $supplier_sql = "SELECT PK_SUPPLIER_ID, COMPANY_NAME FROM supplier";
                        $supplier_result = $conn->query($supplier_sql);
                        if (!$supplier_result) {
                            echo "Error fetching suppliers: " . $conn->error;
                        } else {
                            while ($supplier = $supplier_result->fetch_assoc()) {
                                echo "<option value='{$supplier['PK_SUPPLIER_ID']}'>{$supplier['COMPANY_NAME']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="right">
                    <textarea name="prod_desc" placeholder="Description" required></textarea>
                    <textarea name="prod_spec" placeholder="Specifications" required></textarea>
                </div>
            </div>
            <button type="submit">Add Product</button>
        </form>
    </div>
</body>
</html>