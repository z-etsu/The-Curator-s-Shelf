<?php
require_once __DIR__ . '/../config/database.php';

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: /CURATOR/admin/login.php');
    exit;
}

$productId = intval($_GET['id'] ?? 0);

if ($productId === 0) {
    header('Location: /CURATOR/admin/dashboard.php?error=' . urlencode('Invalid product ID.'));
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    header('Location: /CURATOR/admin/dashboard.php?success=' . urlencode('Product deleted successfully!'));
    exit;
} catch (PDOException $e) {
    header('Location: /CURATOR/admin/dashboard.php?error=' . urlencode('Failed to delete product.'));
    exit;
}
