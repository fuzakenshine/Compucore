<?php
include 'db_connect.php'; 
session_start();

$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminResult = $adminQuery->get_result();
$adminName = $adminResult->num_rows > 0 ? $adminResult->fetch_assoc()['full_name'] : 'Test Admin';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Use the logged-in admin's ID instead of hard-coded value
    $admin_user_id = $_SESSION['user_id']; 

    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $company = $_POST['company'];
    $address = $_POST['address'];

    // Server-side validation
    $errors = [];
    if (!preg_match("/^[A-Za-z .'-]+$/", $fname)) {
        $errors[] = "First name is invalid.";
    }
    if (!preg_match("/^[A-Za-z .'-]+$/", $lname)) {
        $errors[] = "Last name is invalid.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is invalid.";
    }
    if (!preg_match("/^\\+?\\d{10,15}$/", $phone)) {
        $errors[] = "Phone number is invalid.";
    }
    if (strlen($company) < 2) {
        $errors[] = "Company name is too short.";
    }
    if (strlen($address) < 2) {
        $errors[] = "Address is too short.";
    }
    if (empty($_FILES['photo']['name'])) {
        $errors[] = "Photo is required.";
    }
    if (!empty($errors)) {
        echo "<script>alert('" . implode("\\n", $errors) . "'); window.history.back();</script>";
        exit;
    }

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

        input, textarea, input[type="email"], input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
            background: #fff;
            box-sizing: border-box;
        }

        .form-row {
            display: flex;
            gap: 20px;
            width: 100%;
        }

        .form-group {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
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
            <div class="form-row">
                <div class="form-group">
                    <label>First Name: <input type="text" name="fname" required></label>
                </div>
                <div class="form-group">
                    <label>Last Name: <input type="text" name="lname" required></label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email: <input type="email" name="email" required></label>
                </div>
                <div class="form-group">
                    <label>Phone Number: <input type="text" name="phone" required></label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Company Name: <input type="text" name="company" required></label>
                </div>
                <div class="form-group">
                    <label>Address: <input type="text" name="address" required></label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label>Photo:</label>
                    <input type="file" name="photo" accept="image/*" required>
                </div>
            </div>
            <button type="submit">Add Supplier</button>
        </form>

    <script>
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            const fileName = e.target.files.length > 0 ? e.target.files[0].name : 'No file chosen';
            document.getElementById('placeholderText').textContent = fileName;
        });
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            let valid = true;
            let messages = [];

            const fname = this.fname.value.trim();
            const lname = this.lname.value.trim();
            const email = this.email.value.trim();
            const phone = this.phone.value.trim();
            const company = this.company.value.trim();
            const address = this.address.value.trim();
            const photo = this.photo.value;

            // Name validation
            if (!fname.match(/^[A-Za-z .'-]+$/)) {
                valid = false;
                messages.push("First name is invalid.");
            }
            if (!lname.match(/^[A-Za-z .'-]+$/)) {
                valid = false;
                messages.push("Last name is invalid.");
            }

            // Email validation
            if (!email.match(/^[^@]+@[^@]+\.[^@]+$/)) {
                valid = false;
                messages.push("Email is invalid.");
            }

            // Phone validation (Philippines format, adjust as needed)
            if (!phone.match(/^\+?\d{10,15}$/)) {
                valid = false;
                messages.push("Phone number is invalid.");
            }

            // Company and address
            if (company.length < 2) {
                valid = false;
                messages.push("Company name is too short.");
            }
            if (address.length < 2) {
                valid = false;
                messages.push("Address is too short.");
            }

            // Photo required
            if (!photo) {
                valid = false;
                messages.push("Photo is required.");
            }

            if (!valid) {
                e.preventDefault();
                alert(messages.join("\n"));
            }
        });
    </script>
    </div>
</body>
</html>