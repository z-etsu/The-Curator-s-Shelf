<?php
require_once __DIR__ . '/../config/database.php';

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$productId = intval($_GET['id'] ?? 0);

if ($productId === 0) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode($product);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
