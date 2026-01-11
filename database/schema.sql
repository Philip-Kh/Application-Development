-- IOMS Database Schema
-- Inventory & Order Management System

CREATE DATABASE IF NOT EXISTS ioms_quickmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ioms_quickmart;

-- Staff Table with Admin Support
CREATE TABLE IF NOT EXISTS staff (
    staff_id VARCHAR(50) PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    is_active TINYINT(1) DEFAULT 1,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(200) NOT NULL,
    category VARCHAR(100) NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    quantity INT DEFAULT 0,
    price DECIMAL(10, 2) NOT NULL,
    min_stock_level INT DEFAULT 10,
    status ENUM('Normal', 'Low Stock') DEFAULT 'Normal',
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(50),
    updated_by VARCHAR(50),
    FOREIGN KEY (created_by) REFERENCES staff(staff_id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES staff(staff_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_type ENUM('Sale', 'Purchase') NOT NULL,
    customer_supplier_name VARCHAR(200) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    order_date DATE NOT NULL,
    staff_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Log Table
CREATE TABLE IF NOT EXISTS activity_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(50),
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id VARCHAR(50),
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT,
    setting_label VARCHAR(200),
    setting_type ENUM('text', 'number', 'boolean', 'email') DEFAULT 'text',
    updated_by VARCHAR(50),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES staff(staff_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_orders_type ON orders(order_type);
CREATE INDEX idx_orders_date ON orders(order_date);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);
CREATE INDEX idx_activity_staff ON activity_log(staff_id);
CREATE INDEX idx_activity_action ON activity_log(action);
CREATE INDEX idx_activity_date ON activity_log(created_at);

-- Insert requested admin user
-- Email: Philipp@gmail.com
-- Password: Philipp1131@@#
INSERT INTO staff (staff_id, full_name, email, password, role) VALUES
('ADMIN001', 'Philipp Administrator', 'Philipp@gmail.com', 
 '$2y$10$zad.YJkTz3mj5w10OjyWdOGmptZUwDLK.yNfYcctCjf44UJKIVuZW', 'admin');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_label, setting_type) VALUES
('site_name', 'Quick Mart', 'Site Name', 'text'),
('low_stock_threshold', '10', 'Low Stock Threshold', 'number'),
('session_timeout', '30', 'Session Timeout (minutes)', 'number'),
('allow_registration', '1', 'Allow Public Registration', 'boolean'),
('admin_email', 'Philipp@gmail.com', 'Admin Email', 'email'),
('max_login_attempts', '5', 'Max Login Attempts', 'number'),
('lockout_duration', '15', 'Lockout Duration (minutes)', 'number');



-- Sample Data for IOMS Database - Mobile Phone Store
-- Additional Staff Members (passwords are hashed versions of 'Password123!')
INSERT INTO staff (staff_id, full_name, email, phone, password, role, is_active) VALUES
('STAFF001', 'Khalid Mohammed', 'khalid.mohammed@phonehub.com', '+962-79-1234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 1),
('STAFF002', 'Sara Abdullah', 'sara.abdullah@phonehub.com', '+962-79-2345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 1),
('STAFF003', 'Youssef Ali', 'youssef.ali@phonehub.com', '+962-79-3456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 1),
('ADMIN002', 'Noor Salem', 'noor.salem@phonehub.com', '+962-79-4567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Sample Products - Mobile Phone Store Categories
INSERT INTO products (product_name, category, image_path, quantity, price, min_stock_level, status, created_by) VALUES
-- Smartphones - Apple
('iPhone 15 Pro Max 256GB Natural Titanium', 'Smartphones - Apple', '/images/products/iphone15promax_256.jpg', 12, 1299.00, 5, 'Normal', 'ADMIN001'),
('iPhone 15 Pro 128GB Blue Titanium', 'Smartphones - Apple', '/images/products/iphone15pro_128.jpg', 8, 999.00, 5, 'Normal', 'ADMIN001'),
('iPhone 15 Plus 128GB Black', 'Smartphones - Apple', '/images/products/iphone15plus_128.jpg', 15, 899.00, 8, 'Normal', 'STAFF001'),
('iPhone 15 128GB Pink', 'Smartphones - Apple', '/images/products/iphone15_128.jpg', 22, 799.00, 10, 'Normal', 'STAFF001'),
('iPhone 14 128GB Midnight', 'Smartphones - Apple', '/images/products/iphone14_128.jpg', 4, 699.00, 8, 'Low Stock', 'STAFF002'),
('iPhone SE (3rd Gen) 64GB Starlight', 'Smartphones - Apple', '/images/products/iphonese3_64.jpg', 18, 429.00, 10, 'Normal', 'STAFF003'),

-- Smartphones - Samsung
('Samsung Galaxy S24 Ultra 512GB Titanium Gray', 'Smartphones - Samsung', '/images/products/s24ultra_512.jpg', 10, 1199.00, 5, 'Normal', 'ADMIN001'),
('Samsung Galaxy S24+ 256GB Marble Gray', 'Smartphones - Samsung', '/images/products/s24plus_256.jpg', 14, 999.00, 8, 'Normal', 'STAFF001'),
('Samsung Galaxy S24 128GB Onyx Black', 'Smartphones - Samsung', '/images/products/s24_128.jpg', 20, 799.00, 10, 'Normal', 'STAFF002'),
('Samsung Galaxy Z Fold5 512GB Phantom Black', 'Smartphones - Samsung', '/images/products/zfold5_512.jpg', 3, 1799.00, 3, 'Low Stock', 'ADMIN001'),
('Samsung Galaxy Z Flip5 256GB Mint', 'Smartphones - Samsung', '/images/products/zflip5_256.jpg', 8, 999.00, 5, 'Normal', 'STAFF001'),
('Samsung Galaxy A54 5G 128GB Awesome Violet', 'Smartphones - Samsung', '/images/products/a54_128.jpg', 35, 449.00, 15, 'Normal', 'STAFF002'),
('Samsung Galaxy A34 5G 128GB Awesome Silver', 'Smartphones - Samsung', '/images/products/a34_128.jpg', 42, 349.00, 20, 'Normal', 'STAFF003'),

-- Smartphones - Other Brands
('Google Pixel 8 Pro 256GB Obsidian', 'Smartphones - Other', '/images/products/pixel8pro_256.jpg', 6, 999.00, 5, 'Normal', 'ADMIN001'),
('Xiaomi 13T Pro 512GB Meadow Green', 'Smartphones - Other', '/images/products/xiaomi13tpro_512.jpg', 12, 649.00, 8, 'Normal', 'STAFF001'),
('OnePlus 12 256GB Flowy Emerald', 'Smartphones - Other', '/images/products/oneplus12_256.jpg', 9, 799.00, 6, 'Normal', 'STAFF002'),
('OPPO Find X6 Pro 256GB Black', 'Smartphones - Other', '/images/products/oppofindx6_256.jpg', 7, 899.00, 5, 'Normal', 'STAFF003'),
('Huawei Pura 70 Ultra 512GB Black', 'Smartphones - Other', '/images/products/huaweipura70_512.jpg', 5, 1099.00, 4, 'Low Stock', 'STAFF001'),

-- Phone Cases
('iPhone 15 Pro Max Silicone Case MagSafe', 'Phone Cases', '/images/products/case_iphone15promax.jpg', 45, 49.00, 20, 'Normal', 'STAFF001'),
('Samsung S24 Ultra Protective Case', 'Phone Cases', '/images/products/case_s24ultra.jpg', 38, 35.00, 20, 'Normal', 'STAFF002'),
('Universal Leather Wallet Case', 'Phone Cases', '/images/products/case_universal_leather.jpg', 65, 25.00, 30, 'Normal', 'STAFF003'),
('Clear Transparent TPU Case Universal', 'Phone Cases', '/images/products/case_clear_tpu.jpg', 125, 8.00, 50, 'Normal', 'STAFF001'),
('Rugged Armor Case with Kickstand', 'Phone Cases', '/images/products/case_rugged_armor.jpg', 55, 22.00, 25, 'Normal', 'STAFF002'),

-- Screen Protectors
('Tempered Glass Screen Protector iPhone 15 Pro', 'Screen Protectors', '/images/products/screen_iphone15pro.jpg', 85, 12.00, 40, 'Normal', 'STAFF001'),
('Tempered Glass Screen Protector Samsung S24', 'Screen Protectors', '/images/products/screen_s24.jpg', 78, 12.00, 40, 'Normal', 'STAFF002'),
('Privacy Screen Protector Universal 6.5"', 'Screen Protectors', '/images/products/screen_privacy.jpg', 52, 18.00, 25, 'Normal', 'STAFF003'),
('Hydrogel Screen Protector Universal', 'Screen Protectors', '/images/products/screen_hydrogel.jpg', 95, 8.00, 45, 'Normal', 'STAFF001'),
('Camera Lens Protector iPhone 15 Pro Max', 'Screen Protectors', '/images/products/screen_camera_lens.jpg', 68, 10.00, 30, 'Normal', 'STAFF002'),

-- Chargers & Cables
('Apple 20W USB-C Power Adapter', 'Chargers & Cables', '/images/products/charger_apple_20w.jpg', 42, 19.00, 20, 'Normal', 'STAFF001'),
('Samsung 25W Super Fast Charger', 'Chargers & Cables', '/images/products/charger_samsung_25w.jpg', 38, 22.00, 20, 'Normal', 'STAFF002'),
('65W GaN Fast Charger Universal', 'Chargers & Cables', '/images/products/charger_65w_gan.jpg', 25, 35.00, 15, 'Normal', 'STAFF003'),
('USB-C to Lightning Cable 1m Apple MFi', 'Chargers & Cables', '/images/products/cable_usbc_lightning.jpg', 6, 19.00, 25, 'Low Stock', 'STAFF001'),
('USB-C to USB-C Cable 2m', 'Chargers & Cables', '/images/products/cable_usbc_usbc.jpg', 72, 12.00, 35, 'Normal', 'STAFF002'),
('Wireless Charging Pad 15W', 'Chargers & Cables', '/images/products/charger_wireless_15w.jpg', 32, 28.00, 15, 'Normal', 'STAFF003'),
('3-in-1 Wireless Charger Stand', 'Chargers & Cables', '/images/products/charger_3in1_stand.jpg', 18, 45.00, 10, 'Normal', 'STAFF001'),
('Car Charger Dual USB-C 45W', 'Chargers & Cables', '/images/products/charger_car_45w.jpg', 45, 18.00, 20, 'Normal', 'STAFF002'),

-- Power Banks
('Power Bank 10000mAh Slim', 'Power Banks', '/images/products/powerbank_10k_slim.jpg', 55, 25.00, 25, 'Normal', 'STAFF001'),
('Power Bank 20000mAh Fast Charge', 'Power Banks', '/images/products/powerbank_20k_fast.jpg', 38, 45.00, 20, 'Normal', 'STAFF002'),
('MagSafe Power Bank 5000mAh', 'Power Banks', '/images/products/powerbank_magsafe_5k.jpg', 28, 39.00, 15, 'Normal', 'STAFF003'),
('Solar Power Bank 30000mAh', 'Power Banks', '/images/products/powerbank_solar_30k.jpg', 15, 55.00, 10, 'Normal', 'STAFF001'),

-- Headphones & Audio
('Apple AirPods Pro (2nd Gen)', 'Headphones & Audio', '/images/products/airpods_pro2.jpg', 22, 249.00, 10, 'Normal', 'ADMIN001'),
('Apple AirPods (3rd Gen)', 'Headphones & Audio', '/images/products/airpods_3rd.jpg', 28, 169.00, 12, 'Normal', 'STAFF001'),
('Samsung Galaxy Buds2 Pro', 'Headphones & Audio', '/images/products/buds2_pro.jpg', 18, 229.00, 10, 'Normal', 'STAFF002'),
('Sony WH-1000XM5 Wireless Headphones', 'Headphones & Audio', '/images/products/sony_xm5.jpg', 8, 399.00, 5, 'Normal', 'ADMIN001'),
('JBL Tune 760NC Wireless', 'Headphones & Audio', '/images/products/jbl_760nc.jpg', 25, 129.00, 12, 'Normal', 'STAFF003'),
('Anker Soundcore Life P3', 'Headphones & Audio', '/images/products/anker_p3.jpg', 42, 79.00, 20, 'Normal', 'STAFF001'),
('Wired Earphones USB-C', 'Headphones & Audio', '/images/products/earphones_usbc.jpg', 85, 15.00, 40, 'Normal', 'STAFF002'),

-- Smartwatches & Wearables
('Apple Watch Series 9 GPS 45mm', 'Smartwatches', '/images/products/applewatch_s9_45.jpg', 12, 429.00, 6, 'Normal', 'ADMIN001'),
('Apple Watch SE (2nd Gen) 40mm', 'Smartwatches', '/images/products/applewatch_se2_40.jpg', 15, 249.00, 8, 'Normal', 'STAFF001'),
('Samsung Galaxy Watch6 Classic 43mm', 'Smartwatches', '/images/products/galaxywatch6_43.jpg', 10, 399.00, 6, 'Normal', 'STAFF002'),
('Xiaomi Smart Band 8', 'Smartwatches', '/images/products/miband8.jpg', 45, 39.00, 20, 'Normal', 'STAFF003'),
('Fitbit Charge 6', 'Smartwatches', '/images/products/fitbit_charge6.jpg', 22, 159.00, 10, 'Normal', 'STAFF001'),

-- Memory & Storage
('SanDisk microSDXC 256GB', 'Memory & Storage', '/images/products/sandisk_256gb.jpg', 65, 35.00, 30, 'Normal', 'STAFF001'),
('Samsung microSDXC 512GB Pro Plus', 'Memory & Storage', '/images/products/samsung_512gb.jpg', 38, 65.00, 20, 'Normal', 'STAFF002'),
('USB-C Flash Drive 128GB', 'Memory & Storage', '/images/products/usbc_flash_128.jpg', 52, 22.00, 25, 'Normal', 'STAFF003'),
('Portable SSD 1TB USB-C', 'Memory & Storage', '/images/products/portable_ssd_1tb.jpg', 15, 89.00, 10, 'Normal', 'STAFF001'),

-- Phone Holders & Mounts
('Car Phone Mount Magnetic', 'Phone Holders', '/images/products/mount_car_magnetic.jpg', 48, 18.00, 20, 'Normal', 'STAFF002'),
('Desktop Phone Stand Adjustable', 'Phone Holders', '/images/products/stand_desktop.jpg', 62, 12.00, 30, 'Normal', 'STAFF003'),
('Tripod Phone Mount Flexible', 'Phone Holders', '/images/products/tripod_flexible.jpg', 35, 25.00, 15, 'Normal', 'STAFF001'),
('Ring Light with Phone Holder', 'Phone Holders', '/images/products/ringlight_holder.jpg', 28, 45.00, 12, 'Normal', 'STAFF002'),

-- Phone Tools & Accessories
('SIM Card Ejector Tool Set', 'Tools & Accessories', '/images/products/sim_ejector_set.jpg', 125, 3.00, 50, 'Normal', 'STAFF001'),
('Phone Cleaning Kit', 'Tools & Accessories', '/images/products/cleaning_kit.jpg', 75, 8.00, 35, 'Normal', 'STAFF002'),
('Anti-Dust Plugs Set', 'Tools & Accessories', '/images/products/dust_plugs.jpg', 95, 5.00, 45, 'Normal', 'STAFF003'),
('Phone Repair Tool Kit', 'Tools & Accessories', '/images/products/repair_toolkit.jpg', 22, 35.00, 12, 'Normal', 'STAFF001'),
('PopSocket Phone Grip', 'Tools & Accessories', '/images/products/popsocket.jpg', 88, 8.00, 40, 'Normal', 'STAFF002'),
('Phone Lanyard Strap', 'Tools & Accessories', '/images/products/phone_lanyard.jpg', 105, 6.00, 50, 'Normal', 'STAFF003');

-- Sample Orders - Mix of Sales and Purchases
-- Recent Sales (January 2025)
INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Mohammed Al-Khateeb', 2598.00, '2025-01-03', 'STAFF001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 1, 2, 1299.00, 2598.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Layla Hussein', 1199.00, '2025-01-03', 'STAFF002');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 7, 1, 1199.00, 1199.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Mobile Solutions LLC', 1347.00, '2025-01-03', 'STAFF001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 12, 3, 449.00, 1347.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Tech Accessories Store', 80.00, '2025-01-03', 'STAFF003');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 19, 10, 8.00, 80.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Ahmad Saleh', 95.00, '2025-01-02', 'STAFF002');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 28, 5, 19.00, 95.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Sara Mahmoud', 498.00, '2025-01-02', 'STAFF001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 40, 2, 249.00, 498.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'University Bookstore', 3995.00, '2025-01-02', 'STAFF003');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 4, 5, 799.00, 3995.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Hala Rashid', 747.00, '2025-01-01', 'STAFF001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 50, 3, 249.00, 747.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Phone Repair Center', 176.00, '2025-01-01', 'STAFF002');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 24, 8, 22.00, 176.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Electronics Wholesale', 1185.00, '2025-01-01', 'STAFF003');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 35, 15, 79.00, 1185.00);

-- December 2024 Sales
INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Corporate Gift Order', 2997.00, '2024-12-31', 'STAFF001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 2, 3, 999.00, 2997.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Retail Electronics Chain', 1796.00, '2024-12-30', 'STAFF002');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 13, 4, 449.00, 1796.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Case Distributor', 500.00, '2024-12-29', 'STAFF003');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 20, 20, 25.00, 500.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Accessories Boutique', 336.00, '2024-12-28', 'STAFF001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 32, 12, 28.00, 336.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Waleed Othman', 798.00, '2024-12-27', 'STAFF002');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 45, 2, 399.00, 798.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Smart Gadgets Store', 210.00, '2024-12-26', 'STAFF003');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 54, 6, 35.00, 210.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Sale', 'Mobile Retailer Group', 6392.00, '2024-12-25', 'STAFF001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 9, 8, 799.00, 6392.00);

-- Recent Purchases (Stock Replenishment)
INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Apple Official Distributor', 20970.00, '2025-01-02', 'ADMIN001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 5, 30, 699.00, 20970.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Samsung Middle East', 39960.00, '2025-01-02', 'ADMIN001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 8, 40, 999.00, 39960.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Xiaomi Regional Supplier', 16225.00, '2025-01-01', 'ADMIN002');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 14, 25, 649.00, 16225.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Cable Solutions Wholesale', 1900.00, '2025-01-01', 'ADMIN001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 30, 100, 19.00, 1900.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Audio Equipment Distributor', 12450.00, '2024-12-31', 'ADMIN002');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 40, 50, 249.00, 12450.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Phone Accessories Supplier', 1600.00, '2024-12-31', 'ADMIN001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 19, 200, 8.00, 1600.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Screen Protection Wholesale', 1800.00, '2024-12-30', 'ADMIN001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 26, 150, 12.00, 1800.00);

