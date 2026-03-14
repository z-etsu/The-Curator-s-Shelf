<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();

$cart = getCartFromDatabase();
$cartEmpty = empty($cart);
$cartTotal = getCartTotal($cart);

// Debug logging
error_log('DEBUG cart/view.php - Cart state: ' . json_encode($cart));
error_log('DEBUG cart/view.php - Cart keys: ' . implode(', ', array_keys($cart)));
?>

<h2 class="section-title">Shopping Cart</h2>

<?php if ($cartEmpty): ?>
    <div class="empty-cart">
        <h2>Your Cart is Empty</h2>
        <p>Add some amazing action figures to get started!</p>
        <a href="/CURATOR/products/list.php" class="btn">Continue Shopping</a>
    </div>
<?php else: ?>
    <form id="cartForm" method="POST" action="/CURATOR/checkout/index.php">
        <table class="cart-table">
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                    <th style="width: 150px;">Image</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $productId => $item): ?>
                    <tr>
                        <td><input type="checkbox" class="item-select" name="selected_items[]" value="<?php echo $productId; ?>" onchange="updateCheckboxState()"></td>
                        <td>
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 150px; height: auto; object-fit: cover;">
                        </td>
                        <td class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>₱ <?php echo formatPrice($item['price']); ?></td>
                        <td>
                            <div class="quantity-control">
                                <button type="button" onclick="handleQuantityChange(<?php echo $productId; ?>, <?php echo $item['quantity'] - 1; ?>)">−</button>
                                <input type="number" value="<?php echo $item['quantity']; ?>" readonly>
                                <button type="button" onclick="handleQuantityChange(<?php echo $productId; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                            </div>
                        </td>
                        <td>₱ <?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
<?php endif; ?>

<!-- Sticky Bottom Summary Bar -->
<?php if (!$cartEmpty): ?>
<div class="sticky-cart-summary">
    <div class="summary-content">
        <div class="summary-stats">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span id="stickySubtotalPrice">₱ 0.00</span>
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
                <span id="stickyTotalPrice">₱ 0.00</span>
            </div>
        </div>
        <div class="summary-actions">
            <button type="button" class="btn" onclick="proceedToCheckout()" style="flex: 1;">Proceed to Checkout</button>
            <a href="/CURATOR/products/list.php" class="btn btn-outline" style="flex: 1;">Continue Shopping</a>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.item-select');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    updateCheckboxState();
}

function updateCheckboxState() {
    const checkboxes = document.querySelectorAll('.item-select');
    const selectAll = document.getElementById('selectAll');
    const checkedCount = document.querySelectorAll('.item-select:checked').length;
    selectAll.checked = checkedCount === checkboxes.length && checkboxes.length > 0;
    updateSummary();
}

function updateSummary() {
    let subtotal = 0;
    const selectedItems = document.querySelectorAll('.item-select:checked');
    
    selectedItems.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const priceCell = row.cells[3].textContent;
        const price = parseFloat(priceCell.replace('₱ ', '').replace(/,/g, ''));
        const quantityInput = row.cells[4].querySelector('input[type="number"]');
        const quantity = parseInt(quantityInput.value);
        
        subtotal += price * quantity;
    });
    
    // Update sticky bar
    const subtotalFormatted = subtotal.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('stickySubtotalPrice').textContent = '₱ ' + subtotalFormatted;
    document.getElementById('stickyTotalPrice').textContent = '₱ ' + subtotalFormatted;
}

function proceedToCheckout() {
    const selectedItems = document.querySelectorAll('.item-select:checked');
    if (selectedItems.length === 0) {
        showConfirmModal(
            'No Items Selected',
            'Please select at least one item to proceed to checkout.',
            'OK',
            function() {
                // Close modal
            },
            'OK'
        );
        return false;
    }
    
    // Submit form
    document.getElementById('cartForm').submit();
}

function handleQuantityChange(productId, newQuantity) {
    console.log('handleQuantityChange - productId:', productId, 'newQuantity:', newQuantity);
    if (newQuantity < 0 || newQuantity === 0) {
        showConfirmModal(
            'Remove Item',
            'Are you sure you want to remove this item from your cart?',
            'Remove',
            function() {
                removeFromCart(productId);
            }
        );
        return;
    }

    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', newQuantity);

    fetch('/CURATOR/cart/update.php', {
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
    console.log('removeFromCart called with productId:', productId, 'Type:', typeof productId);
    const formData = new FormData();
    formData.append('product_id', productId);

    fetch('/CURATOR/cart/remove.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            console.log('Remove response:', data);
            if (data.success) {
                location.reload();
            } else {
                alert('Error removing from cart: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error removing from cart');
        });
}

// Initialize summary on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSummary();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
