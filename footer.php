<?php
// Get current year for copyright
$current_year = date('Y');
?>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>CompuCore</h3>
            <p>Your one-stop shop for all PC parts and accessories.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="profile.php">My Profile</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Us</h3>
            <p><i class="fas fa-phone"></i> +63 999 123 4567</p>
            <p><i class="fas fa-envelope"></i> support@compucore.com</p>
            <p><i class="fas fa-map-marker-alt"></i> Cebu City, Philippines</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo $current_year; ?> CompuCore. All rights reserved.</p>
    </div>
</footer>

<style>
.footer {
    background-color: #333;
    color: white;
    padding: 40px 0 20px;
    margin-top: 50px;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    padding: 0 20px;
}

.footer-section h3 {
    color: #d32f2f;
    margin-bottom: 15px;
    font-size: 18px;
}

.footer-section p {
    margin: 10px 0;
    color: #ccc;
    line-height: 1.6;
}

.footer-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-section ul li {
    margin-bottom: 10px;
}

.footer-section ul li a {
    color: #ccc;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-section ul li a:hover {
    color: #d32f2f;
}

.footer-section i {
    margin-right: 10px;
    color: #d32f2f;
}

.footer-bottom {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #444;
}

.footer-bottom p {
    color: #ccc;
    margin: 0;
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .footer-section {
        margin-bottom: 30px;
    }
}
</style> 