-- The Curator's Shelf - Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS curator_shelf;
USE curator_shelf;

-- Users table (customers and admins)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    main_category ENUM('Anime', 'Comics', 'Video Games') NOT NULL,
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
    category_id INT,
    series VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_category (category_id),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
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


-- Insert categories
INSERT IGNORE INTO categories (name, main_category) VALUES
('Kaguya-sama: Love is War', 'Anime'),
('Tokyo Ghoul', 'Anime'),
('Bleach', 'Anime'),
('Dragon Ball', 'Anime'),
('Puella Magi Madoka Magica', 'Anime'),
('Naruto', 'Anime'),
('DC Comics', 'Comics'),
('Marvel', 'Comics');

-- Insert product data for a new install, or update if the row already exists
INSERT INTO products (id, name, description, price, stock, image_url, category_id, series) VALUES
(1, 'Kaguya Shinomiya - Love is War', 'Stunning Kaguya figure in elegant school uniform with exceptional sculpting. Features detailed hair and clothing with multiple face plates for various expressions. Premium paint application with lustrous finish.', 4500, 14, 'https://images.amiami.com/amixstaff/img-xxl_large/img000052847563200.jpg', 1, 'Kaguya-sama: Love is War'),
(2, 'Miyuki Shirogane - Genius Secretary', 'Detailed Miyuki figure displaying his characteristic cool composure. Includes blazer with excellent fabric texture and interchangeable hands. Perfect counterpart to Kaguya in any display.', 4300, 16, 'https://images.amiami.com/amixstaff/img-xxl_large/img000053125478900.jpg', 1, 'Kaguya-sama: Love is War'),
(3, 'Ai Hayasaka - Maid Angel', 'Beautiful Hayasaka maid outfit figure with intricate lace details. Highly articulated with multiple interchangeable parts for dynamic poses. Includes signature twin-tails accessories.', 4200, 12, 'https://images.amiami.com/amixstaff/img-xxl_large/img000050234891200.jpg', 1, 'Kaguya-sama: Love is War'),
(4, 'Chika Fujiwara - Heart Throb Idol', 'Cheerful Chika figure in her iconic pink dress with excellent color separation. Dynamic posing capable with flexible joints. Includes multiple hand sets for various gestures.', 4000, 18, 'https://images.amiami.com/amixstaff/img-xxl_large/img000049876543100.jpg', 1, 'Kaguya-sama: Love is War'),
(5, 'Ken Kaneki - One-Eyed Ghoul', 'Iconic Kaneki figure in black suit with white hair perfectly sculpted. Includes signature kagune effect accessories with translucent painting. Excellent muscle detail and clothing textures.', 4800, 11, 'https://images.amiami.com/amixstaff/img-xxl_large/img000051234987600.jpg', 2, 'Tokyo Ghoul'),
(6, 'Touka Kirishima - Ghoul Princess', 'Stunning Touka figure in casual outfit with detailed hair and beauty marks. Multiple face plates showing different expressions. Includes interchangeable hands for dynamic action poses.', 4650, 13, 'https://images.amiami.com/amixstaff/img-xxl_large/img000052098765400.jpg', 2, 'Tokyo Ghoul'),
(7, 'Rize Kamishiro - Binge Eater', 'Elegant Rize figure in her iconic dress with ghoul characteristics emphasized. Premium paint application on hair with realistic shading. Includes alternate head pieces and accessories.', 4500, 10, 'https://images.amiami.com/amixstaff/img-xxl_large/img000050567234800.jpg', 2, 'Tokyo Ghoul'),
(8, 'Eto White - One-Eyed Owl', 'Powerful Eto figure combining human and ghoul forms with incredible detail. Features articulated ghoul parts and multiple expression plates. Premium collectible with excellent presence on display.', 4950, 9, 'https://images.amiami.com/amixstaff/img-xxl_large/img000053456789000.jpg', 2, 'Tokyo Ghoul'),
(9, 'Ichigo Kurosaki - Bleach Soul Society', 'Premium Bankai form Ichigo figure with detailed Zangetsu sword. Includes three interchangeable face plates and multiple hand sets for dynamic posing. Highly articulated with great attention to clothing details.', 4850, 12, 'https://images.amiami.com/amixstaff/img-xx_large/img000052384434600.jpg', 3, 'Bleach'),
(10, 'Goku Ultra Instinct - Dragon Ball Super', 'Dynamic ultra instinct Goku with signature pose and aura effects. Features incredible sculpting detail and includes interchangeable hands and blast effects. Perfect for any Dragon Ball collector.', 4950, 14, 'https://images.hobbylink.tv/images/news/12023/1024/sh-figuarts-goku-ultra-instinct-event-exclusive-color-ver-2.jpg', 4, 'Dragon Ball'),
(11, 'Batman - Dark Knight Trilogy', 'Highly detailed Batman figure in classic tactical suit with exceptional sculpting. Includes detailed cape with excellent fabric texture and multiple interchangeable hands. Premium paint application with dark metallic finish.', 5207, 10, 'https://images2.thumbs.redditmedia.com/DVKw3b_RdL.jpg', 7, 'DC Comics'),
(12, 'Wonder Woman Classic - DC Comics', 'Iconic Wonder Woman figure with golden armor and lasso. Features excellent color separation and dynamic posing capable with multiple interchangeable hands and weapons.', 5039, 9, 'https://images.goodsmile.info/images/product/20000.jpg', 7, 'DC Comics'),
(13, 'Naruto Uzumaki - Sage Mode', 'Detailed Naruto in Six Path Sage Mode with dynamic positioning capabilities. Includes signature rasengan effect accessories with translucent painting and multiple hand sets for various gestures.', 4759, 16, 'https://images.amiami.com/amixstaff/img-xxl_large/img000052847563200.jpg', 6, 'Naruto'),
(14, 'Superman - Justice League', 'Premium Superman figure in classic suit and cape with incredible detail and presence. Includes alternate head pieces and multiple hand sets for dynamic heroic poses.', 5319, 8, 'https://images.goodsmile.info/images/product/20150.jpg', 7, 'DC Comics'),
(15, 'The Flash - DC Comics Fastest Man', 'Dynamic Flash figure with speed force effects and flexible joints for action poses. Features detailed suit paintwork and includes multiple interchangeable hands and blast effects.', 4479, 18, 'https://images.amiami.com/amixstaff/img-xxl_large/img000052847563200.jpg', 7, 'DC Comics'),
(16, 'Iron Man Mark 85 - Avengers Endgame', 'Premium Iron Man Mark 85 figure with intricate suit detail and light-up effects. Includes multiple interchangeable hands, detachable armor parts, and excellent paint application with metallic finish.', 5599, 7, 'https://images.goodsmile.info/images/product/20190.jpg', 8, 'Marvel'),
(17, 'Madoka Kaname - Puella Magi', 'Beautiful Madoka figure in magical girl form with excellent color separation and detail. Includes multiple interchangeable face plates and hand sets for dynamic poses.', 4199, 20, 'https://images.amiami.com/amixstaff/img-xxl_large/img000052847563200.jpg', 5, 'Puella Magi Madoka Magica'),
(18, 'Black Panther - Marvel Legends', 'Highly articulated Black Panther with detailed tactical suit and claw weapons. Features excellent muscle detail and includes multiple interchangeable parts for dynamic action poses.', 5487, 11, 'https://images2.thumbs.redditmedia.com/DVKw3b_RdL.jpg', 8, 'Marvel')
ON DUPLICATE KEY UPDATE
  name=VALUES(name),
  description=VALUES(description),
  price=VALUES(price),
  stock=VALUES(stock),
  image_url=VALUES(image_url),
  category_id=VALUES(category_id),
  series=VALUES(series);

