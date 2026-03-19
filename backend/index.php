<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/controllers/UrlController.php';
require_once __DIR__ . '/controllers/RedirectController.php';

// Obtener la ruta
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/backend';
$path = str_replace($base_path, '', parse_url($request_uri, PHP_URL_PATH));
$path = rtrim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Rutas
if ($method === 'POST' && $path === '/api/urls') {
    $controller = new UrlController();
    $controller->create();

} elseif ($method === 'GET' && $path === '/api/urls') {
    $controller = new UrlController();
    $controller->getAll();

} elseif ($method === 'GET' && preg_match('/^\/api\/urls\/(\d+)\/stats$/', $path, $matches)) {
    $controller = new UrlController();
    $controller->getStats($matches[1]);

} elseif ($method === 'GET' && preg_match('/^\/([a-zA-Z0-9]{6})$/', $path, $matches)) {
    $controller = new RedirectController();
    $controller->redirect($matches[1]);

} else {
    http_response_code(404);
    echo json_encode(["error" => "Ruta no encontrada"]);
}
