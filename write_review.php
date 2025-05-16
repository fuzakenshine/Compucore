<?php
session_start();
include 'db_connect.php';

    if (!isset($_SESSION['customer_id']) || !isset($_GET['order_id']) || !isset($_GET['product_id'])) {
    header('Location: my_orders.php');
    exit;
}

$order_id = $_GET['order_id'];
$product_id = $_GET['product_id'];
$customer_id = $_SESSION['customer_id'];

// Check if order belongs to customer and is approved
$check_sql = "SELECT o.STATUS, p.PROD_NAME, p.IMAGE 
              FROM orders o 
              JOIN order_detail od ON o.PK_ORDER_ID = od.FK2_ORDER_ID 
              JOIN products p ON od.FK1_PRODUCT_ID = p.PK_PRODUCT_ID 
              WHERE o.PK_ORDER_ID = ? AND o.FK1_CUSTOMER_ID = ? AND p.PK_PRODUCT_ID = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("iii", $order_id, $customer_id, $product_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$order_info = $result->fetch_assoc();

if (!$order_info || $order_info['STATUS'] !== 'Approved') {
    header('Location: my_orders.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $image = null;
    
    // Start transaction
    $conn->begin_transaction();
    try {
        // Handle single image upload
        $image_names = [];
        if (!empty($_FILES['review_images']['name'][0])) {
            $target_dir = "uploads/reviews/";
            foreach ($_FILES['review_images']['tmp_name'] as $key => $tmp_name) {
                if (!empty($tmp_name)) {
                    $image_name = time() . '_' . uniqid() . '_' . basename($_FILES['review_images']['name'][$key]);
                    $target_file = $target_dir . $image_name;
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $image_names[] = $image_name;
                    }
                }
            }
        }
        $image = !empty($image_names) ? implode(',', $image_names) : null;
        
        // Insert review with image
        $insert_review_sql = "INSERT INTO REVIEWS (FK1_CUSTOMER_ID, FK2_PRODUCT_ID, FK3_ORDER_ID, RATING, COMMENT, IMAGE, CREATED_AT) 
                             VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_review_sql);
        $insert_stmt->bind_param("iiisss", $customer_id, $product_id, $order_id, $rating, $comment, $image);
        $insert_stmt->execute();
        
        // Check if all products in the order have been reviewed by this customer
        // 1. Count total products in the order
        $sql_total = "SELECT COUNT(*) as total FROM order_detail WHERE FK2_ORDER_ID = ?";
        $stmt_total = $conn->prepare($sql_total);
        $stmt_total->bind_param("i", $order_id);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result()->fetch_assoc();
        $total_products = $result_total['total'];

        // 2. Count reviewed products in the order by this customer
        $sql_reviewed = "SELECT COUNT(*) as reviewed FROM reviews WHERE FK3_ORDER_ID = ? AND FK1_CUSTOMER_ID = ?";
        $stmt_reviewed = $conn->prepare($sql_reviewed);
        $stmt_reviewed->bind_param("ii", $order_id, $customer_id);
        $stmt_reviewed->execute();
        $result_reviewed = $stmt_reviewed->get_result()->fetch_assoc();
        $reviewed_products = $result_reviewed['reviewed'];

        // 3. If all products are reviewed, update order status to 'Completed'
        if ($total_products == $reviewed_products) {
            $sql_update = "UPDATE orders SET STATUS = 'Completed' WHERE PK_ORDER_ID = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $order_id);
            $stmt_update->execute();
        }
        
        $conn->commit();
        header("Location: viewdetail.php?product_id=$product_id&message=Review submitted successfully");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error submitting review: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Write Review</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .review-container {
            flex: 1 0 auto;
            max-width: 1200px;
            margin: 60px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .rating-section {
            margin: 15px 0;
            text-align: center;
        }

        .rating-title {
            font-size: 1.2em;
            color: #333;
            margin-bottom: 15px;
        }

        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }

        .rating-input input {
            display: none;
        }

        .rating-input label {
            cursor: pointer;
            font-size: 32px;
            color: #ddd;
            transition: color 0.2s ease;
        }

        .rating-input input:checked ~ label,
        .rating-input label:hover,
        .rating-input label:hover ~ label {
            color: #ffc107;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 500;
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            min-height: 120px;
            margin-top: 10px;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.3s ease;
        }

        textarea:focus {
            outline: none;
            border-color: #d32f2f;
            box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.1);
        }

        .image-upload {
            margin: 20px 0;
        }

        .custom-file-upload {
            display: inline-block;
            padding: 12px 20px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .custom-file-upload:hover {
            background: #eee;
        }

        .custom-file-upload i {
            margin-right: 8px;
            color: #d32f2f;
        }

        .submit-btn {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 30px auto 0;
        }

        .submit-btn:hover {
            background-color: #b71c1c;
            transform: translateY(-1px);
        }

        .image-preview {
            margin-top: 15px;
            display: none;
        }

        .image-preview img {
            max-width: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .image-previews {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .preview-container {
            position: relative;
            width: 100px;
            height: 100px;
            margin-bottom: 10px;
        }

        .preview-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .remove-image {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #d32f2f;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .remove-image:hover {
            background: #b71c1c;
        }

        .image-hint {
            display: block;
            color: #666;
            margin-top: 8px;
            font-size: 0.9em;
        }

        @media (max-width: 1024px) {
            .review-container {
                margin: 20px;
                padding: 20px;
            }

            .product-info {
                flex-direction: column;
                text-align: center;
            }

            .rating-input label {
                font-size: 28px;
            }
        }
        footer {
            flex-shrink: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="review-container" style = "width: 95vw;">
        <div class="product-info">
            <img src="uploads/<?php echo htmlspecialchars($order_info['IMAGE']); ?>" 
                 alt="<?php echo htmlspecialchars($order_info['PROD_NAME']); ?>" 
                 class="product-image">
            <div>
                <h2><?php echo htmlspecialchars($order_info['PROD_NAME']); ?></h2>
                <p class="order-id">Order #<?php echo htmlspecialchars($order_id); ?></p>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" id="reviewForm">
            <div class="rating-section">
                <h3 class="rating-title">Rate your experience</h3>
                <div class="rating-input">
                    <?php for($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" value="<?php echo $i; ?>" 
                               id="star<?php echo $i; ?>" required>
                        <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="form-group" style = "width: 60.7vw;">
                <label>Share your thoughts</label>
                <textarea name="comment" required 
                          placeholder="What did you like or dislike about this product? How was the quality?"
                          maxlength="500"></textarea>
                <div class="char-count">0/500</div>
            </div>

            <div class="form-group">
                <label class="custom-file-upload">
                    <i class="fas fa-camera"></i>Add Photos (Up to 3)
                    <input type="file" name="review_images[]" accept="image/*" multiple 
                           onchange="previewImages(this)" style="display: none;">
                </label>
                <div class="image-previews" id="imagePreviews"></div>
                <small class="image-hint">You can upload up to 3 photos</small>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i> Submit Review
            </button>
        </form>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    // Replace the existing previewImage function with this new code
    function previewImages(input) {
        const previews = document.getElementById('imagePreviews');
        const maxImages = 3;
        const currentImages = previews.children.length;
        const remainingSlots = maxImages - currentImages;
        
        if (input.files.length > remainingSlots) {
            alert(`You can only upload ${remainingSlots} more image${remainingSlots !== 1 ? 's' : ''}`);
            input.value = '';
            return;
        }

        for (let i = 0; i < input.files.length; i++) {
            const file = input.files[i];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const container = document.createElement('div');
                container.className = 'preview-container';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Preview';
                
                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-image';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.onclick = function() {
                    container.remove();
                    updateFileInput();
                };
                
                container.appendChild(img);
                container.appendChild(removeBtn);
                previews.appendChild(container);
            }
            
            reader.readAsDataURL(file);
        }
    }

    function updateFileInput() {
        const input = document.querySelector('input[name="review_images[]"]');
        // Clear the file input if all previews are removed
        if (document.getElementById('imagePreviews').children.length === 0) {
            input.value = '';
        }
    }

    document.querySelector('textarea[name="comment"]').addEventListener('input', function() {
        const charCount = this.value.length;
        document.querySelector('.char-count').textContent = `${charCount}/500`;
    });

    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        const rating = document.querySelector('input[name="rating"]:checked');
        const comment = document.querySelector('textarea[name="comment"]');

        if (!rating) {
            e.preventDefault();
            alert('Please select a rating');
            return;
        }

        if (comment.value.trim().length < 10) {
            e.preventDefault();
            alert('Please write a review with at least 10 characters');
            return;
        }
    });
    </script>
</body>
</html>