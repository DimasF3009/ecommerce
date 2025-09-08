<?php
require_once __DIR__ . '/../config/database.php';

class Product {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ambil semua produk (dengan kategori & nama toko)
    public function getAll() {
        $sql = "SELECT p.*, 
                       c.name AS category_name, 
                       u.name AS seller_name
                FROM product p
                LEFT JOIN category c ON p.category_id = c.id
                LEFT JOIN user u ON p.seller_id = u.id
                ORDER BY p.created_at DESC";
        return $this->conn->query($sql);
    }

    // Ambil produk berdasarkan ID
    public function getById($id) {
        $stmt = $this->conn->prepare("
            SELECT p.*, 
                   c.name AS category_name, 
                   u.name AS seller_name
            FROM product p
            LEFT JOIN category c ON p.category_id = c.id
            LEFT JOIN user u ON p.seller_id = u.id
            WHERE p.id = ?
        ");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Tambah produk baru (untuk penjual)
    public function create($seller_id, $name, $description, $price, $stock, $category_id, $image_url = null) {
        $stmt = $this->conn->prepare("
            INSERT INTO product (id, seller_id, name, description, price, stock, category_id, image_url, created_at)
            VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("sssdi ss", $seller_id, $name, $description, $price, $stock, $category_id, $image_url);
        return $stmt->execute();
    }

    // Update produk
    public function update($id, $name, $description, $price, $stock, $category_id, $image_url = null) {
        $stmt = $this->conn->prepare("
            UPDATE product 
            SET name=?, description=?, price=?, stock=?, category_id=?, image_url=? 
            WHERE id=?
        ");
        $stmt->bind_param("ssdi sss", $name, $description, $price, $stock, $category_id, $image_url, $id);
        return $stmt->execute();
    }

    // Hapus produk
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM product WHERE id=?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }
}
