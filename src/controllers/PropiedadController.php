<?php

namespace App\Controllers;

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
        "mensaje" => "Listado de propiedades en construccion" 
    ];

    // 3. Enviamos los headers correctos
    header('Content-Type: application/json; charset=utf-8');
        
    // 4. Codificamos con opciones de seguridad
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
    exit;
}

// Funciones para el POST

require_once __DIR__ . '/../sanitizers/PropiedadSanitizer.php';
require_once __DIR__ . '/../validators/PropiedadValidator.php';

use App\Models\Propiedad; 

function crearPropiedad() {
    // 1. Recibir los datos crudos (suponiendo que vienen por POST o JSON)
    $input = $_POST; 

    // 2. SANITIZAR: Limpiamos los datos antes de cualquier chequeo
    $datosLimpios = sanitizarPropiedad($input);

    // 3. VALIDAR: Verificamos si los datos limpios cumplen las reglas
    $errores = validarPropiedad($datosLimpios);

    // 4. CONTROL DE ERRORES
    if (!empty($errores)) {
        // Si hay errores, frenamos y avisamos al usuario
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $errores
        ]);
        return;
    }

    // 5. PERSISTENCIA CON ELOQUENT
    try {
        // Si llegamos acá, los datos están blindados
        $nuevaPropiedad = Propiedad::create($datosLimpios);

        echo json_encode([
            'success' => true,
            'message' => 'Propiedad creada con éxito',
            'id' => $nuevaPropiedad->id
        ]);
    } catch (\Exception $e) {
        // Capturamos errores de la base de datos (ej: falla de conexión)
        echo json_encode([
            'success' => false,
            'message' => 'Error al guardar en la base de datos: ' . $e->getMessage()
        ]);
    }
}