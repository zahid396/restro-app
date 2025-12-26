-- MySQL Database Schema for Restaurant App
-- Run this script to create all required tables

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Restaurants table
CREATE TABLE IF NOT EXISTS restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(255) NOT NULL,
    name_bn VARCHAR(255),
    tagline_en VARCHAR(500),
    tagline_bn VARCHAR(500),
    logo_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login attempts table for rate limiting
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(100),
    success TINYINT(1) DEFAULT 0,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempt_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id VARCHAR(50) PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    name_bn VARCHAR(255),
    icon VARCHAR(100) DEFAULT 'category',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu items table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    category_id VARCHAR(50),
    name_en VARCHAR(255) NOT NULL,
    name_bn VARCHAR(255),
    description_en TEXT,
    description_bn TEXT,
    price INT NOT NULL DEFAULT 0,
    image_url VARCHAR(500),
    weight VARCHAR(50),
    allergens VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    is_trending TINYINT(1) DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 0.0,
    likes INT DEFAULT 0,
    tags JSON,
    mood JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Restaurant tables
CREATE TABLE IF NOT EXISTS restaurant_tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    table_number INT NOT NULL,
    capacity INT DEFAULT 4,
    status ENUM('available', 'busy', 'reserved') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_table (restaurant_id, table_number),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    table_id INT,
    table_number INT NOT NULL,
    status ENUM('received', 'cooking', 'ready', 'delivered', 'cancelled') DEFAULT 'received',
    total_amount INT NOT NULL DEFAULT 0,
    payment_method VARCHAR(50) DEFAULT 'cash',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price INT NOT NULL,
    special_instructions TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment methods table
CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    name_bn VARCHAR(255),
    method_type ENUM('cash', 'card', 'mobile', 'bank', 'other') DEFAULT 'other',
    account_number VARCHAR(100),
    account_name VARCHAR(255),
    instructions TEXT,
    icon VARCHAR(100) DEFAULT 'payments',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    menu_item_id INT,
    user_name VARCHAR(255),
    avatar VARCHAR(10) DEFAULT '😊',
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_visible TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Games rewards table
CREATE TABLE IF NOT EXISTS games_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    name_bn VARCHAR(255),
    description_en TEXT,
    description_bn TEXT,
    image_url VARCHAR(500),
    reward_type ENUM('discount', 'free_item', 'points', 'coupon') DEFAULT 'discount',
    reward_value INT DEFAULT 0,
    probability DECIMAL(3,2) DEFAULT 0.10,
    expires_in INT DEFAULT 15,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User rewards (won rewards)
CREATE TABLE IF NOT EXISTS user_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reward_id INT NOT NULL,
    session_id VARCHAR(255),
    code VARCHAR(20) UNIQUE,
    is_claimed TINYINT(1) DEFAULT 0,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reward_id) REFERENCES games_rewards(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bill splits table
CREATE TABLE IF NOT EXISTS bill_splits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    member_count INT DEFAULT 1,
    per_person_amount INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default data
INSERT INTO restaurants (id, name_en, name_bn, tagline_en, tagline_bn) VALUES
(1, 'Restaurant', 'রেস্টুরেন্ট', 'Delicious food, delivered fresh', 'সুস্বাদু খাবার, তাজা পরিবেশন')
ON DUPLICATE KEY UPDATE name_en = VALUES(name_en);

INSERT INTO admin_users (restaurant_id, username, password_hash, name) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User')
ON DUPLICATE KEY UPDATE username = VALUES(username);
-- Default password is: password
