<?php
require_once __DIR__ . '/../services/StatsService.php';

class RedirectController {
    private $statsService;

    public function __construct() {
        $this->statsService = new StatsService();
    }

    // GET /:short_code - Redirigir a URL original
    public function redirect($short_code) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] 
            ?? $_SERVER['REMOTE_ADDR'] 
            ?? '0.0.0.0';

        // Si hay múltiples IPs tomar la primera
        if (str_contains($ip_address, ',')) {
            $ip_address = trim(explode(',', $ip_address)[0]);
        }

        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        $result = $this->statsService->registerClick($short_code, $ip_address, $user_agent, $referer);

        if (isset($result['error'])) {
            http_response_code(404);
            echo json_encode(["error" => "URL no encontrada"]);
            return;
        }

        header("Location: " . $result['original_url'], true, 302);
        exit();
    }
}
