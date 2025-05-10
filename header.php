<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #d32f2f; /* Red background by default */
            color: white;
            padding: 10px 20px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header.scrolled {
            background-color: #d32f2f; /* Same red color on scroll */
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease;
        }
        
        .header .search-bar input:focus {
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
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
            align-items: center;
            margin-left: 400px;
        }
        
        .header .icons i {
            margin: 0 10px;
            cursor: pointer;
            font-size: 20px;
            color: white;
            transition: transform 0.2s ease;
        }
        
        .header .icons i:hover {
            transform: scale(1.1);
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
            transition: transform 0.2s ease;
        }
        
        .header .burger-menu button:hover {
            transform: scale(1.1);
        }
        
        .header .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
            animation: fadeIn 0.2s ease;
            margin-top: 10px;
        }
        
        .header .dropdown-content a {
            color: #333;
            padding: 12px 20px;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            font-size: 14px;
        }
        
        .header .dropdown-content a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            color: #666;
            font-size: 16px;
        }
        
        .header .dropdown-content a:hover {
            background-color: #f8f9fa;
            color: #d32f2f;
        }
        
        .header .dropdown-content a:hover i {
            color: #d32f2f;
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #eee;
            margin: 8px 0;
        }
        
        .logout-link {
            color: #d32f2f !important;
        }
        
        .logout-link i {
            color: #d32f2f !important;
        }
        
        .header .dropdown-content:before {
            content: '';
            position: absolute;
            top: -8px;
            right: 12px;
            width: 16px;
            height: 16px;
            background-color: white;
            transform: rotate(45deg);
            box-shadow: -2px -2px 2px rgba(0,0,0,0.02);
        }
        
        /* Enhanced notification styles */
        .notification-icon {
            position: relative;
            display: inline-block;
        }
        
        .notification-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background-color: #ff4444;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: transform 0.2s ease;
            min-width: 10px;
            text-align: center;
        }
        
        .notification-icon:hover .notification-badge {
            transform: scale(1.1);
        }
        
        .notification-dropdown {
            display: none;
            position: absolute;
            right: -100px;
            background-color: white;
            min-width: 320px;
            box-shadow: 0px 8px 20px rgba(0,0,0,0.15);
            z-index: 1;
            max-height: 400px;
            overflow-y: auto;
            border-radius: 10px;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            background-color: #f9f9f9;
        }
        
        .notification-header h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        
        .notification-header .mark-all {
            color: #d32f2f;
            font-size: 14px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            position: relative;
            transition: background-color 0.2s ease;
            cursor: pointer;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item.unread {
            background-color: #f6f6f6;
        }
        
        .notification-item.unread:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: #d32f2f;
        }
        
        .notification-item:hover {
            background-color: #f0f0f0;
        }
        
        .notification-content {
            margin-bottom: 5px;
            color: #333;
            font-size: 14px;
        }
        
        .notification-time {
            font-size: 12px;
            color: #888;
            display: flex;
            align-items: center;
        }
        
        .notification-time i {
            font-size: 12px;
            margin-right: 5px;
            color: #aaa;
        }
        
        .notification-icon:hover .notification-dropdown {
            display: block;
        }
        
        .notification-footer {
            padding: 12px;
            text-align: center;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
        }
        
        .notification-footer a {
            color: #d32f2f;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .notification-footer a:hover {
            text-decoration: underline;
        }
        
        .notification-status {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .status-approved {
            background-color: #4CAF50;
        }
        
        .status-rejected {
            background-color: #f44336;
        }
        
        .status-pending {
            background-color: #FF9800;
        }
        
        .icon-container {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
            border: 1px solid #e0e0e0;
        }
        
        .icon-container i {
            color: #333 !important;
            font-size: 16px;
        }
        
        .notification-item-container {
            display: flex;
            align-items: center;
        }
        
        .notification-details {
            flex: 1;
        }
        
        .notification-icon-order {
            color: #d32f2f !important;
        }
        
        .notification-icon-payment {
            color: #4CAF50 !important;
        }
        
        .notification-icon-system {
            color: #2196F3 !important;
        }
        
        .notification-empty {
            padding: 30px 20px;
            text-align: center;
            color: #888;
        }
        
        .notification-empty i {
            font-size: 40px;
            color: #ddd;
            margin-bottom: 10px;
            display: block;
        }

        /* Add this to your existing styles */
        .cart-icon {
            position: relative;
            display: inline-block;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ff4444;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: transform 0.2s ease;
            min-width: 10px;
            text-align: center;
        }

        .cart-icon:hover .cart-badge {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">
            <a href="index.php">
                <img src="uploads/LOGOW.PNG" alt="Compucore Logo" height="30">
            </a>
        </div>
        <div class="search-bar">
            <form action="search_results.php" method="GET">
                <input type="text" name="search_query" placeholder="Search products..." required>
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="icons">
            <div class="notification-icon">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notification-count">0</span>
                <div class="notification-dropdown">
                    <div class="notification-header">
                        <h3>Notifications</h3>
                        <span class="mark-all" id="mark-all-read">Mark all as read</span>
                    </div>
                    <div id="notifications-container">
                        <!-- Notifications will be loaded here -->
                    </div>
                    <div class="notification-footer">
                        <a href="notifications.php">View all notifications</a>
                    </div>
                </div>
            </div>
            <a href="cart.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge" id="cart-count">0</span>
            </a>
            <a href="my_orders.php">
                <i class="fas fa-money-bill"></i> 
            </a>
            <div class="burger-menu" id="burger-menu">
                <button><i class="fas fa-bars"></i></button>
                <div class="dropdown-content">
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <div class="dropdown-divider"></div>
                    <a href="landing.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>

    </header>
    
    <script>
        window.addEventListener('scroll', function () {
            const header = document.querySelector('.header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Notification system
        function updateNotifications() {
            if (!document.querySelector('.notification-icon')) return;
            
            fetch('notifications.php?action=get_notifications')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const container = document.getElementById('notifications-container');
                        const badge = document.getElementById('notification-count');
                        
                        // Update badge count
                        badge.textContent = data.unread_count;
                        badge.style.display = data.unread_count > 0 ? 'block' : 'none';
                        
                        // Clear existing notifications
                        container.innerHTML = '';
                        
                        // Add new notifications
                        if (data.notifications.length === 0) {
                            container.innerHTML = '<div class="notification-empty"><i class="fas fa-bell-slash"></i><p>No notifications</p></div>';
                        } else {
                            data.notifications.forEach(notification => {
                                const notificationElement = document.createElement('div');
                                notificationElement.className = `notification-item ${notification.status === 'unread' ? 'unread' : ''}`;
                                notificationElement.setAttribute('data-id', notification.id);
                                
                                let iconClass = 'fas fa-info-circle notification-icon-system';
                                if (notification.type === 'order') {
                                    iconClass = 'fas fa-shopping-bag notification-icon-order';
                                } else if (notification.type === 'payment') {
                                    iconClass = 'fas fa-money-bill-wave notification-icon-payment';
                                }
                                
                                notificationElement.innerHTML = `
                                    <div class="notification-item-container">
                                        <div class="icon-container">
                                            <i class="${iconClass} notification-icon-order"></i>
                                        </div>
                                        <div class="notification-details">
                                            <div class="notification-content">
                                                ${notification.message}
                                            </div>
                                            <div class="notification-time">
                                                ${formatTimeAgo(notification.created_at)}
                                            </div>
                                        </div>
                                    </div>
                                `;
                                
                                container.appendChild(notificationElement);
                                
                                // Add click handler to mark as read
                                notificationElement.addEventListener('click', function() {
                                    if (this.classList.contains('unread')) {
                                        markNotificationAsRead(this.getAttribute('data-id'));
                                        this.classList.remove('unread');
                                        updateBadgeCount();
                                    }
                                });
                            });
                        }
                    }
                });
        }

        function markNotificationAsRead(id) {
            fetch(`notifications.php?action=mark_read&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateNotifications();
                    }
                });
        }

        function markAllNotificationsAsRead() {
            fetch('notifications.php?action=mark_all_read')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateNotifications();
                    }
                });
        }

        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return 'just now';
            if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
            if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
            return `${Math.floor(diff / 86400)}d ago`;
        }

        // Add event listener for mark all as read button
        document.getElementById('mark-all-read').addEventListener('click', function(e) {
            e.stopPropagation();
            markAllNotificationsAsRead();
        });

        // Update notifications every 30 seconds
        setInterval(updateNotifications, 30000);

        // Initial update
        document.addEventListener('DOMContentLoaded', function() {
            updateNotifications();
        });

        // Add this to the script section of header.php
        document.addEventListener('DOMContentLoaded', function() {
            const burgerMenu = document.getElementById('burger-menu');
            const button = burgerMenu.querySelector('button');
            const dropdown = burgerMenu.querySelector('.dropdown-content');
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!burgerMenu.contains(event.target)) {
                    dropdown.style.display = 'none';
                }
            });

            // Toggle dropdown on button click
            button.addEventListener('click', function(event) {
                event.stopPropagation();
                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            });
        });

        // Add this to your existing script section
        function updateCartCount() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    const cartCount = document.getElementById('cart-count');
                    cartCount.textContent = data.count;
                    cartCount.style.display = data.count > 0 ? 'block' : 'none';
                })
                .catch(error => console.error('Error updating cart count:', error));
        }

        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });

        // Update cart count every 30 seconds
        setInterval(updateCartCount, 30000);
    </script>
</body>
</html>