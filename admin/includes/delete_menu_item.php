<?php
ob_start();
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

$item_id = (int) $_GET['id'];
$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    $connection->begin_transaction();

    // Fetch item & image
    $stmt = $connection->prepare(
        "SELECT image_url FROM menu_items WHERE id = ?"
    );
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $connection->rollback();
        echo json_encode(['success' => false, 'message' => 'Menu item not found']);
        exit;
    }

    $item = $result->fetch_assoc();
    $stmt->close();

    // 1️⃣ Delete dependent order_items FIRST
    $stmt = $connection->prepare(
        "DELETE FROM order_items WHERE menu_item_id = ?"
    );
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $stmt->close();

    // 2️⃣ Delete menu item
    $stmt = $connection->prepare(
        "DELETE FROM menu_items WHERE id = ?"
    );
    $stmt->bind_param("i", $item_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $stmt->close();
        $connection->rollback();
        echo json_encode(['success' => false, 'message' => 'Delete failed']);
        exit;
    }

    $stmt->close();

    // 3️⃣ Delete image (AFTER DB success)
    if (!empty($item['image_url'])) {
        $imagePath = __DIR__ . '/../../uploads/menu/' . $item['image_url'];
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }

    $connection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Menu item deleted successfully'
    ]);
} catch (Throwable $e) {
    $connection->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Server error'
    ]);
}