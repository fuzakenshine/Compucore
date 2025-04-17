<?php
session_start();
include 'db_connect.php'; // Include the database connector

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
$filter = isset($_POST['filter']) ? $_POST['filter'] : '';

// Update SQL query based on filter
switch ($filter) {
    case 'new':
        $sql = "SELECT PK_PRODUCT_ID, PROD_NAME, PRICE, IMAGE FROM products ORDER BY CREATED_AT DESC";
        break;
    case 'price_asc':
        $sql = "SELECT PK_PRODUCT_ID, PROD_NAME, PRICE, IMAGE FROM products ORDER BY PRICE ASC";
        break;
    case 'price_desc':
        $sql = "SELECT PK_PRODUCT_ID, PROD_NAME, PRICE, IMAGE FROM products ORDER BY PRICE DESC";
        break;
    case 'rating':
        // Assuming there's a RATING column
        $sql = "SELECT PK_PRODUCT_ID, PROD_NAME, PRICE, IMAGE FROM products ORDER BY RATING DESC";
        break;
    default:
        $sql = "SELECT PK_PRODUCT_ID, PROD_NAME, PRICE, IMAGE FROM products";
        break;
}

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
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


.hero {
    text-align: left;
    background: url('uploads/hero1.jpg') no-repeat center center/cover;
    height: 550px;
    background-repeat: no-repeat;
    color: white;
    padding: 50px 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
    position: relative;
}

.hero h1 {
    font-size: 38px;
    margin-bottom: 10px;
    margin-left: 40px;
    margin-top: 170px;
    font-weight: bold;
}

.hero p {
    font-size: 19px;
    margin-bottom: 20px;
    margin-left: 40px;
}


.hero button {
    background-color: #d32f2f;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    margin-left: 40px;
    border-radius: 10px;
}

.products {
    padding: 20px;
    margin-left: 40px;
    margin-right: 40px;
}

.filters button {
    margin-right: 10px;
    padding: 5px 10px;
    border: 1px solid #ccc;
    background-color: white;
    cursor: pointer;
}

.product-grid :hover
{
    transition: transform 0.3s ease;
    background-color:rgb(183, 179, 179);
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
    align-items: stretch;
}


.product-card {
    border: 1px solid #ccc;
    padding: 20px;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 90%;
    background-color: white;
    border-radius: 8px;
}
.product-card h3 {
    font-size: 16px;
    margin: 10px 0;
    min-height: 40px;
}

.product-card p {
    font-size: 16px;
    font-weight: bold;
    margin: 10px 0;
}

.product-card form {
    margin-top: auto;
}

.product-card button {
    margin-top: 10px;
    width: 100%;
    padding: 8px;
    border-radius: 5px;
}


.product-card img {
    max-width: 100%;
    height: 150px;
    object-fit: contain;
    margin-bottom: 10px;
}


.product-card button {
    background-color: #d32f2f;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
}

.product-card button:hover {
    background-color: #b71c1c;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
}
.pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin: 0 5px;
            border-radius: 5px;
        }
        .pagination button:hover {
            background-color: #b71c1c;
}

.cta {
    margin-left: 40px;
    margin-right: 40px;
    text-align: left;
    background-image: url('uploads/cta.png');
    background-size: cover;
    color: white;
    padding: 50px 20px;
    margin-top: 20px;
    margin-bottom: 20px;
}

.cta h2 {
    font-size: 36px;
    margin-bottom: 10px;
    margin-left: 40px;
}

.cta p {
    font-size: 18px;
    margin-left: 40px;
}

.cta button {
    margin-left: 40px; /* remove the previous left margin */
    background-color: #d32f2f;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
}


.footer {
    text-align: center;
    background-color: #333;
    color: white;
    padding: 10px 0;
}
a {
    text-decoration: none;
    color: black;
}
html {
    scroll-behavior: smooth;
}

.popup {
    position: fixed;
    top: 50px;
    right: 20px;
    background-color: #4CAF50;
    color: white;
    padding: 15px;
    border-radius: 5px;
    opacity: 0;
    transition: opacity 0.5s ease;
    z-index: 1000;
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
        <form method="POST" action="filter">
        <div class="filters">
                <button type="submit" name="filter" value="new">New</button>
                <button type="submit" name="filter" value="price_asc">Price ascending</button>
                <button type="submit" name="filter" value="price_desc">Price descending</button>
                <button type="submit" name="filter" value="rating">Rating</button>
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
            <button>&laquo;</button>
            <button>1</button>
            <button>2</button>
            <button>&raquo;</button>
        </div>
    </section>

    <section class="cta">
        <h2>Enhance your PC performance!</h2>
        <p>Enhance your PC performance with the perfect components. Shop now and experience the best deals.</p>
        <button onclick="window.location.href='login.php'">Place Order</button>
    </section>

    <footer class="footer">
        <p>&copy; 2025 CompuCore. All rights reserved.</p>
    </footer>

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