<?php

function crearPropiedad() {

    // Importamos las dependencias usando la constante global
    require_once SRC_PATH . 'sanitizers/PropiedadSanitizer.php';
    require_once SRC_PATH . 'validators/PropiedadValidator.php';

    // Obtener los datos del cuerpo de la petición (JSON)
    $data = json_decode(file_get_contents('php://input'), true);

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
    echo json_encode([
        'ok' => true,
        'mensaje' => 'Propiedad validada correctamente',
        'data' => $dataSanitizada
    ]);
    exit;

}