<?php
class Product {
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

    // 2. CREATE (Dengan Upload Foto) [cite: 24, 29]
    public function create($name, $price, $desc, $file) {
        // Setup Upload
        $target_dir = "../uploads/";
        // Buat nama file unik agar tidak bentrok
        $filename = time() . "_" . basename($file["name"]);
        $target_file = $target_dir . $filename;
        
        // Validasi sederhana (hanya izinkan gambar)
        $check = getimagesize($file["tmp_name"]);
        if($check === false) { return false; }

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            // Jika upload sukses, simpan ke DB
            $query = "INSERT INTO " . $this->table . " (name, price, description, image) VALUES (:name, :price, :desc, :image)";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":price", $price);
            $stmt->bindParam(":desc", $desc);
            $stmt->bindParam(":image", $filename);

            return $stmt->execute();
        }
        return false;
    }

    // 3. DELETE (Untuk AJAX) [cite: 31]
    public function delete($id) {
        // Ambil nama file dulu untuk dihapus dari folder
        $querySelect = "SELECT image FROM " . $this->table . " WHERE id = :id";
        $stmtSelect = $this->conn->prepare($querySelect);
        $stmtSelect->bindParam(":id", $id);
        $stmtSelect->execute();
        $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $filePath = "../uploads/" . $row['image'];
            if(file_exists($filePath)) { unlink($filePath); } // Hapus file fisik
        }

        // Hapus data di DB
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // 4. SEARCH (Untuk Live Search AJAX) [cite: 32]
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table . " WHERE name LIKE :keyword ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();
        return $stmt;
    }
}
?>