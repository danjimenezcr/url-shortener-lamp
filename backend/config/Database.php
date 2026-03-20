<?php
class Database {
    private $host = "localhost";
    private $db_name = "url_shortener";
    private $username = "urlapp";
    private $password = "Urlapp1234$";
    private $conn = null;

    public function getConnection() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
            exit();
        }
        return $this->conn;
    }
}
