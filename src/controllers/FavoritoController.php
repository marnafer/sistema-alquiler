<?php
namespace App\Controllers;

// Importamos los archivos de seguridad
require_once __DIR__ . '/../sanitizers/FavoritoSanitizer.php';
require_once __DIR__ . '/../validators/FavoritoValidator.php';

use App\Models\Favorito;

class FavoritosController {

    /**
     * Guarda una propiedad en favoritos
     */
    public function agregar() {
        // 1. Limpiamos los IDs (Sanitización)
        $datosLimpios = sanitizarFavorito($_POST);

        // 2. Verificamos que los IDs existan y no sea un duplicado (Validación)
        $errores = validarFavorito($datosLimpios);

        if (!empty($errores)) {
            // Si hay errores, volvemos atrás con el primer error encontrado
            header('Location: ' . $_SERVER['HTTP_REFERER'] . '&status=error&msg=' . urlencode(current($errores)));
            exit;
        }

        try {
            // 3. Persistencia con Eloquent
            Favorito::create($datosLimpios);
            
            header('Location: ' . $_SERVER['HTTP_REFERER'] . '&status=success');
            exit;
        } catch (\Exception $e) {
            die("Error crítico al guardar: " . $e->getMessage());
        }
    }

    /**
     * Quita una propiedad de favoritos
     */
    public function quitar() {
        // 1. Limpiamos los IDs
        $datosLimpios = sanitizarFavorito($_POST);

        // 2. Verificamos que el favorito realmente exista para poder borrarlo
        $errores = validarQuitarFavorito($datosLimpios);

        if (!empty($errores)) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        try {
            // 3. Eliminación lógica/física según modelo
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
        // Simulamos el ID del usuario logueado (esto luego vendrá de $_SESSION)
        $usuario_id = 1; 

        try {
            // Obtenemos los favoritos del usuario e incluimos la relación con 'propiedad'
            // Esto asume que tienes definida la relación en tu modelo Favorito
            $misFavoritos = Favorito::where('usuario_id', $usuario_id)->get();

            // Cargamos la vista de favoritos pasándole los datos
            // El path de la vista dependerá de cómo tengas organizada tu carpeta 'views'
            require_once SRC_PATH . 'views/favoritos/favoritos_lista.php';

        } catch (\Exception $e) {
            die("Error al cargar la lista de favoritos: " . $e->getMessage());
        }
    }
}