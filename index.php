<?php
require_once 'includes/auth.php';
checkAuthentication(); // This will redirect to login if not authenticated

include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "compucore";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle filter selection
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Base query
$sql = "SELECT p.*, c.CAT_NAME, COALESCE(AVG(r.RATING), 0) as RATING 
        FROM products p 
        LEFT JOIN categories c ON p.FK1_CATEGORY_ID = c.PK_CATEGORY_ID
        LEFT JOIN reviews r ON p.PK_PRODUCT_ID = r.FK2_PRODUCT_ID
        WHERE p.QTY > 0
        GROUP BY p.PK_PRODUCT_ID";

// Add WHERE clause if needed
$where = [];
if (!empty($category)) {
    $where[] = "c.CAT_NAME = '" . $conn->real_escape_string($category) . "'";
}

// Add WHERE clause to query if conditions exist
if (!empty($where)) {
    $sql .= " AND " . implode(' AND ', $where);
}

// Add ORDER BY clause based on filter
switch ($filter) {
    case 'new':
        $sql .= " ORDER BY p.CREATED_AT DESC";
        break;
    case 'price_asc':
        $sql .= " ORDER BY p.PRICE ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.PRICE DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY RATING DESC, p.PROD_NAME ASC";
        break;
    default:
        $sql .= " ORDER BY p.CREATED_AT DESC"; // Default sorting
}

