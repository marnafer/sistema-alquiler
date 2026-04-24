<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/database.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');
error_reporting(E_ALL);

define('SRC_PATH', dirname(__DIR__) . '/src/');

ini_set('display_errors', 1);

// --- CONFIGURACIÓN ---

$method = strtoupper(trim($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

$path_bruto = parse_url($requestUri, PHP_URL_PATH);

$scriptName = $_SERVER['SCRIPT_NAME'];
$baseDir = str_replace('\\', '/', dirname(dirname($scriptName)));

if ($baseDir !== '/' && strpos($path_bruto, $baseDir) === 0) {
    $path_bruto = substr($path_bruto, strlen($baseDir));
}

if (strpos($path_bruto, '/public') === 0) {
    $path_bruto = substr($path_bruto, strlen('/public'));
}

$path = '/' . trim((string)$path_bruto, "/");

// --- DEBUG ---
require_once dirname(__DIR__) . '/src/debug/Debugger.php';

use App\Debug\Debugger;

if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    Debugger::setEnabled(true);
    Debugger::enableErrorReporting();
}

Debugger::request();

// --- RUTAS ---

// Health
if ($path === '/health') {
    renderJson([
        'status' => 'ok',
        'timestamp' => date('Y-m-d H:i:s'),
        'php' => phpversion()
    ]);
    exit;
}

// Home
if ($path === '/') {
    renderJson([
        'message' => 'API Alquiler Permanente funcionando',
        'endpoints' => [
            '/api/propiedades',
            '/api/categorias',
            '/api/localidades',
            '/api/usuarios'
        ]
    ]);
    exit;
}

// --- PROPIEDADES ---
elseif ($path === '/api/propiedades' || preg_match('#^/api/propiedades/\d+$#', $path)) {
    require_once SRC_PATH . 'routes/propiedad_router.php';
    exit;
}

// --- FAVORITOS ---
elseif ($path === '/api/favoritos' || preg_match('#^/api/favoritos/\d+$#', $path)) {
    require_once SRC_PATH . 'routes/favorito_router.php';
    exit;
}

// --- USUARIOS ---
elseif ($path === '/api/usuarios' || preg_match('#^/api/usuarios/\d+$#', $path)) {
    require_once SRC_PATH . 'routes/usuario_router.php';
    exit;
}

// --- LOGS ---
elseif ($path === '/api/logs' || $path === '/api/logs-actividad') {
    require_once SRC_PATH . 'routes/log_router.php';
    exit;
}

// --- LOCALIDADES ---
elseif ($path === '/api/localidades' || preg_match('#^/api/localidades/\d+$#', $path)) {
    require_once SRC_PATH . 'routes/localidad_router.php';
    exit;
}

// --- PROPIEDAD IMÁGENES ---
elseif ($path === '/api/propiedad-imagenes' || preg_match('#^/api/propiedad-imagenes/\d+$#', $path)) {
    require_once SRC_PATH . 'routes/propiedadImagen_router.php';
    exit;
}

// --- CATEGORÍAS ---
elseif ($path === '/api/categorias' || preg_match('#^/api/categorias/\d+$#', $path)) {
    require_once SRC_PATH . 'routes/categoria_router.php';
    exit;
}

// --- PROVINCIAS ---
elseif ($path === '/api/provincias' || preg_match('#^/api/provincias/\d+$#', $path)) {
    require_once SRC_PATH . 'routes/provincia_router.php';
    exit;
}

// --- SERVICIOS ---
elseif ($path === '/api/servicios' || preg_match('#^/api/servicios/\d+$#', $path)) {
    require_once SRC_PATH . 'routes/servicio_router.php';
    exit;
}

// --- RESERVAS ---
elseif ($path === '/api/reservas' || preg_match('#^/api/reservas/\d+$#', $path)) {
    require_once SRC_PATH . 'routes/reserva_router.php';
    exit;
}

// --- RESEÑAS ---
elseif ($path === '/api/resenas' || preg_match('#^/api/resenas/\d+$#', $path)) {
    require_once SRC_PATH . 'routes/resena_router.php';
    exit;
}

// --- CONSULTAS ---
elseif ($path === '/api/consultas' || preg_match('#^/api/consultas/\d+$#', $path)) {
    require_once SRC_PATH . 'routes/consulta_router.php';
    exit;
}

// --- ROLES ---
elseif ($path === '/api/roles' || preg_match('#^/api/roles/\d+$#', $path)) {
    require_once SRC_PATH . 'routes/rol_router.php';
    exit;
}

// --- 404 ---
else {
    renderError("Ruta no encontrada", 404);
    exit;
}

// --- HELPERS ---

function renderJson(array $data, int $code = 200): void {
    header("Content-Type: application/json; charset=utf-8");
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function renderError(string $message, int $code): void {
    renderJson([
        'success' => false,
        'error' => $message,
        'path' => $GLOBALS['path'] ?? null
    ], $code);
}