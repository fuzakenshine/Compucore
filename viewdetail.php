<?php
session_start();
include 'db_connect.php';

$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : null;

if ($product_id) {
    // Update query to use REVIEWS table instead of ratings
    $sql = "SELECT p.*, 
            (SELECT AVG(RATING) FROM REVIEWS WHERE FK2_PRODUCT_ID = p.PK_PRODUCT_ID) as avg_rating,
            (SELECT COUNT(*) FROM REVIEWS WHERE FK2_PRODUCT_ID = p.PK_PRODUCT_ID) as review_count,
            s.COMPANY_NAME as supplier_name
            FROM products p 
            LEFT JOIN supplier s ON p.FK2_SUPPLIER_ID = s.PK_SUPPLIER_ID
            WHERE p.PK_PRODUCT_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    // Replace the existing reviews query with this simplified version
    $reviews_sql = "SELECT r.*, c.F_NAME, c.L_NAME, c.PROFILE_PIC
                    FROM REVIEWS r
                    LEFT JOIN customer c ON r.FK1_CUSTOMER_ID = c.PK_CUSTOMER_ID
                    WHERE r.FK2_PRODUCT_ID = ?
                    ORDER BY r.CREATED_AT DESC";

    $review_stmt = $conn->prepare($reviews_sql);
    $review_stmt->bind_param("i", $product_id);
    $review_stmt->execute();
    $reviews = $review_stmt->get_result();

    // Calculate average rating
    $rating_sql = "SELECT AVG(RATING) as avg_rating, COUNT(*) as total_reviews 
                   FROM REVIEWS WHERE FK2_PRODUCT_ID = ?";
    $rating_stmt = $conn->prepare($rating_sql);
    $rating_stmt->bind_param("i", $product_id);
    $rating_stmt->execute();
    $rating_info = $rating_stmt->get_result()->fetch_assoc();
} else {
    echo "Invalid product ID.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            margin-top: 100px;
        }
        .product-detail {
            display: flex;
            gap: 40px;
            align-items: flex-start;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .product-image {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .product-image img {
            width: 400px;  /* Fixed width */
            height: 400px; /* Fixed height */
            object-fit: contain; /* Maintains aspect ratio without stretching */
            border-radius: 8px;
            background-color: white;
        }
        .product-info {
            flex: 2;
        }
        .product-name {
            color: #d32f2f;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .supplier-info {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .supplier-info i {
            color: #888;
        }
        .rating-box {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .stars {
            display: flex;
            gap: 5px;
        }
        .stars .fa-star {
            color: #ddd;
            font-size: 18px;
        }
        .stars .fa-star.active {
            color: #ffc107;
        }
        .rating-count {
            color: #666;
            font-size: 14px;
        }
        .price {
            font-size: 32px;
            color: #d32f2f;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .order-button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        .order-button:hover {
            background-color: #b71c1c;
        }
        .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
        padding: 10px 20px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background-color: #d32f2f;
        height: 30px;
    }
    .header .logo {
    font-size: 24px;
    width: 100px; /* Adjust width as needed */
    height: 30px; /* Adjust height as needed */
    font-weight: bold;
}
        .header .logo img {
            height: 50px;
            width: 80px;
            display: block;
            margin: 0 auto;
            transition: transform 0.3s ease;
        }
        .header .logo img:hover {
            transform: scale(1.1);
        }
        .header .logo a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 24px;
        }
        .header .search-bar {
            display: flex;
            align-items: center;
            position: relative;
            margin-left: 450px;
        }

        .header .search-bar input {
            padding: 8px 35px 8px 12px;
            border: none;
            border-radius: 30px;
            width: 200px;
            outline: none;
        }

        .header .search-bar button {
            position: absolute;
            right: 5px;
            background: none;
            border: none;
            color: #555;
            cursor: pointer;
            font-size: 16px;
        }

        .header .icons {
            display: flex;
            align-items: right;
            margin-left: 400px;
        }
        .header .icons i {
            margin: 0 10px;
            cursor: pointer;
            font-size: 20px;
            color: white;
        }
        .header .burger-menu {
            position: relative;
            display: inline-block;
        }
        .header .burger-menu button {
            background-color: transparent;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        .header .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .header .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .header .dropdown-content a:hover {
            background-color: #ddd;
        }
        .header .burger-menu:hover .dropdown-content {
            display: block;
        }

        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .quantity {
            margin-bottom: 20px;
        }

        .quantity select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .add-to-cart-btn {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .specs-section, .description-section {
            margin-top: 30px;
        }

        .reviews-section {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #ddd;
        }

        .review-item {
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .reviewer-name {
            font-weight: bold;
        }

        .review-date {
            color: #666;
            font-size: 0.9em;
            margin-top: 10px;
        }

        .average-rating {
            text-align: center;
            margin-bottom: 30px;
        }

        .rating-number {
            font-size: 48px;
            font-weight: bold;
            color: #d32f2f;
        }

        /* Add to your existing styles */
        .review-image {
            margin-top: 10px;
        }

        .review-image img {
            max-width: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .review-text {
            margin: 10px 0;
            line-height: 1.5;
        }

        .reviews-list {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 20px;
        }

        .review-item {
            background-color: white;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .popup {
            position: fixed;
            top: 60px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            opacity: 0;
            transition: opacity 0.5s ease;
            z-index: 1000;
        }

        .reviews-section {
            margin-top: 40px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .rating-summary {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .average-rating {
            text-align: center;
        }

        .big-rating {
            font-size: 48px;
            font-weight: bold;
            color: #333;
        }

        .stars {
            color: #ffc107;
            font-size: 20px;
            margin: 10px 0;
        }

        .total-reviews {
            color: #666;
        }

        .review-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .reviewer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .reviewer-name {
            font-weight: 500;
            color: #333;
        }

        .review-date {
            color: #666;
            font-size: 0.9em;
        }

        .review-rating {
            color: #ffc107;
        }

        .review-content {
            margin: 15px 0;
            line-height: 1.6;
        }

        .review-images {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .review-images img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .review-images img:hover {
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .rating-summary {
                flex-direction: column;
                text-align: center;
            }
            
            .review-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .review-images img {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
    <div class="container">
        <div class="product-detail">
            <div class="product-image">
                <img src="uploads/<?php echo htmlspecialchars($product['IMAGE']); ?>" alt="<?php echo htmlspecialchars($product['PROD_NAME']); ?>">
            </div>
            <div class="product-info">
                <h1 class="product-name"><?php echo htmlspecialchars($product['PROD_NAME']); ?></h1>
                <div class="supplier-info">
                    <i class="fas fa-truck"></i>
                    <span>Supplier: <?php echo htmlspecialchars($product['supplier_name']); ?></span>
                </div>
                <div class="rating-box">
                    <div class="stars">
                        <?php
                        $rating = round($product['avg_rating'] ?? 0);
                        for ($i = 1; $i <= 5; $i++) {
                            echo '<i class="fas fa-star ' . ($i <= $rating ? 'active' : '') . '"></i>';
                        }
                        ?>
                    </div>
                    <span class="rating-count"><?php echo $product['review_count'] ?? 0; ?> Reviews</span>
                </div>
                <div class="price">₱<?php echo number_format($product['PRICE'], 2); ?></div>
                <form method="POST" action="add_to_cart.php" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['PK_PRODUCT_ID']; ?>">
                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['PROD_NAME']); ?>">
                    <input type="hidden" name="product_price" value="<?php echo $product['PRICE']; ?>">
                    <div class="quantity">
                        <label>Quantity:</label>
                        <select name="quantity">
                            <?php for($i=1; $i<=10; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                </form>
                
                <div class="specs-section">
                    <h2>Specifications:</h2>
                    <div class="specs-content">
                        <?php echo nl2br(htmlspecialchars($product['PROD_SPECS'] ?? 'No specifications available')); ?>
                    </div>
                </div>
                
                <div class="description-section">
                    <h2>Description:</h2>
                    <div class="description-content">
                        <?php echo nl2br(htmlspecialchars($product['PROD_DESC'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section">
            <h2>Reviews and Ratings</h2>
            <div class="reviews-list">
                <?php while($review = $reviews->fetch_assoc()): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <img src="uploads/profiles/<?php echo !empty($review['PROFILE_PIC']) ? 
                                    htmlspecialchars($review['PROFILE_PIC']) : 'default-avatar.png'; ?>" 
                                    alt="Reviewer" class="reviewer-avatar">
                                <div>
                                    <div class="reviewer-name">
                                        <?php echo htmlspecialchars($review['F_NAME'] . ' ' . $review['L_NAME']); ?>
                                    </div>
                                    <div class="review-date">
                                        <?php echo date('F d, Y', strtotime($review['CREATED_AT'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="review-rating">
                                <?php
                                for($i = 1; $i <= 5; $i++) {
                                    if($i <= $review['RATING']) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="review-content">
                            <?php echo nl2br(htmlspecialchars($review['COMMENT'])); ?>
                        </div>

                        <?php if(!empty($review['IMAGE'])): ?>
                            <div class="review-images">
                                <?php 
                                $images = explode(',', $review['IMAGE']);
                                foreach ($images as $img): 
                                    $img = trim($img);
                                    if ($img):
                                ?>
                                    <img src="uploads/reviews/<?php echo htmlspecialchars($img); ?>" 
                                         alt="Review image" onclick="openImageModal(this.src)">
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>

    <!-- Modal for enlarged review images -->
    <div id="imageModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.8);justify-content:center;align-items:center;z-index:9999;">
        <img id="modalImg" src="" style="max-width:90vw;max-height:90vh;border-radius:12px;">
    </div>
    <script>
    function showPopup(message) {
        const popup = document.createElement('div');
        popup.className = 'popup';
        popup.innerText = message;
        document.body.appendChild(popup);

        setTimeout(() => {
            popup.style.opacity = '1';
        }, 100);
        
        setTimeout(() => {
            popup.style.opacity = '0';
        }, 3000);
        
        setTimeout(() => {
            document.body.removeChild(popup);
        }, 3500);
    }

    // Handle form submission
    document.querySelector('.add-to-cart-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch('add_to_cart.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.text())
        .then(data => {
            showPopup('Product successfully added to cart');
        })
        .catch(error => {
            showPopup('Error adding product to cart');
        });
    });

    // Show popup if there's a message in URL
    window.onload = function() {
        <?php if (isset($_GET['message'])): ?>
            showPopup("<?php echo htmlspecialchars($_GET['message']); ?>");
        <?php endif; ?>
    };

    function openImageModal(src) {
        document.getElementById('modalImg').src = src;
        document.getElementById('imageModal').style.display = 'flex';
    }
    document.getElementById('imageModal').onclick = function() {
        this.style.display = 'none';
    };
    </script>
</body>
</html>