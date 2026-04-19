<?php 
$tituloPagina = "Mis Favoritos";
include SRC_PATH . 'views/partials/header.php'; 
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-heart-fill text-danger me-2"></i>Mis Favoritos</h1>
        <a href="/propiedades" class="btn btn-outline-primary">Ver más propiedades</a>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            Propiedad quitada de tus favoritos.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($misFavoritos->isEmpty()): ?>
        <div class="text-center py-5">
            <i class="bi bi-bookmark-star shadow-sm p-3 rounded-circle bg-light mb-3" style="font-size: 3rem; color: #ccc;"></i>
            <p class="lead text-muted">Aún no tienes propiedades guardadas en favoritos.</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($misFavoritos as $fav): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><?= htmlspecialchars($fav->propiedad->titulo) ?></h5>
                            <p class="card-text mb-1">
                                <strong>Dirección:</strong> <?= htmlspecialchars($fav->propiedad->direccion) ?>
                            </p>
                            <p class="card-text text-success fw-bold" style="font-size: 1.2rem;">
                                $<?= number_format($fav->propiedad->precio, 2, ',', '.') ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3">
                            <form action="/favoritos/quitar" method="POST">
                                <!-- IDs sanitizados que vienen del controlador -->
                                <input type="hidden" name="usuario_id" value="<?= $fav->usuario_id ?>">
                                <input type="hidden" name="propiedad_id" value="<?= $fav->propiedad_id ?>">
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" 
                                            onclick="return confirm('¿Quitar esta propiedad de tus favoritos?')">
                                        <i class="bi bi-trash me-1"></i> Eliminar de favoritos
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include SRC_PATH . 'views/partials/footer.php'; ?>