<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminResult = $adminQuery->get_result();
$adminName = $adminResult->num_rows > 0 ? $adminResult->fetch_assoc()['full_name'] : 'Test Admin';

// Handle POST request for editing supplier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    try {
        $supplier_id = filter_var($_POST['supplier_id'], FILTER_SANITIZE_NUMBER_INT);
        $fname = htmlspecialchars(trim($_POST['fname']), ENT_QUOTES, 'UTF-8');
        $lname = htmlspecialchars(trim($_POST['lname']), ENT_QUOTES, 'UTF-8');
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $company = htmlspecialchars(trim($_POST['company']), ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars(trim($_POST['address']), ENT_QUOTES, 'UTF-8');

        // Start transaction
        $conn->begin_transaction();

        // Update basic info
        $update_stmt = $conn->prepare("UPDATE supplier SET 
            S_FNAME = ?, 
            S_LNAME = ?, 
            EMAIL = ?, 
            COMPANY_NAME = ?,
            PHONE_NUM = ?,
            SUPPLIER_ADDRESS = ?,
            UPDATE_AT = CURRENT_TIMESTAMP 
            WHERE PK_SUPPLIER_ID = ?");
            
        if (!$update_stmt) {
            throw new Exception("Prepare update failed: " . $conn->error);
        }

        $update_stmt->bind_param("ssssssi", 
            $fname, 
            $lname, 
            $email, 
            $company,
            $phone,
            $address,
            $supplier_id
        );
        
        if (!$update_stmt->execute()) {
            throw new Exception("Execute update failed: " . $update_stmt->error);
        }

        // Handle image upload if present
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['image']['name'];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($filetype, $allowed)) {
                $newname = 'supplier_' . uniqid() . '.' . $filetype;
                $upload_path = 'uploads/' . $newname;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Get old image filename
                    $img_stmt = $conn->prepare("SELECT SUPPLIER_IMAGE FROM supplier WHERE PK_SUPPLIER_ID = ?");
                    $img_stmt->bind_param("i", $supplier_id);
                    $img_stmt->execute();
                    $old_image = $img_stmt->get_result()->fetch_assoc()['SUPPLIER_IMAGE'];

                    // Update database with new image
                    $img_update = $conn->prepare("UPDATE supplier SET SUPPLIER_IMAGE = ? WHERE PK_SUPPLIER_ID = ?");
                    $img_update->bind_param("si", $newname, $supplier_id);
                    $img_update->execute();

                    // Delete old image if exists
                    if ($old_image && file_exists('uploads/' . $old_image)) {
                        unlink('uploads/' . $old_image);
                    }
                }
            }
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Supplier updated successfully'
        ]);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in supplier update: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
        exit;
    }
}

// Supplier query
$sql = "SELECT PK_SUPPLIER_ID, S_FNAME, S_LNAME, EMAIL, CREATE_AT, COMPANY_NAME, 
        SUPPLIER_IMAGE FROM supplier 
        WHERE STATUS = 'Active' 
        ORDER BY CREATE_AT DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Suppliers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .file-input-wrapper {
            position: relative;
            margin-top: 10px;
        }

        .file-input-label {
            display: inline-block;
            padding: 10px 20px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            background: #eee;
        }

        #current_image_container {
            margin: 10px 0;
        }

        #current_image_container img {
            max-width: 100px;
            border-radius: 4px;
            border: 2px solid #ddd;
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

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
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

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
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
        <a href="Admin_reports.php">
            <i class="fas fa-chart-bar"></i> Reports
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
                            $photo = !empty($row['SUPPLIER_IMAGE']) ? 'uploads/' . htmlspecialchars($row['SUPPLIER_IMAGE']) : 'profiles/default.png';
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
                        <a href="javascript:void(0)" class="action-btn edit" onclick="openEditModal(<?= $row['PK_SUPPLIER_ID'] ?>)">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="javascript:void(0)" class="action-btn delete" onclick="deleteSupplier(<?= $row['PK_SUPPLIER_ID'] ?>)">
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
                <input type="hidden" name="supplier_id" id="edit_supplier_id">
                
                <div class="supplier-id-display">
                    Supplier #<span id="supplier_number"></span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_fname"><i class="fas fa-user"></i> First Name</label>
                        <input type="text" id="edit_fname" name="fname" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_lname"><i class="fas fa-user"></i> Last Name</label>
                        <input type="text" id="edit_lname" name="lname" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_company"><i class="fas fa-building"></i> Company Name</label>
                        <input type="text" id="edit_company" name="company" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_phone"><i class="fas fa-phone"></i> Phone</label>
                        <input type="text" id="edit_phone" name="phone" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_address"><i class="fas fa-map-marker-alt"></i> Address</label>
                        <input type="text" id="edit_address" name="address" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
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
        
        fetch('Admin_suppliers.php', {
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
                    closeEditModal();
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to update supplier'
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

    function openEditModal(supplierId) {
        console.log('Opening modal for supplier:', supplierId); // Debug log
        
        // Show loading state
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Fetch supplier data
        fetch(`get_supplier.php?id=${supplierId}`)
            .then(response => {
                console.log('Response status:', response.status); // Debug log
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data); // Debug log
                
                if (data.success) {
                    const supplierData = data.data;
                    
                    // Update form fields
                    document.getElementById('edit_supplier_id').value = supplierData.PK_SUPPLIER_ID;
                    document.getElementById('supplier_number').textContent = supplierData.PK_SUPPLIER_ID;
                    document.getElementById('edit_fname').value = supplierData.S_FNAME;
                    document.getElementById('edit_lname').value = supplierData.S_LNAME;
                    document.getElementById('edit_email').value = supplierData.EMAIL;
                    document.getElementById('edit_company').value = supplierData.COMPANY_NAME;
                    document.getElementById('edit_phone').value = supplierData.PHONE_NUM;
                    document.getElementById('edit_address').value = supplierData.SUPPLIER_ADDRESS;
                    
                    // Update profile image
                    const profilePreview = document.getElementById('profile_preview');
                    if (supplierData.SUPPLIER_IMAGE) {
                        profilePreview.src = `uploads/${supplierData.SUPPLIER_IMAGE}`;
                    } else {
                        profilePreview.src = 'assets/default-profile.png';
                    }
                    
                    // Show modal
                    const modal = document.getElementById('editModal');
                    modal.style.display = 'block';
                    Swal.close();
                } else {
                    throw new Error(data.message || 'Failed to load supplier data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'Failed to load supplier data'
                });
            });
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target == modal) {
            closeEditModal();
        }
    }

    function deleteSupplier(supplierId) {
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
                fetch('Admin_delete_supplier.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ supplier_id: supplierId })
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
                        text: error.message || 'Failed to delete supplier'
                    });
                });
            }
        });
    }
    </script>
</body>
</html>
