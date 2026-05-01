<?php
require_once __DIR__ . '/../config/database.php';

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: /CURATOR/admin/login.php');
    exit;
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $orderId = intval($_POST['order_id']);
    $newStatus = $_POST['order_status'];
    
    // Get current status
    $stmt = $pdo->prepare('SELECT status FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        $errorMessage = 'Order not found.';
    } else {
        $currentStatus = $order['status'];
        
        // Define valid status transitions
        $validTransitions = [
            'pending' => ['to_ship', 'cancelled'],
            'to_ship' => ['to_receive'],
            'to_receive' => ['delivered'],
            'delivered' => [],
            'return_pending' => ['returned', 'return_denied'],
            'return_denied' => [],
            'returned' => [],
            'cancelled' => []
        ];
        
        // Check if transition is allowed
        if (!isset($validTransitions[$currentStatus]) || !in_array($newStatus, $validTransitions[$currentStatus])) {
            $errorMessage = 'Invalid status transition. Current status: ' . ucfirst(str_replace('_', ' ', $currentStatus));
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
                $stmt->execute([$newStatus, $orderId]);
                $successMessage = 'Order status updated to ' . ucfirst(str_replace('_', ' ', $newStatus)) . ' successfully!';
            } catch (PDOException $e) {
                $errorMessage = 'Failed to update order status.';
            }
        }
    }
}

