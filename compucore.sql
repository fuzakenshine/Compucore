-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2025 at 07:36 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `compucore`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `customer_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `product_id`, `product_name`, `product_price`, `quantity`, `customer_id`, `created_at`) VALUES
(2, 3, 'DRRM5 RAM', 1200.00, 1, 1, '2025-04-18 15:28:26'),
(3, 1, 'asda', 123123.00, 2, 1, '2025-04-18 15:28:26'),
(8, 4, 'White GPU', 1200.00, 1, 1, '2025-04-18 15:28:26'),
(9, 9, 'Hard Casing White Astro 2025', 10000.00, 1, 1, '2025-04-18 15:28:26'),
(10, 5, 'mOBO', 1000.00, 1, 1, '2025-04-18 15:28:26'),
(11, 12, 'RGBiot GAMING HARD CASE', 2000.00, 2, 1, '2025-04-18 16:57:32'),
(14, 11, 'RTX 3080 GRAPHIC CARD', 45000.00, 3, 1, '2025-04-18 16:56:51'),
(16, 7, 'Mouse Matte Black', 1000.00, 2, 1, '2025-04-18 16:54:47'),
(17, 10, 'MSI Router', 12000.00, 4, 1, '2025-04-18 17:33:27'),
(18, 13, 'ROG Mobo 2030M', 10000.00, 2, 1, '2025-04-18 17:01:14'),
(19, 18, 'GEFORCE GTX ', 30000.00, 1, 1, '2025-04-18 17:33:40');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `PK_CATEGORY_ID` int(11) NOT NULL,
  `CAT_NAME` varchar(255) NOT NULL,
  `CAT_DESC` varchar(255) NOT NULL,
  `CREATED_AT` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`PK_CATEGORY_ID`, `CAT_NAME`, `CAT_DESC`, `CREATED_AT`) VALUES
(1, 'Monitors', 'Display devices for computers', '2025-04-16 23:56:48'),
(2, 'Graphics Cards', 'Hardware for rendering images', '2025-04-16 23:56:48'),
(3, 'Motherboards', 'Main circuit boards for computers', '2025-04-16 23:56:48'),
(4, 'Processors', 'Central processing units for computing tasks', '2025-04-18 23:53:18'),
(5, 'RAM', 'Volatile memory for temporary data storage', '2025-04-18 23:53:18'),
(6, 'Storage Drives', 'Permanent storage devices like SSDs and HDDs', '2025-04-18 23:53:18'),
(7, 'Power Supply Units', 'Provide power to all PC components', '2025-04-18 23:53:18'),
(8, 'Computer Cases', 'Enclosures for housing computer parts', '2025-04-18 23:53:18'),
(9, 'Cooling Systems', 'Solutions for dissipating heat', '2025-04-18 23:53:18'),
(10, 'Sound Cards', 'Enhance or provide audio input/output capabilities', '2025-04-18 23:53:18'),
(11, 'Network Cards', 'Enable network connectivity', '2025-04-18 23:53:18'),
(12, 'Optical Drives', 'Read/write CDs, DVDs, Blu-rays', '2025-04-18 23:53:18'),
(13, 'Keyboards', 'Input devices for typing', '2025-04-18 23:53:18'),
(14, 'Mice', 'Pointing input devices', '2025-04-18 23:53:18'),
(15, 'Speakers', 'Output devices for audio', '2025-04-18 23:53:18'),
(16, 'Webcams', 'Cameras for video capture and conferencing', '2025-04-18 23:53:18'),
(17, 'Printers', 'Devices for producing physical copies of digital content', '2025-04-18 23:53:18'),
(18, 'UPS', 'Backup power sources during outages', '2025-04-18 23:53:18'),
(19, 'Fans', 'Cooling fans for airflow inside cases', '2025-04-18 23:53:18'),
(20, 'Cables & Adapters', 'Connectivity and conversion components', '2025-04-18 23:53:18'),
(21, 'Expansion Cards', 'Additional functionalities like USB, Firewire, etc.', '2025-04-18 23:53:18'),
(22, 'Capture Cards', 'Record video input from external sources', '2025-04-18 23:53:18'),
(23, 'VR Headsets', 'Virtual reality devices', '2025-04-18 23:53:18'),
(24, 'Docking Stations', 'Multi-port hubs for laptops or tablets', '2025-04-18 23:53:18'),
(25, 'External Storage', 'Portable drives like USB HDDs or SSDs', '2025-04-18 23:53:18');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `PK_CUSTOMER_ID` int(11) NOT NULL,
  `L_NAME` varchar(30) NOT NULL,
  `F_NAME` varchar(30) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `PASSWORD_HASH` varchar(255) NOT NULL,
  `CUSTOMER_ADDRESS` varchar(255) NOT NULL,
  `PHONE_NUM` char(15) NOT NULL,
  `CREATED_AT` datetime NOT NULL DEFAULT current_timestamp(),
  `UPDATE_AT` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`PK_CUSTOMER_ID`, `L_NAME`, `F_NAME`, `EMAIL`, `PASSWORD_HASH`, `CUSTOMER_ADDRESS`, `PHONE_NUM`, `CREATED_AT`, `UPDATE_AT`) VALUES
(1, 'TINGA', 'JOHN RAY', 'jrtjohnray@gmail.com', '$2y$10$lvIdywOwKO9s0elw8Rx36e/ymyBKustce1nXvEre89nohEIts3qyi', 'OPRRA VILLAREMEDIOS KALUNASAN CEBU CITY, 6000', '09991029087', '2025-04-16 22:33:19', '2025-04-16 22:33:19'),
(2, 'skibidi', 'Damien', 'damskie@gmail.com', '$2y$10$N3S5KrSGqM3wpY1PLZmZ8.OSpdaCHP5PalBldxYxhhJ02a6nGqI06', 'Vraman', '098263636482', '2025-04-18 02:17:00', '2025-04-18 02:17:00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `PK_ORDER_ID` int(11) NOT NULL,
  `FK1_CUSTOMER_ID` int(11) NOT NULL,
  `FK2_PAYMENT_ID` int(11) NOT NULL,
  `FK3_USER_ID` int(11) NOT NULL,
  `TOTAL_PRICE` decimal(10,2) NOT NULL,
  `STATUS` char(15) NOT NULL,
  `ORDER_DATE` datetime NOT NULL DEFAULT current_timestamp(),
  `LINE_TOTAL` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_detail`
