<?php
require_once 'classes/auth.php';

header('Content-Type: application/json');

$nama_lengkap = $_POST['nama_lengkap'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if(empty($nama_lengkap) || empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Semua kolom wajib diisi!']);
    exit;
}

$auth = new Auth();
$result = $auth->register($username, $password, $nama_lengkap);

if ($result === true) {
    echo json_encode(['status' => 'success', 'message' => 'Akun berhasil dibuat! Silahkan Login.']);
} else {
    echo json_encode(['status' => 'error', 'message' => $result]);
}
?>