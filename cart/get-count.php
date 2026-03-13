<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

startSession();

// Get cart count via AJAX
$count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
}

echo json_encode(['count' => $count]);
?>
