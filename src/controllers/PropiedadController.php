<?php

// Funciones para el GET

function mostrarFormulario() {
    // 1. Le decimos al navegador: "Lo que viene ahora es una página web"
    header('Content-Type: text/html; charset=utf-8');
    
    // 2. Cargamos el archivo
    require_once SRC_PATH . 'views/propiedades_form.php';
    exit;
}

function listarPropiedades() {

    // 1. Limpiamos cualquier salida previa (espacios, etc)
    if (ob_get_length()) ob_clean();

    // 2. Definimos los datos
    $respuesta = [
        "ok" => true,
        "mensaje" => "Listado de propiedades en construccion" // Sin tilde para probar
    ];

    // 3. Enviamos los headers correctos
    header('Content-Type: application/json; charset=utf-8');
        
    // 4. Codificamos con opciones de seguridad
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
    exit;
}

// Funciones para el POST

function crearPropiedad() {

    // Importamos las dependencias usando la constante global
    require_once SRC_PATH . 'sanitizers/PropiedadSanitizer.php';
    require_once SRC_PATH . 'validators/PropiedadValidator.php';

// Si viene de un formulario HTML, usamos $_POST. Si viene de Postman/JS, usamos php://input.
    $json = json_decode(file_get_contents('php://input'), true);
    $data = $json ?? $_POST;

    // Si el JSON es inválido o está vacío
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos JSON no válidos o vacíos']);
        exit;
    }

    // 1. Sanitizar
    $dataSanitizada = sanitizarPropiedad($data);

    // 2. Validar
    $errores = validarPropiedad($dataSanitizada);

    if (!empty($errores)) {
        http_response_code(400);
        echo json_encode(['errores' => $errores]);
        exit;
    }

    // 3. Respuesta simulada (Próximamente aquí irá la inserción a la DB)
    http_response_code(201);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => true,
        'mensaje' => 'Propiedad validada correctamente',
        'data' => $dataSanitizada
    ]);
    exit;

}