// Fetch all orders with customer info
try {
    $stmt = $pdo->query('
        SELECT o.*, u.first_name, u.last_name, u.email, COUNT(oi.id) as item_count, SUM(oi.quantity) as total_items
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ');
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}

// Fetch all products
try {
    $stmt = $pdo->query('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name');
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

$successMessage = isset($successMessage) ? $successMessage : '';
$errorMessage = isset($errorMessage) ? $errorMessage : '';

// Check for messages from URL parameters
if (isset($_GET['success'])) {
    $successMessage = $_GET['success'];
}
if (isset($_GET['error'])) {
    $errorMessage = $_GET['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CURATOR</title>
    <link rel="stylesheet" href="/CURATOR/assets/css/style.css">
    <style>
        /* Admin-specific overrides for elegant client-side aesthetic */
        
        header {
            background-color: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        header h1 {
            font-size: 1.5rem;
            color: var(--primary-color);
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .header-actions span {
            color: var(--text-dark);
            font-weight: 500;
            font-family: 'Playfair Display', serif;
        }

        .container {
            display: none;
        }
            margin: 0 auto;
            padding: 3rem 2rem;
            min-height: calc(100vh - 200px);
        }

        .tabs {
            display: none;
        }

        .tab {
            display: none;
        }

        .tab:hover {
            color: var(--text-dark);
        }

        .tab.active {
            display: none;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Admin Sidebar Navigation */
        .admin-wrapper {
            display: flex;
            min-height: calc(100vh - 80px);
        }

        .admin-sidebar {
            width: 250px;
            background-color: white;
            border-right: 1px solid var(--border-color);
            padding: 2rem 0;
            position: sticky;
            top: 80px;
            height: calc(100vh - 80px);
            overflow-y: auto;
        }

        .sidebar-item {
            display: block;
            width: 100%;
            padding: 1rem 1.5rem;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            background: none;
            border: none;
            text-align: left;
            font-size: 0.95rem;
        }

        .sidebar-item:hover {
            background-color: var(--bg-light);
            border-left-color: var(--primary-color);
        }

        .sidebar-item.active {
            background-color: var(--bg-light);
            border-left-color: var(--primary-color);
            color: var(--primary-color);
        }

        .admin-main-content {
            flex: 1;
            padding: 2rem;
        }

        .admin-main-content .alert {
            padding: 1rem;
            border-radius: 0;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            color: #0a3622;
        }

        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 0;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .table th {
            background-color: var(--text-dark);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .table tr:hover {
            background-color: var(--bg-light);
        }

        .status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-to_ship {
            background-color: #cfe2ff;
            color: #084298;
        }

        .status-to_receive {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-delivered {
            background-color: #d1e7dd;
            color: #0a3622;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-returned {
            background-color: #e2e3e5;
            color: #41464b;
        }

        .status-return_pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-return_denied {
            background-color: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            border: none;
            border-radius: 0;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
        }

        .btn-edit {
            background-color: #000000;
            color: white;
        }

        .btn-edit:hover {
            background-color: #1a1a1a;
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .btn-disabled {
            display: inline-block;
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            border-radius: 0;
            cursor: not-allowed;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            background-color: #e0e0e0;
            color: #999999;
        }

        .status-select {
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 0;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
        }

        .form-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .form-modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 0;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-hover);
            border: 1px solid var(--border-color);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            font-family: 'Playfair Display', serif;
            font-weight: 400;
            color: var(--text-dark);
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: var(--text-dark);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #1a1a1a;
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .stock-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .stock-low {
            background-color: #f8d7da;
            color: #721c24;
        }

        .stock-ok {
            background-color: #d1e7dd;
            color: #0a3622;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .summary-card h3 {
            font-size: 0.95rem;
            color: var(--text-light);
            margin-bottom: 0.75rem;
            font-weight: 500;
        }

        .summary-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Header buttons */
        .header-actions .btn {
            padding: 0.75rem 1.5rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
            display: inline-block;
        }

        .header-actions .btn:hover {
            background-color: #1a1a1a;
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .header-actions .btn.btn-danger {
            background-color: #dc3545;
        }

        .header-actions .btn.btn-danger:hover {
            background-color: #c82333;
        }

        /* Add New Product button */
        .container .btn {
            padding: 0.75rem 1.5rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .container .btn:hover {
            background-color: #1a1a1a;
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }
    </style>
</head>
<body>
    <header>
        <h1>CURATOR Admin Dashboard</h1>
        <div class="header-actions">
            <span style="margin-right: 20px;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <button class="btn" onclick="location.reload()" title="Refresh Page" style="padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.5rem;">🔄 Refresh</button>
            <a href="/CURATOR/index.php" class="btn">View Store</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </header>

    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <button class="sidebar-item active" onclick="switchTab('orders', event)" data-tab="orders">
                📋 Orders
            </button>
            <button class="sidebar-item" onclick="switchTab('products', event)" data-tab="products">
                📦 Products
            </button>
        </aside>

        <div class="admin-main-content">
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- Orders Tab -->
        <div id="orders" class="tab-content active">
            <div class="summary">
                <div class="summary-card">
                    <h3>Pending Orders</h3>
                    <div class="value"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'pending')); ?></div>
                </div>
                <div class="summary-card">
                    <h3>To Ship</h3>
                    <div class="value"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'to_ship')); ?></div>
                </div>
                <div class="summary-card">
                    <h3>To Receive</h3>
                    <div class="value"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'to_receive')); ?></div>
                </div>
                <div class="summary-card">
                    <h3>Delivered</h3>
                    <div class="value"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'delivered')); ?></div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>ORD-<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?><br>
                                <small style="color: #666;"><?php echo htmlspecialchars($order['email']); ?></small>
                            </td>
                            <td><?php echo $order['total_items'] ?? 0; ?> items</td>
                            <td>₱ <?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>
                                <?php if ($order['status'] === 'cancelled' || $order['status'] === 'returned' || $order['status'] === 'return_denied' || $order['status'] === 'delivered'): ?>
                                    <span class="btn-disabled">Locked</span>
                                <?php else: ?>
                                    <button class="btn-sm btn-edit" onclick="openOrderModal(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">Update</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Products Tab -->
        <div id="products" class="tab-content">
            <div style="margin-bottom: 20px;">
                <button class="btn" onclick="openProductModal()">+ Add New Product</button>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>#<?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                            <td>₱ <?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <span class="stock-badge <?php echo $product['stock'] < 10 ? 'stock-low' : 'stock-ok'; ?>">
                                    <?php echo $product['stock']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-sm btn-edit" onclick="openProductModal(<?php echo $product['id']; ?>)">Edit</button>
                                    <button class="btn-sm btn-edit" onclick="openStockModal(<?php echo $product['id']; ?>, <?php echo $product['stock']; ?>)">Stock</button>
                                    <button class="btn-sm btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>)">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Status Modal -->
    <div id="orderModal" class="form-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Order Status</h2>
                <button class="close-btn" onclick="closeOrderModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="order_id" id="modalOrderId">
                <div class="form-group">
                    <label for="orderStatus">Order Status</label>
                    <select id="orderStatus" name="order_status" required>
                        <!-- Options will be populated by JavaScript -->
                    </select>
                </div>
                <button type="submit" name="update_order_status" class="btn-submit">Update Status</button>
            </form>
        </div>
    </div>

    <!-- Product Modal -->
    <div id="productModal" class="form-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="productModalTitle">Add New Product</h2>
                <button class="close-btn" onclick="closeProductModal()">&times;</button>
            </div>
            <form method="POST" action="/CURATOR/admin/manage-product.php">
                <input type="hidden" name="product_id" id="modalProductId" value="0">
                <div class="form-group">
                    <label for="productName">Product Name *</label>
                    <input type="text" id="productName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="productPrice">Price *</label>
                    <input type="number" id="productPrice" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="productCategory">Category *</label>
                    <select id="productCategory" name="category_id" required>
                        <option value="">Select Category</option>
                        <option value="1">Kaguya-sama: Love is War</option>
                        <option value="2">Tokyo Ghoul</option>
                        <option value="3">Bleach</option>
                        <option value="4">Dragon Ball</option>
                        <option value="5">Puella Magi Madoka Magica</option>
                        <option value="6">Naruto</option>
                        <option value="7">DC Comics</option>
                        <option value="8">Marvel</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="productStock">Stock *</label>
                    <input type="number" id="productStock" name="stock" required>
                </div>
                <div class="form-group">
                    <label for="productImage">Image URL</label>
                    <input type="url" id="productImage" name="image_url">
                </div>
                <div class="form-group">
                    <label for="productSeries">Series</label>
                    <input type="text" id="productSeries" name="series">
                </div>
                <div class="form-group">
                    <label for="productDescription">Description</label>
                    <textarea id="productDescription" name="description"></textarea>
                </div>
                <button type="submit" class="btn-submit">Save Product</button>
            </form>
        </div>
    </div>

    <!-- Stock Modal -->
    <div id="stockModal" class="form-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Adjust Stock</h2>
                <button class="close-btn" onclick="closeStockModal()">&times;</button>
            </div>
            <form method="POST" action="/CURATOR/admin/adjust-stock.php">
                <input type="hidden" name="product_id" id="stockProductId">
                <div class="form-group">
                    <label>Current Stock: <strong id="currentStock">0</strong></label>
                </div>
                <div class="form-group">
                    <label for="newStock">New Stock *</label>
                    <input type="number" id="newStock" name="stock" required>
                </div>
                <button type="submit" class="btn-submit">Update Stock</button>
            </form>
        </div>
        </div>
    </div>

    <script>
        function switchTab(tabName, event) {
            if (event) {
                event.preventDefault();
            }
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Update sidebar items
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        function openOrderModal(orderId, status) {
            document.getElementById('modalOrderId').value = orderId;
            
            // Define valid status transitions and labels
            const statusTransitions = {
                'pending': [
                    { value: 'to_ship', label: 'To Ship' },
                    { value: 'cancelled', label: 'Cancel Order' }
                ],
                'to_ship': [
                    { value: 'to_receive', label: 'To Receive' }
                ],
                'to_receive': [
                    { value: 'delivered', label: 'Delivered' }
                ],
                'delivered': [],
                'return_pending': [
                    { value: 'returned', label: 'Approve Return' },
                    { value: 'return_denied', label: 'Deny Return' }
                ],
                'return_denied': [],
                'returned': [],
                'cancelled': []
            };
            
            const statusSelect = document.getElementById('orderStatus');
            statusSelect.innerHTML = '';
            
            const availableStatuses = statusTransitions[status] || [];
            
            if (availableStatuses.length === 0) {
                statusSelect.innerHTML = '<option disabled>No actions available for this status</option>';
                statusSelect.disabled = true;
            } else {
                availableStatuses.forEach(s => {
                    const option = document.createElement('option');
                    option.value = s.value;
                    option.textContent = s.label;
                    statusSelect.appendChild(option);
                });
                statusSelect.disabled = false;
            }
            
            document.getElementById('orderModal').classList.add('active');
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.remove('active');
        }

        // Handle form submission with confirmation for cancelled status
        document.addEventListener('DOMContentLoaded', function() {
            const orderForm = document.querySelector('#orderModal form');
            if (orderForm) {
                orderForm.addEventListener('submit', function(e) {
                    const status = document.getElementById('orderStatus').value;
                    if (status === 'cancelled') {
                        if (!confirm('Are you sure you want to cancel this order? This action cannot be undone and the order status will be locked permanently.')) {
                            e.preventDefault();
                        }
                    }
                });
            }
        });

        function openProductModal(productId = 0) {
            const modal = document.getElementById('productModal');
            
            if (productId === 0) {
                // Add new product
                document.getElementById('productModalTitle').textContent = 'Add New Product';
                document.getElementById('modalProductId').value = 0;
                document.getElementById('productName').value = '';
                document.getElementById('productPrice').value = '';
                document.getElementById('productCategory').value = '';
                document.getElementById('productStock').value = '';
                document.getElementById('productImage').value = '';
                document.getElementById('productSeries').value = '';
                document.getElementById('productDescription').value = '';
            } else {
                // Edit product - fetch data via AJAX
                document.getElementById('productModalTitle').textContent = 'Edit Product';
                document.getElementById('modalProductId').value = productId;
                fetch(`/CURATOR/admin/get-product.php?id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('productName').value = data.name;
                        document.getElementById('productPrice').value = data.price;
                        document.getElementById('productCategory').value = data.category_id;
                        document.getElementById('productStock').value = data.stock;
                        document.getElementById('productImage').value = data.image_url || '';
                        document.getElementById('productSeries').value = data.series || '';
                        document.getElementById('productDescription').value = data.description || '';
                    });
            }
            
            modal.classList.add('active');
        }

        function closeProductModal() {
            document.getElementById('productModal').classList.remove('active');
        }

        function openStockModal(productId, currentStock) {
            document.getElementById('stockProductId').value = productId;
            document.getElementById('currentStock').textContent = currentStock;
            document.getElementById('newStock').value = currentStock;
            document.getElementById('stockModal').classList.add('active');
        }

        function closeStockModal() {
            document.getElementById('stockModal').classList.remove('active');
        }

        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                window.location.href = `/CURATOR/admin/delete-product.php?id=${productId}`;
            }
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const orderModal = document.getElementById('orderModal');
            const productModal = document.getElementById('productModal');
            const stockModal = document.getElementById('stockModal');

            if (event.target === orderModal) {
                closeOrderModal();
            }
            if (event.target === productModal) {
                closeProductModal();
            }
            if (event.target === stockModal) {
                closeStockModal();
            }
        });

        // Auto-dismiss alerts after 4 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.3s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 4000);
            });
        });
    </script>
</body>
</html>
