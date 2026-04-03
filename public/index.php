<?php
declare(strict_types=1);

date_default_timezone_set('America/Argentina/Buenos_Aires');
error_reporting(E_ALL);

// Definimos la ruta absoluta hacia la carpeta src
// dirname(__DIR__) nos saca de 'public' y nos posiciona en la raíz del proyecto
define('SRC_PATH', dirname(__DIR__) . '/src/');

ini_set('display_errors', 1);

header("Content-Type: application/json");

// --- CONFIGURACIÓN Y DETECCIÓN ---

$method = strtoupper(trim($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// 1. Extraemos el path puro (sin ?query_string)
$path_bruto = parse_url($requestUri, PHP_URL_PATH);

// 2. Detectamos la carpeta del proyecto dinámicamente
// Esto quita "/public/index.php" de la ruta del script
$baseDir = str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']);

// 3. Limpiamos la subcarpeta del path si existe
if ($baseDir !== '' && strpos($path_bruto, $baseDir) === 0) {
    $path_bruto = substr($path_bruto, strlen($baseDir));
}

// 4. NORMALIZACIÓN FINAL
// Limpiamos espacios, saltos de línea y barras sobrantes
$path = '/' . trim((string)$path_bruto, " \t\n\r\0\x0B/");

// --- FIN DE DETECCIÓN ---

// --- SECCIÓN DE RUTAS ---

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

// Si la URL empieza con /propiedades, le pasamos la pelota al archivo de rutas de propiedades
if (strpos($path, '/propiedades') === 0) {
    require_once SRC_PATH . 'routes/propiedades.php';
}

// --- FIN DE RUTAS ---

// Si no se encuentra la ruta, devolver error 404

http_response_code(404);
echo json_encode([
    'error' => 'Ruta no encontrada',
    'path'  => $path
]);
exit;