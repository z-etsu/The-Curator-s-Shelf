<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

startSession();

// Get cart count via AJAX
$count = 0;

if (isLoggedIn()) {
    try {
        global $pdo;
        $userId = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare('SELECT SUM(quantity) as total FROM cart_items WHERE session_id = ?');
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        $count = $result['total'] ?? 0;
    } catch (Exception $e) {
        error_log('Error getting cart count: ' . $e->getMessage());
        $count = 0;
    }
}

echo json_encode(['count' => $count]);
?>
