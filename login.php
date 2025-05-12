<?php
session_start();
include 'db_connect.php';
include 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        // First check if it's an admin
        $stmt = $conn->prepare("SELECT PK_ADMIN_ID, F_NAME, L_NAME, PASSWORD_HASH FROM admin WHERE EMAIL = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['PASSWORD_HASH'])) {
                // Set admin session variables
                $_SESSION['user_id'] = $user['PK_ADMIN_ID'];
                $_SESSION['user_name'] = $user['F_NAME'] . ' ' . $user['L_NAME'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['loggedin'] = true;
                $_SESSION['last_activity'] = time();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login successful!',
                    'redirect' => './Admin_home.php' // Add ./ to ensure relative path from current directory
                ]);
                exit();
            }
        }
        
        // If not admin, check if customer
        $stmt = $conn->prepare("SELECT PK_CUSTOMER_ID, F_NAME, L_NAME, PASSWORD_HASH FROM customer WHERE EMAIL = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['PASSWORD_HASH'])) {
                // Set customer session variables
                $_SESSION['user_id'] = $user['PK_CUSTOMER_ID'];
                $_SESSION['user_name'] = $user['F_NAME'] . ' ' . $user['L_NAME'];
                $_SESSION['user_type'] = 'customer';
                $_SESSION['loggedin'] = true;
                $_SESSION['customer_id'] = $user['PK_CUSTOMER_ID'];
                $_SESSION['last_activity'] = time();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login successful!',
                    'redirect' => 'index.php'
                ]);
                exit();
            }
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CompuCore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        .form-group {
            position: relative;
            margin-bottom: 15px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.1);
        }
        .form-group input.valid {
            border-color: #4CAF50;
            background-color: #f8fff8;
        }
        .form-group input.invalid {
            border-color: #d32f2f;
            background-color: #fff8f8;
        }
        .input-with-icon {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #d32f2f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #b71c1c;
        }
        .register {
            margin-top: 20px;
            text-align: center;
        }
        .register a {
            color: #d32f2f;
            text-decoration: none;
        }
        .register a:hover {
            text-decoration: underline;
        }
        .logo {
            margin-bottom: 20px;
            text-align: center;
        }
        .logo img {
            width: 150px;
            height: auto;
        }
        .error-message {
            color: #d32f2f;
            font-size: 12px;
            display: none;
            margin-top: 4px;
        }
        .success-message {
            color: #4CAF50;
            font-size: 12px;
            display: none;
            margin-top: 4px;
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
        <h1>Welcome Back!</h1>
        
        <form id="loginForm" method="POST" novalidate>
            <div class="form-group">
                <input type="email" name="email" id="email" placeholder="Email Address" required>
                <div class="error-message" id="email_error"></div>
                <div class="success-message" id="email_success"></div>
            </div>
            
            <div class="form-group">
                <div class="input-with-icon">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                </div>
                <div class="error-message" id="password_error"></div>
                <div class="success-message" id="password_success"></div>
            </div>
            
            <button type="submit" id="submitBtn" style="background-color: #d32f2f; color: white; width: 35.7vh;">Login</button>
        </form>
        
        <div class="register">
            Don't have an account? <a href="register.php">Register here!</a>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        function validateEmail(input) {
            const emailError = document.getElementById('email_error');
            const emailSuccess = document.getElementById('email_success');
            const isValidDomain = /@(gmail|yahoo|email)\.com$/.test(input.value);
            const isValidFormat = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
            
            if (isValidFormat && isValidDomain) {
                input.classList.add('valid');
                input.classList.remove('invalid');
                emailError.style.display = 'none';
                emailSuccess.style.display = 'block';
            } else {
                input.classList.add('invalid');
                input.classList.remove('valid');
                emailError.style.display = 'block';
                emailSuccess.style.display = 'none';
                emailError.textContent = !isValidFormat ? 'Please enter a valid email address' : 
                                       'Email must be from gmail.com, yahoo.com, or email.com';
            }
            return isValidFormat && isValidDomain;
        }
        
        function validatePassword(input) {
            const passwordError = document.getElementById('password_error');
            const passwordSuccess = document.getElementById('password_success');
            
            if (input.value.length >= 8) {
                input.classList.add('valid');
                input.classList.remove('invalid');
                passwordError.style.display = 'none';
                passwordSuccess.style.display = 'block';
            } else {
                input.classList.add('invalid');
                input.classList.remove('valid');
                passwordError.style.display = 'block';
                passwordSuccess.style.display = 'none';
                passwordError.textContent = 'Password must be at least 8 characters';
            }
            return input.value.length >= 8;
        }
        
        // Add real-time validation
        document.getElementById('email').addEventListener('input', function() {
            validateEmail(this);
        });
        
        document.getElementById('password').addEventListener('input', function() {
            validatePassword(this);
        });
        
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isValidEmail = validateEmail(document.getElementById('email'));
            const isValidPassword = validatePassword(document.getElementById('password'));
            
            if (isValidEmail && isValidPassword) {
                const formData = new FormData(this);
                
                fetch('login.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Login Successful!',
                            text: 'Welcome back!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.replace(data.redirect);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong! Please try again.'
                    });
                });
            }
        });
    </script>
</body>
</html>