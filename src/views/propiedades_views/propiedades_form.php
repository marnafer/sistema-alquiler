<?php 
$tituloPagina = "Publicar Inmueble";
include SRC_PATH . 'views/partials/header.php'; 
?>

<h1>Publicar Inmueble</h1>

<?php if (!empty($errores)): ?>
    <div class="errores">
        <p>Hubo problemas con los datos ingresados:</p>
        <ul>
            <?php foreach ($errores as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="/propiedades" method="POST" id="form-propiedad" onsubmit="return validarFormulario()">

    <p class="seccion">Datos Principales</p>
    <div class="grid">
        <div class="campo full">
            <label>Título del Anuncio <span class="req">*</span></label>
            <input type="text" name="titulo" maxlength="150" placeholder="Ej: Departamento 2 ambientes luminoso con balcón" 
                   value="<?= htmlspecialchars($datos['titulo'] ?? '') ?>">
            <span class="hint">Máximo 150 caracteres.</span>
            <span class="msg-error" id="error-titulo"></span>
        </div>

        <div class="campo full">
            <label>Descripción <span class="opc">(Opcional)</span></label>
            <textarea name="descripcion" rows="4" placeholder="Detalles sobre el contrato, requisitos o estado del inmueble..."><?= htmlspecialchars($datos['descripcion'] ?? '') ?></textarea>
            <span class="msg-error" id="error-descripcion"></span>
        </div>

        <div class="campo full">
            <label>Dirección <span class="req">*</span></label>
            <input type="text" name="direccion" maxlength="125" placeholder="Calle, número, piso y departamento" 
                   value="<?= htmlspecialchars($datos['direccion'] ?? '') ?>">
            <span class="msg-error" id="error-direccion"></span>
        </div>
    </div>

    <p class="seccion">Ubicación y Gestión</p>
    <div class="grid">
        <div class="campo">
            <label>Localidad <span class="req">*</span></label>
            <select name="localidad_id">
                <option value="">— Seleccionar —</option>
                <?php foreach ($localidades as $loc): ?>
                    <option value="<?= $loc->id ?>" <?= ($datos['localidad_id'] ?? '') == $loc->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc->nombre) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="msg-error" id="error-localidad_id"></span>
        </div>

        <div class="campo">
            <label>Administrador/Dueño <span class="req">*</span></label>
            <select name="administrador_id">
                <option value="">— Seleccionar —</option>
                <?php foreach ($usuarios as $user): ?>
                    <option value="<?= $user->id ?>" <?= ($datos['administrador_id'] ?? '') == $user->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user->apellido . ", " . $user->nombre) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="msg-error" id="error-administrador_id"></span>
        </div>
    </div>

    <p class="seccion">Costos Mensuales</p>
    <div class="grid">
        <div class="campo">
            <label>Alquiler Mensual (ARS) <span class="req">*</span></label>
            <input type="number" name="precio" min="1" step="0.01" placeholder="Monto del alquiler" 
                   value="<?= htmlspecialchars($datos['precio'] ?? '') ?>">
            <span class="msg-error" id="error-precio"></span>
        </div>

        <div class="campo">
            <label>Expensas (ARS) <span class="opc">(Si aplica)</span></label>
            <input type="number" name="expensas" min="0" step="0.01" placeholder="Cargar 0 si no abona" 
                   value="<?= htmlspecialchars($datos['expensas'] ?? '0') ?>">
            <span class="msg-error" id="error-expensas"></span>
        </div>
    </div>

    <p class="seccion">Características del Inmueble</p>
    <div class="grid">
        <div class="campo">
            <label>Categoría <span class="req">*</span></label>
            <select name="categoria_id">
                <option value="">— Seleccionar —</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat->id ?>" <?= ($datos['categoria_id'] ?? '') == $cat->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat->nombre) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="msg-error" id="error-categoria_id"></span>
        </div>

        <div class="campo">
            <label>Capacidad <span class="opc">(Personas)</span></label>
            <input type="number" name="capacidad" min="1" placeholder="Ej: 4" 
                   value="<?= htmlspecialchars($datos['capacidad'] ?? '') ?>">
        </div>

        <div class="campo">
            <label>Ambientes <span class="req">*</span></label>
            <input type="number" name="cantidad_ambientes" min="1" value="<?= htmlspecialchars($datos['cantidad_ambientes'] ?? '') ?>">
            <span class="msg-error" id="error-cantidad_ambientes"></span>
        </div>

        <div class="campo">
            <label>Dormitorios <span class="req">*</span></label>
            <input type="number" name="cantidad_dormitorios" min="0" value="<?= htmlspecialchars($datos['cantidad_dormitorios'] ?? '') ?>">
            <span class="msg-error" id="error-cantidad_dormitorios"></span>
        </div>

        <div class="campo">
            <label>Baños <span class="req">*</span></label>
            <input type="number" name="cantidad_banos" min="1" value="<?= htmlspecialchars($datos['cantidad_banos'] ?? '') ?>">
            <span class="msg-error" id="error-cantidad_banos"></span>
        </div>

        <div class="campo">
            <label>Disponibilidad Inicial <span class="req">*</span></label>
            <select name="disponible">
                <option value="1" <?= ($datos['disponible'] ?? '1') == '1' ? 'selected' : '' ?>>Disponible inmediatamente</option>
                <option value="0" <?= ($datos['disponible'] ?? '') == '0' ? 'selected' : '' ?>>No disponible / Reservado</option>
            </select>
        </div>
    </div>

    <div class="footer">
        <button type="submit">Guardar Propiedad</button>
    </div>

