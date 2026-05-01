<?php
require_once __DIR__ . '/../config/database.php';

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: /CURATOR/admin/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id'] ?? 0);
    $newStock = intval($_POST['stock'] ?? 0);

    if ($productId === 0 || $newStock < 0) {
        header('Location: /CURATOR/admin/dashboard.php?error=' . urlencode('Invalid product or stock value.'));
        exit;
    }

    try {
        $stmt = $pdo->prepare('UPDATE products SET stock = ? WHERE id = ?');
        $stmt->execute([$newStock, $productId]);
        header('Location: /CURATOR/admin/dashboard.php?success=' . urlencode('Stock updated successfully!'));
        exit;
    } catch (PDOException $e) {
        header('Location: /CURATOR/admin/dashboard.php?error=' . urlencode('Failed to update stock.'));
        exit;
    }
}

header('Location: /CURATOR/admin/dashboard.php');
exit;
