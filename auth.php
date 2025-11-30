<?php
require_once 'database.php';

class auth {
    private $conn;
    private $table_name = "users";

    public function __construct() { //buat contractor
        $database = new Database();
        $this->conn = $database->getConnection();
    }


    public function login($username, $password) {
        //bikin query buat ngecek username di database
        $query = "SELECT id, username, password, role, nama_lengkap FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        //prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        //kalo ada usernya maka cek passwordnya
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
        //cek username ad ga
        $checkQuery = "SELECT id FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        //kalo ada username yg sama jalanin ini
        if($stmt->rowCount() > 0){
            return "Username sudah digunakan, cari yang lain";
        }

        //kalo gaada baru encripsi password dan simpan lalu insert
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user';

        $query = "INSERT INTO " . $this->table_name . " (username, password, nama_lengkap, role) VALUES (:username, :password, :nama, :role)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':nama', $nama_lengkap);
        $stmt->bindParam(':role', $role);

        if ($stmt-> execute()) {
            return true;
        }
            return "Pendaftaran gagal, silakan coba lagi.";
    }
}
?>