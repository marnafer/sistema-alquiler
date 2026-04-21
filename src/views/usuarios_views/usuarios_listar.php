<?php 
$tituloPagina = "Listado de Usuarios";
include SRC_PATH . 'views/partials/header.php'; 
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Usuarios del Sistema</h2>
        <a href="/usuarios/nuevo" class="btn btn-success">
            <i class="bi bi-person-plus"></i> Nuevo Usuario
        </a>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ¡Usuario creado con éxito!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= $u->id ?></td>
                            <td><?= htmlspecialchars($u->apellido . ", " . $u->nombre) ?></td>
                            <td><?= htmlspecialchars($u->email) ?></td>
                            <td><?= htmlspecialchars($u->telefono) ?></td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    <?= $u->rol_id == 1 ? 'Admin' : ($u->rol_id == 2 ? 'Empleado' : 'Cliente') ?>
                                </span>
                            </td>
                            <td>
                                <a href="/logs-actividad/usuario?usuario_id=<?= $u->id ?>" class="btn btn-sm btn-outline-secondary" title="Ver Actividad">
                                    <i class="bi bi-list-check"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include SRC_PATH . 'views/partials/footer.php'; ?>