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

// ============================================
// DETECCIÓN DE RUTAS
// ============================================

$method = strtoupper(trim($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// 1. Extraemos el path puro
$path_bruto = parse_url($requestUri, PHP_URL_PATH);

// 2. DETECCIÓN DINÁMICA DE LA RAIZ DEL SCRIPT
$scriptName = $_SERVER['SCRIPT_NAME'];
$baseDir = str_replace('\\', '/', dirname(dirname($scriptName)));

// 3. LIMPIEZA INTELIGENTE
if ($baseDir !== '/' && strpos($path_bruto, $baseDir) === 0) {
    $path_bruto = substr($path_bruto, strlen($baseDir));
}

// Quitamos el prefijo /public si está presente
if (strpos($path_bruto, '/public') === 0) {
    $path_bruto = substr($path_bruto, strlen('/public'));
}

// 4. NORMALIZACIÓN
$path = '/' . trim((string)$path_bruto, "/");

// Hacemos la variable $path global para que esté disponible en los routers
$GLOBALS['path'] = $path;

// ============================================
// RUTAS DEL SISTEMA (respuestas rápidas)
// ============================================

// Health
if ($path === '/health') {
    renderJson([
        'status' => 'ok',
        'timestamp' => date('Y-m-d H:i:s'),
        'php' => phpversion()
    ]);
    exit;
}

if ($path === '/') {
    renderJson([
        'message' => 'API Alquiler Permanente funcionando',
        'endpoints' => [
            '/health',
            '/api/categorias',
            '/api/provincias',
            '/api/localidades',
            '/api/usuarios',
            '/api/propiedades',
            '/api/servicios',
            '/api/propiedades-servicios',
            '/api/reservas',
            '/api/resenas',
            '/api/consultas',
            '/api/favoritos',
            '/api/logs-actividad',
            '/api/roles',
            '/api/propiedad-imagenes',
            '/api/debug/stats'
        ]
    ]);
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
// 10. Módulo Propiedad Servicio 
elseif (strpos($path, '/api/propiedades-servicios') !== false) {
    $routerPath = SRC_PATH . 'routes/propiedadservicio_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: propiedadservicio_router.php", 500, $path);
    }
}
// 11. Módulo Reservas
elseif (strpos($path, '/api/reservas') !== false) {
    $routerPath = SRC_PATH . 'routes/reserva_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: reserva_router.php", 500, $path);
    }
}
// 12. Módulo Reseñas
elseif (strpos($path, '/api/resenas') !== false) {
    $routerPath = SRC_PATH . 'routes/resena_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: resena_router.php", 500, $path);
    }
}
// 13. Módulo Consultas
elseif (strpos($path, '/api/consultas') !== false) {
    $routerPath = SRC_PATH . 'routes/consulta_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: consulta_router.php", 500, $path);
    }
}
// 14. Módulo Roles
elseif (strpos($path, '/api/roles') !== false) {
    $routerPath = SRC_PATH . 'routes/rol_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: rol_router.php", 500, $path);
    }
}
// 15. Módulo Debug
elseif (strpos($path, '/debug') !== false) {
    $routerPath = SRC_PATH . 'routes/debug_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: debug_router.php", 500, $path);
    }
}
// ============================================
// RUTA NO ENCONTRADA (404)
// ============================================
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