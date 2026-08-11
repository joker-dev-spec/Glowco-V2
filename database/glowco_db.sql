-- --- glowco_db.sql ---
CREATE DATABASE IF NOT EXISTS glowco_db;
USE glowco_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image_path VARCHAR(255) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    paystack_ref VARCHAR(100) DEFAULT NULL,
    shipping_name VARCHAR(150) DEFAULT NULL,
    shipping_phone VARCHAR(30) DEFAULT NULL,
    shipping_address VARCHAR(255) DEFAULT NULL,
    shipping_city VARCHAR(100) DEFAULT NULL,
    shipping_state VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist_item (user_id, product_id)
) ENGINE=InnoDB;

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin. Password: REDACTED  (change this on first login!)
INSERT INTO users (name, email, password_hash, role)
VALUES ('Admin', 'admin@example.com', '$2y$12$REDACTED-ROTATE-ON-DEPLOY', 'admin');

-- Sample products so the shop isn't empty on first run
-- (image_path values reference the bundled files in uploads/)
INSERT INTO products (name, description, price, stock, image_path, category) VALUES
('Velvet Shea Body Cream', 'Ultra-rich whipped shea butter cream for deep, lasting moisture. 100% natural butters.', 8500.00, 25, 'uploads/prod_6a4d2a306f7096.74274017.webp', 'Body Lotion'),
('Nectar Glow Body Lotion', 'Lightweight daily lotion with vitamin E and jojoba oil for a soft, radiant finish.', 7500.00, 40, 'uploads/prod_6a4d2b0f801252.45130953.jpg', 'Body Lotion'),
('Midnight Oud Perfume Oil', 'Warm, long-lasting oud and amber fragrance oil. 15ml roll-on.', 12000.00, 15, 'uploads/prod_6a4d2c089c6b54.43595501.jpg', 'Perfume'),
('Rose Nectar Perfume Oil', 'Soft rose and vanilla blend — romantic, everyday luxury. 15ml roll-on.', 11500.00, 20, 'uploads/prod_6a4d2c28e55f19.30530367.jpg', 'Perfume'),
('Coconut Cloud Body Butter', 'Whipped coconut & cocoa butter for extra-dry skin. Melts on contact.', 9800.00, 18, 'uploads/prod_6a4d2c5572bd00.62536518.webp', 'Body Lotion'),
('Amber Nights Perfume Oil', 'Seductive amber, sandalwood and musk. Perfect for evenings out. 15ml roll-on.', 13500.00, 10, 'uploads/prod_6a4d2c74ecb9d5.41885227.webp', 'Perfume');
