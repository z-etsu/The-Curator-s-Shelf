-- SQL Script to update products with local images
-- Generated: March 14, 2026
-- Purpose: Add image_url_2 column and update all products with their respective images from /CURATOR/assets/images/

-- Step 1: Add image_url_2 column if it doesn't exist
ALTER TABLE products ADD COLUMN image_url_2 VARCHAR(255) DEFAULT NULL;

-- Step 2: Update all products with their respective images
-- Kaguya Shinomiya
UPDATE products SET 
  image_url = '/CURATOR/assets/images/kaguya1.jpg',
  image_url_2 = '/CURATOR/assets/images/kaguya2.webp'
WHERE id = 1;

-- Miyuki Shirogane
UPDATE products SET 
  image_url = '/CURATOR/assets/images/miyuki1.jpeg',
  image_url_2 = '/CURATOR/assets/images/miyuki2.jpeg'
WHERE id = 2;

-- Ai Hayasaka (no images, keep placeholder)
-- (skipping - no local images)

-- Chika Fujiwara (no images, keep placeholder)
-- (skipping - no local images)

-- Ken Kaneki
UPDATE products SET 
  image_url = '/CURATOR/assets/images/kaneki1.jpg',
  image_url_2 = '/CURATOR/assets/images/kaneki2.jpg'
WHERE id = 5;

-- Touka Kirishima
UPDATE products SET 
  image_url = '/CURATOR/assets/images/touka1.png',
  image_url_2 = '/CURATOR/assets/images/touka2.png'
WHERE id = 6;

-- Rize Kamishiro
UPDATE products SET 
  image_url = '/CURATOR/assets/images/rize1.png',
  image_url_2 = '/CURATOR/assets/images/rize2.png'
WHERE id = 7;

-- Eto White
UPDATE products SET 
  image_url = '/CURATOR/assets/images/eto1.png',
  image_url_2 = '/CURATOR/assets/images/eto2.png'
WHERE id = 8;

-- Ichigo Kurosaki
UPDATE products SET 
  image_url = '/CURATOR/assets/images/ichigo1.png',
  image_url_2 = '/CURATOR/assets/images/ichigo2.png'
WHERE id = 9;

-- Goku Ultra Instinct
UPDATE products SET 
  image_url = '/CURATOR/assets/images/goku1.png',
  image_url_2 = '/CURATOR/assets/images/goku2.png'
WHERE id = 10;

-- Batman
UPDATE products SET 
  image_url = '/CURATOR/assets/images/batman1.png',
  image_url_2 = '/CURATOR/assets/images/batman2.png'
WHERE id = 11;

-- Wonder Woman
UPDATE products SET 
  image_url = '/CURATOR/assets/images/wonderwoman1.png',
  image_url_2 = '/CURATOR/assets/images/wonderwoman2.png'
WHERE id = 12;

-- Naruto Uzumaki
UPDATE products SET 
  image_url = '/CURATOR/assets/images/naruto1.png',
  image_url_2 = '/CURATOR/assets/images/naruto2.png'
WHERE id = 13;

-- Superman
UPDATE products SET 
  image_url = '/CURATOR/assets/images/superman1.jpg',
  image_url_2 = '/CURATOR/assets/images/superman2.jpg'
WHERE id = 14;

-- The Flash
UPDATE products SET 
  image_url = '/CURATOR/assets/images/flash1.jpg',
  image_url_2 = '/CURATOR/assets/images/flash2.jpg'
WHERE id = 15;

-- Iron Man
UPDATE products SET 
  image_url = '/CURATOR/assets/images/ironman1.jpg',
  image_url_2 = '/CURATOR/assets/images/ironman2.jpg'
WHERE id = 16;

-- Madoka Kaname
UPDATE products SET 
  image_url = '/CURATOR/assets/images/madoka1.png',
  image_url_2 = '/CURATOR/assets/images/madoka2.png'
WHERE id = 17;

-- Black Panther
UPDATE products SET 
  image_url = '/CURATOR/assets/images/blackpanth1.jpg',
  image_url_2 = '/CURATOR/assets/images/blackpanth2.jpg'
WHERE id = 18;

-- Verify the updates
SELECT id, name, image_url, image_url_2 FROM products ORDER BY id;
