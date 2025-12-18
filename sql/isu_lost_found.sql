-- ================================================================
-- ISU Lost & Found Database Schema
-- Version: 1.0.0
-- Database: MySQL 5.7+ / MariaDB 10.2+
-- ================================================================
-- 
-- TABLES OVERVIEW:
-- 1. users          - Registered students, staff, and administrators
-- 2. categories     - Predefined item categories (Electronics, Books, etc.)
-- 3. lost_items     - Items reported as lost by users
-- 4. found_items    - Items reported as found by users
-- 5. activity_log   - Audit trail for admin monitoring
--
-- RELATIONSHIPS:
-- - users (1) → (N) lost_items    [user_id FK, CASCADE]
-- - users (1) → (N) found_items   [user_id FK, CASCADE]
-- - users (1) → (N) activity_log  [user_id FK, SET NULL]
-- - categories (1) → (N) lost_items  [category_id FK, CASCADE]
-- - categories (1) → (N) found_items [category_id FK, CASCADE]
--
-- INDEXES:
-- - Primary keys on all tables (id)
-- - Foreign key indexes for JOINs
-- - FULLTEXT indexes for search functionality
-- - Status/date indexes for filtering
--
-- Run this file in phpMyAdmin or MySQL CLI to create the database structure
-- ================================================================

DROP DATABASE IF EXISTS isu_lost_found;
CREATE DATABASE isu_lost_found CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE isu_lost_found;

