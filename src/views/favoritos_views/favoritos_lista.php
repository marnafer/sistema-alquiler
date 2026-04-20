<?php 
$tituloPagina = "Mis Favoritos REST";
include SRC_PATH . 'views/partials/header.php'; 
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-heart-fill text-danger me-2"></i>Mis Favoritos <small class="text-muted" style="font-size: 0.5em;">(API Mode)</small></h1>
        <a href="/propiedades" class="btn btn-outline-primary btn-sm">Explorar Propiedades</a>
    </div>

    <!-- Contenedor donde se dibujan las tarjetas -->
    <div class="row row-cols-1 row-cols-md-3 g-4" id="contenedor-favoritos">
        <?php if ($misFavoritos->isEmpty()): ?>
            <div class="col-12 text-center py-5" id="mensaje-vacio">
                <p class="text-muted">No tienes propiedades guardadas en favoritos.</p>
            </div>
        <?php else: ?>
            <?php foreach ($misFavoritos as $fav): ?>
                <!-- Sincronizamos el ID: usamos "fav-card-ID" -->
                <div class="col" id="fav-card-<?= $fav->id ?>">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><?= htmlspecialchars($fav->propiedad->titulo) ?></h5>
                            <p class="card-text small text-muted"><?= htmlspecialchars($fav->propiedad->direccion) ?></p>
                        </div>
                        <div class="card-footer bg-white border-0 pb-3">
                            <button type="button" 
                                    class="btn btn-outline-danger btn-sm w-100" 
                                    onclick="eliminarFavorito(<?= $fav->id ?>)"> 
                                <i class="bi bi-trash me-1"></i> Quitar de favoritos
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
async function eliminarFavorito(favoritoId) {
    // 1. SweetAlert2
    const confirmacion = await Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta propiedad se quitará de tus favoritos",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#059669', // El verde de tus botones
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    });

    // Si el usuario cancela, no hacemos nada
    if (!confirmacion.isConfirmed) return;

    try {
        // 2. Llamada a la API
        const response = await fetch(`/api/favoritos/${favoritoId}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' }
        });

        const resultado = await response.json();

        if (response.ok) {
            // 3. Éxito: Animamos y borramos el elemento del DOM
            const elemento = document.getElementById(`fav-card-${favoritoId}`);
            if (elemento) {
                elemento.style.transition = 'all 0.5s ease';
                elemento.style.opacity = '0';
                elemento.style.transform = 'scale(0.9)';
                
                setTimeout(() => {
                    elemento.remove();
                    // Verificamos si quedó vacío para mostrar mensaje
                    const contenedor = document.getElementById('contenedor-favoritos');
                    if (contenedor.querySelectorAll('.col').length === 0) {
                        contenedor.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted">No tienes favoritos.</p></div>';
                    }
                }, 500);
            }

            // 4. Notificación de éxito tipo "Toast" (esquinera y rápida)
            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: resultado.message,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });

        } else {
            // Error de validación o lógica
            Swal.fire('Error', resultado.message || 'No se pudo eliminar', 'error');
        }

    } catch (error) {
        // Error de servidor o red
        console.error("Error de red:", error);
        Swal.fire('Error de conexión', 'El servidor no responde', 'error');
    }
}
</script>

<?php include SRC_PATH . 'views/partials/footer.php'; ?>