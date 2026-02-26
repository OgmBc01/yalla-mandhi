<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
$connection = getDBConnection();
if (!$connection) {
    die("Database connection failed.");
}

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized - Please log in");
}

// Get parameters
$raw_id = $_GET['id'] ?? '';
$receipt_type = isset($_GET['type']) ? $_GET['type'] : 'counter';

if (!$raw_id) {
    die("Invalid order ID: " . htmlspecialchars($raw_id));
}

// Determine if ID is numeric (closed order) or string (draft)
if (is_numeric($raw_id)) {
    $order_id = (int)$raw_id;
    // Fetch closed order
    $query = "SELECT o.*, 
                 u1.full_name as punched_by_name,
                 b.name as branch_name
          FROM orders o
          LEFT JOIN users u1 ON o.punched_by_admin_id = u1.id
          LEFT JOIN branches b ON o.branch_id = b.id
          WHERE o.id = ?";
    $stmt = $connection->prepare($query);
    if (!$stmt) {
        die("Database error: " . $connection->error);
    }
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die("Order not found with ID: " . $order_id);
    }
    $order = $result->fetch_assoc();
    $stmt->close();
    
    // Fetch order items
    $items_query = "SELECT * FROM order_items WHERE order_id = ?";
    $items_stmt = $connection->prepare($items_query);
    if (!$items_stmt) {
        die("Database error preparing items query: " . $connection->error);
    }
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
} else {
    // Fetch draft order from pos_order_drafts
    $id = $connection->real_escape_string($raw_id);
    $sql = "SELECT data FROM pos_order_drafts WHERE id = '$id' AND is_deleted = 0";
    $result = $connection->query($sql);
    if (!$result || $result->num_rows === 0) {
        die("Draft order not found with ID: " . htmlspecialchars($raw_id));
    }
    $row = $result->fetch_assoc();
    $order = json_decode($row['data'], true);
    
    // Prepare draft items array for rendering
    $draft_items = [];
    if (isset($order['items']) && is_array($order['items'])) {
        foreach ($order['items'] as $item) {
            $draft_items[] = [
                'item_name_snapshot' => $item['name'] ?? 'Unknown',
                'quantity' => $item['qty'] ?? 1,
                'unit_price_snapshot' => $item['price'] ?? 0,
                'total_price' => ($item['qty'] ?? 1) * ($item['price'] ?? 0),
                'special_instructions' => $item['special_instructions'] ?? ''
            ];
        }
    }
}

// Log the print (optional - comment out if printer_logs table doesn't exist)
if ($connection->query("SHOW TABLES LIKE 'printer_logs'")->num_rows > 0) {
    $log_stmt = $connection->prepare(
        "INSERT INTO printer_logs (order_id, receipt_type, printed_by, is_reprint) 
         VALUES (?, ?, ?, ?)"
    );
    if ($log_stmt) {
        $is_reprint = isset($_GET['reprint']) ? 1 : 0;
        $log_id = isset($order_id) ? $order_id : $raw_id;
        $log_stmt->bind_param("siii", $log_id, $receipt_type, $_SESSION['user_id'], $is_reprint);
        $log_stmt->execute();
        $log_stmt->close();
    }
}

