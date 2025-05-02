-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 02, 2025 at 08:09 PM
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
(24, 36, 'RGB Hard Glass Case', 2000.00, 2, 1, '2025-04-25 19:25:14'),
(25, 38, 'A4TECH BLACK MOUSE', 1000.00, 1, 1, '2025-04-26 01:59:01'),
(26, 36, 'RGB Hard Glass Case', 2000.00, 1, 2, '2025-04-26 02:19:36'),
(30, 38, 'A4TECH BLACK MOUSE', 1000.00, 2, 4, '2025-04-26 09:36:27'),
(45, 23, 'HyperX RAM 16GB', 3000.00, 1, 5, '2025-05-02 17:35:36');

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
(25, 'External Storage', 'Portable drives like USB HDDs or SSDs', '2025-04-18 23:53:18'),
(26, 'Others', 'For anything', '2025-04-26 02:50:42');

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
(2, 'skibidi', 'Damien', 'damskie@gmail.com', '$2y$10$N3S5KrSGqM3wpY1PLZmZ8.OSpdaCHP5PalBldxYxhhJ02a6nGqI06', 'Vraman', '098263636482', '2025-04-18 02:17:00', '2025-04-18 02:17:00'),
(3, 'TINGA', 'JOHNRAY', 'jttinga@email.com', '$2y$10$NkiHMi4wFPMBGIOkH8S.IOQQUJx90YcjjjaTatavgkeCQ6iMrUP36', 'Villa remedios unit 3A', '09991029087', '2025-04-26 16:10:54', '2025-04-26 16:10:54'),
(4, 'test', '567890-', '67890@1', '$2y$10$GzTEh3vpAb5B1tnEFnonaeBt6ujCvoao3YO9Z7aG.E3yzQ1yCgrcK', '123', 'rewq', '2025-04-26 17:35:08', '2025-04-26 17:35:08'),
(5, 'Doe', 'John', 'j.doe@email.com', '$2y$10$SIninVA/fQWFKe0NFcW2dOxo1awT89whuTJe6TXhV4FCDqe/zk.uW', 'New York', '09123456789', '2025-05-01 20:27:27', '2025-05-01 20:27:27');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `PK_NOTIFICATION_ID` int(11) NOT NULL,
  `FK_CUSTOMER_ID` int(11) NOT NULL,
  `MESSAGE` text NOT NULL,
  `TYPE` enum('order','payment','system') NOT NULL,
  `STATUS` enum('unread','read') NOT NULL DEFAULT 'unread',
  `CREATED_AT` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`PK_NOTIFICATION_ID`, `FK_CUSTOMER_ID`, `MESSAGE`, `TYPE`, `STATUS`, `CREATED_AT`) VALUES
(1, 5, 'Your order #14 has been placed and is awaiting approval.', 'order', 'read', '2025-05-03 01:14:50'),
(2, 5, 'Your order #8 has been approved and is being processed.', 'order', 'read', '2025-05-03 01:17:33'),
(3, 5, 'Your order #15 has been placed and is awaiting approval.', 'order', 'read', '2025-05-03 01:19:05'),
(4, 5, 'Your order for A4TECH BLACK MOUSE has been approved and is being processed.', 'order', 'read', '2025-05-03 01:22:58'),
(5, 5, 'Your order for ROG Strix Z490-E GAMING MOTHERBOARD has been placed and is awaiting approval.', 'order', 'read', '2025-05-03 01:24:59'),
(6, 5, 'Your order for Corsair coolant fan has been placed and is awaiting approval.', 'order', 'read', '2025-05-03 01:25:15'),
(7, 5, 'Your order for ROG Strix Z490-E GAMING MOTHERBOARD has been rejected. Please check the details.', 'order', 'read', '2025-05-03 01:40:23');

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

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`PK_ORDER_ID`, `FK1_CUSTOMER_ID`, `FK2_PAYMENT_ID`, `FK3_USER_ID`, `TOTAL_PRICE`, `STATUS`, `ORDER_DATE`, `LINE_TOTAL`) VALUES
(7, 5, 0, 0, 1000.00, 'Approved', '2025-05-01 23:29:14', 0.00),
(8, 5, 0, 0, 2000.00, 'Approved', '2025-05-01 23:29:22', 0.00),
(9, 5, 0, 0, 13000.00, 'Approved', '2025-05-01 23:30:19', 0.00),
(10, 5, 0, 0, 3000.00, 'Approved', '2025-05-01 23:41:31', 0.00),
(11, 5, 0, 0, 25000.00, 'Approved', '2025-05-01 23:41:52', 0.00),
(12, 5, 0, 0, 10000.00, 'Approved', '2025-05-03 01:05:28', 0.00),
(14, 5, 0, 0, 2000.00, 'Approved', '2025-05-03 01:14:50', 0.00),
(15, 5, 0, 0, 3000.00, 'Approved', '2025-05-03 01:19:05', 0.00),
(16, 5, 0, 0, 5000.00, 'Rejected', '2025-05-03 01:24:59', 0.00),
(17, 5, 0, 0, 3000.00, 'Pending', '2025-05-03 01:25:15', 0.00);

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

