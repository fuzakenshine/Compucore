-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2025 at 06:57 AM
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

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `populateCUSTOMERS` (IN `p_first_name` VARCHAR(50), IN `p_last_name` VARCHAR(50), IN `p_email` VARCHAR(100), IN `p_password_hash` VARCHAR(255), IN `p_customer_address` TEXT, IN `p_phone_num` VARCHAR(20))   BEGIN
    INSERT INTO CUSTOMER (
        F_NAME, 
        L_NAME, 
        EMAIL, 
        PASSWORD_HASH, 
        CUSTOMER_ADDRESS, 
        PHONE_NUM, 
        UPDATE_AT
    ) 
    VALUES (
        p_first_name,
        p_last_name,
        p_email,
        p_password_hash,
        p_customer_address,
        p_phone_num,
        NOW()
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `populateORDERDETAILS` (IN `p_order_id` INT, IN `p_product_id` INT, IN `p_qty` INT, IN `p_price` DECIMAL(10,2))   BEGIN
    INSERT INTO order_detail (FK2_ORDER_ID, FK1_PRODUCT_ID, QTY, PRICE) 
    VALUES (p_order_id, p_product_id, p_qty, p_price);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `populateORDERS` (IN `p_customer_id` INT, IN `p_total_price` DECIMAL(10,2), IN `p_payment_id` INT)   BEGIN
    INSERT INTO orders (
        FK1_CUSTOMER_ID,
        TOTAL_PRICE,
        FK2_PAYMENT_ID,
        STATUS,
        ORDER_DATE
    ) VALUES (
        p_customer_id,
        p_total_price,
        p_payment_id,
        'Pending',
        NOW()
    );
    SELECT LAST_INSERT_ID() AS order_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `populatePRODUCTS` (IN `p_operation` VARCHAR(10), IN `p_product_id` INT, IN `p_category_id` INT, IN `p_supplier_id` INT, IN `p_prod_name` VARCHAR(255), IN `p_prod_desc` TEXT, IN `p_prod_specs` TEXT, IN `p_price` DECIMAL(10,2), IN `p_qty` INT, IN `p_image` VARCHAR(255))   BEGIN
    -- For CREATE operation
    IF p_operation = 'CREATE' THEN
        INSERT INTO products (
            FK1_CATEGORY_ID,
            FK2_SUPPLIER_ID,
            PROD_NAME,
            PROD_DESC,
            PROD_SPECS,
            PRICE,
            QTY,
            IMAGE,
            CREATED_AT,
            UPDATED_AT
        ) VALUES (
            p_category_id,
            p_supplier_id,
            p_prod_name,
            p_prod_desc,
            p_prod_specs,
            p_price,
            p_qty,
            p_image,
            NOW(),
            NOW()
        );
    ELSEIF p_operation = 'READ' THEN
        SELECT * FROM products WHERE PK_PRODUCT_ID = p_product_id;
    ELSEIF p_operation = 'UPDATE' THEN
        UPDATE products
        SET
            FK1_CATEGORY_ID = p_category_id,
            FK2_SUPPLIER_ID = p_supplier_id,
            PROD_NAME = p_prod_name,
            PROD_DESC = p_prod_desc,
            PROD_SPECS = p_prod_specs,
            PRICE = p_price,
            QTY = p_qty,
            IMAGE = p_image,
            UPDATED_AT = NOW()
        WHERE PK_PRODUCT_ID = p_product_id;
    ELSEIF p_operation = 'DELETE' THEN
        DELETE FROM products WHERE PK_PRODUCT_ID = p_product_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `populateSUPPLIER` (IN `p_FK_USER_ID` INT, IN `p_S_FNAME` VARCHAR(50), IN `p_S_LNAME` VARCHAR(50), IN `p_PHONE_NUM` VARCHAR(15), IN `p_COMPANY_NAME` VARCHAR(100), IN `p_EMAIL` VARCHAR(100), IN `p_SUPPLIER_ADDRESS` TEXT, IN `p_SUPPLIER_IMAGE` VARCHAR(255))   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'An error occurred while inserting supplier data';
    END;

    START TRANSACTION;

    -- Insert the supplier data
    INSERT INTO supplier (
        FK_USER_ID,
        S_FNAME,
        S_LNAME,
        PHONE_NUM,
        COMPANY_NAME,
        EMAIL,
        SUPPLIER_ADDRESS,
        SUPPLIER_IMAGE,
        UPDATE_AT
    ) VALUES (
        p_FK_USER_ID,
        p_S_FNAME,
        p_S_LNAME,
        p_PHONE_NUM,
        p_COMPANY_NAME,
        p_EMAIL,
        p_SUPPLIER_ADDRESS,
        p_SUPPLIER_IMAGE,
        NOW()
    );

    COMMIT;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `PK_ADMIN_ID` int(11) NOT NULL,
  `USERNAME` varchar(50) NOT NULL,
  `EMAIL` varchar(100) NOT NULL,
  `PASSWORD_HASH` varchar(255) NOT NULL,
  `F_NAME` varchar(50) NOT NULL,
  `L_NAME` varchar(50) NOT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`PK_ADMIN_ID`, `USERNAME`, `EMAIL`, `PASSWORD_HASH`, `F_NAME`, `L_NAME`, `CREATED_AT`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$mUO3QKcpv.CO.PhZZYtZC.NQ/bZDQjaDWPquTejXM3H6T74BQkNB.', 'Admin', 'User', '2025-05-09 09:37:01'),
(3, '', 'admin@compucore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '2025-05-09 09:42:22');

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
(1, 48, 'TenZ Signature Edition', 18500.00, 2, 0, '2025-05-15 20:03:05'),
(30, 38, 'A4TECH BLACK MOUSE', 1000.00, 2, 4, '2025-04-26 09:36:27'),
(45, 23, 'HyperX RAM 16GB', 3000.00, 1, 5, '2025-05-02 17:35:36'),
(48, 39, 'MSI Router', 3000.00, 1, 2, '2025-05-03 08:04:02'),
(49, 38, 'A4TECH BLACK MOUSE', 1000.00, 1, 2, '2025-05-03 08:04:29'),
(55, 48, 'TenZ Signature Edition', 18500.00, 1, 27, '2025-05-16 04:03:32');

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
  `UPDATE_AT` datetime NOT NULL,
  `PROFILE_PIC` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`PK_CUSTOMER_ID`, `L_NAME`, `F_NAME`, `EMAIL`, `PASSWORD_HASH`, `CUSTOMER_ADDRESS`, `PHONE_NUM`, `CREATED_AT`, `UPDATE_AT`, `PROFILE_PIC`) VALUES
(2, 'CowMeRun', 'Damiaru', 'CowMeRun@gmail.com', '$2y$10$N3S5KrSGqM3wpY1PLZmZ8.OSpdaCHP5PalBldxYxhhJ02a6nGqI06', 'Taga VRAMA ko', '0912345678', '2025-04-18 02:17:00', '2025-05-03 16:46:27', '6815d136de0fa.png'),
(3, 'TINGA', 'JOHNRAY', 'jttinga@email.com', '$2y$10$NkiHMi4wFPMBGIOkH8S.IOQQUJx90YcjjjaTatavgkeCQ6iMrUP36', 'Villa remedios unit 3A', '09991029087', '2025-04-26 16:10:54', '2025-04-26 16:10:54', 'default.png'),
(4, 'test', '567890-', '67890@1', '$2y$10$GzTEh3vpAb5B1tnEFnonaeBt6ujCvoao3YO9Z7aG.E3yzQ1yCgrcK', '123', 'rewq', '2025-04-26 17:35:08', '2025-04-26 17:35:08', 'default.png'),
(5, 'Doe', 'John', 'j.doe@email.com', '$2y$10$SIninVA/fQWFKe0NFcW2dOxo1awT89whuTJe6TXhV4FCDqe/zk.uW', 'New York', '09123456789', '2025-05-01 20:27:27', '2025-05-01 20:27:27', 'default.png'),
(6, 'TINGA', 'JOHNRAY', 'jrtjohnray@gmail.com', '$2y$10$C8.5l0Jf7a0kBJExYsMW0OkXApM.QzPJbDe7auBJowmrl50G2l8kq', 'Villa remedios unit 3A', '+639991029087', '2025-05-04 02:15:13', '2025-05-04 02:15:13', 'default.png'),
(7, 'one', 'test', 'testone@gmail.com', '$2y$10$HNcoTgFv3M989BQyLfRvIu1PJP0yIdNnR8XqtkFDNKzrhwpwe4Cre', 'Test@123', '+63123456789088', '2025-05-04 02:22:35', '2025-05-04 02:22:35', 'default.png'),
(8, 'two', 'test', 'testtwo@gmail.com', '$2y$10$a2hVhxOLZ1.Eh8xyFGejsOPwzx6LLq.vzsA2wWx.iEazrrJVbGTKq', 'Villa remedios, Oppra kalunasan', '+123232323', '2025-05-04 02:27:46', '2025-05-04 02:27:46', 'default.png'),
(9, 'three', 'test', 'testthree@gmail.com', '$2y$10$aW3lIzQk0UoWmSX50aYdXORg7A3JzUAAhapKoaHh6gGh3c3nXhtAC', 'Villa remedios, Oppra kalunasan', '+639991029087', '2025-05-04 02:28:45', '2025-05-04 02:28:45', 'default.png'),
(10, 'DOE', 'JAN', 'jando@gmail.com', '$2y$10$Jrpa1KsAYQx2DKRI34CqE.5CRsWcB00uJl1sarKse.8.0L3ewAi8u', 'San Isidro', '+12345678990088', '2025-05-06 18:44:05', '2025-05-06 18:44:05', 'default.png'),
(24, 'four', 'Testg', 'testfour@gmail.com', '$2y$10$WXNxlFC.NJgVnzOHkn/PmuYc52eKuMV1/Gq7JHYZsK99dbWAJNtRW', 'Oprra Kalunasan', '+63123466879', '2025-05-09 17:07:42', '2025-05-10 00:52:38', 'default.png'),
(25, 'Bustillo', 'Jarom', 'jarom@gmail.com', '$2y$10$.R5OMJFfNxbQb2.QaEkDS.eHd9Fl9pHXv29ILyiP2dE.UIhgjJtMW', 'California', '+6309123456789', '2025-05-10 16:55:09', '2025-05-10 16:55:09', 'default.png'),
(26, 'Lahaylahay', 'Bevs', 'b@gmail.com', '$2y$10$7ENpABtkXCBp/CcwEnHWEOqbZm5wOJtYSJnZ7G6I4q4qHD3ODuJb.', 'CEBU', '+639234560987', '2025-05-10 17:21:07', '2025-05-10 17:21:07', 'default.png'),
(27, 'Doe', 'Jane', 'doe@gmail.com', '$2y$10$sLevqSrDcQuKTcQ0bfiR6eko9XJkwCxxPFdljkgjiCAWZQ35en.ey', 'CEBu', '+63096512387431', '2025-05-16 11:37:12', '2025-05-16 11:37:12', 'default.png');

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
(7, 5, 'Your order for ROG Strix Z490-E GAMING MOTHERBOARD has been rejected. Please check the details.', 'order', 'read', '2025-05-03 01:40:23'),
(8, 5, 'Your order for Corsair coolant fan has been approved and is being processed.', 'order', 'unread', '2025-05-03 14:46:55'),
(9, 1, 'Your order for A4TECH BLACK MOUSE has been placed and is awaiting approval.', 'order', 'read', '2025-05-03 14:51:14'),
(10, 1, 'Your order for A4TECH BLACK MOUSE has been approved and is being processed.', 'order', 'read', '2025-05-03 14:51:41'),
(11, 1, 'Your order for Corsair coolant fan has been placed and is awaiting approval.', 'order', 'read', '2025-05-03 15:37:22'),
(12, 1, 'Your order for Corsair coolant fan has been approved and is being processed.', 'order', 'read', '2025-05-03 15:39:14'),
(13, 5, 'Your order for A4TECH BLACK MOUSE has been placed and is awaiting approval.', 'order', 'unread', '2025-05-03 15:43:03'),
(14, 5, 'Your order for A4TECH BLACK MOUSE has been approved and is being processed.', 'order', 'unread', '2025-05-03 15:43:21'),
(15, 1, 'Your order for RGB Hard Glass Case has been placed and is awaiting approval.', 'order', 'unread', '2025-05-03 17:54:32'),
(16, 1, 'Your order for RGB Hard Glass Case has been approved and is being processed.', 'order', 'unread', '2025-05-03 17:54:43'),
(17, 8, 'Your order for GEFORCE GTX  has been placed and is awaiting approval.', 'order', 'read', '2025-05-04 02:36:00'),
(18, 8, 'Your order for GEFORCE GTX  has been approved and is being processed.', 'order', 'read', '2025-05-04 02:36:14'),
(19, 8, 'Your order for MSI Router has been placed and is awaiting approval.', 'order', 'read', '2025-05-04 02:46:50'),
(20, 8, 'Your order for MSI Router has been approved and is being processed.', 'order', 'read', '2025-05-04 02:55:08'),
(21, 8, 'Your order for MSI Router has been approved and is being processed.', 'order', 'read', '2025-05-04 02:55:09'),
(22, 8, 'Your order for MSI Router has been approved and is being processed.', 'order', 'read', '2025-05-04 02:55:09'),
(23, 8, 'Your order for MSI Router has been approved and is being processed.', 'order', 'read', '2025-05-04 02:55:09'),
(25, 8, 'Your order for Corsair coolant fan has been placed and is awaiting approval.', 'order', 'read', '2025-05-04 02:56:33'),
(26, 8, 'Your order for Corsair coolant fan has been approved and is being processed.', 'order', 'read', '2025-05-04 02:56:40'),
(27, 8, 'Your order for Corsair coolant fan has been rejected. Please check the details.', 'order', 'read', '2025-05-04 02:57:11'),
(29, 8, 'Your order #23 has been rejected.', 'order', 'read', '2025-05-04 02:59:34'),
(30, 8, 'Your order for A4TECH BLACK MOUSE has been placed and is awaiting approval.', 'order', 'read', '2025-05-04 03:00:16'),
(31, 8, 'Your order #25 has been rejected.', 'order', 'read', '2025-05-04 03:00:21'),
(32, 8, 'Your order for MSI mobo  has been placed and is awaiting approval.', 'order', 'read', '2025-05-04 03:02:58'),
(33, 8, 'Your order #26 has been approved and is being processed.', 'order', 'read', '2025-05-04 03:03:03'),
(34, 8, 'Your order for Black Mousepad  has been placed and is awaiting approval.', 'order', 'unread', '2025-05-04 03:06:16'),
(35, 8, 'Your order for Corsair coolant fan has been placed and is awaiting approval.', 'order', 'unread', '2025-05-04 03:09:22'),
(36, 8, 'Your order #28 has been approved and is being processed.', 'order', 'unread', '2025-05-04 03:10:02'),
(37, 8, 'Your order #28 has been rejected.', 'order', 'unread', '2025-05-04 03:10:16'),
(38, 8, 'Your order #27 has been approved and is being processed.', 'order', 'unread', '2025-05-04 03:10:17'),
(39, 8, 'Your order #27 has been approved and is being processed.', 'order', 'unread', '2025-05-04 03:10:23'),
(41, 8, 'Your order #26 has been rejected.', 'order', 'unread', '2025-05-06 18:42:53'),
(42, 8, 'Your order #27 has been rejected.', 'order', 'unread', '2025-05-06 18:42:54'),
(43, 24, 'Your order for A4TECH BLACK MOUSE has been placed and is awaiting approval.', 'order', 'read', '2025-05-09 18:10:19'),
(44, 24, 'Your order #29 has been approved and is being processed.', 'order', 'read', '2025-05-09 18:13:21'),
(45, 24, 'Your order #29 has been approved and is being processed.', 'order', 'read', '2025-05-09 18:13:36'),
(46, 24, 'Your order #29 has been rejected.', 'order', 'read', '2025-05-09 18:15:43'),
(47, 24, 'Your order for MSI Router has been placed and is awaiting approval.', 'order', 'read', '2025-05-09 18:20:32'),
(48, 24, 'Your order #30 has been rejected.', 'order', 'read', '2025-05-09 18:23:52'),
(49, 24, 'Your order #29 has been approved and is being processed.', 'order', 'read', '2025-05-09 18:26:29'),
(50, 8, 'Your order #28 has been rejected.', 'order', 'unread', '2025-05-09 18:28:17'),
(51, 8, 'Your order #27 has been rejected.', 'order', 'unread', '2025-05-09 18:31:53'),
(52, 8, 'Your order #26 has been approved and is being processed.', 'order', 'unread', '2025-05-09 18:31:55'),
(53, 8, 'Your order #25 has been approved and is being processed.', 'order', 'unread', '2025-05-09 18:31:56'),
(54, 8, 'Your order #24 has been approved and is being processed.', 'order', 'unread', '2025-05-09 18:31:57'),
(55, 8, 'Your order #23 has been approved and is being processed.', 'order', 'unread', '2025-05-09 18:31:57'),
(56, 8, 'Your order #22 has been approved and is being processed.', 'order', 'unread', '2025-05-09 18:31:58'),
(57, 1, 'Your order #21 has been approved and is being processed.', 'order', 'unread', '2025-05-09 18:31:59'),
(58, 5, 'Your order #20 has been approved and is being processed.', 'order', 'unread', '2025-05-09 18:32:00'),
(59, 1, 'Your order #19 has been approved and is being processed.', 'order', 'unread', '2025-05-09 18:32:02'),
(60, 1, 'Your order #18 has been approved and is being processed.', 'order', 'unread', '2025-05-09 18:32:04'),
(61, 5, 'Your order #7 has been approved and is being processed.', 'order', 'unread', '2025-05-09 18:32:06'),
(62, 5, 'Your order #8 has been approved and is being processed.', 'order', 'unread', '2025-05-10 00:50:29'),
(63, 5, 'Your order #17 has been approved and is being processed.', 'order', 'unread', '2025-05-10 00:50:30'),
(64, 5, 'Your order #16 has been approved and is being processed.', 'order', 'unread', '2025-05-10 00:50:32'),
(65, 5, 'Your order #15 has been approved and is being processed.', 'order', 'unread', '2025-05-10 00:50:33'),
(66, 5, 'Your order #14 has been approved and is being processed.', 'order', 'unread', '2025-05-10 00:50:33'),
(67, 5, 'Your order #12 has been approved and is being processed.', 'order', 'unread', '2025-05-10 00:50:34'),
(68, 5, 'Your order #11 has been approved and is being processed.', 'order', 'unread', '2025-05-10 00:50:35'),
(69, 5, 'Your order #10 has been approved and is being processed.', 'order', 'unread', '2025-05-10 00:50:36'),
(70, 5, 'Your order #9 has been approved and is being processed.', 'order', 'unread', '2025-05-10 00:50:37'),
(71, 24, 'Your order for Corsair coolant fan has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 02:05:04'),
(72, 24, 'Your order #31 has been approved and is being processed.', 'order', 'read', '2025-05-10 02:08:05'),
(73, 24, 'Your order for GEFORCE GTX  has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 02:10:45'),
(74, 24, 'Your order for RGB Hard Glass Case has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 02:18:19'),
(75, 24, 'Your order #33 has been approved and is being processed.', 'order', 'read', '2025-05-10 02:18:35'),
(76, 24, 'Your order for MSI mobo  has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 02:19:07'),
(77, 24, 'Your order #34 has been rejected.', 'order', 'read', '2025-05-10 02:22:55'),
(78, 24, 'Your order for MSI mobo  has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 02:23:22'),
(79, 24, 'Your order #35 has been approved and is being processed.', 'order', 'read', '2025-05-10 02:23:36'),
(80, 24, 'Your order #32 has been rejected.', 'order', 'read', '2025-05-10 02:47:23'),
(81, 24, 'Your order for Corsair coolant fan has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 02:56:55'),
(82, 24, 'Your order for MSI mobo  has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 02:59:22'),
(83, 24, 'Your order for AMD Radeon GPU FidelityFX has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 02:59:33'),
(84, 24, 'Your order #38 has been approved and is being processed.', 'order', 'read', '2025-05-10 03:12:29'),
(85, 24, 'Your order for MSI mobo  has been approved and is being processed.', 'order', 'read', '2025-05-10 03:14:22'),
(86, 24, 'Your order for Corsair coolant fan has been approved and is being processed.', 'order', 'read', '2025-05-10 03:59:34'),
(87, 24, 'Your order for AMD Radeon GPU FidelityFX has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 11:14:09'),
(88, 24, 'Your order for AMD Radeon GPU FidelityFX has been approved and is being processed.', 'order', 'read', '2025-05-10 11:14:21'),
(89, 24, 'Your order #40 has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 14:11:07'),
(90, 24, 'Your order for RGB Hard Glass Case has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 14:11:07'),
(91, 24, 'Your order #40 has been approved and is being processed.', 'order', 'read', '2025-05-10 14:11:26'),
(92, 24, 'Your order for RGB Hard Glass Case has been approved and is being processed.', 'order', 'read', '2025-05-10 14:11:26'),
(93, 24, 'Your order #41 has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 14:16:02'),
(94, 24, 'Your order for Corsair coolant fan has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 14:16:02'),
(95, 24, 'Your order for Corsair coolant fan has been approved and is being processed.', 'order', 'read', '2025-05-10 14:16:06'),
(96, 24, 'Your order for Corsair coolant fan has been approved and is being processed.', 'order', 'read', '2025-05-10 14:16:06'),
(97, 24, 'Your order #42 has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 14:16:32'),
(98, 24, 'Your order for A4TECH BLACK MOUSE, AMD White Graphic Card 2080, T-FORCE Delta 16gb RAM has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 14:16:32'),
(99, 24, 'Your order for A4TECH BLACK MOUSE, AMD White Graphic Card 2080, T-FORCE Delta 16gb RAM has been approved and is being processed.', 'order', 'read', '2025-05-10 14:16:36'),
(100, 24, 'Your order for A4TECH BLACK MOUSE, AMD White Graphic Card 2080, T-FORCE Delta 16gb RAM has been approved and is being processed.', 'order', 'read', '2025-05-10 14:16:36'),
(101, 25, 'Your order #43 has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 16:56:58'),
(102, 25, 'Your order for Asus monitor has been placed and is awaiting approval.', 'order', 'read', '2025-05-10 16:56:58'),
(103, 25, 'Your order for Asus monitor has been rejected. Please check the details.', 'order', 'read', '2025-05-10 16:58:16'),
(104, 25, 'Your order #43 has been rejected.', 'order', 'read', '2025-05-10 16:58:16'),
(105, 25, 'Your order for Gaming Pc has been placed and is awaiting approval.', 'order', 'unread', '2025-05-10 17:04:36'),
(106, 25, 'Your order for Gaming Pc has been approved and is being processed.', 'order', 'unread', '2025-05-10 17:05:13'),
(107, 26, 'Your order for AN Keyboard MC Blue switch, ELFKS DROID CASING has been placed and is awaiting approval.', 'order', 'unread', '2025-05-10 17:25:54'),
(108, 26, 'Your order for AN Keyboard MC Blue switch, ELFKS DROID CASING has been approved and is being processed.', 'order', 'read', '2025-05-10 17:26:26'),
(109, 25, 'Your order for Asus monitor, The Great Wave of Kanagawa Mousepad has been placed and is awaiting approval.', 'order', 'unread', '2025-05-10 17:32:52'),
(110, 25, 'Your order for Asus monitor, The Great Wave of Kanagawa Mousepad has been approved and is being processed.', 'order', 'unread', '2025-05-10 17:33:02'),
(111, 27, 'Your order for TenZ Signature Edition has been placed and is awaiting approval.', 'order', 'read', '2025-05-16 11:37:41'),
(112, 27, 'Your order for [Pro Series] PRX Something Gaming Mousepad has been placed and is awaiting approval.', 'order', 'read', '2025-05-16 12:07:09'),
(113, 27, 'Your order for [Pro Series] PRX Something Gaming Mousepad has been placed and is awaiting approval.', 'order', 'read', '2025-05-16 12:12:54'),
(114, 27, 'Your order for Asus monitor has been placed and is awaiting approval.', 'order', 'unread', '2025-05-16 12:17:17'),
(115, 27, 'Your order for [Pro Series] PRX Something Gaming Mousepad has been approved and is being processed.', 'order', 'unread', '2025-05-16 12:39:43'),
(116, 27, 'Your order for TenZ Signature Edition has been approved and is being processed.', 'order', 'unread', '2025-05-16 12:44:42'),
(117, 27, 'Your order for Asus monitor has been approved and is being processed.', 'order', 'unread', '2025-05-16 12:47:31'),
(118, 27, 'Your order for MSI Router has been placed and is awaiting approval.', 'order', 'unread', '2025-05-16 12:56:36'),
(119, 27, 'Your order for MSI Router has been approved and is being processed.', 'order', 'unread', '2025-05-16 12:56:44');

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
  `ORDER_DATE` datetime NOT NULL DEFAULT current_timestamp(),
  `LINE_TOTAL` decimal(10,2) NOT NULL,
  `UPDATE_DATE` datetime DEFAULT NULL,
  `STATUS` enum('Pending','Approved','Rejected','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`PK_ORDER_ID`, `FK1_CUSTOMER_ID`, `FK2_PAYMENT_ID`, `FK3_USER_ID`, `TOTAL_PRICE`, `ORDER_DATE`, `LINE_TOTAL`, `UPDATE_DATE`, `STATUS`) VALUES
(7, 5, 0, 0, 1000.00, '2025-05-01 23:29:14', 0.00, '2025-05-09 18:32:06', 'Approved'),
(8, 5, 0, 0, 2000.00, '2025-05-01 23:29:22', 0.00, '2025-05-10 00:50:29', 'Approved'),
(9, 5, 0, 0, 13000.00, '2025-05-01 23:30:19', 0.00, '2025-05-10 00:50:37', 'Approved'),
(10, 5, 0, 0, 3000.00, '2025-05-01 23:41:31', 0.00, '2025-05-10 00:50:36', 'Approved'),
(11, 5, 0, 0, 25000.00, '2025-05-01 23:41:52', 0.00, '2025-05-10 00:50:35', 'Approved'),
(12, 5, 0, 0, 10000.00, '2025-05-03 01:05:28', 0.00, '2025-05-10 00:50:34', 'Approved'),
(14, 5, 0, 0, 2000.00, '2025-05-03 01:14:50', 0.00, '2025-05-10 00:50:33', 'Approved'),
(15, 5, 0, 0, 3000.00, '2025-05-03 01:19:05', 0.00, '2025-05-10 00:50:33', 'Approved'),
(16, 5, 0, 0, 5000.00, '2025-05-03 01:24:59', 0.00, '2025-05-10 00:50:32', 'Approved'),
(17, 5, 0, 0, 3000.00, '2025-05-03 01:25:15', 0.00, '2025-05-10 00:50:30', 'Approved'),
(18, 1, 0, 0, 2000.00, '2025-05-03 14:51:14', 0.00, '2025-05-09 18:32:04', 'Approved'),
(19, 1, 0, 0, 3000.00, '2025-05-03 15:37:22', 0.00, '2025-05-09 18:32:02', 'Approved'),
(20, 5, 0, 0, 1000.00, '2025-05-03 15:43:03', 0.00, '2025-05-09 18:32:00', 'Approved'),
(21, 1, 0, 0, 2150.00, '2025-05-03 17:54:32', 0.00, '2025-05-09 18:31:59', 'Approved'),
(22, 8, 0, 0, 25250.00, '2025-05-04 02:36:00', 0.00, '2025-05-09 18:31:58', 'Approved'),
(23, 8, 0, 0, 9000.00, '2025-05-04 02:46:50', 0.00, '2025-05-09 18:31:57', 'Approved'),
(24, 8, 0, 0, 6000.00, '2025-05-04 02:56:33', 0.00, '2025-05-09 18:31:57', 'Approved'),
(25, 8, 0, 0, 1000.00, '2025-05-04 03:00:16', 0.00, '2025-05-09 18:31:56', 'Approved'),
(26, 8, 0, 0, 20000.00, '2025-05-04 03:02:58', 0.00, '2025-05-09 18:31:55', 'Approved'),
(27, 8, 0, 0, 5000.00, '2025-05-04 03:06:16', 0.00, '2025-05-09 18:31:53', 'Rejected'),
(28, 8, 0, 0, 6000.00, '2025-05-04 03:09:22', 0.00, '2025-05-09 18:28:17', 'Rejected'),
(29, 24, 0, 0, 1000.00, '2025-05-09 18:10:19', 0.00, '2025-05-09 18:26:29', 'Approved'),
(30, 24, 0, 0, 3000.00, '2025-05-09 18:20:32', 0.00, '2025-05-09 18:23:52', 'Rejected'),
(31, 24, 0, 0, 3000.00, '2025-05-10 02:05:04', 0.00, '2025-05-10 02:08:05', 'Approved'),
(32, 24, 0, 0, 25000.00, '2025-05-10 02:10:45', 0.00, '2025-05-10 02:47:23', 'Rejected'),
(33, 24, 0, 0, 6000.00, '2025-05-10 02:18:19', 0.00, '2025-05-10 02:18:35', 'Approved'),
(34, 24, 0, 0, 36000.00, '2025-05-10 02:19:07', 0.00, '2025-05-10 02:22:55', 'Rejected'),
(35, 24, 0, 0, 28000.00, '2025-05-10 02:23:22', 0.00, '2025-05-10 02:23:36', 'Approved'),
(36, 24, 0, 0, 3000.00, '2025-05-10 02:56:55', 0.00, '2025-05-10 03:59:34', 'Approved'),
(37, 24, 0, 0, 4000.00, '2025-05-10 02:59:22', 0.00, '2025-05-10 03:14:22', 'Approved'),
(38, 24, 0, 0, 25000.00, '2025-05-10 02:59:33', 0.00, '2025-05-10 03:12:29', 'Approved'),
(39, 24, 0, 0, 25000.00, '2025-05-10 11:14:09', 0.00, '2025-05-10 11:14:21', 'Approved'),
(40, 24, 0, 0, 30250.00, '2025-05-10 14:11:07', 0.00, '2025-05-10 14:11:26', 'Approved'),
(41, 24, 0, 0, 3000.00, '2025-05-10 14:16:02', 0.00, '2025-05-10 14:16:06', 'Approved'),
(42, 24, 0, 0, 29000.00, '2025-05-10 14:16:32', 0.00, '2025-05-10 14:16:36', 'Approved'),
(43, 25, 0, 0, 5000.00, '2025-05-10 16:56:58', 0.00, '2025-05-10 16:58:16', 'Rejected'),
(44, 25, 0, 0, 200000.00, '2025-05-10 17:04:36', 0.00, '2025-05-10 17:05:13', 'Approved'),
(45, 26, 0, 0, 12250.00, '2025-05-10 17:25:54', 0.00, '2025-05-10 17:26:26', 'Approved'),
(46, 25, 0, 0, 6000.00, '2025-05-10 17:32:52', 0.00, '2025-05-10 17:33:02', 'Approved'),
(56, 27, 0, 0, 18500.00, '2025-05-16 11:37:41', 0.00, '2025-05-16 12:44:42', 'Completed'),
(59, 27, 0, 0, 5250.00, '2025-05-16 12:17:17', 0.00, '2025-05-16 12:47:31', 'Completed'),
(60, 27, 0, 0, 3000.00, '2025-05-16 12:56:36', 0.00, '2025-05-16 12:56:44', 'Completed');

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
(12, 37, 17, 1, 3000.00, '2025-05-03 01:25:15'),
(13, 38, 18, 2, 1000.00, '2025-05-03 14:51:14'),
(14, 37, 19, 1, 3000.00, '2025-05-03 15:37:22'),
(15, 38, 20, 1, 1000.00, '2025-05-03 15:43:03'),
(16, 36, 21, 1, 2000.00, '2025-05-03 17:54:32'),
(17, 35, 22, 1, 25000.00, '2025-05-04 02:36:00'),
(18, 39, 23, 3, 3000.00, '2025-05-04 02:46:50'),
(19, 37, 24, 2, 3000.00, '2025-05-04 02:56:33'),
(20, 38, 25, 1, 1000.00, '2025-05-04 03:00:16'),
(21, 28, 26, 5, 4000.00, '2025-05-04 03:02:58'),
(22, 34, 27, 5, 1000.00, '2025-05-04 03:06:16'),
(23, 37, 28, 2, 3000.00, '2025-05-04 03:09:22'),
(24, 38, 29, 1, 1000.00, '2025-05-09 18:10:19'),
(25, 39, 30, 1, 3000.00, '2025-05-09 18:20:32'),
(26, 37, 31, 1, 3000.00, '2025-05-10 02:05:04'),
(27, 35, 32, 1, 25000.00, '2025-05-10 02:10:45'),
(28, 36, 33, 3, 2000.00, '2025-05-10 02:18:19'),
(29, 28, 34, 9, 4000.00, '2025-05-10 02:19:07'),
(30, 28, 35, 7, 4000.00, '2025-05-10 02:23:22'),
(31, 37, 36, 1, 3000.00, '2025-05-10 02:56:55'),
(32, 28, 37, 1, 4000.00, '2025-05-10 02:59:22'),
(33, 27, 38, 1, 25000.00, '2025-05-10 02:59:33'),
(34, 27, 39, 1, 25000.00, '2025-05-10 11:14:09'),
(35, 36, 40, 15, 2000.00, '2025-05-10 14:11:07'),
(36, 37, 41, 1, 3000.00, '2025-05-10 14:16:02'),
(37, 38, 42, 1, 1000.00, '2025-05-10 14:16:32'),
(38, 31, 42, 1, 25000.00, '2025-05-10 14:16:32'),
(39, 26, 42, 1, 3000.00, '2025-05-10 14:16:32'),
(40, 41, 43, 1, 5000.00, '2025-05-10 16:56:58'),
(41, 44, 44, 10, 20000.00, '2025-05-10 17:04:36'),
(42, 32, 45, 1, 2000.00, '2025-05-10 17:25:54'),
(43, 22, 45, 1, 10000.00, '2025-05-10 17:25:54'),
(44, 41, 46, 1, 5000.00, '2025-05-10 17:32:52'),
(45, 33, 46, 1, 1000.00, '2025-05-10 17:32:52'),
(46, 48, 56, 1, 18500.00, '2025-05-16 11:37:41'),
(47, 49, 0, 1, 5000.00, '2025-05-16 12:07:09'),
(48, 49, 58, 1, 5000.00, '2025-05-16 12:12:54'),
(49, 41, 59, 1, 5000.00, '2025-05-16 12:17:17'),
(50, 39, 60, 1, 3000.00, '2025-05-16 12:56:36');

--
-- Triggers `order_detail`
--
DELIMITER $$
CREATE TRIGGER `CHECK_ORDER_AMOUNT_BEFORE_INSERT` BEFORE INSERT ON `order_detail` FOR EACH ROW BEGIN
    DECLARE available_qty INT;
    SELECT QTY INTO available_qty FROM products WHERE PK_PRODUCT_ID = NEW.FK1_PRODUCT_ID;
    IF NEW.QTY > available_qty THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ordered quantity exceeds available stock!';
    END IF;
END
$$
DELIMITER ;

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
(1, 'cod');

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
(22, 8, 2, 'ELFKS DROID CASING', '-The best cased by elon musk', '-White - Blue combi\r\n-Hard Case', 10000.00, 18, 'Transform your product into a captivating visual experience with 3D product animation!.jpg', '2025-04-26 02:35:46', '2025-04-26 02:35:46'),
(23, 5, 3, 'HyperX RAM 16GB', 'Best for Coding', '-DDRM5\r\n-16GB\r\n', 3000.00, 20, 'The best RAM of 2024_ top memory for your PC.jpg', '2025-04-26 02:36:31', '2025-04-26 02:36:31'),
(24, 19, 4, 'Corsair coolant fan', 'For your cpu ', '-1000mah Fan', 800.00, 20, 'SST-AR04.jpg', '2025-04-26 02:37:25', '2025-04-26 02:37:25'),
(25, 3, 1, 'ROG Strix Z490-E GAMING MOTHERBOARD', 'Best for gaming, coding and editing', '-BLACK\r\n-PURPLE\r\n', 5000.00, 19, 'ROG STRIX Z490-E GAMING _ Motherboards _ ROG Global.jpg', '2025-04-26 02:40:24', '2025-04-26 02:40:24'),
(26, 5, 2, 'T-FORCE Delta 16gb RAM', 'Best for gaming', '-white\r\n-purple\r\n-green\r\n-blue\r\n-pink\r\n-gray', 3000.00, 18, 'p6.png', '2025-04-26 02:41:20', '2025-04-26 02:41:20'),
(27, 2, 3, 'AMD Radeon GPU FidelityFX', 'Best for gaming', '-black\r\n-red', 25000.00, 7, 'p5.png', '2025-04-26 02:43:11', '2025-04-26 02:43:11'),
(28, 3, 4, 'MSI mobo ', 'Best for Gaming...\r\n', '-ALL BLACK', 4000.00, 19, 'p4.png', '2025-04-26 02:43:54', '2025-05-04 03:03:03'),
(29, 1, 1, 'UN Monitor 240hz', 'Best for HD Videos', '-240hz\r\n-1ms\r\n-hdr\r\n-24.5\"', 3000.00, 19, 'p3.png', '2025-04-26 02:45:05', '2025-04-26 02:45:05'),
(30, 19, 2, 'Coolant fans ', 'Good for your eyes', '-RGB', 2000.00, 20, 'p2.png', '2025-04-26 02:45:37', '2025-04-26 02:45:37'),
(31, 2, 3, 'AMD White Graphic Card 2080', 'Better experience for gaming', '-white\r\n-cold', 25000.00, 19, 'p1.png', '2025-04-26 02:46:32', '2025-04-26 02:46:32'),
(32, 13, 3, 'AN Keyboard MC Blue switch', 'Good for typing and gaming', '-Blue switch\r\n-smooth typing\r\n', 2000.00, 18, 'gaming keyboard.jpg', '2025-04-26 02:47:43', '2025-04-26 02:47:43'),
(33, 26, 4, 'The Great Wave of Kanagawa Mousepad', 'Smooth ', '-White, blue lights', 1000.00, 19, 'Flowy Waves Desk Mat, XXL Gaming Mouse Pad, Blue Water Mousepad, Beautiful Nature Desk Mat.jpg', '2025-04-26 02:51:39', '2025-04-26 02:51:39'),
(34, 26, 1, 'Black Mousepad ', 'Smooth for mouse and gaming', '-black', 1000.00, 9, 'DIGSOM Mouse Pad.jpg', '2025-04-26 02:52:11', '2025-05-04 03:10:23'),
(35, 2, 1, 'GEFORCE GTX ', 'Good for gaming', '-32gb RAM', 25000.00, 19, 'db839bf5-d42b-4a59-b48d-6f8f5f3c31fc.jpg', '2025-04-26 02:53:39', '2025-04-26 02:53:39'),
(36, 8, 3, 'RGB Hard Glass Case', 'See through', '-Glass', 2000.00, 0, 'Custom build Gaming PC.jpg', '2025-04-26 02:54:53', '2025-04-26 02:54:53'),
(37, 19, 1, 'Corsair coolant fan', 'Cold and Cool', '-White\r\n', 3000.00, 8, 'Corsair Dominator Platinum RGB Series.jpg', '2025-04-26 02:55:30', '2025-05-04 03:10:02'),
(38, 14, 3, 'A4TECH BLACK MOUSE', 'Good for Valorant', '-black', 1000.00, 11, 'Amazon_com_ Dapesuom Small Mouse Pad 6 x 8 Inch….jpg', '2025-04-26 02:56:30', '2025-05-09 18:13:36'),
(39, 26, 4, 'MSI Router', 'Good for any WIFI', '-FAST ', 3000.00, 4, '977dcc0b-90d6-4ee3-be12-05ac3f3d73be.jpg', '2025-04-26 02:57:19', '2025-05-04 02:55:09'),
(41, 1, 2, 'Asus monitor', 'monitorr', '-black\r\n-red', 5000.00, 18, 'ebb4e37b-74b6-41b8-9719-7b565bec97f5.jpg', '2025-05-10 16:07:21', '2025-05-10 16:07:21'),
(42, 3, 3, 'T-FORCE VULCANZ', 'gaming', 'gaming\r\nblack\r\npaste', 3000.00, 10, '62f7d253-ba88-434e-9ec8-f2d581fb5316.jpg', '2025-05-10 16:10:07', '2025-05-10 16:10:07'),
(44, 21, 2, 'Gaming Pc', 'sadsadsaddsada', 'sdsadsadsaa', 20000.00, 0, 'Der Actina Gaming-PC 5901443414643 ist eine….jpg', '2025-05-10 16:43:46', '2025-05-10 16:43:46'),
(46, 1, 7, 'Samsung Odyssey Neo G9', 'The Samsung Odyssey Neo 57 G9 stretches is an absolute beast at 57 inches, and with the size comes a DUHD resolution of 7680 x 2160. The VA panel with Mini LED ', 'Dual UHD\\r\\n1000R Curved screen\\r\\nQuantum Matrix Technology\\r\\n240Hz Refresh rate', 55000.00, 16, 'ph-odyssey-neo-g9-g95nc-ls57cg952nexxp-546001253.webp', '2025-05-16 03:31:26', '2025-05-16 12:04:23'),
(48, 14, 7, 'TenZ Signature Edition', 'Designed and shaped by TenZ himself, this mouse delivers elite performance with cutting-edge technology built for his playstyle. Tyson \\\"TenZ\\\" Ngo—a world-renowned ex-esports PRO, streamer, and VCT 2021 Masters Reykjavik MVP—is known for his exceptional ', 'Dimension\\r\\nLength: 4.72in (120mm)\\r\\nWidth: 2.52in (64mm)\\r\\nHeight: 1.59in (40.5mm)\\r\\nWeight: ±47g (1.66oz)', 18500.00, 14, 'PulsarTenZsignatureeditionwirelessGamingMouse_01_large.webp', '2025-05-16 03:37:51', '2025-05-16 11:43:09'),
(49, 26, 12, '[Pro Series] PRX Something Gaming Mousepad', 'Collaborating closely with the Pulsar R&D Team, the Paper Rex Art Department, and Ilia \"Something,\" we developed a new surface tailored specifically to his high-sensitivity gameplay needs. The ultra-finely and evenly knitted texture delivers a silky-smoot', 'Speed: ▶︎▶︎▷▷▷▷ Low\r\nStopping: ▶︎▶︎▶︎▶︎▶︎▷ Very High\r\nControl-Focused Base: Firm 4mm Non-Slip Organic Latex\r\nStiff Cushioning: Engineered for Strong Pressing Control\r\nPrecise Microstiching: Optimized Glide for High-Sensitivity Gaming\r\nUltra-Durable 360° Anti-Fray Flush Edges\r\n\r\n', 5000.00, 19, 'PulsarGamingGearsSomethingEditiongamingmousepad_01_large.webp', '2025-05-16 11:42:35', '2025-05-16 11:42:35');

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

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`PK_REVIEW_ID`, `FK1_CUSTOMER_ID`, `FK2_PRODUCT_ID`, `FK3_ORDER_ID`, `RATING`, `COMMENT`, `CREATED_AT`, `IMAGE`) VALUES
(1, 1, 38, 0, 4, 'Nice mouse', '2025-05-03 15:10:29', NULL),
(2, 1, 37, 0, 3, 'makita sa view', '2025-05-03 15:40:14', NULL),
(3, 5, 37, 0, 5, 'NicE!!', '2025-05-03 15:41:49', '1746258109_image-removebg-preview (3) (1).png'),
(6, 1, 36, 0, 4, 'DAMIEN GAHI LUBOT', '2025-05-03 18:13:21', '1746267201_71oSydXEo4S.jpg'),
(7, 8, 35, 0, 5, 'Nice good for gaming ', '2025-05-04 02:36:44', NULL),
(8, 24, 38, 0, 5, 'jarom dancer mo kalit lng redflag', '2025-05-09 18:26:58', NULL),
(9, 24, 27, 0, 5, 'Good for gaming i love it', '2025-05-10 03:28:55', '1746818935_1746266457_0_71oSydXEo4S.jpg'),
(10, 24, 28, 0, 4, 'Good for online class not for gaming', '2025-05-10 03:36:13', '1746819373_681e592db02f8_hero.jpg,1746819373_681e592db050a_hero1.jpg,1746819373_681e592db0628_IG.png'),
(11, 26, 32, 0, 2, 'Disposable ni nga keyboard, baratohon murag insik', '2025-05-10 17:28:53', '1746869333_681f1c555f2b8_351453175_1191126874899419_117306819684368067_n.jpg'),
(12, 27, 49, 0, 5, 'Best Mousepad', '2025-05-16 12:40:25', NULL),
(14, 27, 48, 56, 5, 'I heckin love Tenz', '2025-05-16 12:45:05', NULL),
(15, 27, 41, 59, 1, 'Defective monitor', '2025-05-16 12:47:47', NULL),
(16, 27, 39, 60, 5, 'Nicee router my internet is very fast now', '2025-05-16 12:57:13', NULL);

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
  `UPDATE_AT` datetime NOT NULL,
  `STATUS` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`PK_SUPPLIER_ID`, `FK_USER_ID`, `S_LNAME`, `S_FNAME`, `PHONE_NUM`, `COMPANY_NAME`, `EMAIL`, `SUPPLIER_ADDRESS`, `SUPPLIER_IMAGE`, `CREATE_AT`, `UPDATE_AT`, `STATUS`) VALUES
