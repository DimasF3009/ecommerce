<?php
require_once __DIR__ . '/../config/database.php';

class Rating {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Tambah rating
    public function add($product_id, $buyer_id, $rating, $review) {
        $stmt = $this->conn->prepare("INSERT INTO rating (id, product_id, buyer_id, rating, review, created_at) 
                                      VALUES (UUID(), ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssis", $product_id, $buyer_id, $rating, $review);
        return $stmt->execute();
    }

    // Ambil rating untuk produk
    public function getByProduct($product_id) {
        $stmt = $this->conn->prepare("SELECT r.*, u.name AS buyer_name 
                                      FROM rating r 
                                      JOIN user u ON r.buyer_id = u.id
                                      WHERE r.product_id=? 
                                      ORDER BY r.created_at DESC");
        $stmt->bind_param("s", $product_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Ambil rating rata-rata
    public function getAverage($product_id) {
        $stmt = $this->conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_rating 
                                      FROM rating WHERE product_id=?");
        $stmt->bind_param("s", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Cek apakah user boleh review produk
    public function canReview($buyer_id, $product_id) {
        $sql = "SELECT 1 FROM order_item oi
                JOIN transaction t ON oi.transaction_id = t.id
                WHERE oi.product_id = ? 
                  AND t.buyer_id = ? 
                  AND t.status = 'selesai'
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $product_id, $buyer_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}
?>
