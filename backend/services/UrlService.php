<?php
require_once __DIR__ . '/../models/UrlModel.php';

class UrlService {
    private $urlModel;
    private $base_url = "http://192.9.149.63/";

    public function __construct() {
        $this->urlModel = new UrlModel();
    }

    public function createShortUrl($original_url) {
        if (empty($original_url)) {
            return ["error" => "La URL no puede estar vacía"];
        }

        if (!filter_var($original_url, FILTER_VALIDATE_URL)) {
            return ["error" => "La URL no es válida"];
        }

        $short_code = $this->generateShortCode();
        $id = $this->urlModel->create($short_code, $original_url);

        return [
            "id" => $id,
            "short_code" => $short_code,
            "original_url" => $original_url,
            "short_url" => $this->base_url . $short_code
        ];
    }

    public function getUrlByShortCode($short_code) {
        $url = $this->urlModel->findByShortCode($short_code);
        if (!$url) {
            return ["error" => "URL no encontrada"];
        }
        return $url;
    }

    public function getAllUrls() {
        $urls = $this->urlModel->getAll();
        foreach ($urls as &$url) {
            $url['short_url'] = $this->base_url . $url['short_code'];
        }
        return $urls;
    }

    private function generateShortCode($length = 6) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        // Verificar que no exista ya ese código
        if ($this->urlModel->findByShortCode($code)) {
            return $this->generateShortCode($length);
        }

        return $code;
    }
}
