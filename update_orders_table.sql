-- Update orders table structure
ALTER TABLE `orders` 
DROP COLUMN `FK2_PAYMENT_ID`,
DROP COLUMN `FK3_USER_ID`,
DROP COLUMN `LINE_TOTAL`,
ADD COLUMN `shipping_cost` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `TOTAL_PRICE`,
ADD COLUMN `payment_method` varchar(50) NOT NULL AFTER `SHIPPING_METHOD`;

-- Update order_detail table name to order_items for consistency
RENAME TABLE `order_detail` TO `order_items`;

-- Update order_items table structure
ALTER TABLE `order_items` 
DROP COLUMN `PK_ORDER_DETAIL_ID`,
DROP COLUMN `CREATED_AT`,
CHANGE `FK1_PRODUCT_ID` `product_id` int(11) NOT NULL,
CHANGE `FK2_ORDER_ID` `order_id` int(11) NOT NULL,
CHANGE `QTY` `quantity` int(11) NOT NULL,
CHANGE `PRICE` `price` decimal(10,2) NOT NULL;

-- Add primary key to order_items
ALTER TABLE `order_items` 
ADD PRIMARY KEY (`order_id`, `product_id`); 