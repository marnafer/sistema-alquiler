<?php
$tituloPagina = "Propiedades";
include SRC_PATH . 'views/partials/header.php';
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Propiedades</h3>

        <a href="<?= BASE_URL ?>/propiedades/nuevo" class="btn btn-primary">
            + Nueva Propiedad
        </a>
    </div>

    <div id="alerta"></div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Dirección</th>
                    <th>Precio</th>
                    <th>Categoría</th>
                    <th>Localidad</th>
                    <th>Ambientes</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody id="tabla-propiedades">
                <tr>
                    <td colspan="8" class="text-center">Cargando propiedades...</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
const BASE = "<?= BASE_URL ?>";

/* ================= ALERTAS ================= */
function mostrarAlerta(tipo, msg) {
    document.getElementById("alerta").innerHTML = `
        <div class="alert alert-${tipo}">${msg}</div>
    `;

    setTimeout(() => {
        document.getElementById("alerta").innerHTML = "";
    }, 3000);
}

/* ================= LISTAR ================= */
async function cargarPropiedades() {

    const tbody = document.getElementById("tabla-propiedades");

    try {
        const resp = await fetch(BASE + "/api/propiedades");
        const json = await resp.json();

        if (!resp.ok) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-danger">
                        Error al cargar propiedades
                    </td>
                </tr>
            `;
            return;
        }

        if (!json.data || json.data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center">
                        No hay propiedades registradas
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = json.data.map(p => `
            <tr>
                <td>${p.id}</td>
                <td>${p.titulo ?? '-'}</td>
                <td>${p.direccion ?? '-'}</td>
                <td>$${p.precio ?? 0}</td>
                <td>${p.categoria?.nombre ?? '-'}</td>
                <td>${p.localidad?.nombre ?? '-'}</td>
                <td>${p.cantidad_ambientes ?? '-'}</td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="editar(${p.id})">
                        Editar
                    </button>

                    <button class="btn btn-sm btn-danger" onclick="eliminar(${p.id})">
                        Eliminar
                    </button>
                </td>
            </tr>
        `).join("");

    } catch (err) {
        console.error(err);
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-danger">
                    Error de conexión
                </td>
            </tr>
        `;
    }
}

/* ================= ELIMINAR ================= */
async function eliminar(id) {

    if (!confirm("¿Seguro que querés eliminar esta propiedad?")) return;

    try {
        const resp = await fetch(BASE + "/api/propiedades/" + id, {
            method: "DELETE"
        });

        const json = await resp.json();

        if (!resp.ok) {
            mostrarAlerta("danger", json.error || "Error al eliminar");
            return;
        }

        mostrarAlerta("success", "Propiedad eliminada");
        cargarPropiedades();

    } catch (err) {
        console.error(err);
        mostrarAlerta("danger", "Error de conexión");
    }
}

/* ================= EDITAR (placeholder) ================= */
function editar(id) {
    window.location.href = BASE + "/propiedades/editar/" + id;
}

/* ================= INIT ================= */
document.addEventListener("DOMContentLoaded", cargarPropiedades);
</script>

<?php include SRC_PATH . 'views/partials/footer.php'; ?>