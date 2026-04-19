<?php

namespace App\Controllers;

// Importamos los modelos y las clases de seguridad
use App\Models\Favorito;
use App\Sanitizers\FavoritoSanitizer;
use App\Validators\FavoritoValidator;

class FavoritosController {

    /**
     * Guarda una propiedad en favoritos
     */
    public function agregar() {
        // 1. Limpiamos los IDs usando la clase Sanitizer (Método Estático)
        $datosLimpios = FavoritoSanitizer::sanitizarFavorito($_POST);

        // 2. Verificamos existencia y duplicados usando la clase Validator
        $errores = FavoritoValidator::validarFavorito($datosLimpios);

        if (!empty($errores)) {
            // Si hay errores, volvemos atrás con el primer error encontrado
            $msg = urlencode(current($errores));
            header('Location: ' . $_SERVER['HTTP_REFERER'] . '&status=error&msg=' . $msg);
            exit;
        }

        try {
            // 3. Persistencia con Eloquent
            Favorito::create($datosLimpios);
            
            header('Location: ' . $_SERVER['HTTP_REFERER'] . '&status=success');
            exit;
        } catch (\Exception $e) {
            // En producción, esto debería ir a un log, no a un die()
            die("Error crítico al guardar: " . $e->getMessage());
        }
    }

    /**
     * Quita una propiedad de favoritos
     */
    public function quitar() {
        // 1. Limpiamos los IDs
        $datosLimpios = FavoritoSanitizer::sanitizarFavorito($_POST);

        // 2. Verificamos que el favorito realmente exista para poder borrarlo
        $errores = FavoritoValidator::validarQuitarFavorito($datosLimpios);

        if (!empty($errores)) {
            header('Location: ' . $_SERVER['HTTP_REFERER'] . '&status=error&msg=inexistente');
            exit;
        }

        try {
            // 3. Eliminación física
            Favorito::where('usuario_id', $datosLimpios['usuario_id'])
                    ->where('propiedad_id', $datosLimpios['propiedad_id'])
                    ->delete();

            header('Location: ' . $_SERVER['HTTP_REFERER'] . '&status=deleted');
            exit;
        } catch (\Exception $e) {
            die("Error al eliminar: " . $e->getMessage());
        }
    }

    /**
     * Lista los favoritos del usuario
     */
    public function listar_Favoritos() {
        $usuario_id = 1; // Provisorio hasta tener sesiones (Programación 2)

        try {
            // 'with' carga la relación para evitar el problema de consultas N+1
            $misFavoritos = Favorito::with('propiedad') 
                                    ->where('usuario_id', $usuario_id)
                                    ->get();

            require_once SRC_PATH . 'views/favoritos/favoritos_lista.php';
        } catch (\Exception $e) {
            die("Error al cargar favoritos: " . $e->getMessage());
        }
    }
}