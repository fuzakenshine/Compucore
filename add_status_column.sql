-- Add STATUS column if it doesn't exist
ALTER TABLE PRODUCTS ADD COLUMN IF NOT EXISTS STATUS ENUM('available', 'unavailable') DEFAULT 'available';

-- Update existing products to have a default status
UPDATE PRODUCTS SET STATUS = 'available' WHERE STATUS IS NULL; 