<?php
/**
 * Router de Debug
 */

require_once SRC_PATH . 'controllers/DebugController.php';

use App\Controllers\DebugController;

$controller = new DebugController();
$method = $_SERVER['REQUEST_METHOD'];

// Estadísticas
if ($path === '/api/debug/stats' && $method === 'GET') {
    $controller->stats();
    exit;
}

// Logs
if ($path === '/api/debug/logs' && $method === 'GET') {
    $controller->logs();
    exit;
}

// Limpiar log
if ($path === '/api/debug/clear-log' && $method === 'POST') {
    $controller->clearLog();
    exit;
}

// Test DB
if ($path === '/api/debug/test-db' && $method === 'GET') {
    $controller->testDB();
    exit;
}

// PHP Info
if ($path === '/api/debug/phpinfo' && $method === 'GET') {
    $controller->phpinfo();
    exit;
}