<?php
session_start();
include 'db_connect.php'; // Include the database connector

// Handle login form submission
if (isset($_POST['email'], $_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch customer from the database
    $sql = "SELECT * FROM CUSTOMER WHERE EMAIL = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $customer = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $customer['PASSWORD_HASH'])) {
            // Set session variables
            $_SESSION['customer_id'] = $customer['PK_CUSTOMER_ID'];
            $_SESSION['customer_name'] = $customer['F_NAME'] . ' ' . $customer['L_NAME'];
            $_SESSION['loggedin'] = true;

            // Redirect to dashboard or home page
            header('Location: index.php');
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            height: 100vh;
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
        .right .register {
            margin-top: 10px;
            text-align: center;
        }
        .right .register a {
            color: #d32f2f;
            text-decoration: none;
        }
        .right .register a:hover {
            text-decoration: underline;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
        }
        .logo {
            margin-bottom: -10px;
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
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <div class="register">
            No account yet? <a href="register.php">Register here!</a>
        </div>
    </div>
</body>
</html>