// Restaurant info
$restaurant_name = "YALLA AL MANDI";
$restaurant_phone = "+971 50 375 7274";
$trn = "TRN: 105144729800003";
$address = "Shop No.:08 Royal Class Building, Dubai Investment Park 1, Green Community Village, Dubai.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $receipt_type == 'kitchen' ? 'Kitchen Receipt' : 'Customer Receipt'; ?> - Order #<?php echo htmlspecialchars($order['order_number'] ?? $raw_id); ?></title>
    <style>
        /* RESET ALL STYLES - Override any admin panel styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #f5f5f5;
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Courier New', Courier, monospace;
        }
        
        /* Override any possible admin panel styles */
        body, div, section, main, article, aside, header, footer, nav {
            all: unset;
            display: block;
        }
        
        .receipt-container {
            max-width: 72mm; /* Exactly 72mm for thermal printer */
            width: 72mm;
            margin: 0 auto;
            background: white;
            border-radius: 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 3mm;
            font-size: 10pt;
            line-height: 1.4;
            font-weight: normal;
        }
        
        /* Thermal printer specific - 72mm width */
        @page {
            size: 72mm 297mm; /* Width 72mm, Height 297mm (max) */
            margin: 0;
        }
        
        @media print {
            html, body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 72mm !important;
                min-height: auto !important;
                display: block !important;
            }
            
            .receipt-container {
                max-width: 72mm !important;
                width: 72mm !important;
                margin: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 2mm !important;
                page-break-after: avoid;
                page-break-inside: avoid;
            }
            
            /* Hide all admin panel elements */
            .sidebar, .main-content, .navbar, .header, .footer,
            .menu, .nav, .top-bar, .left-menu, .right-menu,
            .no-print, .print-btn, .btn, button:not(.print-btn) {
                display: none !important;
            }
        }
        
        /* Screen styles */
        @media screen {
            body {
                padding: 20px;
            }
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 2px solid #c41e3a;
        }
        
        .receipt-header h1 {
            font-size: 18pt;
            font-weight: 900;
            color: #c41e3a;
            margin-bottom: 2mm;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .receipt-header h2 {
            font-size: 14pt;
            font-weight: 800;
            color: #f39c12;
            margin-bottom: 1mm;
            text-transform: uppercase;
        }
        
        .receipt-header .trn {
            font-size: 10pt;
            font-weight: 700;
            color: #333;
            margin-bottom: 2mm;
            border-top: 1px dashed #999;
            border-bottom: 1px dashed #999;
            padding: 2mm 0;
        }
        
        .receipt-info {
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 1px solid #ddd;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 10pt;
            margin-bottom: 1.5mm;
            font-weight: 500;
        }
        
        .info-label {
            font-weight: 700;
            color: #333;
        }
        
        .info-value {
            color: #000;
            font-weight: 600;
            text-align: right;
        }
        
        .items-table {
            width: 100%;
            margin-bottom: 4mm;
            border-collapse: collapse;
        }
        
        .items-table th {
            text-align: left;
            font-size: 10pt;
            font-weight: 800;
            padding: 1.5mm 0;
            border-bottom: 2px solid #c41e3a;
            color: #c41e3a;
            text-transform: uppercase;
        }
        
        .items-table td {
            padding: 1.5mm 0;
            font-size: 10pt;
            font-weight: 500;
        }
        
        .items-table .item-name {
            font-weight: 700;
            width: 50%;
        }
        
        .items-table .item-qty {
            text-align: center;
            width: 15%;
            font-weight: 600;
        }
        
        .items-table .item-price {
            text-align: right;
            width: 17%;
            font-weight: 600;
        }
        
        .items-table .item-total {
            text-align: right;
            width: 18%;
            font-weight: 700;
        }
        
        .item-notes {
            font-size: 9pt;
            color: #f39c12;
            font-style: italic;
            font-weight: 600;
            padding-left: 2mm;
        }
        
        .totals {
            margin-top: 4mm;
            padding-top: 3mm;
            border-top: 2px solid #c41e3a;
            border-bottom: 2px solid #c41e3a;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 11pt;
            margin-bottom: 2mm;
            font-weight: 600;
        }
        
        .grand-total {
            font-size: 14pt;
            font-weight: 900;
            color: #c41e3a;
            margin-top: 3mm;
            padding-top: 3mm;
            border-top: 2px solid #f39c12;
        }
        
        .grand-total span:last-child {
            font-size: 16pt;
            font-weight: 900;
        }
        
        .payment-info {
            margin-top: 3mm;
            padding: 2mm;
            background: #f0f0f0;
            border-radius: 2mm;
            font-size: 10pt;
            font-weight: 600;
        }
        
        .payment-info .info-row {
            margin-bottom: 1mm;
        }
        
        .footer {
            text-align: center;
            margin-top: 4mm;
            padding-top: 3mm;
            border-top: 2px solid #c41e3a;
            font-size: 9pt;
            color: #666;
        }
        
        .footer p {
            margin-bottom: 1mm;
        }
        
        .footer .thank-you {
            font-size: 16pt;
            font-weight: 900;
            color: #c41e3a;
            margin: 3mm 0 2mm;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Bolder address section for counter receipt */
        .branch-info {
            margin-top: 3mm;
            font-size: 9pt;
            font-weight: 700;
            color: #333;
            text-align: center;
            border-top: 1px solid #c41e3a;
            border-bottom: 1px solid #c41e3a;
            padding: 2mm;
            background: #fafafa;
        }
        
        .branch-info .address {
            font-weight: 700;
            margin-bottom: 1mm;
        }
        
        .branch-info .phone {
            font-weight: 800;
            color: #c41e3a;
        }
        
        .vendor-badge {
            background: #f39c12;
            color: white;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 9pt;
            font-weight: 600;
            display: inline-block;
        }
        
        .print-btn {
            display: block;
            width: 100%;
            padding: 3mm;
            background: #c41e3a;
            color: white;
            border: none;
            border-radius: 2mm;
            font-size: 12pt;
            font-weight: 700;
            cursor: pointer;
            margin: 3mm 0;
            text-align: center;
        }
        
        .print-btn:hover {
            background: #a01830;
        }
        
        .print-btn i {
            margin-right: 2mm;
        }
        
        .kitchen-only {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 2mm;
            margin-bottom: 3mm;
            font-weight: 700;
            text-align: center;
            font-size: 11pt;
            color: #856404;
        }
        
        .kitchen-only i {
            margin-right: 2mm;
        }
        
        /* Ensure no admin elements interfere */
        .main-content, .sidebar, .navbar, .header, .footer,
        .menu, .nav, .top-bar, .left-menu, .right-menu {
            display: none !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="receipt-container">
        <?php if ($receipt_type == 'kitchen'): ?>
            <!-- KITCHEN RECEIPT (No address here) -->
            <div class="kitchen-only">
                <i class="bi bi-egg-fried"></i> KITCHEN COPY - FOR PREPARATION ONLY
            </div>
            
            <div class="receipt-header">
                <h2><?php echo $restaurant_name; ?></h2>
                <div class="trn"><?php echo $trn; ?></div>
            </div>
            
            <div class="receipt-info">
                <div class="info-row">
                    <span class="info-label">Order #:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['order_number'] ?? $raw_id); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date/Time:</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'] ?? 'now')); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Type:</span>
                    <span class="info-value">
                        <?php 
                        echo ucfirst(str_replace('_', ' ', $order['order_type'] ?? $order['type'] ?? 'N/A'));
                        if (!empty($order['table_number'])) {
                            echo ' - T' . $order['table_number'];
                        }
                        ?>
                    </span>
                </div>
                <?php if (!empty($order['delivery_source']) && $order['delivery_source'] != 'internal'): ?>
                <div class="info-row">
                    <span class="info-label">Vendor:</span>
                    <span class="info-value">
                        <span class="vendor-badge">
                            <i class="bi bi-<?php 
                                echo $order['delivery_source'] == 'noon' ? 'sun' : 
                                    ($order['delivery_source'] == 'deliveroo' ? 'bicycle' : 
                                    ($order['delivery_source'] == 'keeta' ? 'lightning' : 'emoji-smile')); 
                            ?>"></i>
                            <?php echo ucfirst($order['delivery_source']); ?>
                        </span>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="item-qty">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (isset($draft_items)) {
                        // Draft order rendering
                        if (count($draft_items) > 0):
                            foreach ($draft_items as $item):
                    ?>
                    <tr>
                        <td class="item-name"><?php echo htmlspecialchars($item['item_name_snapshot']); ?></td>
                        <td class="item-qty"><?php echo $item['quantity']; ?></td>
                    </tr>
                    <?php if (!empty($item['special_instructions'])): ?>
                    <tr>
                        <td colspan="2" class="item-notes">
                            <i class="bi bi-chat"></i> <?php echo htmlspecialchars($item['special_instructions']); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php 
                            endforeach;
                        else:
                    ?>
                    <tr>
                        <td colspan="2" style="text-align: center; color: #999;">No items</td>
                    </tr>
                    <?php 
                        endif;
                    } else {
                        // Closed order rendering
                        $items_result->data_seek(0);
                        if ($items_result->num_rows > 0):
                            while ($item = $items_result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td class="item-name"><?php echo htmlspecialchars($item['item_name_snapshot']); ?></td>
                        <td class="item-qty"><?php echo $item['quantity']; ?></td>
                    </tr>
                    <?php if (!empty($item['special_instructions'])): ?>
                    <tr>
                        <td colspan="2" class="item-notes">
                            <i class="bi bi-chat"></i> <?php echo htmlspecialchars($item['special_instructions']); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php 
                            endwhile;
                        else:
                    ?>
                    <tr>
                        <td colspan="2" style="text-align: center; color: #999;">No items</td>
                    </tr>
                    <?php 
                        endif;
                    }
                    ?>
                </tbody>
            </table>
            
            <div class="kitchen-only" style="margin-top: 3mm;">
                <i class="bi bi-clock-history"></i> Time: <?php echo date('H:i', strtotime($order['created_at'] ?? 'now')); ?>
            </div>
            
        <?php else: ?>
            <!-- COUNTER / CUSTOMER RECEIPT (With address and phone here) -->
            <div class="receipt-header">
                <h1><?php echo $restaurant_name; ?></h1>
                <div class="trn"><?php echo $trn; ?></div>
            </div>
            
            <div class="receipt-info">
                <div class="info-row">
                    <span class="info-label">Invoice:</span>
                    <span class="info-value"><?php echo $order['invoice_number'] ?? 'INV-' . str_pad(($order['id'] ?? 0), 8, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Order #:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['order_number'] ?? $raw_id); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date:</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'] ?? 'now')); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cashier:</span>
                    <span class="info-value"><?php echo $order['punched_by_name'] ?? ($_SESSION['username'] ?? 'N/A'); ?></span>
                </div>
            </div>
            
            <div class="receipt-info">
                <div class="info-row">
                    <span class="info-label">Customer:</span>
                    <span class="info-value"><?php echo $order['customer_name_snapshot'] ?? $order['customer']['name'] ?? 'Guest'; ?></span>
                </div>
                <?php if (!empty($order['customer_phone_snapshot'] ?? $order['customer']['phone'] ?? '')): ?>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo $order['customer_phone_snapshot'] ?? $order['customer']['phone'] ?? ''; ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['table_number'])): ?>
                <div class="info-row">
                    <span class="info-label">Table:</span>
                    <span class="info-value"><?php echo $order['table_number']; ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="item-qty">Qty</th>
                        <th class="item-price">Price</th>
                        <th class="item-total">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (isset($draft_items)) {
                        // Draft order rendering
                        if (count($draft_items) > 0):
                            foreach ($draft_items as $item):
                    ?>
                    <tr>
                        <td class="item-name"><?php echo htmlspecialchars($item['item_name_snapshot']); ?></td>
                        <td class="item-qty"><?php echo $item['quantity']; ?></td>
                        <td class="item-price"><?php echo number_format($item['unit_price_snapshot'], 2); ?></td>
                        <td class="item-total"><?php echo number_format($item['total_price'], 2); ?></td>
                    </tr>
                    <?php if (!empty($item['special_instructions'])): ?>
                    <tr>
                        <td colspan="4" class="item-notes">
                            <i class="bi bi-chat"></i> <?php echo htmlspecialchars($item['special_instructions']); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php 
                            endforeach;
                        else:
                    ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">No items</td>
                    </tr>
                    <?php 
                        endif;
                    } else {
                        // Closed order rendering
                        $items_result->data_seek(0);
                        if ($items_result->num_rows > 0):
                            while ($item = $items_result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td class="item-name"><?php echo htmlspecialchars($item['item_name_snapshot']); ?></td>
                        <td class="item-qty"><?php echo $item['quantity']; ?></td>
                        <td class="item-price"><?php echo number_format($item['unit_price_snapshot'], 2); ?></td>
                        <td class="item-total"><?php echo number_format($item['total_price'], 2); ?></td>
                    </tr>
                    <?php if (!empty($item['special_instructions'])): ?>
                    <tr>
                        <td colspan="4" class="item-notes">
                            <i class="bi bi-chat"></i> <?php echo htmlspecialchars($item['special_instructions']); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php 
                            endwhile;
                        else:
                    ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">No items</td>
                    </tr>
                    <?php 
                        endif;
                    }
                    ?>
                </tbody>
            </table>
            
            <div class="totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>
                        <?php
                        if (isset($draft_items)) {
                            $subtotal = 0;
                            foreach ($draft_items as $item) {
                                $subtotal += $item['total_price'];
                            }
                            echo number_format($subtotal, 2);
                        } else {
                            echo number_format($order['subtotal'] ?? 0, 2);
                        }
                        ?> AED
                    </span>
                </div>
                <?php if (($order['discount_amount'] ?? 0) > 0): ?>
                <div class="total-row">
                    <span>Discount:</span>
                    <span>-<?php echo number_format($order['discount_amount'] ?? 0, 2); ?> AED</span>
                </div>
                <?php endif; ?>
                <?php if (($order['tax_amount'] ?? 0) > 0): ?>
                <div class="total-row">
                    <span>Tax:</span>
                    <span><?php echo number_format($order['tax_amount'] ?? 0, 2); ?> AED</span>
                </div>
                <?php endif; ?>
                <?php if (($order['delivery_fee'] ?? 0) > 0): ?>
                <div class="total-row">
                    <span>Delivery:</span>
                    <span><?php echo number_format($order['delivery_fee'] ?? 0, 2); ?> AED</span>
                </div>
                <?php endif; ?>
                <div class="grand-total total-row">
                    <span>TOTAL:</span>
                    <span><?php echo number_format($order['total_amount'] ?? 0, 2); ?> AED</span>
                </div>
            </div>
            
            <div class="payment-info">
                <div class="info-row">
                    <span class="info-label">Payment:</span>
                    <span class="info-value">
                        <?php 
                        $method = $order['payment_method'] ?? 'cash';
                        $method_icons = [
                            'cash' => 'cash-coin',
                            'card' => 'credit-card',
                            'online' => 'wifi'
                        ];
                        ?>
                        <i class="bi bi-<?php echo $method_icons[$method] ?? 'cash-coin'; ?>"></i>
                        <?php echo ucfirst(str_replace('_', ' ', $method)); ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <?php if (($order['payment_status'] ?? '') == 'paid'): ?>
                        <span style="color: #27ae60; font-weight: 700;">✓ PAID</span>
                        <?php else: ?>
                        <span style="color: #e74c3c; font-weight: 700;">⨯ <?php echo strtoupper($order['payment_status'] ?? 'pending'); ?></span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            
            <!-- Branch Address & Phone - Bolder and Only on Counter Receipt -->
            <div class="branch-info">
                <div class="address"><i class="bi bi-geo-alt-fill me-1"></i> <?php echo $address; ?></div>
                <div class="phone"><i class="bi bi-telephone-fill me-1"></i> <?php echo $restaurant_phone; ?></div>
            </div>
            
            <!-- Thank You Message - Big and Bold -->
            <div class="footer">
                <div class="thank-you">Thank You!</div>
                <div style="font-weight: 600; margin: 2mm 0;">Visit Us Again</div>
            </div>
        <?php endif; ?>
        
        <!-- Print Button (screen only) -->
        <div class="no-print" style="text-align: center; margin-top: 10px;">
            <button class="print-btn" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Receipt
            </button>
            <br>
            <button class="print-btn" onclick="window.close()" style="background: #6c757d; margin-top: 5px;">
                <i class="bi bi-x-circle"></i> Close Window
            </button>
        </div>
    </div>
</body>
</html>
<?php
if (isset($items_stmt) && $items_stmt) {
    $items_stmt->close();
}
?>