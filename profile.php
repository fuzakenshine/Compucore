<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];
$message = '';

// Handle profile picture upload
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['profile_pic']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed)) {
        $new_filename = uniqid() . '.' . $ext;
        $upload_path = 'uploads/profiles/' . $new_filename;
        
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
            // Update database with new profile picture
            $sql = "UPDATE customer SET PROFILE_PIC = ? WHERE PK_CUSTOMER_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_filename, $customer_id);
            $stmt->execute();
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone_num = $_POST['phone_num'];
    
    // Check if password fields are filled
    if (!empty($_POST['old_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify old password
        $sql = "SELECT PASSWORD_HASH FROM customer WHERE PK_CUSTOMER_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer_data = $result->fetch_assoc();
        
        if (!password_verify($old_password, $customer_data['PASSWORD_HASH'])) {
            $message = "Current password is incorrect!";
            goto end_update;
        }
        
        if ($new_password !== $confirm_password) {
            $message = "New passwords do not match!";
            goto end_update;
        }
        
        // Hash new password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update customer information with new password
        $sql = "UPDATE customer SET 
                F_NAME = ?, 
                L_NAME = ?, 
                EMAIL = ?, 
                CUSTOMER_ADDRESS = ?, 
                PHONE_NUM = ?,
                PASSWORD_HASH = ?,
                UPDATE_AT = NOW()
                WHERE PK_CUSTOMER_ID = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $address, $phone_num, $new_password_hash, $customer_id);
    } else {
        // Update customer information without changing password
        $sql = "UPDATE customer SET 
                F_NAME = ?, 
                L_NAME = ?, 
                EMAIL = ?, 
                CUSTOMER_ADDRESS = ?, 
                PHONE_NUM = ?,
                UPDATE_AT = NOW()
                WHERE PK_CUSTOMER_ID = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $first_name, $last_name, $email, $address, $phone_num, $customer_id);
    }
    
    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile: " . $stmt->error;
    }
    
    end_update:
}

// Fetch current customer data
$sql = "SELECT * FROM customer WHERE PK_CUSTOMER_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CompuCore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .topnav {
            background-color: #d32f2f;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .topnav-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .topnav-title {
            color: white;
            font-size: 1.5em;
            font-weight: bold;
            margin: 0;
        }

        .back-button {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 15px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
        }

        .back-button:hover {
            transform: translateX(-5px);
            background: rgba(255, 255, 255, 0.2);
        }

        .back-button i {
            font-size: 1.1em;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .profile-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
        }

        .profile-header h1 {
            margin: 15px 0 5px;
            color: #333;
            font-size: 24px;
        }

        .profile-header p {
            color: #666;
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-group input:focus {
            border-color: #d32f2f;
            outline: none;
            box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.1);
        }

        .btn {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #b71c1c;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .password-change-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .section-title {
            color: #333;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .password-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .profile-pic-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 4px solid #fff;
        }

        .profile-pic-container:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .profile-pic-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .profile-pic-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .profile-pic-overlay i {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .profile-pic-overlay span {
            font-size: 14px;
            font-weight: 500;
        }

        .profile-pic-container:hover .profile-pic-overlay {
            opacity: 1;
        }

        .form-group input[type="file"] {
            padding: 8px;
            border: 1px dashed #ddd;
            background: #f9f9f9;
        }

        .profile-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .detail-label {
            color: #666;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-label i {
            color: #d32f2f;
            width: 20px;
        }

        .detail-value {
            color: gray;
            font-size: 1em;
            font-weight: 500;
        }

        .section-divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }

        .section-divider:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ddd;
        }

        .section-divider span {
            background: white;
            padding: 0 15px;
            color: #666;
            position: relative;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-pic-container">
                    <img src="uploads/profiles/<?php echo !empty($customer['PROFILE_PIC']) ? htmlspecialchars($customer['PROFILE_PIC']) : 'default-avatar.png'; ?>" 
                         alt="Profile Picture" 
                         id="profile-pic">
                    <div class="profile-pic-overlay">
                        <i class="fas fa-camera"></i>
                        <span>Change Photo</span>
                    </div>
                    <input type="file" id="profile-pic-input" name="profile_pic" accept="image/*" style="display: none;">
                </div>
                <h1><?php echo htmlspecialchars($customer['F_NAME'] . ' ' . $customer['L_NAME']); ?></h1>
                <p>Manage your account information</p>
            </div>

            <div class="profile-details">
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label"><i class="fas fa-user"></i> Full Name</span>
                        <span class="detail-value"><?php echo htmlspecialchars($customer['F_NAME'] . ' ' . $customer['L_NAME']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="fas fa-envelope"></i> Email</span>
                        <span class="detail-value"><?php echo htmlspecialchars($customer['EMAIL']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="fas fa-phone"></i> Phone</span>
                        <span class="detail-value"><?php echo htmlspecialchars($customer['PHONE_NUM']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="fas fa-map-marker-alt"></i> Address</span>
                        <span class="detail-value"><?php echo htmlspecialchars($customer['CUSTOMER_ADDRESS']); ?></span>
                    </div>
                </div>
            </div>

            <div class="section-divider">
                <span>Edit Profile</span>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($customer['F_NAME']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($customer['L_NAME']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['EMAIL']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($customer['CUSTOMER_ADDRESS']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone_num">Phone Number</label>
                    <input type="tel" id="phone_num" name="phone_num" value="<?php echo htmlspecialchars($customer['PHONE_NUM']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="old_password">Current Password (leave blank if not changing)</label>
                    <input type="password" id="old_password" name="old_password">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password (leave blank if not changing)</label>
                    <input type="password" id="new_password" name="new_password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password (leave blank if not changing)</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>

                <button type="submit" class="btn">Update Profile</button>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const profilePicContainer = document.querySelector('.profile-pic-container');
        const profilePicInput = document.getElementById('profile-pic-input');
        const profilePic = document.getElementById('profile-pic');
        
        // Click handler for the profile picture container
        profilePicContainer.addEventListener('click', function() {
            profilePicInput.click();
        });
        
        // Handle file selection
        profilePicInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                const file = this.files[0];
                
                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('Please select an image file');
                    return;
                }
                
                reader.onload = function(e) {
                    // Show preview immediately
                    profilePic.src = e.target.result;
                    
                    // Create form data for upload
                    const formData = new FormData();
                    formData.append('profile_pic', file);
                    
                    // Upload the image
                    fetch('profile.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.ok ? response.text() : Promise.reject('Upload failed'))
                    .then(() => {
                        // Show success message
                        const message = document.createElement('div');
                        message.className = 'message success';
                        message.textContent = 'Profile picture updated successfully!';
                        document.querySelector('.profile-header').appendChild(message);
                        
                        // Remove message after 3 seconds
                        setTimeout(() => message.remove(), 3000);
                    })
                    .catch(error => {
                        alert('Error uploading profile picture: ' + error);
                    });
                };
                
                reader.readAsDataURL(file);
            }
        });
    });
    </script>
</body>
</html>
<?php
$conn->close();
?>