(1, 0, 'Patinyo', 'Rafael', '+123567890', 'Bakal TT Corp', 'Cthulu@gmail', 'Avocado St. Mambaling', '351453175_1191126874899419_117306819684368067_n.jpg', '2025-04-26 02:18:45', '2025-05-03 17:22:47', 'Inactive'),
(2, 0, 'Caumeran', 'Damien', '+987644123', 'Cow Me Run ', 'damskie@gmail.com', 'V.rama', 'ASDASDSDASA.jpg', '2025-04-26 02:22:20', '2025-04-26 02:22:20', 'Active'),
(3, 0, 'Dagupols', 'Client', '+56892134', 'Try me hack', 'Client@gmai.com', 'Buhisan', '6815bc100aa9e.jpg', '2025-04-26 02:23:42', '2025-04-26 02:23:42', 'Active'),
(4, 0, 'Ancero', 'John Rey', '+565723257', 'JAHH Corp.', 'gwapokoancero123@gmail.com', 'B.rod', '6815bc070d4c6.jpg', '2025-04-26 02:25:00', '2025-04-26 02:25:00', 'Active'),
(6, 1, 'Lahaylahay', 'Bevs', '092445678009', 'COMPUSPEC', 'bevs@1.com', 'CEBU', '467743265_2034794076942966_7629118095982581341_n.jpg', '2025-05-10 17:17:20', '2025-05-10 17:17:20', 'Active'),
(7, 1, 'Pork', 'John', '0932145632146', 'J Pork Techs', 'johnpork@gmail.com', 'Cebu', 'd23687188aabd01cf01a9fc4e5cafd22.webp', '2025-05-16 02:54:19', '2025-05-16 02:54:19', 'Active'),
(12, 1, 'Cheese', 'Tim', '09731976541', 'CheeseTechs', 'TCheese@email.com', 'Cebu', 'Screenshot_1743206173571_29.webp', '2025-05-16 11:39:47', '2025-05-16 11:40:01', 'Active');

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
(1, 'Admin', 'System', 'admin@gmail.com', '$2y$10$IuvaHPz32l.pwIiXENQgIuIDDldeKym450tpqFkOoTl4eT/pwKbhW', 'Compucore HQ', '09123456789', '2025-04-26 01:15:58', '0000-00-00 00:00:00', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`PK_ADMIN_ID`),
  ADD UNIQUE KEY `USERNAME` (`USERNAME`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`);

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
  ADD PRIMARY KEY (`PK_REVIEW_ID`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`PK_SUPPLIER_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`PK_USER_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `PK_CATEGORY_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `PK_CUSTOMER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `PK_NOTIFICATION_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `PK_ORDER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `order_detail`
--
ALTER TABLE `order_detail`
  MODIFY `PK_ORDER_DETAIL_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `PK_PAYMENT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `PK_PRODUCT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `PK_REVIEW_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `PK_SUPPLIER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `PK_USER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
