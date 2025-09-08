<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Daftar user baru
    public function register($name, $email, $password, $role, $phone = null, $address = null) {
        // cek email sudah ada
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) return false;

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("INSERT INTO user (id, name, email, password, role, phone, address, status, created_at)
                                    VALUES (UUID(), ?, ?, ?, ?, ?, ?, 'active', NOW())");
        $stmt->bind_param("ssssss", $name, $email, $hash, $role, $phone, $address);
        return $stmt->execute();
    }

    // Login
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = ? AND status = 'active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }
}
?>


