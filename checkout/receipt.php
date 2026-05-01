<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

startSession();

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$user = getCurrentUser();
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$orderId) {
    redirect('/CURATOR/orders/index.php');
}

// Fetch order details
try {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
    $stmt->execute([$orderId, $user['id']]);
    $order = $stmt->fetch();

    if (!$order) {
        redirect('/CURATOR/orders/index.php');
    }

    // Fetch order items
    $stmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll();
} catch (PDOException $e) {
    redirect('/CURATOR/orders/index.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt - CURATOR</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 20px;
        }
        
        .store-name {
            font-size: 28px;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 5px;
        }
        
        .receipt-title {
            font-size: 18px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .order-id-receipt {
            font-size: 16px;
            font-weight: bold;
            color: #0066cc;
        }
        
        .receipt-date {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        
        .receipt-section {
            margin-bottom: 25px;
        }
        
        .receipt-section-title {
            font-size: 14px;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .customer-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            font-size: 13px;
            line-height: 1.8;
        }
        
        .customer-info div {
            margin-bottom: 5px;
        }
        
        .customer-label {
            font-weight: bold;
            color: #333;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .items-table th {
            background: #0066cc;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        
        .items-table tr:last-child td {
            border-bottom: 2px solid #0066cc;
        }
        
        .item-price {
            text-align: right;
            width: 100px;
        }
        
        .item-qty {
            text-align: center;
            width: 80px;
        }
        
        .item-total {
            text-align: right;
            width: 120px;
            font-weight: bold;
        }
        
        .totals-section {
            margin-top: 20px;
            padding-top: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
        }
        
        .total-row.subtotal {
            color: #666;
        }
        
        .total-row.shipping {
            color: #666;
        }
        
        .total-row.final {
            font-size: 16px;
            font-weight: bold;
            color: #0066cc;
            padding-top: 10px;
            border-top: 2px solid #0066cc;
            margin-top: 10px;
        }
        
        .shipping-address {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            font-size: 12px;
            line-height: 1.8;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .receipt-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #999;
        }
        
        .button-group {
            text-align: center;
            margin-top: 30px;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            background: #0066cc;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #0052a3;
        }
        
        .btn-secondary {
            background: #666;
        }
        
        .btn-secondary:hover {
            background: #555;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt-container {
                box-shadow: none;
                max-width: 100%;
            }
            
            .button-group {
                display: none;
            }
        }
        
        @media (max-width: 600px) {
            .receipt-container {
                padding: 20px;
            }
            
            .items-table {
                font-size: 11px;
            }
            
            .items-table th,
            .items-table td {
                padding: 8px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <div class="store-name">CURATOR</div>
            <div class="receipt-title">Order Receipt</div>
            <div class="order-id-receipt">ORD-<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></div>
            <div class="receipt-date"><?php echo date('F d, Y \a\t g:i A', strtotime($order['created_at'] ?? date('Y-m-d H:i:s'))); ?></div>
        </div>

        <!-- Customer Info -->
        <div class="receipt-section">
            <div class="receipt-section-title">Shipping Information</div>
            <div class="shipping-address"><?php echo htmlspecialchars($order['shipping_address']); ?></div>
        </div>

        <!-- Order Items -->
        <div class="receipt-section">
            <div class="receipt-section-title">Order Items</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="item-qty">Qty</th>
                        <th class="item-price">Unit Price</th>
                        <th class="item-total">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td class="item-qty"><?php echo $item['quantity']; ?></td>
                            <td class="item-price">₱ <?php echo formatPrice($item['price']); ?></td>
                            <td class="item-total">₱ <?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="receipt-section totals-section">
            <div class="total-row final">
                <span>Total Amount:</span>
                <span>₱ <?php echo formatPrice($order['total_amount']); ?></span>
            </div>
            <div class="total-row">
                <span>Status:</span>
                <span style="font-weight: bold; color: #28a745; text-transform: capitalize;"><?php echo htmlspecialchars($order['status']); ?></span>
            </div>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <p>Thank you for your purchase!</p>
            <p style="margin-top: 10px;">For questions or issues, please contact us at support@curator.com</p>
        </div>

        <!-- Buttons -->
        <div class="button-group">
            <button class="btn" onclick="downloadPDF()">Download as PDF</button>
            <button class="btn btn-secondary" onclick="window.print()">Print</button>
            <a href="/CURATOR/orders/index.php" class="btn btn-secondary">Back to Orders</a>
        </div>
    </div>

    <!-- HTML to PDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <script>
        function downloadPDF() {
            const element = document.querySelector('.receipt-container');
            const opt = {
                margin: 10,
                filename: 'receipt-ORD-<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
            };
            
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
