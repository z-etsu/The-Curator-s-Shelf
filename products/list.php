<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

// Get all categories
$stmtCat = $pdo->prepare('SELECT DISTINCT category_id FROM products WHERE category_id IS NOT NULL ORDER BY category_id');
$stmtCat->execute();
$categoryIds = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

// Get category details
$categories = [];
if (!empty($categoryIds)) {
    $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
    $stmtCats = $pdo->prepare("SELECT id, name FROM categories WHERE id IN ($placeholders) ORDER BY id");
    $stmtCats->execute($categoryIds);
    $categories = $stmtCats->fetchAll();
}

// Get all products
$stmt = $pdo->prepare('SELECT * FROM products ORDER BY created_at DESC');
$stmt->execute();
$products = $stmt->fetchAll();
?>

<h2 class="section-title">All Products</h2>

<!-- Category Tabs -->
<div class="category-tabs">
    <button class="category-tab active" data-category-id="0" onclick="filterByCategory(0, this)">
        All Products
    </button>
    <?php foreach ($categories as $category): ?>
        <button class="category-tab" data-category-id="<?php echo $category['id']; ?>" onclick="filterByCategory(<?php echo $category['id']; ?>, this)">
            <?php echo htmlspecialchars($category['name']); ?>
        </button>
    <?php endforeach; ?>
</div>

<?php if (empty($products)): ?>
    <div class="empty-cart">
        <h2>No Products Available</h2>
        <p>Check back soon for action figures!</p>
        <a href="/index.php" class="btn">Back to Home</a>
    </div>
<?php else: ?>
    <div class="products-grid" id="productsGrid">
        <?php foreach ($products as $product): ?>
            <div class="product-card" data-product-id="<?php echo $product['id']; ?>" data-full-description="<?php echo htmlspecialchars($product['description']); ?>" data-stock="<?php echo $product['stock']; ?>" data-series="<?php echo htmlspecialchars($product['series']); ?>">
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
                    <p class="product-price">₱ <?php echo formatPrice($product['price']); ?></p>
                    <div class="product-actions">
                        <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                        <a href="/CURATOR/products/detail.php?id=<?php echo $product['id']; ?>" class="view-details-btn">Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

