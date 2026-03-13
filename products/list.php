<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

// Get all products
$stmt = $pdo->prepare('SELECT * FROM products ORDER BY created_at DESC');
$stmt->execute();
$products = $stmt->fetchAll();
?>

<h2 class="section-title">All Products</h2>

<?php if (empty($products)): ?>
    <div class="empty-cart">
        <h2>No Products Available</h2>
        <p>Check back soon for action figures!</p>
        <a href="/index.php" class="btn">Back to Home</a>
    </div>
<?php else: ?>
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
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
                        <a href="/products/detail.php?id=<?php echo $product['id']; ?>" class="view-details-btn">Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
