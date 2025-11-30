<?php
include_once 'database.php';
include_once 'product.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);


if(isset($_POST['action']) && $_POST['action'] == 'update_stock') {
    $id = $_POST['id'];
    $stock = $_POST['stock'];

    // Kita jalankan query update langsung di sini agar tidak perlu ubah file product.php
    $query = "UPDATE products SET stock = :stock WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':stock', $stock);
    $stmt->bindParam(':id', $id);

    if($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit; // Stop agar tidak lanjut ke kode di bawahnya
}

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