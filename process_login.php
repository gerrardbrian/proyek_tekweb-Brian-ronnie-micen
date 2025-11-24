<?php
require_once 'classes/Auth.php';

header('Content-Type: application/json'); 

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$auth = new Auth();

if ($auth->login($username, $password)) {
    echo json_encode([
        'status' => 'success', 
        'message' => 'Login Berhasil!', 
        'role' => $_SESSION['role']
    ]);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Username atau Password salah.'
    ]);
}
?>