// Pagination handling
$items_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Modify your SQL query to include LIMIT and OFFSET
$count_sql = str_replace("p.*", "COUNT(*) as total", $sql);
$total_result = $conn->query($count_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

// Add LIMIT to your existing SQL query
$sql .= " LIMIT $items_per_page OFFSET $offset";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CompuCore - PC Parts</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #f5f5f5;
        }

        .hero {
            text-align: left;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('uploads/hero1.jpg') no-repeat center center/cover;
            height: 600px;
            color: white;
            padding: 50px 20px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            margin-left: 80px;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .hero p {
            font-size: 20px;
            margin-bottom: 15px;
            margin-left: 80px;
            max-width: 600px;
            line-height: 1.6;
        }

        .hero button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 15px 30px;
            cursor: pointer;
            margin-left: 80px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(211, 47, 47, 0.3);
            width: auto;
            max-width: 200px;
        }

        .hero button:hover {
            background-color: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(211, 47, 47, 0.4);
        }

        .products {
            padding: 40px;
            margin: 0 auto;
            max-width: 1400px;
        }

        .filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 15px;
        }

        .filters button {
            background-color: white;
            border: 2px solid #d32f2f;
            color: #d32f2f;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .filters button:hover,
        .filters button.active {
            background-color: #d32f2f;
            color: white;
            transform: translateY(-2px);
        }

        .category-filter select {
            padding: 10px 20px;
            border: 2px solid #d32f2f;
            border-radius: 25px;
            color: #d32f2f;
            background-color: white;
            cursor: pointer;
            font-size: 14px;
            min-width: 180px;
            transition: all 0.3s ease;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
            width: 100%;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: 100%;
            width: 100%;
            box-sizing: border-box;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .product-card img {
            max-width: 100%;
            height: 180px;
            object-fit: contain;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .product-card:hover img {
            transform: scale(1.05);
        }

        .product-card a {
            text-decoration: none;
        }

        .product-card h3 {
            font-size: 18px;
            margin: 15px 0;
            color: #333;
            min-height: 44px;
        }

        .product-card p {
            font-size: 20px;
            font-weight: 600;
            color: #d32f2f;
            margin: 15px 0;
        }

        .product-card form {
            margin-top: auto;
            width: 100%;
        }

        .product-card button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: auto;
            width: 100%;
            max-width: 200px;
        }

        .product-card button:hover {
            background-color: #b71c1c;
            transform: translateY(-2px);
        }

        .pagination {
            text-align: center;
            margin: 40px 0;
        }

        .pagination button {
            background-color: white;
            color: #d32f2f;
            border: 2px solid #d32f2f;
            padding: 10px 20px;
            margin: 0 5px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .pagination button.active {
            background-color: #d32f2f;
            color: white;
        }

        .pagination button:hover {
            background-color: #d32f2f;
            color: white;
            transform: translateY(-2px);
        }

        .cta {
            margin: 60px auto;
            max-width: 1400px;
            text-align: left;
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('uploads/cta.png');
            background-size: cover;
            color: white;
            padding: 80px 40px;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }

        .cta h2 {
            font-size: 42px;
            margin-bottom: 20px;
            font-weight: 800;
        }

        .cta p {
            font-size: 20px;
            margin-bottom: 30px;
            max-width: 600px;
            line-height: 1.6;
        }

        .cta button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 15px 35px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(211, 47, 47, 0.3);
        }

        .cta button:hover {
            background-color: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(211, 47, 47, 0.4);
        }

        .popup {
            position: fixed;
            top: 55px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            opacity: 0;
            transition: opacity 0.5s ease;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .product-card .stock-info {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
        }

        .product-card .stock-info:before {
            content: '•';
            color: #4CAF50;
            margin-right: 5px;
        }

        .rating {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin: 10px 0;
        }

        .rating i {
            font-size: 16px;
        }

        .rating-count {
            color: #666;
            font-size: 14px;
            margin-left: 5px;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 36px;
                margin-left: 20px;
            }

            .hero p {
                margin-left: 20px;
                font-size: 18px;
            }

            .hero button {
                margin-left: 20px;
            }

            .products {
                padding: 20px;
            }

            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }

            .cta {
                margin: 40px 20px;
                padding: 40px 20px;
            }

            .cta h2 {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
    <section class="hero">
        <h1>Get all your PC parts in one place</h1>
        <p>Find everything you need to build or upgrade your PC in one place.</p> 
        <p>From motherboards to graphics cards, we've got you covered with </p>
        <p>easy browsing and great prices.</p>
        <button onclick="document.getElementById('products').scrollIntoView({ behavior: 'smooth' });">Shop now!</button>
    </section>

    <section class="products" id="products">
        <!-- Filter Form -->
        <form method="GET" action="">
        <div class="filters">
            <div class="filter-buttons">
                <button type="submit" name="filter" value="new" <?php echo $filter == 'new' ? 'class="active"' : ''; ?>>New</button>
                <button type="submit" name="filter" value="price_asc" <?php echo $filter == 'price_asc' ? 'class="active"' : ''; ?>>Price ascending</button>
                <button type="submit" name="filter" value="price_desc" <?php echo $filter == 'price_desc' ? 'class="active"' : ''; ?>>Price descending</button>
                <button type="submit" name="filter" value="rating" <?php echo $filter == 'rating' ? 'class="active"' : ''; ?>>Rating</button>
            </div>
            <div class="category-filter">
                <select name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php
                    $cat_sql = "SELECT * FROM categories ORDER BY CAT_NAME";
                    $cat_result = $conn->query($cat_sql);
                    while($cat = $cat_result->fetch_assoc()) {
                        $selected = isset($_GET['category']) && $_GET['category'] == $cat['CAT_NAME'] ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($cat['CAT_NAME']) . "' $selected>" . 
                             htmlspecialchars($cat['CAT_NAME']) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        </form>

        <div class="product-grid" >
            <?php
            if ($result->num_rows > 0) {
                // Output data of each row
                while($row = $result->fetch_assoc()) {
                    echo '<div class="product-card" id="product-' . $row["PK_PRODUCT_ID"] . '">';
                    echo '<a href="viewdetail.php?product_id=' . $row["PK_PRODUCT_ID"] . '">';
                    echo '<img src="uploads/' . $row["IMAGE"] . '" alt="' . $row["PROD_NAME"] . '">';
                    echo '<h3>' . $row["PROD_NAME"] . '</h3>';
                    echo '</a>';
                    echo '<p>₱' . number_format($row["PRICE"], 2) . '</p>';
                    echo '<form method="POST" action="add_to_cart.php">';
                    echo '<input type="hidden" name="product_id" value="' . $row["PK_PRODUCT_ID"] . '">';
                    echo '<input type="hidden" name="product_name" value="' . $row["PROD_NAME"] . '">';
                    echo '<input type="hidden" name="product_price" value="' . $row["PRICE"] . '">';
                    echo '<button type="submit">Add to cart</button>';
                    echo '</form>';
                    echo '</div>';
                }            
            }
             else {
                echo "<p>No products available.</p>";
            }
            ?>
        </div>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?page=<?php echo ($page-1); ?>&filter=<?php echo $filter; ?>&category=<?php echo $category; ?>">
                    <button>&laquo;</button>
                </a>
            <?php endif; ?>
            
            <?php
            // Show up to 5 page numbers
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $start_page + 4);
            
            for($i = $start_page; $i <= $end_page; $i++): ?>
                <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&category=<?php echo $category; ?>">
                    <button <?php echo ($i == $page) ? 'class="active"' : ''; ?>><?php echo $i; ?></button>
                </a>
            <?php endfor; ?>
            
            <?php if($page < $total_pages): ?>
                <a href="?page=<?php echo ($page+1); ?>&filter=<?php echo $filter; ?>&category=<?php echo $category; ?>">
                    <button> &raquo;</button>
                </a>
            <?php endif; ?>
        </div>
    </section>

    <section class="cta">
        <h2>Enhance your PC performance!</h2>
        <p>Enhance your PC performance with the perfect components. Shop now and experience the best deals.</p>
        <button onclick="window.location.href='index.php'">Place Order</button>
    </section>

    <?php include 'footer.php'; ?>

</body>
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

window.onload = function() {
    <?php if (isset($_GET['message'])): ?>
        showPopup("<?php echo htmlspecialchars($_GET['message']); ?>");
    <?php endif; ?>
};


</script>
</html>
<?php
$conn->close();
?>