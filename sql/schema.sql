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

-- Updating existing products (keeping IDs intact)
UPDATE products SET name='Kaguya Shinomiya - Love is War', description='Stunning Kaguya figure in elegant school uniform with exceptional sculpting. Features detailed hair and clothing with multiple face plates for various expressions. Premium paint application with lustrous finish.', price=4500, stock=14, image_url='/CURATOR/assets/images/kaguya1.jpg', category_id=25, series='Kaguya-sama: Love is War' WHERE id=1;
UPDATE products SET name='Miyuki Shirogane - Genius Secretary', description='Detailed Miyuki figure displaying his characteristic cool composure. Includes blazer with excellent fabric texture and interchangeable hands. Perfect counterpart to Kaguya in any display.', price=4300, stock=16, image_url='/CURATOR/assets/images/miyuki1.jpeg', category_id=25, series='Kaguya-sama: Love is War' WHERE id=2;
UPDATE products SET name='Ai Hayasaka - Maid Angel', description='Beautiful Hayasaka maid outfit figure with intricate lace details. Highly articulated with multiple interchangeable parts for dynamic poses. Includes signature twin-tails accessories.', price=4200, stock=12, image_url='/CURATOR/assets/images/hayasaka1.png', category_id=25, series='Kaguya-sama: Love is War' WHERE id=3;
UPDATE products SET name='Chika Fujiwara - Heart Throb Idol', description='Cheerful Chika figure in her iconic pink dress with excellent color separation. Dynamic posing capable with flexible joints. Includes multiple hand sets for various gestures.', price=4000, stock=18, image_url='/CURATOR/assets/images/chika1.png', category_id=25, series='Kaguya-sama: Love is War' WHERE id=4;
UPDATE products SET name='Ken Kaneki - One-Eyed Ghoul', description='Iconic Kaneki figure in black suit with white hair perfectly sculpted. Includes signature kagune effect accessories with translucent painting. Excellent muscle detail and clothing textures.', price=4800, stock=11, image_url='/CURATOR/assets/images/kaneki1.jpg', category_id=26, series='Tokyo Ghoul' WHERE id=5;
UPDATE products SET name='Touka Kirishima - Ghoul Princess', description='Stunning Touka figure in casual outfit with detailed hair and beauty marks. Multiple face plates showing different expressions. Includes interchangeable hands for dynamic action poses.', price=4650, stock=13, image_url='/CURATOR/assets/images/touka1.png', category_id=26, series='Tokyo Ghoul' WHERE id=6;
UPDATE products SET name='Rize Kamishiro - Binge Eater', description='Elegant Rize figure in her iconic dress with ghoul characteristics emphasized. Premium paint application on hair with realistic shading. Includes alternate head pieces and accessories.', price=4500, stock=10, image_url='/CURATOR/assets/images/rize1.png', category_id=26, series='Tokyo Ghoul' WHERE id=7;
UPDATE products SET name='Eto White - One-Eyed Owl', description='Powerful Eto figure combining human and ghoul forms with incredible detail. Features articulated ghoul parts and multiple expression plates. Premium collectible with excellent presence on display.', price=4950, stock=9, image_url='/CURATOR/assets/images/eto1.png', category_id=26, series='Tokyo Ghoul' WHERE id=8;
UPDATE products SET name='Ichigo Kurosaki - Bleach Soul Society', description='Premium Bankai form Ichigo figure with detailed Zangetsu sword. Includes three interchangeable face plates and multiple hand sets for dynamic posing. Highly articulated with great attention to clothing details.', price=4850, stock=12, image_url='/CURATOR/assets/images/ichigo1.jpg', category_id=27, series='Bleach' WHERE id=9;
UPDATE products SET name='Goku Ultra Instinct - Dragon Ball Super', description='Dynamic ultra instinct Goku with signature pose and aura effects. Features incredible sculpting detail and includes interchangeable hands and blast effects. Perfect for any Dragon Ball collector.', price=4950, stock=14, image_url='/CURATOR/assets/images/goku1.png', category_id=28, series='Dragon Ball' WHERE id=10;
UPDATE products SET name='Batman - Dark Knight Trilogy', description='Highly detailed Batman figure in classic tactical suit with exceptional sculpting. Includes detailed cape with excellent fabric texture and multiple interchangeable hands. Premium paint application with dark metallic finish.', price=5207, stock=10, image_url='/CURATOR/assets/images/batman1.jpg', category_id=31, series='DC Comics' WHERE id=11;
UPDATE products SET name='Wonder Woman Classic - DC Comics', description='Iconic Wonder Woman figure with golden armor and lasso. Features excellent color separation and dynamic posing capable with multiple interchangeable hands and weapons.', price=5039, stock=9, image_url='/CURATOR/assets/images/wonderwoman1.png', category_id=31, series='DC Comics' WHERE id=12;
UPDATE products SET name='Naruto Uzumaki - Sage Mode', description='Detailed Naruto in Six Path Sage Mode with dynamic positioning capabilities. Includes signature rasengan effect accessories with translucent painting and multiple hand sets for various gestures.', price=4759, stock=16, image_url='/CURATOR/assets/images/naruto1.jpg', category_id=30, series='Naruto' WHERE id=13;
UPDATE products SET name='Superman - Justice League', description='Premium Superman figure in classic suit and cape with incredible detail and presence. Includes alternate head pieces and multiple hand sets for dynamic heroic poses.', price=5319, stock=8, image_url='/CURATOR/assets/images/superman1.jpg', category_id=31, series='DC Comics' WHERE id=14;
UPDATE products SET name='The Flash - DC Comics Fastest Man', description='Dynamic Flash figure with speed force effects and flexible joints for action poses. Features detailed suit paintwork and includes multiple interchangeable hands and blast effects.', price=4479, stock=18, image_url='/CURATOR/assets/images/flash1.jpg', category_id=31, series='DC Comics' WHERE id=15;
UPDATE products SET name='Iron Man Mark 85 - Avengers Endgame', description='Premium Iron Man Mark 85 figure with intricate suit detail and light-up effects. Includes multiple interchangeable hands, detachable armor parts, and excellent paint application with metallic finish.', price=5599, stock=7, image_url='/CURATOR/assets/images/ironman1.jpg', category_id=32, series='Marvel' WHERE id=16;
UPDATE products SET name='Madoka Kaname - Puella Magi', description='Beautiful Madoka figure in magical girl form with excellent color separation and detail. Includes multiple interchangeable face plates and hand sets for dynamic poses.', price=4199, stock=20, image_url='/CURATOR/assets/images/madoka1.png', category_id=29, series='Puella Magi Madoka Magica' WHERE id=17;
UPDATE products SET name='Black Panther - Marvel Legends', description='Highly articulated Black Panther with detailed tactical suit and claw weapons. Features excellent muscle detail and includes multiple interchangeable parts for dynamic action poses.', price=5487, stock=11, image_url='/CURATOR/assets/images/blackpanther1.jpg', category_id=32, series='Marvel' WHERE id=18;

