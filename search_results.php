<?php
session_start();
include 'db_connect.php';

// Get search query
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';

// Pagination settings
$items_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Base query for products
$sql = "SELECT p.*, c.CAT_NAME 
        FROM products p 
        LEFT JOIN categories c ON p.FK1_CATEGORY_ID = c.PK_CATEGORY_ID
        WHERE p.QTY > 0 AND (
            p.PROD_NAME LIKE ? OR 
            p.PROD_DESC LIKE ? OR 
            p.PROD_SPECS LIKE ? OR
            c.CAT_NAME LIKE ?
        )";

// Get total count for pagination
$count_sql = str_replace("p.*, c.CAT_NAME", "COUNT(*) as total", $sql);
$count_stmt = $conn->prepare($count_sql);
$search_param = "%$search_query%";
$count_stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
$count_stmt->execute();
$total_items = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// Add pagination to main query
$sql .= " ORDER BY p.CREATED_AT DESC LIMIT ? OFFSET ?";

// Prepare and execute the main query
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssii", $search_param, $search_param, $search_param, $search_param, $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Compucore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .search-results {
            max-width: 1400px;
            margin: 40px auto;
            padding: 20px;
        }

        .search-header {
            margin-bottom: 30px;
        }

        .search-header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .search-header p {
            color: #666;
            margin: 0;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
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

        .product-card button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: auto;
        }

        .product-card button:hover {
            background-color: #b71c1c;
            transform: translateY(-2px);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }

        .pagination button {
            background-color: white;
            border: 2px solid #d32f2f;
            color: #d32f2f;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination button:hover,
        .pagination button.active {
            background-color: #d32f2f;
            color: white;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .no-results h2 {
            color: #333;
            margin-bottom: 15px;
        }

        .no-results p {
            color: #666;
            margin-bottom: 20px;
        }

        .no-results a {
            display: inline-block;
            background-color: #d32f2f;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .no-results a:hover {
            background-color: #b71c1c;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="search-results">
        <div class="search-header">
            <h1>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h1>
            <p>Found <?php echo $total_items; ?> results</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="product-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <a href="viewdetail.php?product_id=<?php echo $row['PK_PRODUCT_ID']; ?>">
                            <img src="uploads/<?php echo htmlspecialchars($row['IMAGE']); ?>" 
                                 alt="<?php echo htmlspecialchars($row['PROD_NAME']); ?>">
                            <h3><?php echo htmlspecialchars($row['PROD_NAME']); ?></h3>
                        </a>
                        <p>₱<?php echo number_format($row['PRICE'], 2); ?></p>
                        <form method="POST" action="add_to_cart.php">
                            <input type="hidden" name="product_id" value="<?php echo $row['PK_PRODUCT_ID']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['PROD_NAME']); ?>">
                            <input type="hidden" name="product_price" value="<?php echo $row['PRICE']; ?>">
                            <button type="submit">Add to Cart</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?search_query=<?php echo urlencode($search_query); ?>&page=<?php echo ($page-1); ?>">
                            <button>&laquo; Previous</button>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $start_page + 4);
                    
                    for($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="?search_query=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>">
                            <button <?php echo ($i == $page) ? 'class="active"' : ''; ?>><?php echo $i; ?></button>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <a href="?search_query=<?php echo urlencode($search_query); ?>&page=<?php echo ($page+1); ?>">
                            <button>Next &raquo;</button>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <h2>No products found</h2>
                <p>We couldn't find any products matching your search.</p>
                <a href="index.php">Return to Homepage</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 