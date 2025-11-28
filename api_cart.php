<?php
require_once 'cart.php';

$action = $_POST['action'] ?? '';
$cart = new Cart();

if ($action == 'add') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    
    $totalQty = $cart->addToCart($id, $name, $price, $image);
    
    echo json_encode(['status' => 'success', 'total_qty' => $totalQty]);
}
?>