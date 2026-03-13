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
    <meta data-user-id="<?php echo isLoggedIn() ? $_SESSION['user_id'] ?? '' : ''; ?>">
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
                    <div class="user-menu">
                        <span class="user-greeting" onclick="toggleUserMenu()">Welcome, <?php echo htmlspecialchars($currentUser['first_name'] ?? $currentUser['name']); ?>!</span>
                        <div class="user-dropdown" id="userDropdown">
                            <a href="/CURATOR/auth/settings.php" class="dropdown-item">Settings</a>
                            <a href="/CURATOR/auth/logout.php" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <a href="/CURATOR/admin/dashboard.php" class="btn btn-small">Admin</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/CURATOR/auth/login.php" class="btn btn-small">Login</a>
                    <a href="/CURATOR/auth/register.php" class="btn btn-small btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main class="main-content">
