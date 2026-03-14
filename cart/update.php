<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

startSession();

// Update cart quantity via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Accept either 'product_id' or 'cart_item_id'
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : (isset($_POST['cart_item_id']) ? intval($_POST['cart_item_id']) : 0);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($productId <= 0 || $quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        exit();
    }
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Please log in']);
        exit();
    }

    // Update in database
    if (updateCartQuantityDatabase($productId, $quantity)) {
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not in cart']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
