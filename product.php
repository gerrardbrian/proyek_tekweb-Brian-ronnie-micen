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

    // 2. CREATE 
    public function create($name, $price, $desc, $stock, $image) {
        // Setup Upload
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($image["name"]);
        move_uploaded_file($image["tmp_name"], $target_file);
        $image_name = $image["name"];
        
        $query = "INSERT INTO " . $this->table . " (name, price, description, stock, image) VALUES (?, ?, ?, ?, ?)";
    
        $stmt = $this->conn->prepare($query);

        
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

    // 3. DELETE 
    public function delete($id) {
        try {
            // 1. Cek apakah barang ini ada di order_details (Sudah pernah laku?)
            $queryCek = "SELECT COUNT(*) as total FROM order_details WHERE product_id = :id";
            $stmtCek = $this->conn->prepare($queryCek);
            $stmtCek->bindParam(":id", $id);
            $stmtCek->execute();
            $rowCek = $stmtCek->fetch(PDO::FETCH_ASSOC);

            // Jika barang sudah pernah dibeli, JANGAN HAPUS.
            if ($rowCek['total'] > 0) {
                return "Gagal: Barang ini ada di riwayat transaksi. Tidak bisa dihapus.";
            }

            // 2. Jika aman, hapus gambarnya dulu
            $querySelect = "SELECT image FROM " . $this->table . " WHERE id = :id";
            $stmtSelect = $this->conn->prepare($querySelect);
            $stmtSelect->bindParam(":id", $id);
            $stmtSelect->execute();
            $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);

            if($row) {
                $filePath = "uploads/" . $row['image']; 
                if(file_exists($filePath)) { unlink($filePath); } 
            }

            // 3. Hapus data di DB
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            
            if($stmt->execute()){
                return true; // Berhasil
            } else {
                return "Gagal eksekusi query delete.";
            }

        } catch (PDOException $e) {
            return "Database Error: " . $e->getMessage();
        }
    }

    // 4. SEARCH (Untuk Live Search AJAX)
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table . " WHERE name LIKE :keyword ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }
}
?>