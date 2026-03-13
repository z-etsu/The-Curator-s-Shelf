<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();

$cart = $_SESSION['cart'] ?? [];
$cartEmpty = empty($cart);
$cartTotal = getCartTotal($cart);
?>

<h2 class="section-title">Shopping Cart</h2>

<?php if ($cartEmpty): ?>
    <div class="empty-cart">
        <h2>Your Cart is Empty</h2>
        <p>Add some amazing action figures to get started!</p>
        <a href="/products/list.php" class="btn">Continue Shopping</a>
    </div>
<?php else: ?>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart as $productId => $item): ?>
                <tr>
                    <td class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>₱ <?php echo formatPrice($item['price']); ?></td>
                    <td>
                        <div class="quantity-control">
                            <button type="button" onclick="updateQuantity(<?php echo $productId; ?>, <?php echo $item['quantity'] - 1; ?>)">−</button>
                            <input type="number" value="<?php echo $item['quantity']; ?>" readonly>
                            <button type="button" onclick="updateQuantity(<?php echo $productId; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                        </div>
                    </td>
                    <td>₱ <?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                    <td>
                        <button class="remove-btn" onclick="removeFromCart(<?php echo $productId; ?>)">Remove</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="cart-summary">
        <div class="summary-row">
            <span>Subtotal:</span>
            <span>৳ <?php echo formatPrice($cartTotal); ?></span>
        </div>
        <div class="summary-row">
            <span>Shipping:</span>
            <span>Free</span>
        </div>
        <div class="summary-row">
            <span>Tax:</span>
            <span>₱ 0.00</span>
        </div>
        <div class="summary-row total">
            <span>Total:</span>
            <span>৳ <?php echo formatPrice($cartTotal); ?></span>
        </div>
        <br>
        <a href="/checkout/index.php" class="btn" style="width: 100%; text-align: center;">Proceed to Checkout</a>
        <a href="/products/list.php" class="btn btn-outline" style="width: 100%; text-align: center; margin-top: 0.5rem;">Continue Shopping</a>
    </div>
<?php endif; ?>

<script>
function updateQuantity(productId, newQuantity) {
    if (newQuantity < 1) {
        alert('Quantity must be at least 1');
        return;
    }
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', newQuantity);

    fetch('/cart/update.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating quantity');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating quantity');
        });
}

function removeFromCart(productId) {
    if (confirm('Remove this item from cart?')) {
        const formData = new FormData();
        formData.append('product_id', productId);

        fetch('/cart/remove.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error removing from cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error removing from cart');
            });
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
