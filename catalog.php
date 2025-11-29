<?php
require_once 'database.php'; // Asumsi file koneksi database Ronnie

class catalog {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Mengambil produk dengan Pagination (Limit & Offset)
    public function getProducts($page = 1, $perPage = 6, $search = '') {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT * FROM products";

        //lalo ada input di search ubah query jdi kyk gini
        if (!empty($search)) {
            $query .= " WHERE name LIKE :search";
        }
        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $keyword = "%" . $search . "%"; // Tambah % depan belakang untuk SQL LIKE
            $stmt->bindValue(':search', $keyword);
        }
        $stmt->bindValue(':limit', (int) $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Menghitung total halaman
    public function getTotalPages($perPage = 6,$search = '') {
        $query = "SELECT COUNT(*) as total FROM products";

        if (!empty($search)) {
            $query .= " WHERE name LIKE :search";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $keyword = "%" . $search . "%";
            $stmt->bindValue(':search', $keyword);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row['total'] == 0) return 1;
        return ceil($row['total'] / $perPage);
    }
}
?>