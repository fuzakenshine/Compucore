<?php
session_start();
include 'db_connect.php';

// Handle POST request for editing customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    try {
        $customer_id = filter_var($_POST['customer_id'], FILTER_SANITIZE_NUMBER_INT);
        $fname = htmlspecialchars(trim($_POST['f_name']), ENT_QUOTES, 'UTF-8');
        $lname = htmlspecialchars(trim($_POST['l_name']), ENT_QUOTES, 'UTF-8');
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone = htmlspecialchars(trim($_POST['phone_num']), ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars(trim($_POST['address']), ENT_QUOTES, 'UTF-8');

        // Start transaction
        $conn->begin_transaction();

        // Update basic info
        $update_stmt = $conn->prepare("UPDATE customer SET 
            F_NAME = ?, 
            L_NAME = ?, 
            EMAIL = ?, 
            PHONE_NUM = ?,
            CUSTOMER_ADDRESS = ?,
            UPDATE_AT = CURRENT_TIMESTAMP 
            WHERE PK_CUSTOMER_ID = ?");
            
        if (!$update_stmt) {
            throw new Exception("Prepare update failed: " . $conn->error);
        }

        $update_stmt->bind_param("sssssi", 
            $fname, 
            $lname, 
            $email, 
            $phone,
            $address,
            $customer_id
        );
        
        if (!$update_stmt->execute()) {
            throw new Exception("Execute update failed: " . $update_stmt->error);
        }

        // Handle image upload if present
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['profile_pic']['name'];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($filetype, $allowed)) {
                $newname = 'customer_' . uniqid() . '.' . $filetype;
                $upload_path = 'uploads/profiles/' . $newname;

                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                    // Get old image filename
                    $img_stmt = $conn->prepare("SELECT PROFILE_PIC FROM customer WHERE PK_CUSTOMER_ID = ?");
                    $img_stmt->bind_param("i", $customer_id);
                    $img_stmt->execute();
                    $old_image = $img_stmt->get_result()->fetch_assoc()['PROFILE_PIC'];

                    // Update database with new image
                    $img_update = $conn->prepare("UPDATE customer SET PROFILE_PIC = ? WHERE PK_CUSTOMER_ID = ?");
                    $img_update->bind_param("si", $newname, $customer_id);
                    $img_update->execute();

                    // Delete old image if exists
                    if ($old_image && file_exists('uploads/profiles/' . $old_image)) {
                        unlink('uploads/profiles/' . $old_image);
                    }
                }
            }
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Customer updated successfully'
        ]);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in customer update: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
        exit;
    }
}

/**
$admin_id = $_SESSION['user_id'];*/
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminResult = $adminQuery->get_result();
$adminName = $adminResult->num_rows > 0 ? $adminResult->fetch_assoc()['full_name'] : 'Test Admin';

/** Add admin name fetch
$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminName = $adminQuery->get_result()->fetch_assoc()['full_name'];*/