--

CREATE TABLE `order_detail` (
  `PK_ORDER_DETAIL_ID` int(11) NOT NULL,
  `FK1_PRODUCT_ID` int(11) NOT NULL,
  `FK2_ORDER_ID` int(11) NOT NULL,
  `QTY` int(11) NOT NULL,
  `PRICE` decimal(10,2) NOT NULL,
  `CREATED_AT` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `PK_PAYMENT_ID` int(11) NOT NULL,
  `PAYMENT_METHOD` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `PK_PRODUCT_ID` int(11) NOT NULL,
  `FK1_CATEGORY_ID` int(11) NOT NULL,
  `FK2_SUPPLIER_ID` int(11) NOT NULL,
  `PROD_NAME` varchar(255) NOT NULL,
  `PROD_DESC` varchar(255) NOT NULL,
  `PROD_SPECS` text DEFAULT NULL,
  `PRICE` decimal(10,2) NOT NULL,
  `QTY` int(11) NOT NULL DEFAULT 0,
  `IMAGE` varchar(255) NOT NULL,
  `CREATED_AT` datetime NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`PK_PRODUCT_ID`, `FK1_CATEGORY_ID`, `FK2_SUPPLIER_ID`, `PROD_NAME`, `PROD_DESC`, `PROD_SPECS`, `PRICE`, `QTY`, `IMAGE`, `CREATED_AT`, `UPDATED_AT`) VALUES
