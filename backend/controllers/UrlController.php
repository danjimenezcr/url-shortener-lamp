<?php
require_once __DIR__ . '/../services/UrlService.php';
require_once __DIR__ . '/../services/StatsService.php';

class UrlController {
    private $urlService;
    private $statsService;

    public function __construct() {
        $this->urlService = new UrlService();
        $this->statsService = new StatsService();
    }

    // POST /api/urls - Crear URL corta
    public function create() {
        $body = json_decode(file_get_contents("php://input"), true);

        if (!isset($body['original_url'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo original_url es requerido"]);
            return;
        }

        $result = $this->urlService->createShortUrl($body['original_url']);

        if (isset($result['error'])) {
            http_response_code(400);
        } else {
            http_response_code(201);
        }

        echo json_encode($result);
    }

    // GET /api/urls - Obtener todas las URLs
    public function getAll() {
        $result = $this->urlService->getAllUrls();
        http_response_code(200);
        echo json_encode($result);
    }

    // GET /api/urls/:id/stats - Obtener estadísticas de una URL
    public function getStats($id) {
        $result = $this->statsService->getStats($id);

        if (isset($result['error'])) {
            http_response_code(404);
        } else {
            http_response_code(200);
        }

        echo json_encode($result);
    }
}
