<?php
require_once __DIR__ . '/../models/UrlModel.php';
require_once __DIR__ . '/../models/ClickModel.php';

class StatsService {
    private $urlModel;
    private $clickModel;

    public function __construct() {
        $this->urlModel = new UrlModel();
        $this->clickModel = new ClickModel();
    }

    public function registerClick($short_code, $ip_address, $user_agent, $referer) {
        $url = $this->urlModel->findByShortCode($short_code);
        if (!$url) {
            return ["error" => "URL no encontrada"];
        }

        // Obtener país desde IP usando ipinfo.io
        $country = $this->getCountryFromIp($ip_address);

        $this->clickModel->create($url['id'], $ip_address, $user_agent, $referer);
        $this->urlModel->incrementClickCount($url['id']);

        return [
            "original_url" => $url['original_url'],
            "country" => $country
        ];
    }

    public function getStats($id) {
        $url = $this->urlModel->findById($id);
        if (!$url) {
            return ["error" => "URL no encontrada"];
        }

        $clicks = $this->clickModel->getByUrlId($id);
        $clicksByDay = $this->clickModel->getClicksByDay($id);
        $ipStats = $this->clickModel->getCountryStats($id);

        // Obtener países únicos
        $countries = [];
        foreach ($ipStats as $stat) {
            $country = $this->getCountryFromIp($stat['ip_address']);
            if (!in_array($country, $countries)) {
                $countries[] = $country;
            }
        }

        return [
            "id" => $url['id'],
            "short_code" => $url['short_code'],
            "original_url" => $url['original_url'],
            "created_at" => $url['created_at'],
            "click_count" => $url['click_count'],
            "countries" => $countries,
            "clicks_by_day" => $clicksByDay
        ];
    }

    private function getCountryFromIp($ip) {
        // IPs locales
        if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.')) {
            return "Local";
        }

        $response = @file_get_contents("https://ipinfo.io/{$ip}/json");
        if ($response) {
            $data = json_decode($response, true);
            return $data['country'] ?? "Desconocido";
        }

        return "Desconocido";
    }
}
