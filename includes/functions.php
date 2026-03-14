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
        $stmt = $pdo->prepare('SELECT id, email, first_name, last_name, role FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}

function logout() {
    startSession();
    session_destroy();
    header('Location: /CURATOR/index.php');
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

// ===== Database-backed Cart Functions =====

/**
 * Get all cart items for the current user from the database
 * Returns associative array with product_id as key
 */
function getCartFromDatabase() {
    startSession();
    
    if (!isLoggedIn()) {
        return [];
    }
    
    global $pdo;
    $userId = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare('
            SELECT ci.product_id, p.name, p.price, p.image_url, ci.quantity
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.session_id = ?
            ORDER BY ci.created_at DESC
        ');
        $stmt->execute([$userId]);
        
        $cart = [];
        while ($row = $stmt->fetch()) {
            $cart[$row['product_id']] = [
                'name' => $row['name'],
                'price' => $row['price'],
                'image_url' => $row['image_url'],
                'quantity' => $row['quantity']
            ];
        }
        
        return $cart;
    } catch (Exception $e) {
        error_log('Error getting cart from database: ' . $e->getMessage());
        return [];
    }
}

/**
 * Add item to database cart or update quantity if exists
 */
function addToCartDatabase($productId, $quantity = 1) {
    startSession();
    
    if (!isLoggedIn()) {
        return false;
    }
    
    global $pdo;
    $userId = $_SESSION['user_id'];
    
    try {
        // Check if product already in cart
        $stmt = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE session_id = ? AND product_id = ?');
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update quantity if exists
            $stmt = $pdo->prepare('UPDATE cart_items SET quantity = quantity + ? WHERE session_id = ? AND product_id = ?');
            $stmt->execute([$quantity, $userId, $productId]);
        } else {
            // Insert new item
            $stmt = $pdo->prepare('INSERT INTO cart_items (session_id, product_id, quantity) VALUES (?, ?, ?)');
            $stmt->execute([$userId, $productId, $quantity]);
        }
        
        return true;
    } catch (Exception $e) {
        error_log('Error adding to cart database: ' . $e->getMessage());
        return false;
    }
}

/**
 * Remove item from database cart
 */
function removeFromCartDatabase($productId) {
    startSession();
    
    if (!isLoggedIn()) {
        return false;
    }
    
    global $pdo;
    $userId = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE session_id = ? AND product_id = ?');
        $stmt->execute([$userId, $productId]);
        return true;
    } catch (Exception $e) {
        error_log('Error removing from cart database: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update quantity of item in database cart
 */
function updateCartQuantityDatabase($productId, $quantity) {
    startSession();
    
    if (!isLoggedIn()) {
        return false;
    }
    
    global $pdo;
    $userId = $_SESSION['user_id'];
    
    try {
        if ($quantity <= 0) {
            // Delete if quantity is 0 or less
            return removeFromCartDatabase($productId);
        }
        
        $stmt = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE session_id = ? AND product_id = ?');
        $stmt->execute([$quantity, $userId, $productId]);
        return true;
    } catch (Exception $e) {
        error_log('Error updating cart quantity: ' . $e->getMessage());
        return false;
    }
}

/**
 * Clear all items from user's cart
 */
function clearCartDatabase() {
    startSession();
    
    if (!isLoggedIn()) {
        return false;
    }
    
    global $pdo;
    $userId = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE session_id = ?');
        $stmt->execute([$userId]);
        return true;
    } catch (Exception $e) {
        error_log('Error clearing cart: ' . $e->getMessage());
        return false;
    }
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