-- December Purchases
INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Apple Official Distributor', 64950.00, '2024-12-20', 'ADMIN001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 1, 50, 1299.00, 64950.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Samsung Middle East', 35970.00, '2024-12-21', 'ADMIN001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 7, 30, 1199.00, 35970.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Power Solutions International', 2500.00, '2024-12-22', 'ADMIN002');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 36, 100, 25.00, 2500.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Audio Equipment Distributor', 19920.00, '2024-12-23', 'ADMIN001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 43, 80, 249.00, 19920.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Wearables Technology Supplier', 17160.00, '2024-12-24', 'ADMIN002');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 51, 40, 429.00, 17160.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Storage Media Wholesale', 2100.00, '2024-12-25', 'ADMIN001');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 55, 60, 35.00, 2100.00);

INSERT INTO orders (order_type, customer_supplier_name, total_amount, order_date, staff_id) VALUES
('Purchase', 'Accessories Manufacturing Co', 1200.00, '2024-12-26', 'ADMIN002');
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(LAST_INSERT_ID(), 62, 150, 8.00, 1200.00);

-- Sample Activity Log
INSERT INTO activity_log (staff_id, action, entity_type, entity_id, details, ip_address) VALUES
('ADMIN001', 'Login', 'staff', 'ADMIN001', 'Successful admin login', '192.168.1.100'),
('ADMIN001', 'Create Product', 'product', '1', 'Added: iPhone 15 Pro Max 256GB', '192.168.1.100'),
('ADMIN001', 'Create Product', 'product', '7', 'Added: Samsung Galaxy S24 Ultra 512GB', '192.168.1.100'),
('STAFF001', 'Login', 'staff', 'STAFF001', 'Successful login', '192.168.1.101'),
('STAFF001', 'Create Order', 'order', '1', 'Sale: Mohammed Al-Khateeb', '192.168.1.101'),
('STAFF002', 'Login', 'staff', 'STAFF002', 'Successful login', '192.168.1.102'),
('STAFF002', 'Create Order', 'order', '2', 'Sale: Layla Hussein', '192.168.1.102'),
('STAFF002', 'Update Product', 'product', '5', 'Updated stock: iPhone 14 128GB', '192.168.1.102'),
('ADMIN001', 'Create Order', 'order', '18', 'Purchase: Apple Official Distributor', '192.168.1.100'),
('STAFF003', 'Login', 'staff', 'STAFF003', 'Successful login', '192.168.1.103'),
('STAFF003', 'Create Order', 'order', '4', 'Sale: Tech Accessories Store', '192.168.1.103'),
('ADMIN001', 'Update Settings', 'settings', 'low_stock_threshold', 'Updated threshold to 5', '192.168.1.100'),
('STAFF001', 'Create Order', 'order', '7', 'Sale: University Bookstore', '192.168.1.101'),
('ADMIN002', 'Login', 'staff', 'ADMIN002', 'Successful admin login', '192.168.1.104'),
('ADMIN002', 'Create Order', 'order', '20', 'Purchase: Xiaomi Regional Supplier', '192.168.1.104'),
('STAFF002', 'Update Product', 'product', '30', 'Stock replenishment completed', '192.168.1.102'),
('STAFF003', 'Create Order', 'order', '10', 'Sale: Electronics Wholesale', '192.168.1.103'),
('STAFF001', 'Update Product', 'product', '10', 'Updated status: Low Stock alert', '192.168.1.101'),
('STAFF002', 'Create Order', 'order', '13', 'Sale: Retail Electronics Chain', '192.168.1.102'),
('ADMIN001', 'Create Order', 'order', '25', 'Purchase: Samsung Middle East', '192.168.1.100');

