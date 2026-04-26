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
elseif (strpos($path, '/api/propiedades') === 0) {
    require_once SRC_PATH . 'routes/propiedad_router.php';
    exit;
}

// --- FAVORITOS (por usuario) ---
elseif (preg_match('#^/api/usuarios/\d+/favoritos$#', $path)) {
    require_once SRC_PATH . 'routes/favorito_router.php';
    exit;
}

// --- FAVORITOS (general) ---
elseif (strpos($path, '/api/favoritos') === 0) {
    require_once SRC_PATH . 'routes/favorito_router.php';
    exit;
}

// --- USUARIOS ---
elseif (strpos($path, '/api/usuarios') === 0) {
    require_once SRC_PATH . 'routes/usuario_router.php';
    exit;
}

// --- LOGS ---
elseif (strpos($path, '/api/logs') === 0 || strpos($path, '/api/logs-actividad') === 0) {
    require_once SRC_PATH . 'routes/log_router.php';
    exit;
}

// --- LOCALIDADES ---
elseif (strpos($path, '/api/localidades') === 0) {
    require_once SRC_PATH . 'routes/localidad_router.php';
    exit;
}

// --- PROPIEDAD IMÁGENES ---
elseif (strpos($path, '/api/propiedad-imagenes') === 0) {
    require_once SRC_PATH . 'routes/propiedadImagen_router.php';
    exit;
}

// --- CATEGORÍAS ---
elseif (strpos($path, '/api/categorias') === 0) {
    require_once SRC_PATH . 'routes/categoria_router.php';
    exit;
}

// --- PROVINCIAS ---
elseif (strpos($path, '/api/provincias') === 0) {
    require_once SRC_PATH . 'routes/provincia_router.php';
    exit;
}

// --- SERVICIOS ---
elseif (strpos($path, '/api/servicios') === 0) {
    require_once SRC_PATH . 'routes/servicio_router.php';
    exit;
}

// --- RESERVAS ---
elseif (strpos($path, '/api/reservas') === 0) {
    require_once SRC_PATH . 'routes/reserva_router.php';
    exit;
}

// --- RESEÑAS ---
elseif (strpos($path, '/api/resenas') === 0) {
    require_once SRC_PATH . 'routes/resena_router.php';
    exit;
}

// --- CONSULTAS ---
elseif (strpos($path, '/api/consultas') === 0) {
    require_once SRC_PATH . 'routes/consulta_router.php';
    exit;
}

// --- ROLES ---
elseif (strpos($path, '/api/roles') === 0) {
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