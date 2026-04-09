<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Propiedad</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; max-width: 600px; margin: 30px auto; line-height: 1.6; color: #333; padding: 0 16px; }
        h1 { font-size: 22px; margin-bottom: 20px; }

        .errores { background: #fdecea; border: 1px solid #f5c6c6; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px; }
        .errores p { font-weight: bold; margin: 0 0 6px; color: #b91c1c; }
        .errores ul { margin: 0; padding-left: 18px; color: #b91c1c; font-size: 14px; }

        .seccion { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; color: #888; margin: 24px 0 10px; border-top: 1px solid #eee; padding-top: 14px; }
        .seccion:first-of-type { border-top: none; margin-top: 0; }

        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .campo { display: flex; flex-direction: column; gap: 4px; }
        .campo.full { grid-column: 1 / -1; }

        label { font-size: 13px; font-weight: bold; color: #444; }
        label .req { color: #dc2626; margin-left: 2px; }
        label .opc { font-size: 11px; font-weight: normal; color: #999; margin-left: 4px; }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            background: #fff;
            color: #333;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #4f7ef8;
            box-shadow: 0 0 0 2px rgba(79,126,248,0.15);
        }
        textarea { resize: vertical; }

        .hint { font-size: 11px; color: #999; }
        .campo-error input,
        .campo-error select,
        .campo-error textarea { border-color: #dc2626; }
        .msg-error { font-size: 11px; color: #dc2626; display: none; }
        .campo-error .msg-error { display: block; }
        .campo-error .hint { display: none; }

        .footer { margin-top: 28px; }
        button[type="submit"] {
            background: #16a34a;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }
        button[type="submit"]:hover { background: #15803d; }
    </style>
</head>
<body>

<h1>Cargar nueva propiedad</h1>

<?php if (!empty($errores)): ?>
    <div class="errores">
        <p>Por favor corregí los siguientes errores:</p>
        <ul>
            <?php foreach ($errores as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="/propiedades" method="POST" id="form-propiedad" onsubmit="return validarFormulario()">

    <p class="seccion">Información general</p>

    <div class="grid">

        <div class="campo full <?= in_array('titulo', $camposConError ?? []) ? 'campo-error' : '' ?>">
            <label>Título <span class="req">*</span></label>
            <input type="text" name="titulo" minlength="5" maxlength="30"
                   placeholder="Ej: Departamento céntrico luminoso"
                   value="<?= htmlspecialchars($datos['titulo'] ?? '') ?>">
            <span class="hint">Entre 5 y 30 caracteres</span>
            <span class="msg-error" id="error-titulo"></span>
        </div>

        <div class="campo full <?= in_array('descripcion', $camposConError ?? []) ? 'campo-error' : '' ?>">
            <label>Descripción <span class="opc">(opcional)</span></label>
            <textarea name="descripcion" rows="3" maxlength="255"
                      placeholder="Descripción detallada de la propiedad..."><?= htmlspecialchars($datos['descripcion'] ?? '') ?></textarea>
            <span class="hint">Si la completás, debe tener entre 10 y 255 caracteres</span>
            <span class="msg-error" id="error-descripcion"></span>
        </div>

        <div class="campo full <?= in_array('ubicacion', $camposConError ?? []) ? 'campo-error' : '' ?>">
            <label>Ubicación <span class="req">*</span></label>
            <input type="text" name="ubicacion" minlength="10" maxlength="100"
                   placeholder="Ej: Av. Corrientes 1234, CABA, Argentina"
                   value="<?= htmlspecialchars($datos['ubicacion'] ?? '') ?>">
            <span class="hint">Entre 10 y 100 caracteres</span>
            <span class="msg-error" id="error-ubicacion"></span>
        </div>

    </div>

    <p class="seccion">Detalles</p>

    <div class="grid">

        <div class="campo <?= in_array('precio', $camposConError ?? []) ? 'campo-error' : '' ?>">
            <label>Precio (ARS) <span class="req">*</span></label>
            <input type="number" name="precio" min="1"
                   placeholder="Ej: 150000"
                   value="<?= htmlspecialchars($datos['precio'] ?? '') ?>">
            <span class="msg-error" id="error-precio"></span>
        </div>

        <div class="campo <?= in_array('categoria_id', $camposConError ?? []) ? 'campo-error' : '' ?>">
            <label>Categoría <span class="req">*</span></label>
            <select name="categoria_id">
                <option value="">— Seleccionar —</option>
                <?php
                $categorias = $categorias ?? [
                    1 => 'Casa',
                    2 => 'Departamento',
                    3 => 'Cabaña'
                ];
                foreach ($categorias as $id => $nombre):
                ?>
                    <option value="<?= $id ?>" <?= ($datos['categoria_id'] ?? '') == $id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($nombre) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="msg-error" id="error-categoria_id"></span>

        </div> <div class="campo <?= in_array('cantidad_ambientes', $camposConError ?? []) ? 'campo-error' : '' ?>"> 
            <label>Ambientes <span class="req">*</span></label>
            <input type="number" name="cantidad_ambientes" min="1" max="10"
                   placeholder="1 – 10"
                   value="<?= htmlspecialchars($datos['cantidad_ambientes'] ?? '') ?>">
            <span class="msg-error" id="error-cantidad_ambientes"></span>
        </div>

        <div class="campo <?= in_array('cantidad_dormitorios', $camposConError ?? []) ? 'campo-error' : '' ?>">
            <label>Dormitorios <span class="req">*</span></label>
            <input type="number" name="cantidad_dormitorios" min="1" max="10"
                   placeholder="1 – 10"
                   value="<?= htmlspecialchars($datos['cantidad_dormitorios'] ?? '') ?>">
            <span class="hint">No puede ser mayor a la cantidad de ambientes</span>
            <span class="msg-error" id="error-cantidad_dormitorios"></span>
        </div>

        <div class="campo <?= in_array('cantidad_banos', $camposConError ?? []) ? 'campo-error' : '' ?>">
            <label>Baños <span class="req">*</span></label>
            <input type="number" name="cantidad_banos" min="1" max="10"
                   placeholder="1 – 10"
                   value="<?= htmlspecialchars($datos['cantidad_banos'] ?? '') ?>">
            <span class="hint">No puede ser mayor a la cantidad de ambientes</span>
            <span class="msg-error" id="error-cantidad_banos"></span>
        </div>

        <div class="campo <?= in_array('capacidad', $camposConError ?? []) ? 'campo-error' : '' ?>">
            <label>Capacidad <span class="opc">(opcional)</span></label>
            <input type="number" name="capacidad" min="1" max="20"
                   placeholder="1 – 20 personas"
                   value="<?= htmlspecialchars($datos['capacidad'] ?? '') ?>">
            <span class="msg-error" id="error-capacidad"></span>
        </div>

    </div>

    <p class="seccion">Estado</p>

    <div class="grid">

        <div class="campo <?= in_array('disponible', $camposConError ?? []) ? 'campo-error' : '' ?>">
            <label>Disponible <span class="req">*</span></label>
            <select name="disponible">
                <option value="">— Seleccionar —</option>
                <option value="1" <?= isset($datos['disponible']) && $datos['disponible'] == '1' ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= isset($datos['disponible']) && $datos['disponible'] == '0' ? 'selected' : '' ?>>No</option>
            </select>
            <span class="msg-error" id="error-disponible"></span>
        </div>

    </div>

    <div class="footer">
        <button type="submit">Guardar propiedad</button>
    </div>

</form>

<script>
function setError(campo, mensaje) {
    var wrapper = document.querySelector('[name="' + campo + '"]').closest('.campo');
    var span = document.getElementById('error-' + campo);
    wrapper.classList.add('campo-error');
    if (span) span.textContent = mensaje;
}

function clearErrors() {
    document.querySelectorAll('.campo-error').forEach(function(el) {
        el.classList.remove('campo-error');
    });
    document.querySelectorAll('.msg-error').forEach(function(el) {
        el.textContent = '';
    });
}

function valStr(name) {
    var el = document.querySelector('[name="' + name + '"]');
    return el ? el.value.trim() : '';
}

function valNum(name) {
    var v = valStr(name);
    return v === '' ? null : Number(v);
}

function validarFormulario() {
    clearErrors();
    var ok = true;

    var titulo = valStr('titulo');
    if (!titulo) {
        setError('titulo', 'Campo obligatorio');
        ok = false;
    } else if (titulo.length < 5 || titulo.length > 30) {
        setError('titulo', 'Debe tener entre 5 y 30 caracteres');
        ok = false;
    }

    var descripcion = valStr('descripcion');
    if (descripcion !== '') {
        if (descripcion.length < 10 || descripcion.length > 255) {
            setError('descripcion', 'Debe tener entre 10 y 255 caracteres');
            ok = false;
        }
    }

    var ubicacion = valStr('ubicacion');
    if (!ubicacion) {
        setError('ubicacion', 'Campo obligatorio');
        ok = false;
    } else if (ubicacion.length < 10 || ubicacion.length > 100) {
        setError('ubicacion', 'Debe tener entre 10 y 100 caracteres');
        ok = false;
    }

    var precio = valNum('precio');
    if (precio === null) {
        setError('precio', 'Campo obligatorio');
        ok = false;
    } else if (precio <= 0) {
        setError('precio', 'Debe ser mayor que 0');
        ok = false;
    }

    var categoria = valNum('categoria_id');
    if (categoria === null || valStr('categoria_id') === '') {
        setError('categoria_id', 'Campo obligatorio');
        ok = false;
    } else if (categoria <= 0) {
        setError('categoria_id', 'Categoría inválida');
        ok = false;
    }

    var ambientes = valNum('cantidad_ambientes');
    if (ambientes === null) {
        setError('cantidad_ambientes', 'Campo obligatorio');
        ok = false;
    } else if (ambientes < 1 || ambientes > 10) {
        setError('cantidad_ambientes', 'Debe ser un número entre 1 y 10');
        ok = false;
    }

    var dormitorios = valNum('cantidad_dormitorios');
    if (dormitorios === null) {
        setError('cantidad_dormitorios', 'Campo obligatorio');
        ok = false;
    } else if (dormitorios < 1 || dormitorios > 10) {
        setError('cantidad_dormitorios', 'Debe ser un número entre 1 y 10');
        ok = false;
    } else if (ambientes !== null && dormitorios > ambientes) {
        setError('cantidad_dormitorios', 'No puede ser mayor que la cantidad de ambientes');
        ok = false;
    }

    var banos = valNum('cantidad_banos');
    if (banos === null) {
        setError('cantidad_banos', 'Campo obligatorio');
        ok = false;
    } else if (banos < 1 || banos > 10) {
        setError('cantidad_banos', 'Debe ser un número entre 1 y 10');
        ok = false;
    } else if (ambientes !== null && banos > ambientes) { 
        setError('cantidad_banos', 'No puede ser mayor que la cantidad de ambientes');
        ok = false;  
    }

    var capacidad = valNum('capacidad');
    if (valStr('capacidad') !== '' && capacidad !== null) {
        if (capacidad < 1 || capacidad > 20) {
            setError('capacidad', 'Debe ser un número entre 1 y 20');
            ok = false;
        }
    }

    var disponible = valStr('disponible');
    if (disponible === '') {
        setError('disponible', 'Campo obligatorio');
        ok = false;
    }

    if (!ok) {
        var primerError = document.querySelector('.campo-error');
        if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    return ok;
}
</script>
</body>
</html>
