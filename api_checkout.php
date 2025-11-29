<?php
session_start();
require_once 'order.php';

header('Content-Type: application/json');

if(empty($_SESSION['cart'])){
    echo json_encode(['status' => 'error', 'message' => 'Keranjang belanja Anda kosong.']);
    exit;
}

$userID= $_SESSION['user_id'];
$cartItems = $_SESSION['cart'];

$order = new Order();
$result = $order->checkout($userID, $cartItems);

if($result=== True){
    unset($_SESSION['cart']); // kosongkan keranjang setelah checkout
    echo json_encode(['status' => 'success', 'message' => 'Transaksi Berhasil! Pesanan sedang diproses.']);
}else{
    echo json_encode(['status' => 'error', 'message' => $result]);
}


?>