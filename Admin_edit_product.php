<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty = (int)$_POST['qty'];
    $price = (float)$_POST['price'];
    $description = trim($_POST['description']);
    
    $update_sql = "UPDATE PRODUCTS SET QTY = ?, PRICE = ?, DESCRIPTION = ? WHERE PK_PRODUCT_ID = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("idsi", $qty, $price, $description, $product_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
        exit();
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
        exit();
    }
}

// Get product details
$sql = "SELECT * FROM PRODUCTS WHERE PK_PRODUCT_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: Admin_product.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .edit-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        input[type="number"],
        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 150px;
            resize: vertical;
            font-family: inherit;
            line-height: 1.5;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .save-btn, .cancel-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .save-btn {
            background-color: #2196F3;
            color: white;
        }

        .save-btn:hover {
            background-color: #1976D2;
        }

        .cancel-btn {
            background-color: #f44336;
            color: white;
        }

        .cancel-btn:hover {
            background-color: #d32f2f;
        }

        .error {
            color: #f44336;
            margin-bottom: 20px;
        }

        .product-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .product-info p {
            margin: 5px 0;
            color: #666;
        }

        .product-info strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <h1>Edit Product</h1>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="product-info">
            <p><strong>Product Name:</strong> <?php echo htmlspecialchars($product['PROD_NAME']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($product['FK1_CATEGORY_ID']); ?></p>
        </div>
        
        <form method="POST">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            
            <div class="form-group">
                <label for="description">Product Description & Specifications</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($product['DESCRIPTION'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="qty">Quantity</label>
                <input type="number" id="qty" name="qty" value="<?php echo $product['QTY']; ?>" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="price">Price (₱)</label>
                <input type="number" id="price" name="price" value="<?php echo $product['PRICE']; ?>" min="0" step="0.01" required>
            </div>
            
            <div class="buttons">
                <button type="submit" class="save-btn">Save Changes</button>
                <button type="button" class="cancel-btn" onclick="window.location.href='Admin_product.php'">Cancel</button>
            </div>
        </form>
    </div>
</body>
</html> 