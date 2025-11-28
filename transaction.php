<?php
require_once 'database.php';

class Transaction {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function checkout($userId, $cartItems) {
        try {
            $this->conn->beginTransaction();

            // 1. Hitung Total
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['qty'];
            }

            // 2. Insert ke Orders
            $sqlOrder = "INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?, ?, 'pending', NOW())";
            $stmt = $this->conn->prepare($sqlOrder);
            $stmt->execute([$userId, $totalAmount]);
            $orderId = $this->conn->lastInsertId();

            // 3. Insert ke Order Details
            $sqlDetail = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmtDetail = $this->conn->prepare($sqlDetail);

            foreach ($cartItems as $item) {
                $stmtDetail->execute([$orderId, $item['id'], $item['qty'], $item['price']]);
            }

            $this->conn->commit();
            return $orderId;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>