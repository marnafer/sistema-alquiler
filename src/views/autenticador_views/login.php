<?php
$tituloPagina = "Iniciar Sesión";
include SRC_PATH . 'views/partials/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0">Acceder</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($_GET['error']) && $_GET['error'] === 'auth'): ?>
                        <div class="alert alert-danger" role="alert">
                            Usuario o contraseña incorrectos. Intente nuevamente.
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="formLogin" novalidate>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Correo electrónico</label>
                            <input type="email" name="email" class="form-control" placeholder="usuario@ejemplo.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Contraseña</label>
                            <input type="password" name="contrasena" class="form-control" placeholder="Contraseña" required>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Recuérdame</label>
                            </div>
                            <a href="#" class="small">¿Olvidaste tu contraseña?</a>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2">Entrar</button>
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-center text-muted mt-3 small">
                ¿No tienes cuenta? Contacta al administrador para crear un usuario.
            </p>
        </div>
    </div>
</div>

<script>
(function() {
    // Pequeña validación cliente para mejorar UX
    const form = document.getElementById('formLogin');
    form.addEventListener('submit', function(e) {
        const email = form.querySelector('[name="email"]').value.trim();
        const pass = form.querySelector('[name="contrasena"]').value;
        if (!email || !pass) {
            e.preventDefault();
            if (window.Swal) {
                Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Completa correo y contraseña.' });
            } else {
                alert('Completa correo y contraseña.');
            }
            return false;
        }
        // Permitir envío normal (servidor procesará la autenticación)
    });

    // Auto-focus en el primer campo
    const first = form.querySelector('[name="email"]');
    if (first) first.focus();
})();
</script>

<?php include SRC_PATH . 'views/partials/footer.php'; ?>