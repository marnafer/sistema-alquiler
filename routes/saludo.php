<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

$nombre = $_GET['nombre'] ?? 'invitado';

echo json_encode([
    "mensaje" => "Hola " . $nombre
]);