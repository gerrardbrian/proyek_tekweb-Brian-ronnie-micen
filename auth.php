<?php
require_once 'database.php';

class auth {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function login($username, $password) {
        $query = "SELECT id, username, password, role, nama_lengkap FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['nama'] = $row['nama_lengkap'];
                return true; 
            }
        }
        return false; 
    }

    public function register($username,$password,$nama_lengkap){
        $checkQuery = "SELECT id FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bindParam(':username', $username);
        $stmt->execeute();

        if($stmt->rowCount() > 0){
            return "Username sudah digunakan, cari yang lain";
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user';

        $query = "INSERT INTO " . $this->table_name . " (username, password, nama_lengkap, role) VALUES (:username, :password, :nama, :role)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':nama', $nama_lengkap);
        $stmt->bindParam(':role', $role);

        if ($stmt-> execute()) {
            return "success";
        }
            return "Pendaftaran gagal, silakan coba lagi.";
    }
}
?>