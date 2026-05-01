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

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $orderToCancel = intval($_POST['order_id']);
    
    // Verify order belongs to user and is pending
    $stmt = $pdo->prepare('SELECT status FROM orders WHERE id = ? AND user_id = ?');
    $stmt->execute([$orderToCancel, $userId]);
    $orderStatus = $stmt->fetch();
    
    if ($orderStatus && $orderStatus['status'] === 'pending') {
        try {
            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ? AND user_id = ?');
            $stmt->execute(['cancelled', $orderToCancel, $userId]);
            $viewOrderId = null; // Reset view to show list
            $cancelSuccess = true;
        } catch (PDOException $e) {
            $cancelError = true;
        }
    }
}

// Handle order return request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_order'])) {
    $orderToReturn = intval($_POST['order_id']);
    
    // Verify order belongs to user and is delivered
    $stmt = $pdo->prepare('SELECT status FROM orders WHERE id = ? AND user_id = ?');
    $stmt->execute([$orderToReturn, $userId]);
    $orderStatus = $stmt->fetch();
    
    if ($orderStatus && $orderStatus['status'] === 'delivered') {
        try {
            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ? AND user_id = ?');
            $stmt->execute(['return_pending', $orderToReturn, $userId]);
            $viewOrderId = null; // Reset view to show list
            $returnSuccess = true;
        } catch (PDOException $e) {
            $returnError = true;
        }
    }
}

// Get selected status filter
$selectedStatus = isset($_GET['status']) ? $_GET['status'] : null;
$validStatuses = ['pending', 'to_ship', 'to_receive', 'delivered', 'return_pending', 'return_denied', 'returned', 'cancelled'];

