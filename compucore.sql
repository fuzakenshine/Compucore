-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2025 at 05:08 PM
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
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,0) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`product_id`, `product_name`, `product_price`, `quantity`) VALUES
(1, 'Gigabyte GeForce RTX 5080', 93695, 1),
(1, 'Gigabyte GeForce RTX 5080', 93695, 1),
(1, 'Gigabyte GeForce RTX 5080', 93695, 1);

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
(0, 'TINGA', 'JOHN RAY', 'jrtjohnray@gmail.com', '$2y$10$lvIdywOwKO9s0elw8Rx36e/ymyBKustce1nXvEre89nohEIts3qyi', 'OPRRA VILLAREMEDIOS KALUNASAN CEBU CITY, 6000', '09991029087', '2025-04-16 22:33:19', '2025-04-16 22:33:19');

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
  `PRICE` decimal(10,2) NOT NULL,
  `QTY` int(11) NOT NULL DEFAULT 0,
  `IMAGE` varchar(255) NOT NULL,
  `CREATED_AT` datetime NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` datetime NOT NULL
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
  `CREATE_AT` datetime NOT NULL DEFAULT current_timestamp(),
  `UPDATE_AT` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for dumped tables
--

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
-- Constraints for dumped tables
--

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
-- Constraints for table `supplier`
--
ALTER TABLE `supplier`
  ADD CONSTRAINT `supplier_ibfk_1` FOREIGN KEY (`FK_USER_ID`) REFERENCES `users` (`PK_USER_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
