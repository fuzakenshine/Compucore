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

// Fetch categories and suppliers for filters
$categories = $conn->query("SELECT PK_CATEGORY_ID, CAT_NAME, CAT_DESC FROM categories ORDER BY CAT_NAME");
$suppliers = $conn->query("SELECT PK_SUPPLIER_ID, COMPANY_NAME FROM supplier WHERE STATUS = 'Active' ORDER BY COMPANY_NAME");

// Handle search/filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : '';
$supplier_id = isset($_GET['supplier']) ? intval($_GET['supplier']) : '';

// Build the SQL query with filters
$sql = "SELECT p.*, 
        CASE WHEN p.QTY <= 5 THEN true ELSE false END as is_low_stock,
        CASE WHEN p.QTY = 0 THEN true ELSE false END as is_no_stock,
        c.CAT_NAME, c.CAT_DESC, s.COMPANY_NAME
        FROM PRODUCTS p
        LEFT JOIN categories c ON p.FK1_CATEGORY_ID = c.PK_CATEGORY_ID
        LEFT JOIN supplier s ON p.FK2_SUPPLIER_ID = s.PK_SUPPLIER_ID
        WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (p.PROD_NAME LIKE '%" . $conn->real_escape_string($search) . "%' ";
    $sql .= " OR c.CAT_NAME LIKE '%" . $conn->real_escape_string($search) . "%' ";
    $sql .= " OR c.CAT_DESC LIKE '%" . $conn->real_escape_string($search) . "%' ";
    $sql .= " OR s.COMPANY_NAME LIKE '%" . $conn->real_escape_string($search) . "%')";
}
if (!empty($category_id)) {
    $sql .= " AND p.FK1_CATEGORY_ID = " . intval($category_id);
}
if (!empty($supplier_id)) {
    $sql .= " AND p.FK2_SUPPLIER_ID = " . intval($supplier_id);
}

$sql .= " ORDER BY is_no_stock DESC, is_low_stock DESC, p.QTY ASC LIMIT $items_per_page OFFSET $offset";
$result = $conn->query($sql);

// Update count for pagination with filters
$count_sql = "SELECT COUNT(*) as total FROM PRODUCTS p 
              LEFT JOIN categories c ON p.FK1_CATEGORY_ID = c.PK_CATEGORY_ID 
              LEFT JOIN supplier s ON p.FK2_SUPPLIER_ID = s.PK_SUPPLIER_ID 
              WHERE 1=1";
