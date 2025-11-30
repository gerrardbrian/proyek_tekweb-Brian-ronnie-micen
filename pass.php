<?php
require_once 'database.php'; 

$db = new Database();
$conn = $db->getConnection();


$username = 'admin';
$passwordBaru = 'admin'; 

// enkripsi 
$hashedPassword = password_hash($passwordBaru, PASSWORD_DEFAULT);

$query = "UPDATE users SET password = :pass WHERE username = :user";
$stmt = $conn->prepare($query);
$stmt->bindParam(':pass', $hashedPassword);
$stmt->bindParam(':user', $username);

if($stmt->execute()) {
    echo "Password untuk user 'admin' berhasil direset menjadi 'admin'. Silahkan coba login.";
} else {
    echo "Gagal mereset password.";
}
?>