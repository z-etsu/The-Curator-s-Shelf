<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

// Get product ID from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    redirect('/products/list.php');
}

// Get product details
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    redirect('/products/list.php');
}

// Determine stock status
$stockStatus = $product['stock'] > 20 ? 'in-stock' : ($product['stock'] > 0 ? 'low-stock' : 'out-of-stock');
$stockText = $product['stock'] > 0 ? $product['stock'] . ' in stock' : 'Out of stock';
?>

<div class="product-detail">
    <div class="product-detail-image">
        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    </div>
    
    <div class="product-detail-info">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        
        <div class="product-meta">
            <div class="product-meta-item">
                <span class="product-meta-label">Price:</span>
                <span class="product-meta-value price">₱ <?php echo formatPrice($product['price']); ?></span>
            </div>
            <div class="product-meta-item">
                <span class="product-meta-label">Stock:</span>
                <span class="product-meta-value <?php echo $stockStatus; ?>"><?php echo $stockText; ?></span>
            </div>
            <div class="product-meta-item">
                <span class="product-meta-label">Series:</span>
                <span class="product-meta-value"><?php echo htmlspecialchars($product['series']); ?></span>
            </div>
            <div class="product-meta-item">
                <span class="product-meta-label">Category:</span>
                <span class="product-meta-value"><?php 
                    // Get category main_category from category_id
                    if ($product['category_id']) {
                        $catStmt = $pdo->prepare('SELECT main_category FROM categories WHERE id = ?');
                        $catStmt->execute([$product['category_id']]);
                        $category = $catStmt->fetch();
                        if ($category) {
                            echo htmlspecialchars($category['main_category']);
                        }
                    }
                ?></span>
            </div>
        </div>

        <h4>Description</h4>
        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

        <?php if ($product['stock'] > 0): ?>
            <form class="product-detail-form" onsubmit="return handleAddToCart(event, <?php echo $product['id']; ?>)">
                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1" required>
                </div>
                <div class="product-button-group">
                    <button type="submit" class="btn">Add to Cart</button>
                    <button type="button" class="btn btn-secondary" onclick="buyNow(<?php echo $product['id']; ?>, document.getElementById('quantity').value)">Buy Now</button>
                </div>
            </form>
        <?php else: ?>
            <button class="btn" disabled style="opacity: 0.5; cursor: not-allowed;">Out of Stock</button>
        <?php endif; ?>

        <p style="margin-top: 2rem; color: #999; font-size: 0.9rem;">Last updated: <?php echo date('F d, Y', strtotime($product['created_at'])); ?></p>
    </div>
</div>

<script>
function handleAddToCart(event, productId) {
    event.preventDefault();
    const quantity = document.getElementById('quantity').value;
    addToCart(productId, quantity);
    return false;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
