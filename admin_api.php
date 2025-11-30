<?php
include_once '../config/database.php';
include_once '../classes/product.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle Delete via AJAX
if ($action == 'delete' && isset($_GET['id'])) {
    if($product->delete($_GET['id'])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}

// Handle Live Search via AJAX
if ($action == 'search' && isset($_GET['keyword'])) {
    $stmt = $product->search($_GET['keyword']);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
}
?>