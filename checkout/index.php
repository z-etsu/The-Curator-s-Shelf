<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

startSession();

// Check if user is logged in, if not redirect to login
if (!isLoggedIn()) {
    redirect('/auth/login.php?redirect=/checkout/index.php');
}

$user = getCurrentUser();
$errors = [];
$orderPlaced = false;
$orderId = null;

// Check for direct buy (Buy Now button) - product_id and quantity in URL
$directProductId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$directQuantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
$selectedCart = [];

if ($directProductId > 0) {
    // Direct buy mode - fetch product details
    $stmt = $pdo->prepare('SELECT id, name, price FROM products WHERE id = ?');
    $stmt->execute([$directProductId]);
    $product = $stmt->fetch();
    
    if ($product && $directQuantity >= 1) {
        $selectedCart[$directProductId] = [
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $directQuantity,
            'image_url' => '' // Not needed for checkout
        ];
        $selectedItems = [$directProductId];
    } else {
        redirect('/CURATOR/products/list.php');
    }
} else {
    // Regular checkout from cart
    $cart = getCartFromDatabase();
    $cartEmpty = empty($cart);
    
    // If cart is empty, redirect
    if ($cartEmpty) {
        redirect('/CURATOR/cart/view.php');
    }
    
    // Get selected items from POST
    $selectedItems = $_POST['selected_items'] ?? [];
    
    if (empty($selectedItems)) {
        redirect('/CURATOR/cart/view.php');
    }
    
    // Filter cart to only include selected items
    foreach ($selectedItems as $itemId) {
        $itemId = intval($itemId);
        if (isset($cart[$itemId])) {
            $selectedCart[$itemId] = $cart[$itemId];
        }
    }
}

$cartTotal = getCartTotal($selectedCart);

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Validate form inputs
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $zipCode = sanitize($_POST['zip_code'] ?? '');

    // Validate required fields
    if (empty($firstName)) $errors[] = 'First name is required';
    if (empty($lastName)) $errors[] = 'Last name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($address)) $errors[] = 'Address is required';
    if (empty($city)) $errors[] = 'City is required';
    if (empty($zipCode)) $errors[] = 'ZIP code is required';
    
    // Validate phone number - numbers only
    if (!empty($phone) && !preg_match('/^[0-9\-\+\s()]*$/', $phone)) {
        $errors[] = 'Phone number can only contain numbers, spaces, and dashes';
    }
    
    // Validate zip code - numbers only
    if (!empty($zipCode) && !preg_match('/^[0-9\-\s]*$/', $zipCode)) {
        $errors[] = 'ZIP code can only contain numbers, spaces, and dashes';
    }

    // Process order if no errors
    if (empty($errors)) {
        try {
            // Check stock availability for all items before creating order
            foreach ($selectedCart as $productId => $item) {
                $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                
                if (!$product || $product['stock'] < $item['quantity']) {
                    $errors[] = 'Insufficient stock for one or more items. Please review your cart.';
                    break;
                }
            }

            // Only create order if stock check passed
            if (empty($errors)) {
                // Build shipping address string
                $shippingAddress = "$firstName $lastName\n$address\n$city, $state $zipCode\nPhone: $phone\nEmail: $email";

                // Create order
                $stmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES (?, ?, ?, ?)');
                $stmt->execute([$user['id'], $cartTotal, 'pending', $shippingAddress]);
                $newOrderId = $pdo->lastInsertId();

                // Add order items from selected cart only and update stock
                foreach ($selectedCart as $productId => $item) {
                    $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$newOrderId, $productId, $item['quantity'], $item['price']]);
                    
                    // Update product stock - decrease by quantity purchased
                    $stmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
                    $stmt->execute([$item['quantity'], $productId]);
                }

                // Remove selected items from database cart
                foreach ($selectedItems as $itemId) {
                    $itemId = intval($itemId);
                    removeFromCartDatabase($itemId);
                }

                $orderPlaced = true;
                $orderId = $newOrderId;
            }
            
        } catch (PDOException $e) {
            $errors[] = 'Failed to place order. Please try again.';
        }
    }
}

