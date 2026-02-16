<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = (int)$_GET['id'];
$current_user_role = $_SESSION['role'] ?? '';

// Fetch order details
$query = "SELECT o.* FROM orders o WHERE o.id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: orders.php");
    exit();
}

$order = $result->fetch_assoc();
$stmt->close();

// Check if order can be edited
if (!in_array($order['order_status'], ['draft', 'pending', 'confirmed'])) {
    $_SESSION['error'] = "This order cannot be edited in its current status";
    header("Location: orders.php?source=view_order&id=" . $order_id);
    exit();
}

// Fetch order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = $connection->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$order_items = [];
while ($item = $items_result->fetch_assoc()) {
    $order_items[] = $item;
}
$items_stmt->close();

// Get menu categories for the POS interface
$categories = [];
$cat_query = "SELECT id, name FROM menu_categories WHERE is_active = 1 ORDER BY sort_order, name";
$cat_result = $connection->query($cat_query);
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!-- Include the POS interface but pre-filled with order data -->
<div class="pos-container">
    <!-- Same POS structure as pos_order.php but with data pre-filled -->
    <!-- I'll include the full POS structure with order data pre-populated -->
    
    <script>
    // Pre-populate the order data
    $(document).ready(function() {
        // Set order type
        $(`.type-btn[data-type="<?php echo $order['order_type']; ?>"]`).click();
        
        // Set delivery source if applicable
        $('#delivery_source').val("<?php echo $order['delivery_source'] ?? 'internal'; ?>");
        
        // Set table number
        $('#table_number').val("<?php echo $order['table_number'] ?? ''; ?>");
        
        // Set customer info
        $('#customer_phone').val("<?php echo addslashes($order['customer_phone_snapshot'] ?? ''); ?>");
        $('#customer_name').val("<?php echo addslashes($order['customer_name_snapshot'] ?? ''); ?>");
        $('#delivery_address').val("<?php echo addslashes($order['delivery_address_snapshot'] ?? ''); ?>");
        
        // Set payment method
        $('#payment_method').val("<?php echo $order['payment_method'] ?? 'cash'; ?>");
        
        // Set order ID
        $('#current_order_id').val(<?php echo $order_id; ?>);
        
        // Add items to order
        <?php foreach ($order_items as $item): ?>
        addItemToOrder({
            id: <?php echo $item['menu_item_id'] ?? 0; ?>,
            name: "<?php echo addslashes($item['item_name_snapshot']); ?>",
            price: <?php echo $item['unit_price_snapshot']; ?>,
            quantity: <?php echo $item['quantity']; ?>,
            instructions: "<?php echo addslashes($item['special_instructions'] ?? ''); ?>"
        });
        <?php endforeach; ?>
        
        // Recalculate totals
        calculateTotals();
    });
    </script>
</div>