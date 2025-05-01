<?php
require_once 'includes/auth.php';
checkAdminAccess();
include 'db_connect.php';

// Fetch admin name
$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminName = $adminQuery->get_result()->fetch_assoc()['full_name'];

// Supplier query
$sql = "SELECT PK_SUPPLIER_ID, S_FNAME, S_LNAME, EMAIL, CREATE_AT, COMPANY_NAME, SUPPLIER_IMAGE FROM supplier ORDER BY CREATE_AT DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Suppliers</title>
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

        .supplier-section {
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
        }

        .header button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 14px;
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

        /* Keep your existing table styles */
        .supplier-section table td img {
            vertical-align: middle;
        }

        .action-btn {
            padding: 8px;
            margin: 0 5px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
        }
        
        .action-btn.edit {
            background-color: #2196F3;
        }
        
        .action-btn.delete {
            background-color: #f44336;
        }
        
        .action-btn:hover {
            opacity: 0.8;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .form-group input[type="text"],
        .form-group input[type="email"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #2196F3;
            color: white;
        }

        .btn-secondary {
            background-color: #757575;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        #current_image_container {
            margin: 10px 0;
        }

        #current_image_container img {
            max-width: 100px;
            border-radius: 4px;
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
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Keep your existing supplier section content -->
    <div class="supplier-section">
        <div class="header">
            <h2>Suppliers</h2>
            <button onclick="window.location.href='Admin_add_supplier.php'">Add Supplier</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Date Created</th>
                    <th>Company</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                        <?php
                            $photo = !empty($row['SUPPLIER_IMAGE']) ? 'uploads/' . htmlspecialchars($row['SUPPLIER_IMAGE']) : 'assets/default-profile.png';
                        ?>
                        <img src="<?= $photo ?>" alt="Avatar" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">                            
                            <div>
                                <strong><?= htmlspecialchars($row['S_FNAME'] . ' ' . $row['S_LNAME']) ?></strong><br>
                                <small><?= htmlspecialchars($row['EMAIL']) ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?= date("m/d/Y", strtotime($row['CREATE_AT'])) ?></td>
                    <td><?= htmlspecialchars($row['COMPANY_NAME']) ?></td>
                    <td>
                        <a href="javascript:void(0)" onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)" class="action-btn edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="javascript:void(0)" onclick="deleteSupplier(<?= $row['PK_SUPPLIER_ID'] ?>)" class="action-btn delete">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Supplier</h2>
                <span class="close">&times;</span>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="supplier_id" id="edit_supplier_id">
                <div class="form-group">
                    <label for="edit_fname">First Name</label>
                    <input type="text" id="edit_fname" name="fname" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_lname">Last Name</label>
                    <input type="text" id="edit_lname" name="lname" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_company">Company Name</label>
                    <input type="text" id="edit_company" name="company" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_image">Profile Image</label>
                    <div id="current_image_container"></div>
                    <input type="file" id="edit_image" name="image" accept="image/*">
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Modal functionality
    const modal = document.getElementById('editModal');
    const closeBtn = document.getElementsByClassName('close')[0];

    function openEditModal(supplier) {
        document.getElementById('edit_supplier_id').value = supplier.PK_SUPPLIER_ID;
        document.getElementById('edit_fname').value = supplier.S_FNAME;
        document.getElementById('edit_lname').value = supplier.S_LNAME;
        document.getElementById('edit_email').value = supplier.EMAIL;
        document.getElementById('edit_company').value = supplier.COMPANY_NAME;
        
        // Handle current image display
        const imageContainer = document.getElementById('current_image_container');
        if (supplier.SUPPLIER_IMAGE) {
            imageContainer.innerHTML = `<img src="uploads/${supplier.SUPPLIER_IMAGE}" alt="Current profile">`;
        } else {
            imageContainer.innerHTML = '';
        }
        
        modal.style.display = 'block';
    }

    function closeEditModal() {
        modal.style.display = 'none';
    }

    // Close modal when clicking the X or outside the modal
    closeBtn.onclick = closeEditModal;
    window.onclick = function(event) {
        if (event.target == modal) {
            closeEditModal();
        }
    }

    // Handle form submission
    document.getElementById('editForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('Admin_edit_supplier.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error updating supplier: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the supplier');
        });
    };

    function deleteSupplier(supplierId) {
        if (confirm('Are you sure you want to delete this supplier?')) {
            window.location.href = 'Admin_delete_supplier.php?id=' + supplierId;
        }
    }
    </script>
</body>
</html>