// Show order confirmation if order was placed
if ($orderPlaced): ?>
    <div class="order-confirmation">
        <div class="success-message">✓ Order Placed Successfully!</div>
        <p>Thank you for your purchase! Your order has been confirmed and is being processed.</p>
        
        <div class="order-id">
            <div class="order-id-label">Order ID:</div>
            <div class="order-id-value">ORD-<?php echo str_pad($orderId, 8, '0', STR_PAD_LEFT); ?></div>
        </div>

        <div style="margin: 2rem 0; text-align: left; background: #f5f5f5; padding: 1rem; border-radius: 4px;">
            <h4>Order Summary</h4>
            <ul style="list-style: none; margin-top: 1rem;">
                <?php foreach ($selectedCart as $item): ?>
                    <li style="padding: 0.5rem 0; border-bottom: 1px solid #ddd;">
                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                        <span style="float: right;">₱ <?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                        <div style="font-size: 0.9rem; color: #666;">Quantity: <?php echo $item['quantity']; ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div style="padding-top: 1rem; border-top: 2px solid #ddd; margin-top: 1rem; font-weight: 700; font-size: 1.1rem;">
                <span>Total Amount: </span>
                <span style="float: right; color: #0066cc;">₱ <?php echo formatPrice($cartTotal); ?></span>
            </div>
        </div>

        <p style="color: #666; margin-bottom: 2rem;">A confirmation email has been sent to your email address. You can track your order status anytime.</p>
        
        <a href="/CURATOR/checkout/receipt.php?order_id=<?php echo $orderId; ?>" class="btn" target="_blank">Download Receipt (PDF)</a>
        <a href="/CURATOR/orders/index.php?order_id=<?php echo $orderId; ?>" class="btn btn-outline" style="margin-left: 1rem;">View Order</a>
        <a href="/CURATOR/products/list.php" class="btn btn-outline" style="margin-left: 1rem;">Continue Shopping</a>
    </div>

<?php else: ?>

    <h2 class="section-title">Checkout</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="checkout-container">
        <form method="POST" class="checkout-form">
            <!-- Hidden inputs for selected items -->
            <?php foreach ($selectedItems as $itemId): ?>
                <input type="hidden" name="selected_items[]" value="<?php echo intval($itemId); ?>">
            <?php endforeach; ?>
            
            <h3>Shipping Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="text" id="phone" name="phone" pattern="[0-9\-\+\s()]*" inputmode="numeric" placeholder="e.g., +63-9XX-XXX-XXXX" required>
            </div>

            <div class="form-group">
                <label for="address">Street Address *</label>
                <input type="text" id="address" name="address" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">City *</label>
                    <input type="text" id="city" name="city" required>
                </div>
                <div class="form-group">
                    <label for="state">State/Province</label>
                    <input type="text" id="state" name="state">
                </div>
            </div>

            <div class="form-group">
                <label for="zip_code">ZIP Code *</label>
                <input type="text" id="zip_code" name="zip_code" pattern="[0-9\-\s]*" inputmode="numeric" placeholder="e.g., 1234" required>
            </div>

            <button type="submit" name="place_order" class="btn" style="width: 100%; margin-top: 2rem;">Complete Order</button>
        </form>

        <div class="checkout-review">
            <h3>Order Summary</h3>
            
            <?php foreach ($selectedCart as $productId => $item): ?>
                <div class="review-item">
                    <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                    <span>₱ <?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                </div>
            <?php endforeach; ?>

            <div class="review-total">
                <span>Total:</span>
                <span>₱ <?php echo formatPrice($cartTotal); ?></span>
            </div>

            <div style="margin-top: 1rem; padding: 1rem; background: #f0f0f0; border-radius: 4px; font-size: 0.9rem; color: #666;">
                <strong>Shipping:</strong> Free
                <br>
                <strong>Tax:</strong> Not applicable
                <br>
                All items ship within 2-3 business days.
            </div>
        </div>
    </div>

<?php endif; ?>

<script>
    // Validate phone number input - only allow numbers, spaces, dashes, parentheses, and plus sign
    document.getElementById('phone')?.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^0-9\-\+\s()]/g, '');
    });

    // Validate zip code input - only allow numbers, spaces, and dashes
    document.getElementById('zip_code')?.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^0-9\-\s]/g, '');
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
