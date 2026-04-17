<h1>Mis Favoritos</h1>

<?php if ($misFavoritos->isEmpty()): ?>
    <p>Aún no tienes propiedades guardadas en favoritos.</p>
<?php else: ?>
    <div class="favoritos-container">
        <?php foreach ($misFavoritos as $fav): ?>
            <div class="card-favorito">
                <h3><?= htmlspecialchars($fav->propiedad->titulo) ?></h3>
                <p>Dirección: <?= htmlspecialchars($fav->propiedad->direccion) ?></p>
                <p>Precio: $<?= number_format($fav->propiedad->precio, 2, ',', '.') ?></p>
                
                <form action="/favoritos/quitar" method="POST">
                    <input type="hidden" name="usuario_id" value="<?= $fav->usuario_id ?>">
                    <input type="hidden" name="propiedad_id" value="<?= $fav->propiedad_id ?>">
                    <button type="submit" onclick="return confirm('¿Quitar de favoritos?')">
                        Eliminar
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>