<?php
// Helper functions

// Session management
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    startSession();
    if (isLoggedIn()) {
        global $pdo;
        $stmt = $pdo->prepare('SELECT id, email, name, role FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}

function logout() {
    startSession();
    session_destroy();
    header('Location: /index.php');
    exit();
}

// Password hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Form validation
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    // Min 6 characters
    return strlen($password) >= 6;
}

// Cart helpers
function getCartTotal($items) {
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function formatPrice($price) {
    return number_format($price, 2, '.', ',');
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Redirect helper
function redirect($location) {
    header('Location: ' . $location);
    exit();
}
?>
