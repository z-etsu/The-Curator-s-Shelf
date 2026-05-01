<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: /CURATOR/admin/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $categoryId = intval($_POST['category_id'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $imageUrl = sanitize($_POST['image_url'] ?? '');
    $series = sanitize($_POST['series'] ?? '');
    $description = sanitize($_POST['description'] ?? '');

    try {
        if ($productId === 0) {
            // Add new product
            $stmt = $pdo->prepare('INSERT INTO products (name, price, category_id, stock, image_url, series, description) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $price, $categoryId, $stock, $imageUrl, $series, $description]);
            $message = 'Product added successfully!';
        } else {
            // Update existing product
            $stmt = $pdo->prepare('UPDATE products SET name = ?, price = ?, category_id = ?, stock = ?, image_url = ?, series = ?, description = ? WHERE id = ?');
            $stmt->execute([$name, $price, $categoryId, $stock, $imageUrl, $series, $description, $productId]);
            $message = 'Product updated successfully!';
        }

        header('Location: /CURATOR/admin/dashboard.php?success=' . urlencode($message));
        exit;
    } catch (PDOException $e) {
        header('Location: /CURATOR/admin/dashboard.php?error=' . urlencode('Failed to save product.'));
        exit;
    }
}

header('Location: /CURATOR/admin/dashboard.php');
exit;
