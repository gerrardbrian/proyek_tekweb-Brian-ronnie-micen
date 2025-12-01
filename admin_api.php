<?php
// 1. Mulai Session & Cek Security
session_start();

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); 
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit;
}

header('Content-Type: application/json');

// 2. Include File
require_once 'database.php';
require_once 'product.php';

// 3. Koneksi Database
$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

// 4. Tangkap Action 
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';


// A. UPDATE STOK (via POST)
if ($action == 'update_stock' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $stock = $_POST['stock'];

    if(empty($id) || $stock === '') {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
        exit;
    }

    // Update langsung
    $query = "UPDATE products SET stock = :stock WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':stock', $stock);
    $stmt->bindParam(':id', $id);

    if($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

// B. HAPUS PRODUK 
if ($action == 'delete') {
    $id = isset($_GET['id']) ? $_GET['id'] : '';

    if (!empty($id)) {
        // Panggil fungsi delete
        $result = $product->delete($id);

        if($result === true) {
            // Jika return true, berarti sukses
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
        } else {
            // Jika return string (pesan error), kirim ke frontend
            echo json_encode(['status' => 'error', 'message' => $result]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID Kosong']);
    }
    exit;
}

// C. PENCARIAN 
if ($action == 'search') {
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
    
    // Panggil fungsi search dari product.php
    $data = $product->search($keyword);
    
    // Karena di product.php sudah pakai fetchAll(), 
    // hasilnya sudah berupa Array. Langsung encode saja.
    echo json_encode($data);
    exit;
}

// Default jika action salah
echo json_encode(['status' => 'error', 'message' => 'Action tidak valid']);
exit;
?>