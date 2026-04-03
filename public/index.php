<?php
declare(strict_types=1);

date_default_timezone_set('America/Argentina/Buenos_Aires');
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

// Método HTTP
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// URI pedida
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Quitar query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Detectar base automáticamente (ej: /sistema-alquiler/public)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir  = str_replace('\\', '/', dirname($scriptName));

// Quitar base del path
if ($scriptDir !== '/' && strpos($path, $scriptDir) === 0) {
    $path = substr($path, strlen($scriptDir));
}

// Normalizar formato
$path = '/' . trim($path, '/');

// Ruta de prueba
if ($method === 'GET' && $path === '/health') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(200);

    echo json_encode([
        'status'      => 'ok',
        'timestamp'   => date('Y-m-d H:i:s'),
        'php_version' => phpversion(),
        'server'      => $_SERVER['SERVER_SOFTWARE'] ?? 'Apache'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    exit;
}

// Ruta base opcional
if ($method === 'GET' && $path === '/') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(200);

    echo json_encode([
        'message' => 'API funcionando',
        'health'  => '/health'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    exit;
}

// No encontrada
header('Content-Type: application/json; charset=utf-8');
http_response_code(404);
echo json_encode([
    'error' => 'Not Found',
    'path'  => $path
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);