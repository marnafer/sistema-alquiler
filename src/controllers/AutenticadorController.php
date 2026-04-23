<?php
namespace App\Controllers;

use App\Models\Usuario;

class AutenticadorController {
    
    // Muestra el formulario de Login
    public function loginVista() {
        require_once SRC_PATH . 'views/autenticador_views/login.php';
    }

    // Procesa el formulario
    public function login() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['contrasena'] ?? '';

        $usuario = Usuario::where('email', $email)->first();

        if ($usuario && password_verify($password, $usuario->contrasena)) {
            // Si es correcto, iniciamos sesión
            if (session_status() === PHP_SESSION_NONE) session_start();
            
            $_SESSION['user_id']   = $usuario->id;
            $_SESSION['user_rol']  = $usuario->rol_id;
            $_SESSION['user_nome'] = $usuario->nombre;

            header('Location: /sistema-alquiler/propiedades');
            exit;
        } else {
            // Si falla, volvemos con error
            header('Location: /sistema-alquiler/login?error=auth');
            exit;
        }
    }

    // Cierra la sesión
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header('Location: /sistema-alquiler/login');
        exit;
    }
}