// Replace the existing SQL query with this one
$sql = "SELECT PK_CUSTOMER_ID, F_NAME, L_NAME, EMAIL, PHONE_NUM, CUSTOMER_ADDRESS, 
        PROFILE_PIC, CREATED_AT, UPDATE_AT 
        FROM customer ORDER BY CREATED_AT DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Customers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .customer-section {
            margin-left: 250px;
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }

        .header button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .header button:hover {
            background-color: #b71c1c;
        }

        table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        table th,
        table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
        }

        table tr:hover {
            background-color: #f5f5f5;
        }

        .profile-pic {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-cell {
            width: 70px;
        }

        .customer-id {
            font-size: 0.8em;
            color: #666;
            margin-top: 4px;
        }

        .address-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .last-updated {
            font-size: 0.8em;
            color: #666;
            margin-top: 4px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-buttons button {
            border: none;
            background: none;
            padding: 6px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .view-btn {
            color: #2196F3;
        }

        .edit-btn {
            color: #FFA000;
        }

        .delete-btn {
            color: #F44336;
        }

        .action-buttons button:hover {
            background-color: #f5f5f5;
        }

        table td {
            vertical-align: middle;
        }

        table td i {
            width: 16px;
            margin-right: 8px;
            color: #666;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 3% auto;
            padding: 0;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            position: relative;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            background-color: #d32f2f;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .close:hover {
            transform: scale(1.1);
        }

        .supplier-id-display {
            background: #f8f9fa;
            padding: 15px 20px;
            color: #666;
            font-size: 1.1em;
            border-bottom: 1px solid #eee;
        }

        .form-group {
            margin: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label i {
            color: #d32f2f;
            width: 16px;
        }

        .form-group input[type="text"],
        .form-group input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: #d32f2f;
            outline: none;
            box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.1);
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 0 0 8px 8px;
        }

        .save-btn {
            background: #d32f2f;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .save-btn:hover {
            background: #b71c1c;
            transform: translateY(-1px);
        }

        .cancel-btn {
            background: #fff;
            color: #666;
            border: 1px solid #ddd;
            padding: 10px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .cancel-btn:hover {
            background: #f5f5f5;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .modal-content {
                margin: 20px;
                width: auto;
            }
        }

        .profile-display-container {
            width: 120px;
            height: 120px;
            margin: 0 auto;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .profile-display-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px;
        }

        .form-row.full-width {
            grid-template-columns: 1fr;
        }

        .form-row.full-width textarea {
            width: 100%;
            height: 100px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-row.full-width textarea:focus {
            border-color: #d32f2f;
            outline: none;
            box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.1);
        }

        .customer-id-display {
            background: #f8f9fa;
            padding: 15px 20px;
            color: #666;
            font-size: 1.1em;
            border-bottom: 1px solid #eee;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .modal-profile-header {
            text-align: center;
            padding: 30px 0;
            background: #d32f2f;
            border-radius: 8px 8px 0 0;
            position: relative;
        }

        .profile-upload-container {
            width: 120px;
            height: 120px;
            margin: 0 auto;
            border-radius: 50%;
            overflow: hidden;
            position: relative;
            border: 4px solid white;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .profile-upload-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-overlay {
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
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .profile-upload-container:hover .upload-overlay {
            opacity: 1;
        }

        .upload-overlay i {
            color: white;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .upload-overlay span {
            color: white;
            font-size: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px;
        }

        .form-group {
            margin: 0;
        }

        .form-group textarea {
            width: 100%;
            height: 100px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-group textarea:focus {
            border-color: #d32f2f;
            outline: none;
            box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.1);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #d32f2f;
            color: white;
        }

        .btn-primary:hover {
            background-color: #b71c1c;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #fff;
            color: #666;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background-color: #f5f5f5;
        }
        .modal-footer {
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            border-radius: 0 0 8px 8px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #d32f2f;
            color: white;
        }

        .btn-primary:hover {
            background-color: #b71c1c;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #fff;
            color: #666;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background-color: #f5f5f5;
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
        <a href="Admin_product.php">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="Admin_customers.php" class="active">
            <i class="fas fa-users"></i> Customers
        </a>
        <a href="Admin_reports.php">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="customer-section">
        <div class="header">
            <h2>Customers</h2>
            <button onclick="window.location.href='Admin_add_customer.php'">Add Customer</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>Contact Information</th>
                    <th>Address</th>
                    <th>Date Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="profile-cell">
                        <img src="uploads/profiles/<?= !empty($row['PROFILE_PIC']) ? 
                            htmlspecialchars($row['PROFILE_PIC']) : 'default-avatar.png' ?>" 
                            alt="Profile Picture" class="profile-pic">
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($row['F_NAME'] . ' ' . $row['L_NAME']) ?></strong>
                        <div class="customer-id">ID: <?= htmlspecialchars($row['PK_CUSTOMER_ID']) ?></div>
                    </td>
                    <td>
                        <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($row['EMAIL']) ?></div>
                        <div><i class="fas fa-phone" style="margin-top: 10px;"></i> <?= htmlspecialchars($row['PHONE_NUM']) ?></div>
                    </td>
                    <td>
                        <div class="address-cell">
                            <i class="fas fa-map-marker-alt"></i> 
                            <?= htmlspecialchars($row['CUSTOMER_ADDRESS']) ?>
                        </div>
                    </td>
                    <td>
                        <div> <?= date("M d, Y", strtotime($row['CREATED_AT'])) ?></div>
                        <div class="last-updated">Updated: <?= date("M d, Y", strtotime($row['UPDATE_AT'])) ?></div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="editCustomer(<?= $row['PK_CUSTOMER_ID'] ?>)" class="edit-btn">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteCustomer(<?= $row['PK_CUSTOMER_ID'] ?>)" class="delete-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div> <!-- End of customer-section -->

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-profile-header">
                <div class="profile-upload-container" onclick="document.getElementById('edit_image').click()">
                    <img id="profile_preview" src="assets/default-profile.png" alt="Profile Picture">
                    <div class="upload-overlay">
                        <i class="fas fa-camera"></i>
                        <span>Change Photo</span>
                    </div>
                </div>
            </div>

            <form id="editForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="customer_id" id="edit_customer_id">
                <input type="hidden" name="action" value="edit">
                <input type="file" id="edit_image" name="profile_pic" accept="image/*" style="display: none;">
                
                <div class="customer-id-display">
                    Customer #<span id="customer_number"></span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_fname"><i class="fas fa-user"></i> First Name</label>
                        <input type="text" id="edit_fname" name="f_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_lname"><i class="fas fa-user"></i> Last Name</label>
                        <input type="text" id="edit_lname" name="l_name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_phone"><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="text" id="edit_phone" name="phone_num" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="edit_address"><i class="fas fa-map-marker-alt"></i> Address</label>
                        <textarea id="edit_address" name="address" required></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form submission handler
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', handleFormSubmit);
    }

    // Initialize image preview handler
    const imageInput = document.getElementById('edit_image');
    if (imageInput) {
        imageInput.addEventListener('change', handleImagePreview);
    }
});

function handleImagePreview(e) {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile_preview').src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    }
}

function handleFormSubmit(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    formData.append('action', 'edit');
    
    fetch('Admin_customers.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                closeModal();
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to update customer'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred'
        });
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function editCustomer(customerId) {
    console.log('Opening modal for customer:', customerId); // Debug log
    
    // Show loading state
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Fetch customer data
    fetch(`get_customer.php?id=${customerId}`)
        .then(response => {
            console.log('Response status:', response.status); // Debug log
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data); // Debug log
            
            if (data.success) {
                const customerData = data.data;
                
                // Update form fields
                document.getElementById('edit_customer_id').value = customerData.PK_CUSTOMER_ID;
                document.getElementById('customer_number').textContent = customerData.PK_CUSTOMER_ID;
                document.getElementById('edit_fname').value = customerData.F_NAME;
                document.getElementById('edit_lname').value = customerData.L_NAME;
                document.getElementById('edit_email').value = customerData.EMAIL;
                document.getElementById('edit_phone').value = customerData.PHONE_NUM;
                document.getElementById('edit_address').value = customerData.CUSTOMER_ADDRESS;
                
                // Update profile image
                const profilePreview = document.getElementById('profile_preview');
                if (customerData.PROFILE_PIC) {
                    profilePreview.src = `uploads/profiles/${customerData.PROFILE_PIC}`;
                } else {
                    profilePreview.src = 'uploads/profiles/default-avatar.png';
                }
                
                // Show modal
                const modal = document.getElementById('editModal');
                modal.style.display = 'block';
                Swal.close();
            } else {
                throw new Error(data.message || 'Failed to load customer data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Failed to load customer data'
            });
        });
}

function closeModal() {
    const modal = document.getElementById('editModal');
    modal.style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeModal();
    }
}

function deleteCustomer(customerId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('delete_customer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ customer_id: customerId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'Failed to delete customer'
                });
            });
        }
    });
}
</script>
</body>
</html>