<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

startSession();

$cart = $_SESSION['cart'] ?? [];
$cartEmpty = empty($cart);

// If cart is empty, redirect
if ($cartEmpty) {
    redirect('/cart/view.php');
}

// Check if user is logged in, if not redirect to login
if (!isLoggedIn()) {
    redirect('/auth/login.php?redirect=/checkout/index.php');
}

$user = getCurrentUser();
$cartTotal = getCartTotal($cart);
$errors = [];
$orderPlaced = false;
$orderId = null;

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form inputs
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $zipCode = sanitize($_POST['zip_code'] ?? '');
    $country = sanitize($_POST['country'] ?? '');

    // Validate required fields
    if (empty($firstName)) $errors[] = 'First name is required';
    if (empty($lastName)) $errors[] = 'Last name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($address)) $errors[] = 'Address is required';
    if (empty($city)) $errors[] = 'City is required';
    if (empty($zipCode)) $errors[] = 'ZIP code is required';

    // Process order if no errors
    if (empty($errors)) {
        try {
            // Build shipping address string
            $shippingAddress = "$firstName $lastName\n$address\n$city, $state $zipCode\n$country\nPhone: $phone\nEmail: $email";

            // Create order
            $stmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user['id'], $cartTotal, 'completed', $shippingAddress]);
            $newOrderId = $pdo->lastInsertId();

            // Add order items
            foreach ($cart as $productId => $item) {
                $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
                $stmt->execute([$newOrderId, $productId, $item['quantity'], $item['price']]);
            }

            // Clear cart from session
            unset($_SESSION['cart']);

            $orderPlaced = true;
            $orderId = $newOrderId;
            
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
                <?php foreach ($cart as $item): ?>
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
        
        <a href="/index.php" class="btn">Back to Home</a>
        <a href="/products/list.php" class="btn btn-outline" style="margin-left: 1rem;">Continue Shopping</a>
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
            <h3>Shipping Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" required>
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

            <div class="form-row">
                <div class="form-group">
                    <label for="zip_code">ZIP Code *</label>
                    <input type="text" id="zip_code" name="zip_code" required>
                </div>
                <div class="form-group">
                    <label for="country">Country *</label>
                    <input type="text" id="country" name="country" value="Bangladesh" required>
                </div>
            </div>

            <button type="submit" class="btn" style="width: 100%; margin-top: 2rem;">Complete Order</button>
        </form>

        <div class="checkout-review">
            <h3>Order Summary</h3>
            
            <?php foreach ($cart as $productId => $item): ?>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