-- Summary Query to Verify Data
SELECT 
    'Total Staff Members' as Metric, COUNT(*) as Count 
FROM staff
UNION ALL
SELECT 'Total Products', COUNT(*) 
FROM products WHERE is_deleted = 0
UNION ALL
SELECT 'Products - Smartphones Apple', COUNT(*) 
FROM products WHERE category = 'Smartphones - Apple' AND is_deleted = 0
UNION ALL
SELECT 'Products - Smartphones Samsung', COUNT(*) 
FROM products WHERE category = 'Smartphones - Samsung' AND is_deleted = 0
UNION ALL
SELECT 'Products - Smartphones Other', COUNT(*) 
FROM products WHERE category = 'Smartphones - Other' AND is_deleted = 0
UNION ALL
SELECT 'Products - Phone Cases', COUNT(*) 
FROM products WHERE category = 'Phone Cases' AND is_deleted = 0
UNION ALL
SELECT 'Products - Screen Protectors', COUNT(*) 
FROM products WHERE category = 'Screen Protectors' AND is_deleted = 0
UNION ALL
SELECT 'Products - Chargers & Cables', COUNT(*) 
FROM products WHERE category = 'Chargers & Cables' AND is_deleted = 0
UNION ALL
SELECT 'Products - Power Banks', COUNT(*) 
FROM products WHERE category = 'Power Banks' AND is_deleted = 0
UNION ALL
SELECT 'Products - Headphones & Audio', COUNT(*) 
FROM products WHERE category = 'Headphones & Audio' AND is_deleted = 0
UNION ALL
SELECT 'Products - Smartwatches', COUNT(*) 
FROM products WHERE category = 'Smartwatches' AND is_deleted = 0
UNION ALL
SELECT 'Products - Memory & Storage', COUNT(*) 
FROM products WHERE category = 'Memory & Storage' AND is_deleted = 0
UNION ALL
SELECT 'Products - Phone Holders', COUNT(*) 
FROM products WHERE category = 'Phone Holders' AND is_deleted = 0
UNION ALL
SELECT 'Products - Tools & Accessories', COUNT(*) 
FROM products WHERE category = 'Tools & Accessories' AND is_deleted = 0
UNION ALL
SELECT 'Low Stock Products', COUNT(*) 
FROM products WHERE status = 'Low Stock' AND is_deleted = 0
UNION ALL
SELECT 'Total Sales Orders', COUNT(*) 
FROM orders WHERE order_type = 'Sale'
UNION ALL
SELECT 'Total Purchase Orders', COUNT(*) 
FROM orders WHERE order_type = 'Purchase'
UNION ALL
SELECT 'Activity Log Entries', COUNT(*) 
FROM activity_log;