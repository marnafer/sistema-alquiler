<?php
declare(strict_types=1);

date_default_timezone_set('America/Argentina/Buenos_Aires');
error_reporting(E_ALL);

// Definimos la ruta absoluta hacia la carpeta src
// dirname(__DIR__) nos saca de 'public' y nos posiciona en la raíz del proyecto
define('SRC_PATH', dirname(__DIR__) . '/src/');

ini_set('display_errors', 1);

header("Content-Type: application/json");

// Método HTTP
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// --- DETECCIÓN DINÁMICA Y AUTOMÁTICA ---
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// 1. Obtenemos la ruta al index.php (ej: /sistema-alquiler/public/index.php)
$scriptName = $_SERVER['SCRIPT_NAME']; 

// 2. Obtenemos la carpeta del proyecto quitando "/public/index.php"
// Esto nos deja solo con "/sistema-alquiler" (o la subcarpeta que sea)
$baseDir = str_replace('/public/index.php', '', $scriptName);

// 3. Si el path empieza con esa carpeta base, la removemos del path
if ($baseDir !== '' && strpos($path, $baseDir) === 0) {
    $path = substr($path, strlen($baseDir));
}

// 4. Normalizamos para que siempre sea /propiedades, /health, etc.
$path = '/' . trim($path, '/');
// --- FIN DETECCIÓN ---

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

// Si el metodo es POST y la ruta es /propiedades, se llama a la función crearPropiedad() en /controllers/PropiedadController.php

if ($method === 'POST' && $path === '/propiedades') {
    require_once SRC_PATH . 'controllers/PropiedadController.php';

    crearPropiedad();

    exit;
}

// Si no se encuentra la ruta, devolver error 404

http_response_code(404);
echo json_encode([
    'error' => 'Ruta no encontrada',
    'path'  => $path
]);
exit;