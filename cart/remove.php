<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

startSession();

// Remove from cart via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Accept either 'product_id' or 'cart_item_id' (they're the same thing in database)
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : (isset($_POST['cart_item_id']) ? intval($_POST['cart_item_id']) : 0);

    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit();
    }
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Please log in']);
        exit();
    }

    // Remove from database
    if (removeFromCartDatabase($productId)) {
        echo json_encode(['success' => true, 'message' => 'Product removed from cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not in cart']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