(1, 2, 1, 'asda', '1', NULL, 123123.00, 1, 'p5.png', '2025-04-17 00:03:03', '2025-04-17 00:03:03'),
(2, 1, 1, 'ASUS', 'The best of the Best ', NULL, 1200.00, 3, 'p3.png', '2025-04-17 00:32:56', '2025-04-17 00:32:56'),
(3, 2, 1, 'DRRM5 RAM', 'RAM FOR BETTER EXPERIENCE', NULL, 1200.00, 5, 'p6.png', '2025-04-17 22:03:18', '2025-04-17 22:03:18'),
(4, 2, 1, 'White GPU', 'WHITE ', NULL, 1200.00, 5, 'p1.png', '2025-04-17 22:46:11', '2025-04-17 22:46:11'),
(5, 3, 1, 'mOBO', 'EWEW', NULL, 1000.00, 3, 'p4.png', '2025-04-17 23:02:01', '2025-04-17 23:02:01'),
(6, 1, 1, 'Mousepad', 'Gaming mousepad', NULL, 200.00, 20, 'Flowy Waves Desk Mat, XXL Gaming Mouse Pad, Blue Water Mousepad, Beautiful Nature Desk Mat.jpg', '2025-04-18 02:07:15', '2025-04-18 02:07:15'),
(7, 1, 1, 'Mouse Matte Black', 'Good for Office Works and light works', NULL, 1000.00, 20, 'Amazon_com_ Dapesuom Small Mouse Pad 6 x 8 Inch….jpg', '2025-04-18 02:08:00', '2025-04-18 02:08:00'),
(8, 1, 1, 'Black Sticky Mousepad', 'Black mousepad for better gaming', NULL, 450.00, 20, 'DIGSOM Mouse Pad.jpg', '2025-04-18 02:08:40', '2025-04-18 02:08:40'),
(9, 1, 2, 'Hard Casing White Astro 2025', 'For better', NULL, 10000.00, 10, 'Transform your product into a captivating visual experience with 3D product animation!.jpg', '2025-04-18 02:09:35', '2025-04-18 02:09:35'),
(10, 1, 2, 'MSI Router', 'For better Wifi Experience', NULL, 12000.00, 20, '977dcc0b-90d6-4ee3-be12-05ac3f3d73be.jpg', '2025-04-18 02:10:13', '2025-04-18 02:10:13'),
(11, 2, 2, 'RTX 3080 GRAPHIC CARD', 'GOOD FOR GAMING EXPERIENCE', NULL, 45000.00, 10, 'Video game graphics are a ticking time bomb — the industry needs to focus on art over tech.jpg', '2025-04-18 02:10:51', '2025-04-18 02:10:51'),
(12, 1, 2, 'RGBiot GAMING HARD CASE', '-Good for eyes', NULL, 2000.00, 30, 'Custom build Gaming PC.jpg', '2025-04-18 02:11:47', '2025-04-18 02:11:47'),
(13, 3, 2, 'ROG Mobo 2030M', 'Good for gaming', NULL, 10000.00, 10, 'ROG STRIX Z490-E GAMING _ Motherboards _ ROG Global.jpg', '2025-04-18 02:12:39', '2025-04-18 02:12:39'),
(14, 5, 0, 'HyperX RAM 16GB', '-RGB HYPERX 16GB RAM', '-BLACK\r\n-PURPLE\r\n-RED\r\n-WWHITE', 5000.00, 10, 'The best RAM of 2024_ top memory for your PC.jpg', '2025-04-19 00:29:45', '2025-04-19 00:29:45'),
(15, 5, 0, 'Corsair coolant fan', 'Best for gaming 123', '-white\r\n-black\r\n-pink\r\n-blue', 20000.00, 10, 'Corsair Dominator Platinum RGB Series.jpg', '2025-04-19 01:09:28', '2025-04-19 01:09:28'),
(16, 13, 2, 'Keyboard GYA', 'Best for gaming', '-keyboard\r\n-blue\r\n-white\r\n-rgb', 2000.00, 30, 'gaming keyboard.jpg', '2025-04-19 01:10:16', '2025-04-19 01:10:16'),
(17, 2, 1, 'RTX 3080 ', 'Best for gaming', '-white\r\n-with fan\r\n', 45000.00, 10, 'white graphic card.jpg', '2025-04-19 01:11:04', '2025-04-19 01:11:04'),
(18, 2, 0, 'GEFORCE GTX ', 'Best for gaming', '-green\r\n-gray\r\n-black', 30000.00, 10, 'db839bf5-d42b-4a59-b48d-6f8f5f3c31fc.jpg', '2025-04-19 01:12:52', '2025-04-19 01:12:52'),
(19, 19, 1, 'Coolant fan', 'Best for coolants', '-simple\r\n-neat', 2000.00, 10, 'SST-AR04.jpg', '2025-04-19 01:13:27', '2025-04-19 01:13:27');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `PK_REVIEW_ID` int(11) NOT NULL,
  `FK1_CUSTOMER_ID` int(11) NOT NULL,
  `FK2_PRODUCT_ID` int(11) NOT NULL,
  `FK3_ORDER_ID` int(11) NOT NULL,
  `RATING` int(11) NOT NULL,
  `COMMENT` text DEFAULT NULL,
  `CREATED_AT` datetime DEFAULT current_timestamp(),
  `IMAGE` varchar(255) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `PK_SUPPLIER_ID` int(11) NOT NULL,
  `FK_USER_ID` int(11) NOT NULL,
  `S_LNAME` varchar(30) NOT NULL,
  `S_FNAME` varchar(30) NOT NULL,
  `PHONE_NUM` char(15) NOT NULL,
  `COMPANY_NAME` varchar(255) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `SUPPLIER_ADDRESS` varchar(255) NOT NULL,
  `SUPPLIER_IMAGE` varchar(255) DEFAULT NULL,
  `CREATE_AT` datetime NOT NULL DEFAULT current_timestamp(),
  `UPDATE_AT` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`PK_SUPPLIER_ID`, `FK_USER_ID`, `S_LNAME`, `S_FNAME`, `PHONE_NUM`, `COMPANY_NAME`, `EMAIL`, `SUPPLIER_ADDRESS`, `SUPPLIER_IMAGE`, `CREATE_AT`, `UPDATE_AT`) VALUES
