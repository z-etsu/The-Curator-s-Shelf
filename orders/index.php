<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

startSession();

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/CURATOR/auth/login.php');
}

$user = getCurrentUser();
$userId = $_SESSION['user_id'];

// Get all orders for the user
$stmt = $pdo->prepare('SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

// Get order details when viewing a specific order
$viewOrderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
$orderDetails = null;

if ($viewOrderId) {
    // Verify the order belongs to the current user
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
    $stmt->execute([$viewOrderId, $userId]);
    $orderDetails = $stmt->fetch();
    
    if ($orderDetails) {
        // Get order items
        $stmt = $pdo->prepare('
            SELECT oi.*, p.name FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ');
        $stmt->execute([$viewOrderId]);
        $orderItems = $stmt->fetchAll();
    }
}
?>

<h2 class="section-title">My Orders</h2>

<?php if (!$viewOrderId): ?>
    <!-- Orders List View -->
    <?php if (empty($orders)): ?>
        <div class="empty-orders">
            <h3>No Orders Yet</h3>
            <p>You haven't placed any orders. Start shopping to see your orders here!</p>
            <a href="/CURATOR/products/list.php" class="btn">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h4>Order #<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></h4>
                            <p class="order-date"><?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="order-amount">
                            <span class="amount-label">Total</span>
                            <span class="amount-value">₱ <?php echo formatPrice($order['total_amount']); ?></span>
                        </div>
                    </div>
                    <div class="order-status">
                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    <div class="order-footer">
                        <a href="?order_id=<?php echo $order['id']; ?>" class="btn btn-outline btn-small">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Order Details View -->
    <?php if ($orderDetails): ?>
        <div class="order-details-container">
            <a href="/CURATOR/orders/index.php" class="btn btn-outline" style="margin-bottom: 1rem;">← Back to Orders</a>
            
            <div class="order-details">
                <div class="order-details-header">
                    <h3>Order #<?php echo str_pad($orderDetails['id'], 8, '0', STR_PAD_LEFT); ?></h3>
                    <span class="status-badge status-<?php echo strtolower($orderDetails['status']); ?>">
                        <?php echo ucfirst($orderDetails['status']); ?>
                    </span>
                </div>

                <div class="order-details-info">
                    <div class="info-section">
                        <h4>Order Information</h4>
                        <div class="info-row">
                            <span class="label">Order Date:</span>
                            <span class="value"><?php echo date('F d, Y \a\t g:i A', strtotime($orderDetails['created_at'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Total Amount:</span>
                            <span class="value" style="color: #0066cc; font-weight: 700;">₱ <?php echo formatPrice($orderDetails['total_amount']); ?></span>
                        </div>
                    </div>

                    <div class="info-section">
                        <h4>Shipping Address</h4>
                        <div class="shipping-address">
                            <?php echo nl2br(htmlspecialchars($orderDetails['shipping_address'])); ?>
                        </div>
                    </div>
                </div>

                <div class="order-items">
                    <h4>Items Ordered</h4>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>₱ <?php echo formatPrice($item['price']); ?></td>
                                    <td>₱ <?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <a href="/CURATOR/orders/index.php" class="btn btn-outline">← Back to Orders</a>
            </div>
        </div>
    <?php else: ?>
        <div class="error-message">
            <h3>Order Not Found</h3>
            <p>The order you're looking for doesn't exist or you don't have access to it.</p>
            <a href="/CURATOR/orders/index.php" class="btn">Back to Orders</a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
