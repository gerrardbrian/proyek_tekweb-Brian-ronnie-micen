<?php
class cart {
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function addToCart($id, $name, $price, $image) {
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty']++;
        } else {
            $_SESSION['cart'][$id] = [
                'id' => $id,
                'name' => $name,
                'price' => $price,
                'image' => $image,
                'qty' => 1
            ];
        }
        return $this->totalItems();
    }

    public function totalItems() {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['qty'];
        }
        return $total;
    }

    public function getContent() {
        return $_SESSION['cart'];
    }
    
    public function clear() {
        $_SESSION['cart'] = [];
    }
}
?>