<?php
class AdminOrder {
    private $conn;
    private $table = "orders";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Mengambil semua order + nama user yang memesan
    public function getAllOrders() {
        // Join table orders dengan users untuk tahu siapa yang beli
        $query = "SELECT o.id, o.total_amount, o.order_date, o.status, u.username 
                  FROM " . $this->table . " o
                  JOIN users u ON o.user_id = u.id
                  ORDER BY o.order_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>