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
    
    $validStatuses = ['pending', 'to_ship', 'to_receive', 'delivered', 'cancelled'];
    
    if (in_array($newStatus, $validStatuses)) {
        try {
            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute([$newStatus, $orderId]);
            $successMessage = 'Order status updated successfully!';
        } catch (PDOException $e) {
            $errorMessage = 'Failed to update order status.';
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
            max-width: 1440px;
            margin: 0 auto;
            padding: 3rem 2rem;
            min-height: calc(100vh - 200px);
        }

        .tabs {
            display: flex;
            gap: 2rem;
            margin-bottom: 3rem;
            border-bottom: 1px solid var(--border-color);
            overflow-x: auto;
        }

        .tab {
            padding: 1rem 0;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-light);
            position: relative;
            transition: color 0.3s ease;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
        }

        .tab:hover {
            color: var(--text-dark);
        }

        .tab.active {
            color: var(--text-dark);
            font-weight: 600;
        }

        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .alert {
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
            <a href="/CURATOR/index.php" class="btn">View Store</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </header>

    <div class="container">
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('orders')">Orders</button>
            <button class="tab" onclick="switchTab('products')">Products</button>
        </div>

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
                                <button class="btn-sm btn-edit" onclick="openOrderModal(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">Update</button>
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
                        <option value="pending">Pending</option>
                        <option value="to_ship">To Ship</option>
                        <option value="to_receive">To Receive</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
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

    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));

            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function openOrderModal(orderId, status) {
            document.getElementById('modalOrderId').value = orderId;
            document.getElementById('orderStatus').value = status;
            document.getElementById('orderModal').classList.add('active');
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.remove('active');
        }

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
    </script>
</body>
</html>
