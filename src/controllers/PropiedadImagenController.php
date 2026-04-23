<?php

namespace App\Controllers;

use App\Models\PropiedadImagen;
use App\Models\Propiedad;
use App\Sanitizers\PropiedadImagenSanitizer;
use App\Validators\PropiedadImagenValidator;

class PropiedadImagenController
{
    /**
     * VISTA HTML: Galería/listado de imágenes (GET /propiedades/imagenes)
     */
    public function listarVistas()
    {
        try {
            $imagenes = PropiedadImagen::orderBy('id', 'desc')->get();
            require_once SRC_PATH . 'views/propiedad_imagen_views/imagenes_listar.php';
        } catch (\Exception $e) {
            renderError("Error al cargar la galería: " . $e->getMessage(), 500);
        }
    }

    /**
     * VISTA HTML: Mostrar detalle de imagen (GET /propiedades/imagenes/ver?id={id})
     */
    public function mostrarVista()
    {
        $id = PropiedadImagenSanitizer::sanitizeId($_GET['id'] ?? null);
        if (!PropiedadImagenValidator::validateId($id)) {
            renderError("ID inválido", 400);
            return;
        }

        $imagen = PropiedadImagen::find($id);
        if (!$imagen) {
            renderError("Imagen no encontrada", 404);
            return;
        }

        require_once SRC_PATH . 'views/propiedad_imagen_views/imagen_detalle.php';
    }

    /**
     * GET /api/propiedad-imagenes
     * Opcional: ?propiedad_id=
     */
    public function indexApi()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $propiedadId = isset($_GET['propiedad_id']) ? (int) $_GET['propiedad_id'] : null;
            $query = PropiedadImagen::query();

            if ($propiedadId) {
                $query->where('propiedad_id', $propiedadId);
            }

            $imagenes = $query->orderBy('id', 'desc')->get();

            echo json_encode(['status' => 'success', 'data' => $imagenes], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/propiedad-imagenes/{id}
     */
    public function mostrarApi($id)
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = PropiedadImagenSanitizer::sanitizeId($id);
        if (!PropiedadImagenValidator::validateId($id)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            return;
        }

        try {
            $img = PropiedadImagen::find($id);
            if (!$img) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Imagen no encontrada']);
                return;
            }
            echo json_encode(['status' => 'success', 'data' => $img], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/propiedad-imagenes
     * Soporta multipart/form-data con campo 'imagen' o JSON { imagen_base64, propiedad_id, descripcion }
     */
    public function crear()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Leer payload JSON (si viene) o usar $_POST/$_FILES
        $inputRaw = file_get_contents('php://input');
        $json = json_decode($inputRaw, true);

        $payload = $json ?? $_POST;
        $file = $_FILES['imagen'] ?? null;

        $datos = PropiedadImagenSanitizer::sanitizarPropiedadImagen($payload);
        $errores = PropiedadImagenValidator::validarPropiedadImagen($datos, $file);

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'errors' => $errores], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // Preparar carpeta destino
            $publicDir = dirname(dirname(__DIR__)) . '/public';
            $uploadDir = $publicDir . '/uploads/propiedades';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $nombreArchivo = null;

            // 1) Si se recibió archivo multipart
            if ($file && isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $nombreArchivo = time() . '_' . bin2hex(random_bytes(6)) . '.' . strtolower($ext);
                $dest = $uploadDir . '/' . $nombreArchivo;
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    throw new \Exception('No se pudo mover el archivo subido');
                }
            }
            // 2) Si se recibió base64 en JSON
            elseif (!empty($datos['imagen_base64'])) {
                $matches = [];
                if (preg_match('/^data:(image\/[a-zA-Z]+);base64,(.+)$/', $datos['imagen_base64'], $matches)) {
                    $mime = $matches[1];
                    $base64 = $matches[2];
                    $ext = explode('/', $mime)[1];
                    $nombreArchivo = time() . '_' . bin2hex(random_bytes(6)) . '.' . strtolower($ext);
                    $dest = $uploadDir . '/' . $nombreArchivo;
                    $bin = base64_decode($base64);
                    if ($bin === false || file_put_contents($dest, $bin) === false) {
                        throw new \Exception('No se pudo escribir el archivo base64');
                    }
                } else {
                    throw new \Exception('Formato base64 inválido');
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'No se proporcionó imagen'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Construir datos para BD
            $rutaPublica = '/uploads/propiedades/' . $nombreArchivo;
            $registro = PropiedadImagen::create([
                'propiedad_id' => $datos['propiedad_id'],
                'ruta'         => $rutaPublica,
                'nombre'       => $nombreArchivo,
                'descripcion'  => $datos['descripcion'] ?? null
            ]);

            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Imagen guardada', 'data' => $registro], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * DELETE /api/propiedad-imagenes/{id}
     */
    public function eliminar($id)
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = PropiedadImagenSanitizer::sanitizarId($id);
        if (!PropiedadImagenValidator::validarId($id)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            return;
        }

        try {
            $img = PropiedadImagen::find($id);
            if (!$img) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Imagen no encontrada']);
                return;
            }

            // Borrar archivo físico si existe
            $publicPath = dirname(dirname(__DIR__)) . '/public';
            if (!empty($img->ruta)) {
                $f = $publicPath . $img->ruta;
                if (file_exists($f)) @unlink($f);
            }

            $img->delete();
            echo json_encode(['status' => 'success', 'message' => "Imagen #$id eliminada"]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}