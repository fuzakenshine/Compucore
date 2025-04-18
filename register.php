<?php
session_start();
include 'db_connect.php'; // Include the database connector

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $address = $_POST['address'];
    $phone_num = $_POST['phone_num'];

    // Insert customer into the database
    $sql = "INSERT INTO CUSTOMER (F_NAME, L_NAME, EMAIL, PASSWORD_HASH, CUSTOMER_ADDRESS, PHONE_NUM, UPDATE_AT) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $first_name, $last_name, $email, $password, $address, $phone_num);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            background-color: #f9f9f9;
        }
        .left {
            flex: 1;
            background: url('uploads/BG1.png') no-repeat center center/cover;
            position: relative;
            height:100vh;
        }
        .left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(211, 47, 47, 0.16);
        }
        .social-icons {
            position: absolute;
            bottom: 20px;
            left: 20px;
            display: flex;
            gap: 10px;
        }
        .social-icons img {
            width: 30px;
            height: 30px;
            cursor: pointer;
        }
        .right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background-color: #fff;
        }
        .right h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #d32f2f;
        }
        .right form {
            width: 100%;
            max-width: 300px;
        }
        .right form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .right form button {
            width: 100%;
            padding: 10px;
            background-color: #d32f2f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .right form button:hover {
            background-color: #b71c1c;
        }
        .right .login {
            margin-top: 10px;
            text-align: center;
        }
        .right .login a {
            color: #d32f2f;
            text-decoration: none;
        }
        .right .login a:hover {
            text-decoration: underline;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
        }
        .logo {
            margin-bottom: -1   0px;
            text-align: center;
        }
        .logo img {
            width: 150px; /* Adjust the size as needed */
            height: auto;
        }
    </style>
</head>
<body>
    <div class="left">
        <div class="social-icons">
            <img src="uploads/GM.png" alt="Email">
            <img src="uploads/FB.png" alt="Facebook">
            <img src="uploads/IG.png" alt="Instagram">
        </div>
    </div>
    <div class="right">
    <div class="logo">
        <img src="uploads/logo.png" alt="CompuCore Logo">
    </div>
        <h1>Enhance your PC performance!</h1>
        <form method="POST">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="text" name="phone_num" placeholder="Phone No." required>
            <button type="submit">Register</button>
        </form>
        <div class="login">
            Already have an account? <a href="login.php">Login here!</a>
        </div>
    </div>
</body>
</html>