<?php
$tituloPagina = "Logs por Usuario";
include SRC_PATH . 'views/partials/header.php';
?>

<h1>Logs del Usuario</h1>

<p><a href="/logs-actividad" class="btn btn-light">Volver al listado</a></p>

<table class="table table-sm table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Acción</th>
            <th>Detalle</th>
            <th>Fecha</th>
            <th>IP</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($logs)): ?>
            <?php foreach ($logs as $log): ?>
                <tr data-id="<?= htmlspecialchars($log->id) ?>">
                    <td><?= htmlspecialchars($log->id) ?></td>
                    <td><?= htmlspecialchars($log->accion) ?></td>
                    <td><?= htmlspecialchars(substr($log->detalle ?? '', 0, 80)) ?></td>
                    <td><?= htmlspecialchars($log->fecha) ?></td>
                    <td><?= htmlspecialchars($log->ip ?? '—') ?></td>
                    <td>
                        <a href="/logs-actividad/ver?id=<?= htmlspecialchars($log->id) ?>" class="btn btn-sm btn-primary">Ver</a>
                        <button class="btn btn-sm btn-danger btn-eliminar" data-id="<?= htmlspecialchars($log->id) ?>">Eliminar</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6">No hay registros para este usuario.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
async function eliminarLogUsuario(id, row) {
    if (!confirm('¿Eliminar registro #' + id + '?')) return;
    try {
        const resp = await fetch('/api/logs/' + id, { method: 'DELETE' });
        const js = await resp.json();
        if (resp.ok) {
            if (window.Swal) Swal.fire({ icon: 'success', title: 'Eliminado', text: js.message || 'Registro eliminado' });
            row.remove();
        } else {
            if (window.Swal) Swal.fire('Error', js.message || 'No se pudo eliminar', 'error'); else alert(js.message || 'No se pudo eliminar');
        }
    } catch (err) {
        if (window.Swal) Swal.fire('Error', 'Error de conexión', 'error'); else alert('Error de conexión');
    }
}

document.querySelectorAll('.btn-eliminar').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const id = btn.getAttribute('data-id');
        const row = btn.closest('tr');
        eliminarLogUsuario(id, row);
    });
});
</script>

<?php include SRC_PATH . 'views/partials/footer.php'; ?>