// Get all orders for the user, optionally filtered by status
if ($selectedStatus === 'returns_cancelled') {
    // Show all return and cancelled orders
    $stmt = $pdo->prepare('SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? AND status IN (?, ?, ?, ?) ORDER BY created_at DESC');
    $stmt->execute([$userId, 'return_pending', 'return_denied', 'returned', 'cancelled']);
} elseif ($selectedStatus && in_array($selectedStatus, $validStatuses)) {
    $stmt = $pdo->prepare('SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? AND status = ? ORDER BY created_at DESC');
    $stmt->execute([$userId, $selectedStatus]);
} else {
    $stmt = $pdo->prepare('SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);
}
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
    <!-- Status Filter Tabs -->
    <div class="orders-filters">
        <a href="/CURATOR/orders/index.php" class="status-tab <?php echo $selectedStatus === null ? 'active' : ''; ?>">
            All Orders
        </a>
        <a href="?status=pending" class="status-tab <?php echo $selectedStatus === 'pending' ? 'active' : ''; ?>">
            Pending
        </a>
        <a href="?status=to_ship" class="status-tab <?php echo $selectedStatus === 'to_ship' ? 'active' : ''; ?>">
            To Ship
        </a>
        <a href="?status=to_receive" class="status-tab <?php echo $selectedStatus === 'to_receive' ? 'active' : ''; ?>">
            To Receive
        </a>
        <a href="?status=delivered" class="status-tab <?php echo $selectedStatus === 'delivered' ? 'active' : ''; ?>">
            Delivered
        </a>
        <a href="?status=returns_cancelled" class="status-tab <?php echo $selectedStatus === 'returns_cancelled' ? 'active' : ''; ?>">
            Returns & Cancelled
        </a>
    </div>

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
                        <?php echo ucfirst(str_replace('_', ' ', $orderDetails['status'])); ?>
                    </span>
                </div>

                <!-- Order Tracking Timeline -->
                <div class="order-tracking">
                    <div class="tracking-timeline">
                        <div class="tracking-step <?php echo in_array($orderDetails['status'], ['pending', 'to_ship', 'to_receive', 'delivered']) ? 'completed' : ''; ?>">
                            <div class="step-circle"></div>
                            <div class="step-label">Pending</div>
                        </div>
                        <div class="tracking-line <?php echo in_array($orderDetails['status'], ['to_ship', 'to_receive', 'delivered']) ? 'completed' : ''; ?>"></div>
                        <div class="tracking-step <?php echo in_array($orderDetails['status'], ['to_ship', 'to_receive', 'delivered']) ? 'completed' : ''; ?>">
                            <div class="step-circle"></div>
                            <div class="step-label">To Ship</div>
                        </div>
                        <div class="tracking-line <?php echo in_array($orderDetails['status'], ['to_receive', 'delivered']) ? 'completed' : ''; ?>"></div>
                        <div class="tracking-step <?php echo in_array($orderDetails['status'], ['to_receive', 'delivered']) ? 'completed' : ''; ?>">
                            <div class="step-circle"></div>
                            <div class="step-label">To Receive</div>
                        </div>
                        <div class="tracking-line <?php echo $orderDetails['status'] === 'delivered' ? 'completed' : ''; ?>"></div>
                        <div class="tracking-step <?php echo $orderDetails['status'] === 'delivered' ? 'completed' : ''; ?>">
                            <div class="step-circle"></div>
                            <div class="step-label">Delivered</div>
                        </div>
                    </div>
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

                <?php if ($orderDetails['status'] === 'pending'): ?>
                    <div class="cancel-order-section">
                        <p class="cancel-notice">This order is still pending and can be cancelled.</p>
                        <button class="btn btn-danger" onclick="confirmCancelOrder(<?php echo $orderDetails['id']; ?>)">Cancel Order</button>
                    </div>
                    
                    <div id="cancelModal" class="modal-overlay" style="display: none;">
                        <div class="modal">
                            <div class="modal-content">
                                <h3>Cancel Order?</h3>
                                <p>Are you sure you want to cancel this order? This action cannot be undone.</p>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $orderDetails['id']; ?>">
                                    <button type="submit" name="cancel_order" class="btn btn-danger">Yes, Cancel Order</button>
                                    <button type="button" class="btn" onclick="closeCancelModal()">No, Keep Order</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                        function confirmCancelOrder(orderId) {
                            document.getElementById('cancelModal').style.display = 'flex';
                        }
                        function closeCancelModal() {
                            document.getElementById('cancelModal').style.display = 'none';
                        }
                    </script>
                <?php endif; ?>

                <?php if ($orderDetails['status'] === 'delivered'): ?>
                    <div class="cancel-order-section">
                        <p class="cancel-notice">This order has been delivered. You can request a return if needed.</p>
                        <button class="btn btn-outline" onclick="confirmReturnOrder(<?php echo $orderDetails['id']; ?>)">Request Return</button>
                    </div>
                    
                    <div id="returnModal" class="modal-overlay" style="display: none;">
                        <div class="modal">
                            <div class="modal-content">
                                <h3>Request Return?</h3>
                                <p>Are you sure you want to request a return for this order?</p>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $orderDetails['id']; ?>">
                                    <button type="submit" name="return_order" class="btn">Yes, Request Return</button>
                                    <button type="button" class="btn btn-outline" onclick="closeReturnModal()">Cancel</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                        function confirmReturnOrder(orderId) {
                            document.getElementById('returnModal').style.display = 'flex';
                        }
                        function closeReturnModal() {
                            document.getElementById('returnModal').style.display = 'none';
                        }
                    </script>
                <?php endif; ?>

                <?php if ($orderDetails['status'] === 'return_pending'): ?>
                    <div class="cancel-order-section" style="background-color: #fff3cd; border-left: 4px solid #ffc107;">
                        <p class="cancel-notice" style="color: #856404; margin: 0;">⏳ Your return request is pending. We're reviewing it and will get back to you soon.</p>
                    </div>
                <?php endif; ?>

                <?php if ($orderDetails['status'] === 'return_denied'): ?>
                    <div class="cancel-order-section" style="background-color: #f8d7da; border-left: 4px solid #dc3545;">
                        <p class="cancel-notice" style="color: #721c24; margin: 0;">❌ Your return request was not approved. This order remains as delivered. Please contact support if you have any questions.</p>
                    </div>
                <?php endif; ?>

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
