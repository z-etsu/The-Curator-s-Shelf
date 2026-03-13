<?php
// Header include - Navigation and HTML top
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

startSession();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Curator's Shelf - Action Figures</title>
    <link rel="stylesheet" href="/CURATOR/assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="/CURATOR/index.php" class="navbar-brand-link">
                    <h1>The Curator's Shelf</h1>
                </a>
            </div>
            <nav class="navbar-menu">
                <a href="/CURATOR/products/list.php" class="nav-link">Shop</a>
                <a href="#about" class="nav-link">About</a>
                <a href="/CURATOR/cart/view.php" class="nav-link">
                    Cart
                    <span class="cart-count" id="cartCount">0</span>
                </a>
            </nav>
            <div class="navbar-auth">
                <?php if ($currentUser): ?>
                    <span class="user-greeting">Welcome, <?php echo htmlspecialchars($currentUser['name']); ?></span>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <a href="/CURATOR/admin/dashboard.php" class="btn btn-small">Admin</a>
                    <?php endif; ?>
                    <a href="/CURATOR/auth/logout.php" class="btn btn-small btn-danger">Logout</a>
                <?php else: ?>
                    <a href="/CURATOR/auth/login.php" class="btn btn-small">Login</a>
                    <a href="/CURATOR/auth/register.php" class="btn btn-small btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main class="main-content">