--
-- Dumping data for table `order_detail`
--

INSERT INTO `order_detail` (`PK_ORDER_DETAIL_ID`, `FK1_PRODUCT_ID`, `FK2_ORDER_ID`, `QTY`, `PRICE`, `CREATED_AT`) VALUES
(1, 39, 0, 2, 3000.00, '2025-05-01 22:51:59'),
(2, 38, 7, 1, 1000.00, '2025-05-01 23:29:14'),
(3, 32, 8, 1, 2000.00, '2025-05-01 23:29:22'),
(4, 28, 9, 3, 4000.00, '2025-05-01 23:30:19'),
(5, 34, 9, 1, 1000.00, '2025-05-01 23:30:19'),
(6, 26, 10, 1, 3000.00, '2025-05-01 23:41:31'),
(7, 27, 11, 1, 25000.00, '2025-05-01 23:41:52'),
(8, 22, 12, 1, 10000.00, '2025-05-03 01:05:28'),
(9, 36, 14, 1, 2000.00, '2025-05-03 01:14:50'),
(10, 37, 15, 1, 3000.00, '2025-05-03 01:19:05'),
(11, 25, 16, 1, 5000.00, '2025-05-03 01:24:59'),
(12, 37, 17, 1, 3000.00, '2025-05-03 01:25:15');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `PK_PAYMENT_ID` int(11) NOT NULL,
  `PAYMENT_METHOD` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`PK_PAYMENT_ID`, `PAYMENT_METHOD`) VALUES
