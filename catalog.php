<?php
require_once 'database.php'; // Asumsi file koneksi database Ronnie

class catalog {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Mengambil produk dengan Pagination (Limit & Offset)
    public function getProducts($page = 1, $perPage = 6) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT * FROM products LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int) $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Menghitung total halaman
    public function getTotalPages($perPage = 6) {
        $query = "SELECT COUNT(*) as total FROM products";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ceil($row['total'] / $perPage);
    }
}
?>