(0, 1, 'Patino', 'Rafael', '091234567890', 'Bakal TT Corp.', 'Cthulu@gmail.com', 'avocado St Mamba-ling', '351453175_1191126874899419_117306819684368067_n.jpg', '2025-04-18 03:03:57', '2025-04-18 03:03:57'),
(1, 1, 'Doe', 'John', '1234567890', 'Tech Supplies Co.', 'john.doe@techsupplies.com', '123 Tech Street, Tech City', NULL, '2025-04-16 23:58:54', '2025-04-16 23:58:54'),
(2, 2, 'Smith', 'Jane', '0987654321', 'Hardware Hub', 'jane.smith@hardwarehub.com', '456 Hardware Ave, Hardware Town', NULL, '2025-04-16 23:58:54', '2025-04-16 23:58:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `PK_USER_ID` int(11) NOT NULL,
  `L_NAME` varchar(30) NOT NULL,
  `F_NAME` varchar(30) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `PASSWORD_HASH` varchar(255) NOT NULL,
  `ADDRESS` varchar(255) NOT NULL,
  `PHONE_NUM` char(15) NOT NULL,
  `CREATED_AT` datetime NOT NULL DEFAULT current_timestamp(),
  `UPDATE_AT` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`PK_USER_ID`, `L_NAME`, `F_NAME`, `EMAIL`, `PASSWORD_HASH`, `ADDRESS`, `PHONE_NUM`, `CREATED_AT`, `UPDATE_AT`) VALUES
(1, 'Doe', 'John', 'john.doe@example.com', 'hashedpassword1', '123 Main St', '1234567890', '2025-04-16 23:58:41', '2025-04-16 23:58:41'),
(2, 'Smith', 'Jane', 'jane.smith@example.com', 'hashedpassword2', '456 Elm St', '0987654321', '2025-04-16 23:58:41', '2025-04-16 23:58:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`PK_CATEGORY_ID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`PK_CUSTOMER_ID`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`PK_ORDER_ID`),
  ADD KEY `FK1_CUSTOMER_ID` (`FK1_CUSTOMER_ID`),
  ADD KEY `FK2_PAYMENT_ID` (`FK2_PAYMENT_ID`),
  ADD KEY `FK3_USER_ID` (`FK3_USER_ID`);

--
-- Indexes for table `order_detail`
--
ALTER TABLE `order_detail`
  ADD PRIMARY KEY (`PK_ORDER_DETAIL_ID`),
  ADD KEY `FK1_PRODUCT_ID` (`FK1_PRODUCT_ID`),
  ADD KEY `FK2_ORDER_ID` (`FK2_ORDER_ID`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`PK_PAYMENT_ID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`PK_PRODUCT_ID`),
  ADD KEY `FK1_CATEGORY_ID` (`FK1_CATEGORY_ID`),
  ADD KEY `FK2_SUPPLIER_ID` (`FK2_SUPPLIER_ID`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`PK_REVIEW_ID`),
  ADD KEY `FK1_CUSTOMER_ID` (`FK1_CUSTOMER_ID`),
  ADD KEY `FK2_PRODUCT_ID` (`FK2_PRODUCT_ID`),
  ADD KEY `FK3_ORDER_ID` (`FK3_ORDER_ID`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`PK_SUPPLIER_ID`),
  ADD KEY `FK_USER_ID` (`FK_USER_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`PK_USER_ID`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `PK_CUSTOMER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `PK_PRODUCT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `PK_REVIEW_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`PK_PRODUCT_ID`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`PK_CUSTOMER_ID`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`FK1_CUSTOMER_ID`) REFERENCES `customer` (`PK_CUSTOMER_ID`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`FK2_PAYMENT_ID`) REFERENCES `payments` (`PK_PAYMENT_ID`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`FK3_USER_ID`) REFERENCES `users` (`PK_USER_ID`);

--
-- Constraints for table `order_detail`
--
ALTER TABLE `order_detail`
  ADD CONSTRAINT `order_detail_ibfk_1` FOREIGN KEY (`FK1_PRODUCT_ID`) REFERENCES `products` (`PK_PRODUCT_ID`),
  ADD CONSTRAINT `order_detail_ibfk_2` FOREIGN KEY (`FK2_ORDER_ID`) REFERENCES `orders` (`PK_ORDER_ID`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`FK1_CATEGORY_ID`) REFERENCES `categories` (`PK_CATEGORY_ID`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`FK2_SUPPLIER_ID`) REFERENCES `supplier` (`PK_SUPPLIER_ID`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`FK1_CUSTOMER_ID`) REFERENCES `customer` (`PK_CUSTOMER_ID`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`FK2_PRODUCT_ID`) REFERENCES `products` (`PK_PRODUCT_ID`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`FK3_ORDER_ID`) REFERENCES `orders` (`PK_ORDER_ID`);

--
-- Constraints for table `supplier`
--
ALTER TABLE `supplier`
  ADD CONSTRAINT `supplier_ibfk_1` FOREIGN KEY (`FK_USER_ID`) REFERENCES `users` (`PK_USER_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
