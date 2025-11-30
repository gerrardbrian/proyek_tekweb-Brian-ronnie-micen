<?php

require_once 'database.php';

class order {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function checkout($userid, $cartItems) {
        try {
            // 1. Mulai Transaksi Database
            $this->conn->beginTransaction();

            // A. VALIDASI STOK DULU (Penting!)
            // Kita cek satu per satu barang di keranjang, apakah stok di database cukup
            $queryCheck = "SELECT stock, name FROM products WHERE id = :pid";
            $stmtCheck = $this->conn->prepare($queryCheck);

            $totalAmount = 0;

            foreach ($cartItems as $item) {
                // Hitung total harga sekalian
                $totalAmount += $item['price'] * $item['qty'];

                // Cek stok di database
                $stmtCheck->bindParam(':pid', $item['id']);
                $stmtCheck->execute();
                $productDB = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                // Jika barang tidak ada atau stok kurang dari yang dibeli
                if (!$productDB || $productDB['stock'] < $item['qty']) {
                    // Batalkan semua proses (Rollback) dan lempar error
                    throw new Exception("Stok untuk produk '" . $item['name'] . "' tidak mencukupi. Sisa stok: " . ($productDB['stock'] ?? 0));
                }
            }

            // B. INSERT KE TABEL ORDERS (Header)
            $query = "INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (:uid, :total, 'pending', NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':uid', $userid);
            $stmt->bindParam(':total', $totalAmount);
            $stmt->execute();

            // Ambil ID Order yang baru saja dibuat
            $orderid = $this->conn->lastInsertId();

            // C. SIAPKAN QUERY UNTUK DETAIL DAN UPDATE STOK
            // Query Insert Detail
            $querydetail = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (:oid, :pid, :qty, :price)";
            $stmtdetail = $this->conn->prepare($querydetail);

            // Query Update Stok (Kurangi Stok)
            $queryStock = "UPDATE products SET stock = stock - :qty WHERE id = :pid";
            $stmtStock = $this->conn->prepare($queryStock);

            // D. LOOPING UNTUK EKSEKUSI (Insert Detail & Potong Stok)
            foreach ($cartItems as $item) {
                // 1. Eksekusi Insert ke order_details
                $stmtdetail->bindParam(':oid', $orderid);
                $stmtdetail->bindParam(':pid', $item['id']);
                $stmtdetail->bindParam(':qty', $item['qty']);
                $stmtdetail->bindParam(':price', $item['price']);
                $stmtdetail->execute();

                // 2. Eksekusi Update Stok (INI YANG TADINYA HILANG)
                // Kita kurangi stok barang di tabel products
                $stmtStock->bindParam(':qty', $item['qty']);
                $stmtStock->bindParam(':pid', $item['id']);
                $stmtStock->execute();
            }

            // Jika semua lancar, simpan permanen (Commit)
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Jika ada error (misal stok kurang atau koneksi putus), batalkan semua perubahan
            $this->conn->rollBack();
            // Kembalikan pesan error agar bisa dibaca di api_checkout.php
            return $e->getMessage();
        }
    }
}
?>