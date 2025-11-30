<?php
class product {
    private $conn;
    private $table = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. READ ALL (Untuk Tabel Dashboard)
    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 2. CREATE (PERBAIKAN: Menggunakan Syntax PDO yang Benar)
    public function create($name, $price, $desc, $stock, $image) {
        // Setup Upload
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($image["name"]);
        move_uploaded_file($image["tmp_name"], $target_file);
        $image_name = $image["name"];
        
        // Query menggunakan tanda tanya (?) sebagai placeholder
        $query = "INSERT INTO " . $this->table . " (name, price, description, stock, image) VALUES (?, ?, ?, ?, ?)";
    
        $stmt = $this->conn->prepare($query);

        // DI SINI PERUBAHANNYA:
        // Hapus bind_param (itu punya MySQLi).
        // Masukkan data langsung ke dalam execute() berupa array urut.
        $data = [
            htmlspecialchars(strip_tags($name)),
            htmlspecialchars(strip_tags($price)),
            htmlspecialchars(strip_tags($desc)),
            htmlspecialchars(strip_tags($stock)),
            htmlspecialchars(strip_tags($image_name))
        ];

        if($stmt->execute($data)) {
            return true;
        }
        return false;
    }

    // 3. DELETE (Untuk AJAX)
    public function delete($id) {
        // Ambil nama file dulu untuk dihapus dari folder
        $querySelect = "SELECT image FROM " . $this->table . " WHERE id = :id";
        $stmtSelect = $this->conn->prepare($querySelect);
        $stmtSelect->bindParam(":id", $id);
        $stmtSelect->execute();
        $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $filePath = "uploads/" . $row['image']; // Pastikan path sesuai
            if(file_exists($filePath)) { unlink($filePath); } 
        }

        // Hapus data di DB
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // 4. SEARCH (Untuk Live Search AJAX)
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table . " WHERE name LIKE :keyword ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Mengembalikan array asosiatif agar mudah di-loop di JS
    }
}
?>