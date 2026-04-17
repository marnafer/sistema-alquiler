<?php

declare(strict_types=1);

// Cargamos el Autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Cargamos el Capsule Manager para la DB
require_once __DIR__ . '/../src/database.php';

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

// 1. Extraemos el path puro
$path_bruto = parse_url($requestUri, PHP_URL_PATH);

// 2. DETECCIÓN DINÁMICA DE LA RAIZ DEL SCRIPT
// Esto nos da la carpeta donde está el proyecto en XAMPP (ej: /sistema-alquiler)
$scriptName = $_SERVER['SCRIPT_NAME']; // /sistema-alquiler/public/index.php
$baseDir = str_replace('\\', '/', dirname(dirname($scriptName))); // Nos sube un nivel fuera de public

// 3. LIMPIEZA INTELIGENTE
// Quitamos la carpeta del proyecto (sistema-alquiler)
if ($baseDir !== '/' && strpos($path_bruto, $baseDir) === 0) {
    $path_bruto = substr($path_bruto, strlen($baseDir));
}

// Quitamos el prefijo /public si todavía está presente en la URL
if (strpos($path_bruto, '/public') === 0) {
    $path_bruto = substr($path_bruto, strlen('/public'));
}

// 4. NORMALIZACIÓN
$path = '/' . trim((string)$path_bruto, "/");

// --- FIN DE DETECCIÓN ---

// --- SECCIÓN DE RUTAS ---

// 1. Rutas de Sistema (Respuestas rápidas)
if ($path === '/health') {
    renderJson([
        'status'    => 'ok',
        'timestamp' => date('Y-m-d H:i:s'),
        'php'       => phpversion()
    ]);
}

if ($path === '/') {
    renderJson([
        'message' => 'API Alquiler Permanente funcionando',
        'endpoints' => ['/propiedades', '/health']
    ]);
}

// Rutas Módulo de Propiedades
// Usamos strpos !== false para que tome /propiedades, /propiedades_form, etc.
if (strpos($path, '/propiedades') !== false) {
    $routerPath = SRC_PATH . 'routes/propiedad_router.php'; // router específico
    
    if (file_exists($routerPath)) {
        require_once $routerPath;
        // Importante: El router.php debe gestionar el 404 interno si no coincide la sub-ruta
    } else {
        renderError("Archivo de rutas no encontrado en: " . $routerPath, 500);
    }
} else {
    // 3. Si no es ninguna de las anteriores
    renderError("Ruta no encontrada: " . $path, 404);
}

// Rutas Módulo Favoritos

elseif (strpos($path, '/favoritos') !== false) {
    $routerPath = SRC_PATH . 'routes/favoritos_router.php'; // router específico
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado en: " . $routerPath, 500);
    }
}
else {
    renderError("Ruta no encontrada: " . $path, 404);
}

// --- FUNCIONES AUXILIARES ---

function renderJson(array $data, int $code = 200): void {
    header("Content-Type: application/json; charset=utf-8");
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit; // Crucial para detener la ejecución aquí
}

function renderError(string $message, int $code): void {
    renderJson(["error" => $message, "path_detectado" => $GLOBALS['path'] ?? null], $code);
}