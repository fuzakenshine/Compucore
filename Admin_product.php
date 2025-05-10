<?php
session_start();
include 'db_connect.php';

$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminResult = $adminQuery->get_result();
$adminName = $adminResult->num_rows > 0 ? $adminResult->fetch_assoc()['full_name'] : 'Test Admin';

/*
$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminName = $adminQuery->get_result()->fetch_assoc()['full_name'];
**/
// Pagination settings
$items_per_page = 18;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total count of products
$count_sql = "SELECT COUNT(*) as total FROM PRODUCTS";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

// Update the product query near the top of the file
$sql = "SELECT *, 
        CASE WHEN QTY <= 5 THEN true ELSE false END as is_low_stock,
        CASE WHEN QTY = 0 THEN true ELSE false END as is_no_stock
        FROM PRODUCTS 
        ORDER BY is_no_stock DESC, is_low_stock DESC, QTY ASC
        LIMIT $items_per_page OFFSET $offset";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Products</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
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

        .product-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr); /* 6 items per row */
            gap: 20px;
            padding: 20px 0;
        }

        .product-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .product-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .product-card h3 {
            margin: 10px 0;
            font-size: 16px;
            color: #333;
            height: 40px;
            overflow: hidden;
        }

        .product-card p {
            margin: 5px 0;
            color: #666;
        }

        .product-card p:first-of-type {
            color: #d32f2f;
            font-weight: bold;
            font-size: 18px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            justify-content: center;
        }

        .edit-btn, .delete-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .edit-btn {
            background-color: #2196F3;
            color: white;
        }

        .edit-btn:hover {
            background-color: #1976D2;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
        }

        .delete-btn:hover {
            background-color: #d32f2f;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
            align-items: center;
        }

        .pagination button {
            background-color: white;
            border: 1px solid #ddd;
            color: #333;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination button:hover {
            background-color: #f5f5f5;
            border-color: #d32f2f;
            color: #d32f2f;
        }

        .pagination button.active {
            background-color: #d32f2f;
            border-color: #d32f2f;
            color: white;
        }

        .pagination a {
            text-decoration: none;
        }

        .pagination button[disabled] {
            background-color: #f5f5f5;
            border-color: #ddd;
            color: #999;
            cursor: not-allowed;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .close:hover {
            color: #333;
        }

        .modal-title {
            margin-bottom: 20px;
            color: #333;
        }

        .modal-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .modal-btn.primary {
            background-color: #2196F3;
            color: white;
        }

        .modal-btn.primary:hover {
            background-color: #1976D2;
        }

        .modal-btn.danger {
            background-color: #f44336;
            color: white;
        }

        .modal-btn.danger:hover {
            background-color: #d32f2f;
        }

        .modal-btn.secondary {
            background-color: #9e9e9e;
            color: white;
        }

        .modal-btn.secondary:hover {
            background-color: #757575;
        }

        .product-card.low-stock {
            border: 2px solid #ff4444;
            position: relative;
        }

        .low-stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ff4444;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            z-index: 1;
        }

        .qty-warning {
            color: #ff4444;
            font-weight: bold;
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
        <a href="Admin_product.php" class="active">
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
    <div class="main-content">
        <div class="header">
            <h1>Products</h1>
            <button onclick="window.location.href='Admin_add_product.php'">Add Product</button>
        </div>
        <div class="product-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="product-card <?php echo $row['is_low_stock'] ? 'low-stock' : ''; ?>">
                        <?php if($row['is_no_stock']): ?>
                            <div class="low-stock-badge" style="background-color: #888;">
                                <i class="fas fa-times-circle"></i> No Stock
                            </div>
                        <?php elseif($row['is_low_stock']): ?>
                            <div class="low-stock-badge">
                                <i class="fas fa-exclamation-triangle"></i> Low Stock
                            </div>
                        <?php endif; ?>
                        <img src="uploads/<?php echo htmlspecialchars($row['IMAGE']); ?>" alt="<?php echo htmlspecialchars($row['PROD_NAME']); ?>">
                        <h3><?php echo htmlspecialchars($row['PROD_NAME']); ?></h3>
                        <p>₱<?php echo number_format($row['PRICE'], 2); ?></p>
                        <p class="<?php echo $row['QTY'] <= 5 ? 'qty-warning' : ''; ?>">Qty: <?php echo $row['QTY']; ?></p>
                        <div class="product-actions">
                            <button class="edit-btn" onclick="editProduct(<?php echo $row['PK_PRODUCT_ID']; ?>, '<?php echo htmlspecialchars($row['PROD_NAME']); ?>', <?php echo $row['QTY']; ?>, <?php echo $row['PRICE']; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="delete-btn" onclick="deleteProduct(<?php echo $row['PK_PRODUCT_ID']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?page=<?php echo ($page-1); ?>">
                    <button><i class="fas fa-chevron-left"></i></button>
                </a>
            <?php else: ?>
                <button disabled><i class="fas fa-chevron-left"></i></button>
            <?php endif; ?>
            
            <?php
            // Show up to 5 page numbers
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $start_page + 4);
            
            if($start_page > 1) {
                echo '<a href="?page=1"><button>1</button></a>';
                if($start_page > 2) echo '<span>...</span>';
            }
            
            for($i = $start_page; $i <= $end_page; $i++): ?>
                <a href="?page=<?php echo $i; ?>">
                    <button <?php echo ($i == $page) ? 'class="active"' : ''; ?>><?php echo $i; ?></button>
                </a>
            <?php endfor;

            if($end_page < $total_pages) {
                if($end_page < $total_pages - 1) echo '<span>...</span>';
                echo '<a href="?page=' . $total_pages . '"><button>' . $total_pages . '</button></a>';
            }
            ?>
            
            <?php if($page < $total_pages): ?>
                <a href="?page=<?php echo ($page+1); ?>">
                    <button><i class="fas fa-chevron-right"></i></button>
                </a>
            <?php else: ?>
                <button disabled><i class="fas fa-chevron-right"></i></button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h2 class="modal-title">Edit Product</h2>
            <form id="editForm" class="modal-form" method="POST">
                <input type="hidden" id="editProductId" name="product_id">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" id="editProductName" disabled>
                </div>
                <div class="form-group">
                    <label for="editQty">Quantity</label>
                    <input type="number" id="editQty" name="qty" min="0" required>
                </div>
                <div class="form-group">
                    <label for="editPrice">Price (₱)</label>
                    <input type="number" id="editPrice" name="price" min="0" step="0.01" required>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="modal-btn secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="modal-btn primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Product Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('deleteModal')">&times;</span>
            <h2 class="modal-title">Delete Product</h2>
            <p>Are you sure you want to delete this product?</p>
            <div class="modal-buttons">
                <button type="button" class="modal-btn secondary" onclick="closeModal('deleteModal')">Cancel</button>
                <button type="button" class="modal-btn danger" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>

    <script>
        let currentProductId = null;

        function editProduct(productId, productName, qty, price) {
            currentProductId = productId;
            document.getElementById('editProductId').value = productId;
            document.getElementById('editProductName').value = productName;
            document.getElementById('editQty').value = qty;
            document.getElementById('editPrice').value = price;
            document.getElementById('editModal').style.display = 'block';
        }

        function deleteProduct(productId) {
            currentProductId = productId;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function confirmDelete() {
            if (currentProductId) {
                window.location.href = 'Admin_delete_product.php?id=' + currentProductId;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }

        // Handle edit form submission
        document.getElementById('editForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('product_id', currentProductId);

            fetch('Admin_edit_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error updating product: ' + data.error);
                }
            })
            .catch(error => {
                alert('Error updating product: ' + error);
            });
        };
    </script>
</body>
</html>