if (!empty($search)) {
    $count_sql .= " AND (p.PROD_NAME LIKE '%" . $conn->real_escape_string($search) . "%' ";
    $count_sql .= " OR c.CAT_NAME LIKE '%" . $conn->real_escape_string($search) . "%' ";
    $count_sql .= " OR c.CAT_DESC LIKE '%" . $conn->real_escape_string($search) . "%' ";
    $count_sql .= " OR s.COMPANY_NAME LIKE '%" . $conn->real_escape_string($search) . "%')";
}
if (!empty($category_id)) {
    $count_sql .= " AND p.FK1_CATEGORY_ID = " . intval($category_id);
}
if (!empty($supplier_id)) {
    $count_sql .= " AND p.FK2_SUPPLIER_ID = " . intval($supplier_id);
}
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'edit') {
        try {
            $product_id = intval($_POST['product_id']);
            $prod_name = $conn->real_escape_string($_POST['prod_name']);
            $prod_desc = $conn->real_escape_string($_POST['prod_desc']);
            $prod_specs = $conn->real_escape_string($_POST['prod_specs']);
            $supplier_id = intval($_POST['supplier_id']);
            $category_id = intval($_POST['category_id']);
            $price = floatval($_POST['price']);
            $qty = intval($_POST['qty']);

            // Debug log
            error_log("Updating product ID: $product_id");
            error_log("Name: $prod_name, Desc: $prod_desc, Specs: $prod_specs");
            error_log("Category: $category_id, Supplier: $supplier_id");
            error_log("Price: $price, Qty: $qty");

            $update_sql = "UPDATE PRODUCTS SET 
                PROD_NAME = ?,
                PROD_DESC = ?,
                PROD_SPECS = ?,
                FK1_CATEGORY_ID = ?,
                FK2_SUPPLIER_ID = ?,
                PRICE = ?,
                QTY = ?,
                UPDATED_AT = NOW()
                WHERE PK_PRODUCT_ID = ?";

            $stmt = $conn->prepare($update_sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("sssiiidi", 
                $prod_name, 
                $prod_desc, 
                $prod_specs, 
                $category_id, 
                $supplier_id, 
                $price, 
                $qty, 
                $product_id
            );

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No changes made to the product']);
            }

            $stmt->close();
            exit;
        } catch (Exception $e) {
            error_log("Error updating product: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $e->getMessage()]);
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
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

        /* Updated Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            font-family: 'Poppins', sans-serif;
        }

        .modal-content {
            position: relative;
            background-color: white;
            margin: 10px auto;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #d32f2f;
        }

        .modal-title {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 1.5em;
            font-weight: 600;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-form {
            display: grid;
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
            font-size: 0.9em;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9em;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #d32f2f;
            outline: none;
            box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 500;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .modal-btn.primary {
            background-color: #d32f2f;
            color: white;
        }

        .modal-btn.primary:hover {
            background-color: #b71c1c;
            transform: translateY(-1px);
        }

        .modal-btn.secondary {
            background-color: #f5f5f5;
            color: #666;
        }

        .modal-btn.secondary:hover {
            background-color: #e0e0e0;
            color: #333;
        }

        /* SweetAlert Customization */
        .swal2-popup {
            font-family: 'Poppins', sans-serif !important;
            border-radius: 12px !important;
        }

        .swal2-title {
            font-weight: 600 !important;
        }

        .swal2-confirm {
            background-color: #d32f2f !important;
        }

        .swal2-confirm:hover {
            background-color: #b71c1c !important;
        }

        .swal2-cancel {
            background-color: #f5f5f5 !important;
            color: #666 !important;
        }

        .swal2-cancel:hover {
            background-color: #e0e0e0 !important;
            color: #333 !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .modal-content {
                margin: 10% auto;
                padding: 20px;
                width: 95%;
            }

            .modal-buttons {
                flex-direction: column;
            }

            .modal-btn {
                width: 100%;
            }
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

        .search-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .search-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .search-group label {
            font-weight: 500;
            color: #333;
            font-size: 0.9em;
        }

        .search-group input,
        .search-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .search-group input:focus,
        .search-group select:focus {
            border-color: #d32f2f;
            outline: none;
            box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.1);
        }

        .search-btn {
            background: #d32f2f;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .search-btn:hover {
            background: #b71c1c;
            transform: translateY(-1px);
        }

        .no-results {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px 0;
        }

        .no-results i {
            font-size: 48px;
            color: #d32f2f;
            margin-bottom: 20px;
        }

        .no-results h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .no-results p {
            color: #666;
            margin-bottom: 20px;
        }

        .clear-filters {
            background: #f5f5f5;
            color: #666;
            border: 1px solid #ddd;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .clear-filters:hover {
            background: #eee;
            color: #333;
        }

        .active-filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .filter-tag {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85em;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .filter-tag i {
            cursor: pointer;
            font-size: 0.9em;
        }

        .filter-tag i:hover {
            color: #1b5e20;
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

        <div class="search-container">
            <form method="GET" class="search-form">
                <div class="search-group">
                    <label><i class="fas fa-search"></i> Search Products</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, category, or supplier">
                </div>
                <div class="search-group">
                    <label><i class="fas fa-tags"></i> Category</label>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php if ($categories) while($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['PK_CATEGORY_ID']; ?>" 
                                    <?php echo ($category_id == $cat['PK_CATEGORY_ID']) ? 'selected' : ''; ?>
                                    title="<?php echo htmlspecialchars($cat['CAT_DESC']); ?>">
                                <?php echo htmlspecialchars($cat['CAT_NAME']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="search-group">
                    <label><i class="fas fa-truck"></i> Supplier</label>
                    <select name="supplier">
                        <option value="">All Suppliers</option>
                        <?php if ($suppliers) while($sup = $suppliers->fetch_assoc()): ?>
                            <option value="<?php echo $sup['PK_SUPPLIER_ID']; ?>" <?php echo ($supplier_id == $sup['PK_SUPPLIER_ID']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sup['COMPANY_NAME']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="search-group">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>

            <?php if (!empty($search) || !empty($category_id) || !empty($supplier_id)): ?>
            <div class="active-filters">
                <?php if (!empty($search)): ?>
                    <div class="filter-tag">
                        Search: <?php echo htmlspecialchars($search); ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['search' => ''])); ?>">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($category_id)): 
                    $cat_name = '';
                    $cat_desc = '';
                    $categories->data_seek(0);
                    while($cat = $categories->fetch_assoc()) {
                        if ($cat['PK_CATEGORY_ID'] == $category_id) {
                            $cat_name = $cat['CAT_NAME'];
                            $cat_desc = $cat['CAT_DESC'];
                            break;
                        }
                    }
                ?>
                    <div class="filter-tag" title="<?php echo htmlspecialchars($cat_desc); ?>">
                        Category: <?php echo htmlspecialchars($cat_name); ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => ''])); ?>">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($supplier_id)):
                    $sup_name = '';
                    $suppliers->data_seek(0);
                    while($sup = $suppliers->fetch_assoc()) {
                        if ($sup['PK_SUPPLIER_ID'] == $supplier_id) {
                            $sup_name = $sup['COMPANY_NAME'];
                            break;
                        }
                    }
                ?>
                    <div class="filter-tag">
                        Supplier: <?php echo htmlspecialchars($sup_name); ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['supplier' => ''])); ?>">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                <?php endif; ?>

                <a href="Admin_product.php" class="clear-filters">
                    <i class="fas fa-times-circle"></i> Clear All Filters
                </a>
            </div>
            <?php endif; ?>
        </div>

            <?php if ($result->num_rows > 0): ?>
            <div class="product-grid">
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
                            <button class="edit-btn" onclick="openEditModal(<?php echo $row['PK_PRODUCT_ID']; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="delete-btn" onclick="deleteProduct(<?php echo $row['PK_PRODUCT_ID']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="no-results">
                <i class="fas fa-box-open"></i>
                <h3>No Products Found</h3>
                <?php if (!empty($search) || !empty($category_id) || !empty($supplier_id)): ?>
                    <p>No products match your current search criteria.</p>
                    <a href="Admin_product.php" class="clear-filters">
                        <i class="fas fa-times-circle"></i> Clear All Filters
                    </a>
                <?php else: ?>
                    <p>There are no products available at the moment.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
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
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2 class="modal-title">Edit Product</h2>
            <form id="editForm" class="modal-form" method="POST">
                <input type="hidden" id="edit_product_id" name="product_id">
                <input type="hidden" name="action" value="edit">
                <div class="form-group">
                    <label for="edit_prod_name">Product Name</label>
                    <input type="text" id="edit_prod_name" name="prod_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_prod_desc">Description</label>
                    <textarea id="edit_prod_desc" name="prod_desc" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_prod_specs">Specifications</label>
                    <textarea id="edit_prod_specs" name="prod_specs" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_category">Category</label>
                    <select id="edit_category" name="category_id" required>
                        <?php 
                        $categories->data_seek(0);
                        while($cat = $categories->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $cat['PK_CATEGORY_ID']; ?>">
                                <?php echo htmlspecialchars($cat['CAT_NAME']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_supplier">Supplier</label>
                    <select id="edit_supplier" name="supplier_id" required>
                        <?php 
                        $suppliers->data_seek(0);
                        while($sup = $suppliers->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $sup['PK_SUPPLIER_ID']; ?>">
                                <?php echo htmlspecialchars($sup['COMPANY_NAME']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_price">Price</label>
                    <input type="number" id="edit_price" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="edit_qty">Quantity</label>
                    <input type="number" id="edit_qty" name="qty" required>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="modal-btn secondary" onclick="closeEditModal()">Cancel</button>
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

        function openEditModal(productId) {
            // Fetch product details
            fetch(`get_product.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_product_id').value = data.PK_PRODUCT_ID;
                    document.getElementById('edit_prod_name').value = data.PROD_NAME;
                    document.getElementById('edit_prod_desc').value = data.PROD_DESC;
                    document.getElementById('edit_prod_specs').value = data.PROD_SPECS;
                    document.getElementById('edit_category').value = data.FK1_CATEGORY_ID;
                    document.getElementById('edit_supplier').value = data.FK2_SUPPLIER_ID;
                    document.getElementById('edit_price').value = data.PRICE;
                    document.getElementById('edit_qty').value = data.QTY;
                    
            document.getElementById('editModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading product details');
                });
        }

        function deleteProduct(productId) {
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
                    window.location.href = 'Admin_delete_product.php?id=' + productId;
                }
            });
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function closeEditModal() {
            Swal.fire({
                title: 'Discard Changes?',
                text: "Are you sure you want to close without saving?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, close it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('editModal').style.display = 'none';
                }
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }

        // Handle form submission
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Show loading state with SweetAlert
            Swal.fire({
                title: 'Saving Changes',
                html: 'Please wait while we update the product...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('Admin_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Product has been updated successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Unknown error occurred',
                        confirmButtonColor: '#d33'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to update product. Please try again.',
                    confirmButtonColor: '#d33'
                });
            });
        });
    </script>
</body>
</html>