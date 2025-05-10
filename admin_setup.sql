
-- Create users table if it doesn't exist
CREATE TABLE IF NOT EXISTS users (
    PK_USER_ID INT(11) AUTO_INCREMENT PRIMARY KEY,
    L_NAME VARCHAR(30) NOT NULL,
    F_NAME VARCHAR(30) NOT NULL,
    EMAIL VARCHAR(255) NOT NULL,
    PASSWORD_HASH VARCHAR(255) NOT NULL,
    ADDRESS VARCHAR(255) NOT NULL,
    PHONE_NUM CHAR(15) NOT NULL,
    CREATED_AT DATETIME DEFAULT CURRENT_TIMESTAMP,
    UPDATE_AT DATETIME,
    IS_ADMIN TINYINT(1) DEFAULT 0,
    INDEX (EMAIL)
);

-- Insert default admin user
INSERT INTO users (
    F_NAME, 
    L_NAME, 
    EMAIL, 
    PASSWORD_HASH,
    ADDRESS,
    PHONE_NUM,
    IS_ADMIN,
    UPDATE_AT
) VALUES (
    'Admin',
    'User',
    'Admin@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Default password: Admin@123
    'CompuCore Admin Office',
    '09123456789',
    1,
    CURRENT_TIMESTAMP
);