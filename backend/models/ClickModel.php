<?php
require_once __DIR__ . '/../config/Database.php';

class ClickModel {
    private $conn;
    private $table = "clicks";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create($url_id, $ip_address, $user_agent, $referer) {
        $query = "INSERT INTO " . $this->table . " (url_id, ip_address, user_agent, referer) 
                  VALUES (:url_id, :ip_address, :user_agent, :referer)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':url_id', $url_id);
        $stmt->bindParam(':ip_address', $ip_address);
        $stmt->bindParam(':user_agent', $user_agent);
        $stmt->bindParam(':referer', $referer);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function getByUrlId($url_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE url_id = :url_id ORDER BY clicked_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':url_id', $url_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCountryStats($url_id) {
        $query = "SELECT ip_address, COUNT(*) as total 
                  FROM " . $this->table . " 
                  WHERE url_id = :url_id 
                  GROUP BY ip_address";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':url_id', $url_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClicksByDay($url_id) {
        $query = "SELECT DATE(clicked_at) as day, COUNT(*) as total 
                  FROM " . $this->table . " 
                  WHERE url_id = :url_id 
                  GROUP BY DATE(clicked_at) 
                  ORDER BY day ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':url_id', $url_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
