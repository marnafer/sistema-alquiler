<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons para el ojo de la contraseña -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .was-validated .form-control:valid { border-color: #198754; }
        .was-validated .form-control:invalid { border-color: #dc3545; }
        .password-toggle { cursor: pointer; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Usuario</h5>
                </div>
                <div class="card-body p-4">
                    
                    <form id="formUsuario" novalidate>
                        <!-- Nombre y Apellido -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       required minlength="2" placeholder="Ej: Maria">
                                <div class="invalid-feedback">El nombre es obligatorio (mín. 2 letras).</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" 
                                       required minlength="2" placeholder="Ej: Garcia">
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
                                           required placeholder="usuario@correo.com">
                                    <div class="invalid-feedback" id="feedback-email">Ingrese un email válido.</div>
                                </div>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       required pattern="[0-9]{8,15}" placeholder="Solo números">
                                <div class="invalid-feedback">8 a 15 dígitos numéricos.</div>
                            </div>
                        </div>

                        <!-- Domicilio -->
                        <div class="mb-3">
                            <label for="domicilio" class="form-label">Domicilio</label>
                            <input type="text" class="form-control" id="domicilio" name="domicilio" 
                                   required placeholder="Calle 123, Ciudad">
                            <div class="invalid-feedback">El domicilio es requerido para el contrato.</div>
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
                                <option value="1">Administrador</option>
                                <option value="2">Empleado</option>
                                <option value="3">Cliente</option>
                            </select>
                            <div class="invalid-feedback">Debe asignar un rol.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Guardar Usuario
                            </button>
                            <button type="reset" class="btn btn-link btn-sm text-muted">Limpiar formulario</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función para mostrar/ocultar contraseña
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

document.getElementById('formUsuario').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const pass = document.getElementById('contrasena');
    const confirmPass = document.getElementById('confirmar_contrasena');

    // 1. Reset de validaciones previas
    form.classList.remove('was-validated');
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    // 2. Validación de coincidencia de contraseñas (Client-side)
    if (pass.value !== confirmPass.value) {
        confirmPass.setCustomValidity("Invalid");
        confirmPass.classList.add('is-invalid');
    } else {
        confirmPass.setCustomValidity("");
    }

    // 3. Validación general Bootstrap
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }

    // 4. Envío de datos con Fetch
    const formData = new FormData(form);

    try {
        const response = await fetch('/tu-sistema/usuarios/crear', { // REEMPLAZA CON TU RUTA REAL
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert("¡Usuario creado exitosamente!");
            form.reset();
            form.classList.remove('was-validated');
        } else {
            // Manejo de errores devueltos por PHP (ej: Email duplicado)
            mostrarErroresServidor(result.errors);
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Ocurrió un error al conectar con el servidor.");
    }
});

function mostrarErroresServidor(errores) {
    for (const campo in errores) {
        const input = document.getElementById(campo);
        if (input) {
            input.classList.add('is-invalid');
            // Buscamos el div de feedback para inyectar el mensaje del servidor
            const feedback = input.parentElement.querySelector('.invalid-feedback') || 
                             input.nextElementSibling;
            if (feedback) feedback.textContent = errores[campo];
        }
    }
}

// Validación dinámica mientras escriben en confirmar contraseña
document.getElementById('confirmar_contrasena').addEventListener('input', function() {
    const pass = document.getElementById('contrasena').value;
    if (this.value !== pass) {
        this.classList.add('is-invalid');
    } else {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>