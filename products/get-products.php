<?php
// API endpoint to get products filtered by category
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

try {
    if ($categoryId === null || $categoryId === 0) {
        // Get all products
        $stmt = $pdo->prepare('SELECT * FROM products ORDER BY created_at DESC');
        $stmt->execute();
    } else {
        // Get products by category
        $stmt = $pdo->prepare('SELECT * FROM products WHERE category_id = ? ORDER BY created_at DESC');
        $stmt->execute([$categoryId]);
    }
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format products for display
    $formattedProducts = [];
    foreach ($products as $product) {
        $formattedProducts[] = [
            'id' => $product['id'],
            'name' => htmlspecialchars($product['name']),
            'description' => htmlspecialchars($product['description']),
            'price' => formatPrice($product['price']),
            'image_url' => htmlspecialchars($product['image_url']),
            'full_description' => htmlspecialchars($product['description']),
            'stock' => $product['stock'],
            'series' => htmlspecialchars($product['series'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $formattedProducts,
        'count' => count($formattedProducts)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching products'
    ]);
}
?>