(0, 'cod');

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
(20, 2, 1, 'RTX 3080 ', 'Best for gaming ', '-White\r\n-Cool fan', 3500.00, 11, 'white graphic card.jpg', '2025-04-26 02:26:00', '2025-04-26 02:26:00'),
(21, 2, 1, 'RTX 3080 BLACK', 'Best For gaming', '-Black\r\n-cool fan', 30000.00, 10, 'Video game graphics are a ticking time bomb — the industry needs to focus on art over tech.jpg', '2025-04-26 02:34:07', '2025-04-26 02:34:07'),
(22, 8, 2, 'ELFKS DROID CASING', '-The best cased by elon musk', '-White - Blue combi\r\n-Hard Case', 10000.00, 20, 'Transform your product into a captivating visual experience with 3D product animation!.jpg', '2025-04-26 02:35:46', '2025-04-26 02:35:46'),
(23, 5, 3, 'HyperX RAM 16GB', 'Best for Coding', '-DDRM5\r\n-16GB\r\n', 3000.00, 20, 'The best RAM of 2024_ top memory for your PC.jpg', '2025-04-26 02:36:31', '2025-04-26 02:36:31'),
(24, 19, 4, 'Corsair coolant fan', 'For your cpu ', '-1000mah Fan', 800.00, 20, 'SST-AR04.jpg', '2025-04-26 02:37:25', '2025-04-26 02:37:25'),
(25, 3, 1, 'ROG Strix Z490-E GAMING MOTHERBOARD', 'Best for gaming, coding and editing', '-BLACK\r\n-PURPLE\r\n', 5000.00, 20, 'ROG STRIX Z490-E GAMING _ Motherboards _ ROG Global.jpg', '2025-04-26 02:40:24', '2025-04-26 02:40:24'),
(26, 5, 2, 'T-FORCE Delta 16gb RAM', 'Best for gaming', '-white\r\n-purple\r\n-green\r\n-blue\r\n-pink\r\n-gray', 3000.00, 20, 'p6.png', '2025-04-26 02:41:20', '2025-04-26 02:41:20'),
(27, 2, 3, 'AMD Radeon GPU FidelityFX', 'Best for gaming', '-black\r\n-red', 25000.00, 10, 'p5.png', '2025-04-26 02:43:11', '2025-04-26 02:43:11'),
(28, 3, 4, 'MSI mobo ', 'Best for Gaming...\r\n', '-ALL BLACK', 4000.00, 20, 'p4.png', '2025-04-26 02:43:54', '2025-04-26 02:43:54'),
(29, 1, 1, 'UN Monitor 240hz', 'Best for HD Videos', '-240hz\r\n-1ms\r\n-hdr\r\n-24.5\"', 3000.00, 19, 'p3.png', '2025-04-26 02:45:05', '2025-04-26 02:45:05'),
(30, 19, 2, 'Coolant fans ', 'Good for your eyes', '-RGB', 2000.00, 20, 'p2.png', '2025-04-26 02:45:37', '2025-04-26 02:45:37'),
(31, 2, 3, 'AMD White Graphic Card 2080', 'Better experience for gaming', '-white\r\n-cold', 25000.00, 20, 'p1.png', '2025-04-26 02:46:32', '2025-04-26 02:46:32'),
(32, 13, 3, 'AN Keyboard MC Blue switch', 'Good for typing and gaming', '-Blue switch\r\n-smooth typing\r\n', 2000.00, 20, 'gaming keyboard.jpg', '2025-04-26 02:47:43', '2025-04-26 02:47:43'),
(33, 26, 4, 'The Great Wave of Kanagawa Mousepad', 'Smooth ', '-White, blue lights', 1000.00, 20, 'Flowy Waves Desk Mat, XXL Gaming Mouse Pad, Blue Water Mousepad, Beautiful Nature Desk Mat.jpg', '2025-04-26 02:51:39', '2025-04-26 02:51:39'),
(34, 26, 1, 'Black Mousepad ', 'Smooth for mouse and gaming', '-black', 1000.00, 20, 'DIGSOM Mouse Pad.jpg', '2025-04-26 02:52:11', '2025-04-26 02:52:11'),
(35, 2, 1, 'GEFORCE GTX ', 'Good for gaming', '-32gb RAM', 25000.00, 20, 'db839bf5-d42b-4a59-b48d-6f8f5f3c31fc.jpg', '2025-04-26 02:53:39', '2025-04-26 02:53:39'),
(36, 8, 3, 'RGB Hard Glass Case', 'See through', '-Glass', 2000.00, 20, 'Custom build Gaming PC.jpg', '2025-04-26 02:54:53', '2025-04-26 02:54:53'),
(37, 19, 1, 'Corsair coolant fan', 'Cold and Cool', '-White\r\n', 3000.00, 20, 'Corsair Dominator Platinum RGB Series.jpg', '2025-04-26 02:55:30', '2025-04-26 02:55:30'),
(38, 14, 3, 'A4TECH BLACK MOUSE', 'Good for Valorant', '-black', 1000.00, 20, 'Amazon_com_ Dapesuom Small Mouse Pad 6 x 8 Inch….jpg', '2025-04-26 02:56:30', '2025-04-26 02:56:30'),
(39, 26, 4, 'MSI Router', 'Good for any WIFI', '-FAST ', 3000.00, 20, '977dcc0b-90d6-4ee3-be12-05ac3f3d73be.jpg', '2025-04-26 02:57:19', '2025-04-26 02:57:19');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 0, 'Patinyo', 'Rafael', '+123567890', 'Bakal TT Corp', 'Cthulu@gmail', 'Avocado St. Mambaling', '351453175_1191126874899419_117306819684368067_n.jpg', '2025-04-26 02:18:45', '2025-04-26 02:18:45'),
(2, 0, 'Caumeran', 'Damien', '+987644123', 'Cow Me Run ', 'damskie@gmail.com', 'V.rama', 'ASDASDSDASA.jpg', '2025-04-26 02:22:20', '2025-04-26 02:22:20'),
(3, 0, 'Dagupols', 'Client', '+56892134', 'Try me hack', 'Client@gmai.com', 'Buhisan', '467743265_2034794076942966_7629118095982581341_n.jpg', '2025-04-26 02:23:42', '2025-04-26 02:23:42'),
(4, 0, 'Ancero', 'John Rey', '+565723257', 'JAHH Corp.', 'gwapokoancero123@gmail.com', 'B.rod', '486231808_29198721426439246_8070934184723318600_n.jpg', '2025-04-26 02:25:00', '2025-04-26 02:25:00');

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
  `UPDATE_AT` datetime NOT NULL,
  `IS_ADMIN` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`PK_USER_ID`, `L_NAME`, `F_NAME`, `EMAIL`, `PASSWORD_HASH`, `ADDRESS`, `PHONE_NUM`, `CREATED_AT`, `UPDATE_AT`, `IS_ADMIN`) VALUES
(0, 'Admin', 'System', 'admin@compucore.com', '$2y$10$IuvaHPz32l.pwIiXENQgIuIDDldeKym450tpqFkOoTl4eT/pwKbhW', 'Compucore HQ', '09123456789', '2025-04-26 01:15:58', '0000-00-00 00:00:00', 1);

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
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`PK_NOTIFICATION_ID`),
  ADD KEY `FK_CUSTOMER_ID` (`FK_CUSTOMER_ID`);

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
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `PK_CUSTOMER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `PK_NOTIFICATION_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `PK_ORDER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order_detail`
--
ALTER TABLE `order_detail`
  MODIFY `PK_ORDER_DETAIL_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `PK_PRODUCT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `PK_REVIEW_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `PK_SUPPLIER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`FK_CUSTOMER_ID`) REFERENCES `customer` (`PK_CUSTOMER_ID`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`FK2_SUPPLIER_ID`) REFERENCES `supplier` (`PK_SUPPLIER_ID`) ON DELETE CASCADE;

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
