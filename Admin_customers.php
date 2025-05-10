<?php
session_start();
include 'db_connect.php';

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
                <div class="profile-upload-container">
                    <img id="profile_preview" src="assets/default-profile.png" alt="Profile Picture">
                    <div class="upload-overlay">
                        <i class="fas fa-camera"></i>
                        <span>View Photo</span>
                    </div>
                </div>
            </div>

            <form id="editForm" method="POST">
                <input type="hidden" name="customer_id" id="edit_customer_id">
                
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
    const modal = document.getElementById('editModal');
    const closeBtn = document.getElementsByClassName('close')[0];

    // Function to edit customer
    window.editCustomer = function(customerId) {
        // Fetch customer data
        fetch(`get_customer.php?id=${customerId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_customer_id').value = data.PK_CUSTOMER_ID;
                document.getElementById('customer_number').textContent = data.PK_CUSTOMER_ID;
                document.getElementById('edit_fname').value = data.F_NAME;
                document.getElementById('edit_lname').value = data.L_NAME;
                document.getElementById('edit_email').value = data.EMAIL;
                document.getElementById('edit_phone').value = data.PHONE_NUM;
                document.getElementById('edit_address').value = data.CUSTOMER_ADDRESS;

                // Update profile picture
                const profilePreview = document.getElementById('profile_preview');
                if (data.PROFILE_PIC) {
                    profilePreview.src = `uploads/profiles/${data.PROFILE_PIC}`;
                } else {
                    profilePreview.src = 'uploads/profiles/default-avatar.png';
                }
                
                modal.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error fetching customer data');
            });
    }

    // Function to delete customer
    window.deleteCustomer = function(customerId) {
        if (confirm('Are you sure you want to delete this customer?')) {
            fetch('delete_customer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ customer_id: customerId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting customer');
                }
            });
        }
    }

    // Function to close modal
    window.closeModal = function() {
        modal.style.display = 'none';
    }

    // Close modal when clicking on X
    closeBtn.onclick = closeModal;

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }

    // Replace the existing form submission code
    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        fetch('update_customer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Customer updated successfully',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    closeModal();
                    location.reload();
                });
            } else {
                throw new Error(data.message || 'Error updating customer');
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Error updating customer'
            });
        })
        .finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});
</script>
</body>
</html>