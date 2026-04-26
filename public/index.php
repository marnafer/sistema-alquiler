<?php

declare(strict_types=1);

// ============================================
// FUNCIONES GLOBALES (disponibles para todo)
// ============================================

/**
 * Enviar respuesta JSON exitosa
 */
function renderJson(array $data, int $code = 200): void {
    header("Content-Type: application/json; charset=utf-8");
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Enviar respuesta JSON de error
 */
function renderError(string $message, int $code, ?string $pathDetectado = null): void {
    $data = ["error" => $message];
    if ($pathDetectado !== null) {
        $data["path_detectado"] = $pathDetectado;
    }
    renderJson($data, $code);
}

// ============================================
// CONFIGURACIÓN INICIAL
// ============================================

// Cargamos el Autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Cargamos el Capsule Manager para la DB
require_once __DIR__ . '/../src/database.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');
error_reporting(E_ALL);

// Definimos la ruta absoluta hacia la carpeta src
define('SRC_PATH', dirname(__DIR__) . '/src/');

ini_set('display_errors', 1);

// ============================================
// DEBUG (solo en desarrollo)
// ============================================

// Cargar la clase Debugger
require_once dirname(__DIR__) . '/src/debug/Debugger.php';

use App\Debug\Debugger;

// Activar debug SOLO en desarrollo (localhost)
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    Debugger::setEnabled(true);
    Debugger::enableErrorReporting();
}

// Registrar la petición
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
            '/api/propiedades-imagen',
            '/api/debug/stats'
        ]
    ]);
}

// ============================================
// RUTAS DE LA API
// ============================================

// 1. Módulo de Propiedades
if (strpos($path, '/propiedades') !== false) {
    $routerPath = SRC_PATH . 'routes/propiedad_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: propiedad_router.php", 500, $path);
    }
}
// 2. Módulo Favoritos
elseif (strpos($path, '/favoritos') !== false) {
    $routerPath = SRC_PATH . 'routes/favorito_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: favorito_router.php", 500, $path);
    }
}
// 3. Módulo Usuarios
elseif (strpos($path, '/usuarios') !== false) {
    $routerPath = SRC_PATH . 'routes/usuario_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: usuario_router.php", 500, $path);
    }
}
// 4. Módulo Logs de Actividad
elseif (strpos($path, '/logs') !== false || strpos($path, '/logs-actividad') !== false) {
    $routerPath = SRC_PATH . 'routes/log_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: log_router.php", 500, $path);
    }
}
// 5. Módulo Localidades
elseif (strpos($path, '/localidades') !== false) {
    $routerPath = SRC_PATH . 'routes/localidad_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: localidad_router.php", 500, $path);
    }
}
// 6. Módulo Propiedad Imágenes
elseif (strpos($path, '/propiedad-imagenes') !== false || strpos($path, '/propiedades/imagenes') !== false) {
    $routerPath = SRC_PATH . 'routes/propiedadImagen_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: propiedadImagen_router.php", 500, $path);
    }
}
// 7. Módulo Categorías
elseif (strpos($path, '/api/categorias') !== false) {
    $routerPath = SRC_PATH . 'routes/categoria_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: categoria_router.php", 500, $path);
    }
}
// 8. Módulo Provincias
elseif (strpos($path, '/api/provincias') !== false) {
    $routerPath = SRC_PATH . 'routes/provincia_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: provincia_router.php", 500, $path);
    }
}
// 9. Módulo Servicios
elseif (strpos($path, '/api/servicios') !== false) {
    $routerPath = SRC_PATH . 'routes/servicio_router.php';
    if (file_exists($routerPath)) {
        require_once $routerPath;
    } else {
        renderError("Archivo de rutas no encontrado: servicio_router.php", 500, $path);
    }
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
    renderError("Ruta no encontrada", 404, $path);
}