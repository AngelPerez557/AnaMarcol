<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-box-open me-2" style="color:#de777d;"></i>Catálogo
        </h3>
        <small class="text-muted" id="contadorProductos">
            <?= count($productos) ?> producto<?= count($productos) !== 1 ? 's' : '' ?>
        </small>
    </div>

    <!-- Categorías chips -->
    <div class="d-flex gap-2 flex-wrap mb-4">
        <a href="<?= APP_URL ?>Tienda/catalogo"
           class="chip-categoria <?= $categoriaId === 0 ? 'activo' : '' ?>">
            <i class="fas fa-th me-1"></i>Todos
        </a>
        <?php foreach ($categorias as $cat): ?>
        <?php if ($cat->activo): ?>
        <a href="<?= APP_URL ?>Tienda/catalogo?categoria=<?= $cat->id ?>"
           class="chip-categoria <?= $categoriaId === $cat->id ? 'activo' : '' ?>">
            <?= htmlspecialchars($cat->nombre) ?>
        </a>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Buscador -->
    <div class="mb-4">
        <div class="input-group" style="max-width:400px;">
            <span class="input-group-text bg-white">
                <i class="fas fa-search text-muted"></i>
            </span>
            <input type="text"
                   class="form-control border-start-0"
                   id="buscarProducto"
                   placeholder="Buscar producto...">
        </div>
    </div>

    <!-- Grid de productos -->
    <?php if (empty($productos)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-box-open fa-3x mb-3 d-block" style="opacity:0.3;"></i>
        No hay productos en esta categoría.
    </div>
    <?php else: ?>
    <div class="row g-3" id="gridProductos">
        <?php foreach ($productos as $p): ?>
        <div class="col-6 col-md-4 col-lg-3 producto-item"
             data-nombre="<?= strtolower(htmlspecialchars($p->nombre)) ?>">
            <div class="producto-card h-100 d-flex flex-column">
                <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>">
                    <div class="producto-img"
                         style="background-image:url('<?= $p->getImageUrl() ?>');">
                    </div>
                </a>
                <div class="p-3 flex-fill d-flex flex-column">
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>"
                       style="text-decoration:none; color:inherit;">
                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($p->nombre) ?></h6>
                    </a>
                    <?php if ($p->descripcion): ?>
                    <small class="text-muted mb-2" style="font-size:0.8rem;">
                        <?= htmlspecialchars(substr($p->descripcion, 0, 60)) ?>...
                    </small>
                    <?php endif; ?>
                    <div class="fw-bold mb-3 mt-auto" style="color:#de777d;">
                        <?php if ($p->tieneVariantes()): ?>
                        <small class="text-muted fw-normal">Desde</small>
                        <?php endif; ?>
                        L. <?= number_format((float)$p->precio_base, 2) ?>
                    </div>
                    <?php if ($p->tieneVariantes()): ?>
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>"
                       class="btn-rosa d-block text-center text-decoration-none">
                        <i class="fas fa-eye me-1"></i>Ver opciones
                    </a>
                    <?php else: ?>
                    <button type="button"
                            class="btn-rosa w-100"
                            onclick="agregarAlCarrito(
                                <?= $p->id ?>,
                                '<?= addslashes(htmlspecialchars($p->nombre)) ?>',
                                <?= $p->precio_base ?>,
                                '<?= $p->getImageUrl() ?>',
                                null, null)">
                        <i class="fas fa-cart-plus me-1"></i>Agregar al carrito
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscar   = document.getElementById('buscarProducto');
    const items    = document.querySelectorAll('.producto-item');
    const contador = document.getElementById('contadorProductos');

    buscar?.addEventListener('input', function () {
        const texto  = this.value.toLowerCase();
        let visible  = 0;

        items.forEach(item => {
            const nombre = item.dataset.nombre || '';
            if (nombre.includes(texto)) {
                item.style.display = '';
                visible++;
            } else {
                item.style.display = 'none';
            }
        });

        contador.textContent = `${visible} producto${visible !== 1 ? 's' : ''}`;
    });
});
</script>