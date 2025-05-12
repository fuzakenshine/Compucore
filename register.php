<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connect.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone_num = trim($_POST['phone_num']);
    $password = $_POST['password'];
    $address = trim($_POST['address']);
    
    // Server-side validation
    $errors = [];
    
    // Name validation
    if (!preg_match("/^[a-zA-Z\s]*$/", $first_name)) {
        $errors[] = "First name must only contain letters and spaces";
    }
    if (!preg_match("/^[a-zA-Z\s]*$/", $last_name)) {
        $errors[] = "Last name must only contain letters and spaces";
    }
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@(gmail|yahoo|email)\.com$/', $email)) {
        $errors[] = "Please enter a valid email address (@gmail.com, @yahoo.com, or @email.com)";
    }
    
    // Phone validation
    if (!preg_match("/^\+[1-9]\d{1,14}$/", $phone_num)) {
        $errors[] = "Please enter a valid phone number with country code";
    }
    
    // Password validation
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*]/', $password)) {
        $errors[] = "Password must contain at least one special character (!@#$%^&*)";
    }
    
    // Confirm password validation
    if ($password !== $_POST['confirm_password']) {
        $errors[] = "Passwords do not match";
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $profile_pic = 'default.png'; // Set default profile picture if not uploading
        
        try {
            // Use stored procedure to insert customer
            $stmt = $conn->prepare("CALL populateCUSTOMERS(?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $password_hash, $address, $phone_num);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Registration successful!']);
                exit();
            } else {
                throw new Exception("Registration failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            width: 107.5%;
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
        .form-group {
            position: relative;
            margin-bottom: 5px;
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
        
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 2px 0;
            transition: color 0.3s ease;
        }
        
        .requirement i {
            font-size: 10px;
            transition: color 0.3s ease;
        }
        
        .requirement.valid {
            color: #4CAF50;
        }
        
        .requirement.invalid {
            color: #d32f2f;
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
        
        <form method="POST" id="registrationForm" novalidate>
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <input type="text" name="first_name" id="first_name" placeholder="First Name" required>
                <div class="error-message" id="first_name_error"></div>
                <div class="success-message" id="first_name_success"></div>
            </div>
            
            <div class="form-group">
                <input type="text" name="last_name" id="last_name" placeholder="Last Name" required>
                <div class="error-message" id="last_name_error"></div>
                <div class="success-message" id="last_name_success"></div>
            </div>
            
            <div class="form-group">
                <input type="email" name="email" id="email" placeholder="Email Address (@gmail.com, @yahoo.com, or @email.com)" required>
                <div class="error-message" id="email_error"></div>
                <div class="success-message" id="email_success"></div>
            </div>
            
            <div class="form-group">
                <input type="text" name="phone_num" id="phone_num" placeholder="Phone Number (e.g., +631234567890)" required>
                <div class="error-message" id="phone_error"></div>
                <div class="success-message" id="phone_success"></div>
            </div>
            
            <div class="form-group">
                <div class="input-with-icon">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                </div>
                <div class="password-requirements">
                    <div class="requirement" id="length">
                        <i class="fas fa-circle"></i> At least 8 characters
                    </div>
                    <div class="requirement" id="uppercase">
                        <i class="fas fa-circle"></i> One uppercase letter
                    </div>
                    <div class="requirement" id="lowercase">
                        <i class="fas fa-circle"></i> One lowercase letter
                    </div>
                    <div class="requirement" id="number">
                        <i class="fas fa-circle"></i> One number
                    </div>
                    <div class="requirement" id="special">
                        <i class="fas fa-circle"></i> One special character
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-with-icon">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')"></i>
                </div>
                <div class="error-message" id="confirm_password_error"></div>
                <div class="success-message" id="confirm_password_success"></div>
            </div>
            
            <div class="form-group">
                <input type="text" name="address" id="address" placeholder="Address" required>
            </div>
            
            <button type="submit" id="submitBtn">Register</button>
        </form>
        
        <div class="login">
            Already have an account? <a href="login.php">Login here!</a>
        </div>
    </div>
    
    <script>
        const form = document.getElementById('registrationForm');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validateName(input, errorElement, successElement) {
            const nameRegex = /^[a-zA-Z\s]*$/;
            const isValid = nameRegex.test(input.value);
            
            if (isValid) {
                input.classList.add('valid');
                input.classList.remove('invalid');
                errorElement.style.display = 'none';
                successElement.style.display = 'block';
            } else {
                input.classList.add('invalid');
                input.classList.remove('valid');
                errorElement.style.display = 'block';
                successElement.style.display = 'none';
                errorElement.textContent = 'Name must only contain letters and spaces';
            }
            return isValid;
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
        
        function validatePhone(input) {
            const phoneError = document.getElementById('phone_error');
            const phoneSuccess = document.getElementById('phone_success');
            const isValid = /^\+[1-9]\d{1,14}$/.test(input.value);
            
            if (isValid) {
                input.classList.add('valid');
                input.classList.remove('invalid');
                phoneError.style.display = 'none';
                phoneSuccess.style.display = 'block';
            } else {
                input.classList.add('invalid');
                input.classList.remove('valid');
                phoneError.style.display = 'block';
                phoneSuccess.style.display = 'none';
                phoneError.textContent = 'Enter a valid phone number with country code (e.g., +631234567890)';
            }
            return isValid;
        }
        
        function validatePassword(input) {
            const requirements = {
                length: {
                    met: input.value.length >= 8,
                    message: 'At least 8 characters'
                },
                uppercase: {
                    met: /[A-Z]/.test(input.value),
                    message: 'One uppercase letter'
                },
                lowercase: {
                    met: /[a-z]/.test(input.value),
                    message: 'One lowercase letter'
                },
                number: {
                    met: /[0-9]/.test(input.value),
                    message: 'One number'
                },
                special: {
                    met: /[!@#$%^&*]/.test(input.value),
                    message: 'One special character'
                }
            };
            
            let isValid = true;
            
            Object.keys(requirements).forEach(req => {
                const element = document.getElementById(req);
                const requirement = requirements[req];
                
                element.classList.toggle('valid', requirement.met);
                element.classList.toggle('invalid', !requirement.met);
                element.querySelector('i').className = requirement.met ? 
                    'fas fa-check-circle' : 'fas fa-times-circle';
                
                if (!requirement.met) {
                    isValid = false;
                }
            });

            if (isValid) {
                input.classList.add('valid');
                input.classList.remove('invalid');
            } else {
                input.classList.add('invalid');
                input.classList.remove('valid');
            }
            
            return isValid;
        }
        
        function validateConfirmPassword() {
            const error = document.getElementById('confirm_password_error');
            const success = document.getElementById('confirm_password_success');
            const isValid = password.value === confirmPassword.value;
            
            if (isValid) {
                confirmPassword.classList.add('valid');
                confirmPassword.classList.remove('invalid');
                error.style.display = 'none';
                success.style.display = 'block';
            } else {
                confirmPassword.classList.add('invalid');
                confirmPassword.classList.remove('valid');
                error.style.display = 'block';
                success.style.display = 'none';
                error.textContent = 'Passwords do not match';
            }
            return isValid;
        }
        
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
        
        // Add real-time validation
        document.getElementById('first_name').addEventListener('input', function() {
            validateName(this, 
                document.getElementById('first_name_error'),
                document.getElementById('first_name_success')
            );
        });
        
        document.getElementById('last_name').addEventListener('input', function() {
            validateName(this, 
                document.getElementById('last_name_error'),
                document.getElementById('last_name_success')
            );
        });
        
        document.getElementById('email').addEventListener('input', function() {
            validateEmail(this);
        });
        
        document.getElementById('phone_num').addEventListener('input', function() {
            validatePhone(this);
        });
        
        password.addEventListener('input', function() {
            validatePassword(this);
            if (confirmPassword.value) validateConfirmPassword();
        });
        
        confirmPassword.addEventListener('input', validateConfirmPassword);
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isValidFirstName = validateName(document.getElementById('first_name'), 
                                                document.getElementById('first_name_error'),
                                                document.getElementById('first_name_success')
                                            );
            const isValidLastName = validateName(document.getElementById('last_name'), 
                                               document.getElementById('last_name_error'),
                                               document.getElementById('last_name_success')
                                            );
            const isValidEmail = validateEmail(document.getElementById('email'));
            const isValidPhone = validatePhone(document.getElementById('phone_num'));
            const isValidPassword = validatePassword(password);
            const isValidConfirmPassword = validateConfirmPassword();
            
            if (isValidFirstName && isValidLastName && isValidEmail && 
                isValidPhone && isValidPassword && isValidConfirmPassword) {
                
                const formData = new FormData(this);
                
                fetch('register.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registration Successful!',
                            text: 'Your account has been created.',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = 'login.php';
                        });
                    } else {
                        throw new Error(data.message || 'Registration failed');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: error.message
                    });
                });
            }
        });
    </script>
</body>
</html>