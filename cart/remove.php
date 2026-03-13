<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

startSession();

// Remove from cart via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit();
    }

    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        echo json_encode(['success' => true, 'message' => 'Product removed from cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not in cart']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
