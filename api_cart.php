<?php
require_once 'cart.php';

$action = $_POST['action'] ?? '';
$cart = new Cart();

// 1. ADD ITEM
if ($action == 'add') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    
    $cart->addToCart($id, $name, $price, $image);
    echo json_encode(['status' => 'success', 'total_qty' => $cart->totalItems()]);
}

// 2. UPDATE QTY
if ($action == 'update') {
    $id = $_POST['id'];
    $qty = $_POST['qty'];
    
    $cart->updateQty($id, $qty);
    
    // Kembalikan Total Harga Baru agar UI bisa update realtime
    echo json_encode([
        'status' => 'success',
        'total_sum' => number_format($cart->getTotalSum(), 0, ',', '.')
    ]);
}

// 3. REMOVE ITEM
if ($action == 'remove') {
    $id = $_POST['id'];
    
    $cart->remove($id);
    
    echo json_encode([
        'status' => 'success',
        'total_sum' => number_format($cart->getTotalSum(), 0, ',', '.'),
        'total_qty' => $cart->totalItems() // Update badge di navbar
    ]);
}
?>