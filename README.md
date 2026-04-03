# sistema-alquiler
App web para alquileres permanentes


Contrato del Endpoint POST

Endpoint: POST /api/propiedades

Request Body (JSON):
{
  "titulo": "Casa amplia con patio",
  "descripcion": "Hermosa casa familiar con jardín y cochera",
  "precio": 150000,
  "ubicacion": "Calle Falsa 123, Resistencia",
  "cantidad_ambientes": 4,
  "cantidad_dormitorios": 3,
  "cantidad_banos": 2,
  "capacidad": 5,
  "disponible": true,
  "categoria_id": 1
}

Response exitosa (201 Created)

HTTP/1.1 201 Created
{
  "ok": true,
  "mensaje": "Propiedad creada correctamente",
  "data": {
    "id": 10,
    "titulo": "Casa amplia con patio",
    "precio": 150000,
    "disponible": true
  }
}

Response con errores (400 Bad Request)

HTTP/1.1 400 Bad Request
{
  "ok": false,
  "errores": {
    "titulo": "Campo obligatorio",
    "precio": "Debe ser mayor que 0",
    "ubicacion": "Debe contener entre 10 y 100 caracteres"
  }
}

SANITIZACION

function sanitizarPropiedad($data) {
    return [
        'titulo' => htmlspecialchars(trim($data['titulo'] ?? '')),
        'descripcion' => isset($data['descripcion']) 
            ? htmlspecialchars(trim($data['descripcion'])) 
            : null,
        'precio' => (int) filter_var($data['precio'] ?? 0, FILTER_SANITIZE_NUMBER_INT),
        'ubicacion' => htmlspecialchars(trim($data['ubicacion'] ?? '')),
        'cantidad_ambientes' => (int) filter_var($data['cantidad_ambientes'] ?? 0, FILTER_SANITIZE_NUMBER_INT),
        'cantidad_dormitorios' => (int) filter_var($data['cantidad_dormitorios'] ?? 0, FILTER_SANITIZE_NUMBER_INT),
        'cantidad_banos' => (int) filter_var($data['cantidad_banos'] ?? 0, FILTER_SANITIZE_NUMBER_INT),
        'capacidad' => isset($data['capacidad']) 
            ? (int) filter_var($data['capacidad'], FILTER_SANITIZE_NUMBER_INT) 
            : null,
        'disponible' => isset($data['disponible']) 
            ? filter_var($data['disponible'], FILTER_VALIDATE_BOOLEAN) 
            : true,
        'categoria_id' => (int) filter_var($data['categoria_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT)
    ];
}

VALIDACIONES

function validarPropiedad($data) {
    $errores = [];

    // TITULO
    if (!$data['titulo']) {
        $errores[] = "Campo obligatorio: titulo";
    } elseif (strlen($data['titulo']) < 5 || strlen($data['titulo']) > 30) {
        $errores[] = "El titulo debe contener entre 5 y 30 caracteres";
    }

    // DESCRIPCION (opcional)
    if ($data['descripcion'] !== null) {
        if (strlen($data['descripcion']) < 10 || strlen($data['descripcion']) > 255) {
            $errores[] = "La descripcion debe contener entre 10 y 255 caracteres";
        }
    }

    // PRECIO
    if ($data['precio'] === null) {
        $errores[] = "Campo obligatorio: precio";
    } elseif ($data['precio'] <= 0) {
        $errores[] = "El precio debe ser mayor que 0";
    }

    // UBICACION
    if (!$data['ubicacion']) {
        $errores[] = "Campo obligatorio: ubicacion";
    } elseif (strlen($data['ubicacion']) < 10 || strlen($data['ubicacion']) > 100) {
        $errores[] = "La ubicacion debe contener entre 10 y 100 caracteres";
    }

    // CANTIDAD AMBIENTES
    if ($data['cantidad_ambientes'] === null) {
        $errores[] = "Campo obligatorio: cantidad_ambientes";
    } elseif ($data['cantidad_ambientes'] < 1 || $data['cantidad_ambientes'] > 10) {
        $errores[] = "Debe ser un numero entre 1 y 10 (cantidad_ambientes)";
    }

    // CANTIDAD DORMITORIOS
    if ($data['cantidad_dormitorios'] === null) {
        $errores[] = "Campo obligatorio: cantidad_dormitorios";
    } elseif ($data['cantidad_dormitorios'] < 1 || $data['cantidad_dormitorios'] > 10) {
        $errores[] = "Debe ser un numero entre 1 y 10 (cantidad_dormitorios)";
    } elseif ($data['cantidad_dormitorios'] >= $data['cantidad_ambientes']) {
        $errores[] = "Los dormitorios deben ser menores que la cantidad de ambientes";
    }

    // CANTIDAD BAÑOS
    if ($data['cantidad_banos'] === null) {
        $errores[] = "Campo obligatorio: cantidad_banos";
    } elseif ($data['cantidad_banos'] < 1 || $data['cantidad_banos'] > 10) {
        $errores[] = "Debe ser un numero entre 1 y 10 (cantidad_banos)";
    }

    // CAPACIDAD (opcional)
    if ($data['capacidad'] !== null) {
        if ($data['capacidad'] < 1 || $data['capacidad'] > 10) {
            $errores[] = "La capacidad debe ser un numero entre 1 y 10";
        }
    }

    // DISPONIBLE
    if (!isset($data['disponible'])) {
        $errores[] = "Campo obligatorio: disponible";
    }

    // CATEGORIA_ID
    if ($data['categoria_id'] === null) {
        $errores[] = "Campo obligatorio: categoria_id";
    } elseif ($data['categoria_id'] <= 0) {
        $errores[] = "Categoria invalida";
    }

    return $errores;
}

