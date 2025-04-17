<?php
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

// Fetch products from the database
$sql = "SELECT PK_PRODUCT_ID, PROD_NAME, PRICE, IMAGE FROM products";
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
        .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: transparent; /* Transparent by default */
        transition: background-color 0.3s ease;
        color: white;
        padding: 20px 25px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
    }

    .header.scrolled {
        background-color: #d32f2f; /* Red on scroll */
    }
  
        .header .logo {
            font-size: 24px;
            font-weight: bold;
        }
        .header .search-bar {
            display: flex;
            align-items: center;
            position: relative;
            margin-left: 80px;
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
        .auth-buttons {
    display: flex;
    align-items: center;
    gap: 10px;
}

.auth-buttons a {
    padding: 6px 14px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;
}

.login-btn {
    background-color: transparent;
    color: white;
    border: 2px solid white;
    transition: background-color 0.3s, color 0.3s;
}

.login-btn:hover {
    background-color: white;
    color: #d32f2f;
}

.signup-btn {
    background-color: white;
    color: #d32f2f;
    transition: background-color 0.3s, color 0.3s;
}

.signup-btn:hover {
    background-color: #fbe9e7;
    color: #d32f2f;
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

        .product-grid :hover {
            transition: transform 0.3s ease;
            background-color: rgb(183, 179, 179);
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
        .cta {
            margin-left: 40px;
            margin-right: 40px;
            text-align: center;
            background-image: url('uploads/cta.png');
            border-radius: 0px;
            background-size: cover;
            color: white;
            padding: 50px 20px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .cta h2 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .cta p {
            font-size: 18px;
            padding: 20px;
        }

        .cta button {
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
    </style>
</head>
<body>
<header class="header">
    <div class="logo">CompuCore</div>
    
    <div class="search-bar">
        <input type="text" placeholder="Search">
        <button><i class="fas fa-search"></i></button>
    </div>
    
    <div class="auth-buttons">
        <a href="login.php" class="login-btn">Login</a>
        <a href="register.php" class="signup-btn">Sign Up</a>
    </div>
</header>


    <section class="hero">
        <h1>Get all your PC parts in one place</h1>
        <p>Find everything you need to build or upgrade your PC in one place.</p>
        <p>From motherboards to graphics cards, we've got you covered with </p>
        <p>easy browsing and great prices.</p>
        <button onclick="window.location.href='login.php'">Shop now!</button>
    </section>

    <section class="products">
        <div class="filters">
            <button onclick="window.location.href='login.php'">New</button>
            <button onclick="window.location.href='login.php'">Price ascending</button>
            <button onclick="window.location.href='login.php'">Price descending</button>
            <button onclick="window.location.href='login.php'">Rating</button>
        </div>
        <div class="product-grid">
            <?php
            if ($result->num_rows > 0) {
                // Output data of each row
                while($row = $result->fetch_assoc()) {
                    echo '<div class="product-card">';
                    echo '<img src="uploads/' . $row["IMAGE"] . '" alt="' . $row["PROD_NAME"] . '">';
                    echo '<h3>' . $row["PROD_NAME"] . '</h3>';
                    echo '<p>₱' . number_format($row["PRICE"], 2) . '</p>';
                    echo '<form method="POST" action="login.php">';
                    echo '<input type="hidden" name="product_id" value="' . $row["PK_PRODUCT_ID"] . '">';
                    echo '<input type="hidden" name="product_name" value="' . $row["PROD_NAME"] . '">';
                    echo '<input type="hidden" name="product_price" value="' . $row["PRICE"] . '">';
                    echo '<button type="submit" action="login.php">Add to cart</button>';
                    echo '</form>';
                    echo '</div>';
                }
            } else {
                echo "<p>No products available.</p>";
            }
            ?>
        </div>
        <div class="pagination">
            <button onclick="window.location.href='login.php'">&lt;</button>
            <button onclick="window.location.href='login.php'">1</button>
            <button onclick="window.location.href='login.php'">2</button>
            <button onclick="window.location.href='login.php'">&gt;</button>
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
<script>    
        window.addEventListener('scroll', function () {
        const header = document.querySelector('.header');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
</script>

</body>
</html>
<?php
$conn->close();
?> 