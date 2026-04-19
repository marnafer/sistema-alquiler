<?php 
$tituloPagina = "Registro de Usuario";
include SRC_PATH . 'views/partials/header.php'; 
?>

<!-- Link específico para iconos que no está en el header general -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
    .was-validated .form-control:valid { border-color: #198754; }
    .was-validated .form-control:invalid { border-color: #dc3545; }
    .password-toggle { cursor: pointer; }
</style>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Usuario</h5>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Los errores del servidor se muestran aquí (estilo Propiedades) -->
                    <?php if (!empty($errores)): ?>
                        <div class="errores mb-4">
                            <p>Corrija los siguientes errores:</p>
                            <ul>
                                <?php foreach ($errores as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form id="formUsuario" action="/usuarios/guardar" method="POST" novalidate>
                        <!-- Nombre y Apellido -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       required minlength="2" placeholder="Ej: Maria"
                                       value="<?= htmlspecialchars($datos['nombre'] ?? '') ?>">
                                <div class="invalid-feedback">El nombre es obligatorio (mín. 2 letras).</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" 
                                       required minlength="2" placeholder="Ej: Garcia"
                                       value="<?= htmlspecialchars($datos['apellido'] ?? '') ?>">
                                <div class="invalid-feedback">El apellido es obligatorio.</div>
                            </div>
                        </div>

                        <!-- Email y Teléfono -->
                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text">@</span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           required placeholder="usuario@correo.com"
                                           value="<?= htmlspecialchars($datos['email'] ?? '') ?>">
                                    <div class="invalid-feedback" id="feedback-email">Ingrese un email válido.</div>
                                </div>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       required pattern="[0-9]{8,15}" placeholder="Solo números"
                                       value="<?= htmlspecialchars($datos['telefono'] ?? '') ?>">
                                <div class="invalid-feedback">8 a 15 dígitos numéricos.</div>
                            </div>
                        </div>

                        <!-- Domicilio -->
                        <div class="mb-3">
                            <label for="domicilio" class="form-label">Domicilio</label>
                            <input type="text" class="form-control" id="domicilio" name="domicilio" 
                                   required placeholder="Calle 123, Ciudad"
                                   value="<?= htmlspecialchars($datos['domicilio'] ?? '') ?>">
                                <div class="invalid-feedback">El domicilio es requerido.</div>
                        </div>

                        <!-- Contraseñas -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="contrasena" name="contrasena" 
                                           required minlength="8" placeholder="Mín. 8 caracteres">
                                    <button class="btn btn-outline-secondary password-toggle" type="button" onclick="togglePassword('contrasena', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">Mínimo 8 caracteres.</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmar_contrasena" 
                                           required placeholder="Repita su contraseña">
                                    <button class="btn btn-outline-secondary password-toggle" type="button" onclick="togglePassword('confirmar_contrasena', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <div class="invalid-feedback" id="feedback-confirmacion">Las contraseñas no coinciden.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Rol -->
                        <div class="mb-4">
                            <label for="rol_id" class="form-label">Rol del Usuario</label>
                            <select class="form-select" id="rol_id" name="rol_id" required>
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <option value="1" <?= ($datos['rol_id'] ?? '') == '1' ? 'selected' : '' ?>>Administrador</option>
                                <option value="2" <?= ($datos['rol_id'] ?? '') == '2' ? 'selected' : '' ?>>Empleado</option>
                                <option value="3" <?= ($datos['rol_id'] ?? '') == '3' ? 'selected' : '' ?>>Cliente</option>
                            </select>
                            <div class="invalid-feedback">Debe asignar un rol.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Guardar Usuario
                            </button>
                            <a href="/usuarios" class="btn btn-link btn-sm text-muted">Volver al listado</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = "password";
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// Validación de contraseñas antes de enviar
document.getElementById('formUsuario').addEventListener('submit', function(e) {
    const pass = document.getElementById('contrasena');
    const confirmPass = document.getElementById('confirmar_contrasena');

    if (pass.value !== confirmPass.value) {
        confirmPass.setCustomValidity("Invalid");
        confirmPass.classList.add('is-invalid');
        e.preventDefault();
        e.stopPropagation();
    } else {
        confirmPass.setCustomValidity("");
    }

    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    this.classList.add('was-validated');
});
</script>

<?php include SRC_PATH . 'views/partials/footer.php'; ?>