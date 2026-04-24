<?php
/**
 * Script de prueba para verificar todas las entidades
 */

require_once 'database.php';
require_once 'src/debug/Debugger.php';

use App\Debug\Debugger;

Debugger::enableErrorReporting();

$entidades = [
    'categorias' => '/api/categorias',
    'provincias' => '/api/provincias',
    'localidades' => '/api/localidades',
    'usuarios' => '/api/usuarios',
    'propiedades' => '/api/propiedades',
    'servicios' => '/api/servicios',
    'reservas' => '/api/reservas',
    'reseñas' => '/api/resenas',
    'consultas' => '/api/consultas',
    'favoritos' => '/api/favoritos',
    'logs_actividad' => '/api/logs-actividad',
    'roles' => '/api/roles'
];

echo "=== TEST DE ENTIDADES ===\n\n";

foreach ($entidades as $nombre => $url) {
    echo "Probando $nombre... ";
    
    $ch = curl_init("http://localhost$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "✅ OK (HTTP $httpCode)\n";
    } else {
        echo "❌ ERROR (HTTP $httpCode)\n";
    }
}

echo "\n=== TEST COMPLETADO ===\n";