</form>

<script>
// Auxiliares para mostrar errores visuales
function setError(campo, mensaje) {
    const el = document.querySelector('[name="' + campo + '"]');
    if (el) {
        const wrapper = el.closest('.campo');
        const span = document.getElementById('error-' + campo);
        wrapper.classList.add('campo-error');
        if (span) span.textContent = mensaje;
    }
}

function clearErrors() {
    document.querySelectorAll('.campo-error').forEach(el => el.classList.remove('campo-error'));
    document.querySelectorAll('.msg-error').forEach(el => el.textContent = '');
}

function valStr(name) {
    const el = document.querySelector('[name="' + name + '"]');
    return el ? el.value.trim() : '';
}

function valNum(name) {
    const v = valStr(name);
    return v === '' ? null : Number(v);
}

// Validación robusta antes del envío
function validarFormulario() {
    clearErrors();
    let ok = true;

    // Título y Dirección
    if (valStr('titulo').length < 5) { setError('titulo', 'El título debe tener al menos 5 caracteres'); ok = false; }
    if (valStr('direccion').length < 5) { setError('direccion', 'Ingrese una dirección válida'); ok = false; }

    // Precios y Expensas
    if (valNum('precio') <= 0) { setError('precio', 'El alquiler debe ser mayor a 0'); ok = false; }
    if (valNum('expensas') < 0) { setError('expensas', 'Las expensas no pueden ser negativas'); ok = false; }

    // Selectores obligatorios
    if (!valStr('localidad_id')) { setError('localidad_id', 'Seleccione una localidad'); ok = false; }
    if (!valStr('administrador_id')) { setError('administrador_id', 'Asigne un dueño/admin'); ok = false; }
    if (!valStr('categoria_id')) { setError('categoria_id', 'Seleccione una categoría'); ok = false; }

    // Lógica de ambientes (No pueden haber más dormitorios que ambientes totales)
    const amb = valNum('cantidad_ambientes');
    const dorm = valNum('cantidad_dormitorios');
    if (amb < 1) { setError('cantidad_ambientes', 'Mínimo 1 ambiente'); ok = false; }
    if (dorm > amb) { setError('cantidad_dormitorios', 'No puede superar el total de ambientes'); ok = false; }

    if (!ok) {
        const firstErr = document.querySelector('.campo-error');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    return ok;
}
</script>

<?php 
include SRC_PATH . 'views/partials/footer.php'; 
?>

