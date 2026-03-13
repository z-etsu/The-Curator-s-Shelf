-- The Curator's Shelf - Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS curator_shelf;
USE curator_shelf;

-- Users table (customers and admins)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table (action figures)
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
);

-- Cart items table (session-based cart storage)
CREATE TABLE IF NOT EXISTS cart_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_session (session_id)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'completed', 'shipped', 'cancelled') DEFAULT 'completed',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

-- Insert sample action figure data
INSERT INTO products (name, description, price, stock, image_url) VALUES
('Ichigo Kurosaki - Bleach Soul Society', 'Premium Bankai form Ichigo figure with detailed Zangetsu sword. Includes three interchangeable face plates and multiple hand sets for dynamic posing. Highly articulated with great attention to clothing details.', 85.99, 12, 'https://images.amiami.com/amixstaff/img-xx_large/img000052384434600.jpg'),
('Goku Ultra Instinct - Dragon Ball Super', 'Dynamic ultra instinct Goku with signature pose and aura effects. Features incredible sculpting detail and includes interchangeable hands and blast effects. Perfect for any Dragon Ball collector.', 87.99, 14, 'https://images.hobbylink.tv/images/news/12023/1024/sh-figuarts-goku-ultra-instinct-event-exclusive-color-ver-2.jpg'),
('Batman - Dark Knight Trilogy', 'Highly detailed Batman figure in classic tactical suit with multiple accessories including Batarangs and grappling gun. Excellent articulation for dynamic display poses.', 92.99, 10, 'https://images2.thumbs.redditmedia.com/DVKw3b_RdL_nYCy6PVoNhQlM2FE=/320x320/filters:no_upscale():max_bytes(150000):strip_icomment()/t5_2s6c0/styles/communityIcon_r4gzp0.png'),
('Wonder Woman Classic - DC Comics', 'Iconic Wonder Woman figure with golden armor and lasso of truth. Premium paint applications and articulated frame allows for powerful action poses. Includes alternate hands and accessories.', 89.99, 9, 'https://images.goodsmile.info/images/product/20200206/10118/10118_main_00g.jpg'),
('Naruto Uzumaki - Sage Mode', 'Detailed Naruto in Six Path Sage Mode with dynamic energy effects and multiple face plates. Includes signature hand sign accessories and optional chakra effects. Excellent sculpting and paint work.', 84.99, 16, 'https://images.amiami.com/amixstaff/img-xxl_large/img000046693597600.jpg'),
('Superman - Justice League', 'Premium Superman figure with classic suit and powerful stance. Detailed cape sculpting and perfect proportions. Includes interchangeable hands for various poses and flying effects stand.', 94.99, 8, 'https://images.goodsmile.info/images/product/20150401/11556/11556_main_00g.jpg'),
('The Flash - DC Comics Fastest Man', 'Dynamic Flash figure with speed force effects and lightning accessories. Highly detailed suit with excellent paint applications. Multiple hand sets for action poses.', 79.99, 18, 'https://images.amiami.com/amixstaff/img-xxl_large/img000041234567800.jpg'),
('Iron Man Mark 85 - Avengers Endgame', 'Premium Iron Man figure with intricate suit details and LED light-up repulsor effects. Movie-accurate design with rotating chest piece. Highly collectible with great poseability.', 99.99, 7, 'https://images.goodsmile.info/images/product/20190829/10987/10987_main_01g.jpg'),
('Madoka Kaname - Puella Magi', 'Beautiful Madoka figure in magical girl form with detailed costume and ribbon effects. Includes multiple interchangeable faces and weapon accessories. Excellent paint detail on outfit.', 74.99, 20, 'https://images.amiami.com/amixstaff/img-xxl_large/img000038765432100.jpg'),
('Black Panther - Marvel Legends', 'Highly articulated Black Panther with detailed panther-head suit design and royal cloak. Includes multiple hand sets and vibranium effect accessories. Premium collectible piece.', 97.99, 11, 'https://images.goodsmile.info/images/product/20180615/10234/10234_main_00g.jpg');

