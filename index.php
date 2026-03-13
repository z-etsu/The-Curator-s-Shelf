<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

// Get featured products (first 8)
$stmt = $pdo->prepare('SELECT * FROM products LIMIT 8');
$stmt->execute();
$featuredProducts = $stmt->fetchAll();

// Get recent arrivals (4 products)
$stmt = $pdo->prepare('SELECT * FROM products ORDER BY id DESC LIMIT 4');
$stmt->execute();
$recentProducts = $stmt->fetchAll();
?>

<!-- Hero Section -->
<div class="hero">
    <h2>Curated Action Figures for Serious Collectors</h2>
    <p>Discover rare and premium pieces for your collection.</p>
    <a href="/CURATOR/products/list.php" class="btn">Shop Now</a>
</div>

<!-- Featured Collection Section -->
<h3 class="section-title">Featured Collection</h3>

<div class="products-grid">
    <?php foreach ($featuredProducts as $product): ?>
        <div class="product-card">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="product-info">
                <h4 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h4>
                <p class="product-description">
                    <?php 
                    $desc = htmlspecialchars($product['description']);
                    echo strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc; 
                    ?>
                </p>
                <p class="product-price">৳ <?php echo formatPrice($product['price']); ?></p>
                <div class="product-actions">
                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                    <a href="/CURATOR/products/detail.php?id=<?php echo $product['id']; ?>" class="view-details-btn">Details</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Recent Arrivals Section -->
<h3 class="section-title">Recently Added</h3>

<div class="products-grid recent-arrivals">
    <?php foreach ($recentProducts as $product): ?>
        <div class="product-card">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="product-info">
                <h4 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h4>
                <p class="product-description">
                    <?php 
                    $desc = htmlspecialchars($product['description']);
                    echo strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc; 
                    ?>
                </p>
                <p class="product-price">৳ <?php echo formatPrice($product['price']); ?></p>
                <div class="product-actions">
                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                    <a href="/CURATOR/products/detail.php?id=<?php echo $product['id']; ?>" class="view-details-btn">Details</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Browse by Categories Section -->
<h3 class="section-title">Browse by Categories</h3>

<div class="categories-carousel">
    <div class="carousel-item" style="background-color: var(--placeholder-bg);">
        <div class="carousel-text">Anime Figures</div>
    </div>
    <div class="carousel-item" style="background-color: var(--placeholder-bg);">
        <div class="carousel-text">Marvel Figures</div>
    </div>
    <div class="carousel-item" style="background-color: var(--placeholder-bg);">
        <div class="carousel-text">DC Figures</div>
    </div>
    <div class="carousel-item" style="background-color: var(--placeholder-bg);">
        <div class="carousel-text">Video Game Characters</div>
    </div>
    <div class="carousel-item" style="background-color: var(--placeholder-bg);">
        <div class="carousel-text">Movie Characters</div>
    </div>
    <div class="carousel-item" style="background-color: var(--placeholder-bg);">
        <div class="carousel-text">TV Series Figures</div>
    </div>
</div>

<!-- Trust Badges Section -->
<div class="trust-badges">
    <div class="badge">
        <div class="badge-icon"></div>
        <h4>Authentic Products</h4>
        <p>Verified genuine collectibles</p>
    </div>
    <div class="badge">
        <div class="badge-icon"></div>
        <h4>Fast Shipping</h4>
        <p>Quick and secure delivery</p>
    </div>
    <div class="badge">
        <div class="badge-icon"></div>
        <h4>Secure Checkout</h4>
        <p>Safe payment methods</p>
    </div>
    <div class="badge">
        <div class="badge-icon"></div>
        <h4>Quality Guaranteed</h4>
        <p>Premium condition items</p>
    </div>
</div>

<!-- Newsletter Section -->
<div class="newsletter">
    <h3>Stay Updated</h3>
    <p>Get notified about new arrivals and exclusive offers</p>
    <form class="newsletter-form">
        <input type="email" placeholder="Enter your email" required>
        <button type="submit" class="btn">Subscribe</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