-- Users table: stores registered students, staff, and admins
CREATE TABLE users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(30) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    student_staff_id VARCHAR(20) NOT NULL,
    phone VARCHAR(15),
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_is_admin (is_admin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table: predefined item categories
CREATE TABLE categories (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    icon_class VARCHAR(50),
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO categories (name, icon_class) VALUES
('Electronics', 'electronics'),
('Books & Documents', 'documents'),
('Clothing & Accessories', 'accessories'),
('Bags & Wallets', 'bags'),
('Keys & Cards', 'keys'),
('Jewelry', 'jewelry'),
('Sports Equipment', 'sports'),
('Others', 'others');

-- Lost items table: items reported as lost by users
CREATE TABLE lost_items (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NOT NULL,
    category_id INT(11) UNSIGNED NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    description TEXT,
    date_lost DATE NOT NULL,
    location VARCHAR(100) NOT NULL,
    image_path VARCHAR(255),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(15),
    status ENUM('active', 'claimed') DEFAULT 'active',
    is_deleted TINYINT(1) DEFAULT 0,
    date_posted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    views INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_category_id (category_id),
    INDEX idx_date_lost (date_lost),
    INDEX idx_user_id (user_id),
    INDEX idx_is_deleted (is_deleted),
    FULLTEXT idx_search (item_name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Found items table: items reported as found by users
CREATE TABLE found_items (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NOT NULL,
    category_id INT(11) UNSIGNED NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    description TEXT,
    date_found DATE NOT NULL,
    location VARCHAR(100) NOT NULL,
    image_path VARCHAR(255),
    returned_to VARCHAR(100),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(15),
    status ENUM('active', 'claimed') DEFAULT 'active',
    is_deleted TINYINT(1) DEFAULT 0,
    date_posted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    views INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_category_id (category_id),
    INDEX idx_date_found (date_found),
    INDEX idx_user_id (user_id),
    INDEX idx_is_deleted (is_deleted),
    FULLTEXT idx_search (item_name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity log table: track user actions for admin monitoring
CREATE TABLE activity_log (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create default admin account (password: Admin12345)
-- Password is hashed using password_hash() with PASSWORD_DEFAULT
INSERT INTO users (full_name, username, email, student_staff_id, password, is_admin, is_active)
VALUES (
    'System Administrator',
    'admin',
    'admin@isu.edu.ph',
    'ADMIN-001',
    '$2y$10$itaIerQ1umwTuz8gPQ1nfuv7vmO5pE4/xn0ytsRAkClw6B7f2oLfm',
    1,
    1
);

-- Insert dummy users for testing (password: password123)
INSERT INTO users (full_name, username, email, student_staff_id, phone, password, is_admin, is_active) VALUES
('Juan Dela Cruz', 'juan_dela_cruz', 'juan.delacruz@isu.edu.ph', '2021001', '09123456789', '$2y$10$PlOCMNOcxBI/yBuXEQisRul8HoVnRKqbarOrZiBAj2Y6JvyiHF0X6', 0, 1),
('Maria Santos', 'maria_santos', 'maria.santos@isu.edu.ph', '2021002', '09187654321', '$2y$10$PlOCMNOcxBI/yBuXEQisRul8HoVnRKqbarOrZiBAj2Y6JvyiHF0X6', 0, 1),
('Pedro Reyes', 'pedro_reyes', 'pedro.reyes@isu.edu.ph', '2021003', '09234567890', '$2y$10$PlOCMNOcxBI/yBuXEQisRul8HoVnRKqbarOrZiBAj2Y6JvyiHF0X6', 0, 1),
('Ana Garcia', 'ana_garcia', 'ana.garcia@isu.edu.ph', '2021004', '09345678901', '$2y$10$PlOCMNOcxBI/yBuXEQisRul8HoVnRKqbarOrZiBAj2Y6JvyiHF0X6', 0, 1),
('Carlos Mendoza', 'carlos_mendoza', 'carlos.mendoza@isu.edu.ph', '2021005', '09456789012', '$2y$10$PlOCMNOcxBI/yBuXEQisRul8HoVnRKqbarOrZiBAj2Y6JvyiHF0X6', 0, 1),
('Luz Fernandez', 'luz_fernandez', 'luz.fernandez@isu.edu.ph', '2021006', '09567890123', '$2y$10$PlOCMNOcxBI/yBuXEQisRul8HoVnRKqbarOrZiBAj2Y6JvyiHF0X6', 0, 1),
('Miguel Torres', 'miguel_torres', 'miguel.torres@isu.edu.ph', '2021007', '09678901234', '$2y$10$PlOCMNOcxBI/yBuXEQisRul8HoVnRKqbarOrZiBAj2Y6JvyiHF0X6', 0, 1),
('Rosa Castillo', 'rosa_castillo', 'rosa.castillo@isu.edu.ph', '2021008', '09789012345', '$2y$10$PlOCMNOcxBI/yBuXEQisRul8HoVnRKqbarOrZiBAj2Y6JvyiHF0X6', 0, 1);

-- Insert dummy lost items with embedded images
INSERT INTO lost_items (user_id, category_id, item_name, description, date_lost, location, image_path, contact_email, contact_phone, status) VALUES
(2, 1, 'iPhone 12 Pro Max', 'Black iPhone 12 Pro Max with cracked screen protector. Has a red phone case with "ISU" engraved. Last seen in the library.', '2024-11-20', 'University Library', 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400&h=300&fit=crop', 'maria.santos@isu.edu.ph', '09187654321', 'active'),
(3, 1, 'MacBook Air M1', 'Silver MacBook Air M1 13-inch. Has stickers from various tech conferences. Contains important school projects.', '2024-11-18', 'Computer Laboratory', 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=400&h=300&fit=crop', 'pedro.reyes@isu.edu.ph', '09234567890', 'active'),
(4, 4, 'Black Leather Wallet', 'Brown leather wallet containing ID cards, ATM cards, and some cash. Has a name engraved inside.', '2024-11-15', 'Cafeteria', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=300&fit=crop', 'ana.garcia@isu.edu.ph', '09345678901', 'active'),
(5, 5, 'Student ID Card', 'ISU Student ID card with photo. Name: Carlos Mendoza, Student ID: 2021005', '2024-11-22', 'Gymnasium', 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=400&h=300&fit=crop', 'carlos.mendoza@isu.edu.ph', '09456789012', 'active'),
(6, 2, 'Calculus Textbook', 'Green hardcover Calculus textbook by James Stewart. Has notes and highlights throughout.', '2024-11-19', 'Mathematics Building', 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=400&h=300&fit=crop', 'luz.fernandez@isu.edu.ph', '09567890123', 'active'),
(7, 3, 'Blue Hoodie', 'Navy blue ISU hoodie with white logo on front. Size Medium. Comfortable and warm.', '2024-11-17', 'Basketball Court', 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=400&h=300&fit=crop', 'miguel.torres@isu.edu.ph', '09678901234', 'active'),
(8, 1, 'Wireless Earbuds', 'White Apple AirPods Pro in original case. Excellent sound quality, noise cancellation.', '2024-11-21', 'Auditorium', 'https://images.unsplash.com/photo-1572569511254-d8f925fe2cbb?w=400&h=300&fit=crop', 'rosa.castillo@isu.edu.ph', '09789012345', 'active'),
(2, 6, 'Silver Necklace', 'Delicate silver necklace with heart pendant. Very sentimental value.', '2024-11-16', 'Student Center', 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=400&h=300&fit=crop', 'maria.santos@isu.edu.ph', '09187654321', 'active'),
(3, 7, 'Basketball', 'Wilson NBA basketball, size 7. Used for PE classes. Has some wear but still playable.', '2024-11-14', 'Sports Complex', 'https://images.unsplash.com/photo-1574623452334-1e0ac2b3ccb4?w=400&h=300&fit=crop', 'pedro.reyes@isu.edu.ph', '09234567890', 'active'),
(4, 4, 'Canvas Backpack', 'Gray canvas backpack with multiple compartments. Perfect for carrying books and laptop.', '2024-11-23', 'Engineering Building', 'https://m.media-amazon.com/images/I/81zQbHL0N6L._AC_UY350_.jpg', 'ana.garcia@isu.edu.ph', '09345678901', 'active');

-- Insert dummy found items with embedded images
INSERT INTO found_items (user_id, category_id, item_name, description, date_found, location, image_path, contact_email, contact_phone, status) VALUES
(3, 1, 'Samsung Galaxy S21', 'Blue Samsung Galaxy S21 found on the ground. Screen is cracked but phone seems to work.', '2024-11-20', 'Parking Lot', 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=400&h=300&fit=crop', 'pedro.reyes@isu.edu.ph', '09234567890', 'active'),
(4, 5, 'Door Keys', 'Set of 3 keys on a keychain with a small flashlight. Found near the main entrance.', '2024-11-19', 'Main Entrance', 'https://images.unsplash.com/photo-1633265486064-086b219458ec?w=400&h=300&fit=crop', 'ana.garcia@isu.edu.ph', '09345678901', 'active'),
(5, 2, 'Physics Textbook', 'Thick blue Physics textbook. Has some water damage on the cover but pages are readable.', '2024-11-18', 'Library Steps', 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=400&h=300&fit=crop', 'carlos.mendoza@isu.edu.ph', '09456789012', 'active'),
(6, 3, 'Red Scarf', 'Wool red scarf, very soft and warm. No identifying marks.', '2024-11-17', 'Cafeteria', 'https://images.unsplash.com/photo-1520903920243-00d872a2d1c9?w=400&h=300&fit=crop', 'luz.fernandez@isu.edu.ph', '09567890123', 'active'),
(7, 1, 'Power Bank', 'Black 10000mAh power bank with USB-C and USB-A ports. Still has charge.', '2024-11-16', 'Computer Lab', 'https://images.unsplash.com/photo-1625842268584-8f3296236761?w=400&h=300&fit=crop', 'miguel.torres@isu.edu.ph', '09678901234', 'active'),
(8, 4, 'Brown Leather Belt', 'Genuine leather belt, size 32. Has a silver buckle. Good condition.', '2024-11-15', 'Restroom', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=300&fit=crop', 'rosa.castillo@isu.edu.ph', '09789012345', 'active'),
(2, 6, 'Gold Earrings', 'Pair of small gold stud earrings. Very delicate and shiny.', '2024-11-14', 'Classroom 101', 'https://images.unsplash.com/photo-1596944924616-7b38e7cfac36?w=400&h=300&fit=crop', 'maria.santos@isu.edu.ph', '09187654321', 'active'),
(3, 7, 'Tennis Racket', 'Wilson tennis racket, adult size. Strings are a bit loose but still usable.', '2024-11-13', 'Tennis Court', 'https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?w=400&h=300&fit=crop', 'pedro.reyes@isu.edu.ph', '09234567890', 'active'),
(4, 1, 'Bluetooth Speaker', 'Small portable Bluetooth speaker. Black color, good sound quality.', '2024-11-12', 'Auditorium', 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=400&h=300&fit=crop', 'ana.garcia@isu.edu.ph', '09345678901', 'active'),
(5, 2, 'Notebook', 'Spiral notebook with math notes and drawings. About half filled.', '2024-11-11', 'Mathematics Building', 'https://images.unsplash.com/photo-1531346878377-a5be20888e57?w=400&h=300&fit=crop', 'carlos.mendoza@isu.edu.ph', '09456789012', 'active'),
(6, 3, 'Baseball Cap', 'Black baseball cap with "ISU" embroidered. Size Medium.', '2024-11-10', 'Gymnasium', 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=400&h=300&fit=crop', 'luz.fernandez@isu.edu.ph', '09567890123', 'active'),
(7, 4, 'Umbrella', 'Black compact umbrella. Automatic open/close. Found during rainy day.', '2024-11-09', 'Main Gate', 'https://cdn.britannica.com/70/123270-004-12C8DDD0/Umbrella.jpg', 'miguel.torres@isu.edu.ph', '09678901234', 'active'),
(8, 1, 'USB Flash Drive', '16GB USB flash drive. Black with red cap. Contains some files.', '2024-11-08', 'Computer Laboratory', 'https://images.unsplash.com/photo-1661961110372-8a7682543120?w=400&h=300&fit=crop', 'rosa.castillo@isu.edu.ph', '09789012345', 'active'),
(2, 5, 'Library Card', 'ISU Library card. Name partially visible. Please contact to claim.', '2024-11-07', 'Library', 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=400&h=300&fit=crop', 'maria.santos@isu.edu.ph', '09187654321', 'active'),
(3, 6, 'Watch', 'Silver wristwatch with leather band. Still ticking. No visible brand.', '2024-11-06', 'Student Center', 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?w=400&h=300&fit=crop', 'pedro.reyes@isu.edu.ph', '09234567890', 'active'),
(4, 4, 'Leather Backpack', 'Black leather backpack with laptop compartment. High quality and expensive looking.', '2024-11-05', 'Cafeteria', 'https://thingsremembered.com.ph/cdn/shop/files/Screenshot2023-12-13at10.49.48AM.png?v=1702436311', 'ana.garcia@isu.edu.ph', '09345678901', 'active');
