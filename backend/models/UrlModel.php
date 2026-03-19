<?php
require_once __DIR__ . '/../config/Database.php';

class UrlModel {
    private $conn;
    private $table = "urls";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create($short_code, $original_url) {
        $query = "INSERT INTO " . $this->table . " (short_code, original_url) VALUES (:short_code, :original_url)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':short_code', $short_code);
        $stmt->bindParam(':original_url', $original_url);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function findByShortCode($short_code) {
        $query = "SELECT * FROM " . $this->table . " WHERE short_code = :short_code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':short_code', $short_code);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function incrementClickCount($id) {
        $query = "UPDATE " . $this->table . " SET click_count = click_count + 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
