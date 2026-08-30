<?php
// Helper descuento — calcula si aplica y el precio con descuento
if (!function_exists('calcDesc')) {
    function calcDesc(object $p, ?array $d): array {
        if (empty($d)) return ['aplica' => false, 'pct' => 0, 'precio' => null];
        $aplica = $d['aplica_a'] === 'todo' ||
                  ($d['aplica_a'] === 'categoria' && (int)$p->categoria_id === (int)$d['categoria_id']);
        if (!$aplica) return ['aplica' => false, 'pct' => 0, 'precio' => null];
        return [
            'aplica' => true,
            'pct'    => (float)$d['porcentaje'],
            'precio' => round((float)$p->precio_base * (1 - (float)$d['porcentaje'] / 100), 2),
        ];
    }
}
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-box-open me-2" style="color:#de777d;"></i>Catálogo
        </h3>
        <small class="text-muted" id="contadorProductos">
            <?= count($productos) ?> producto<?= count($productos) !== 1 ? 's' : '' ?>
        </small>
    </div>

    <?php if (!empty($descuentoActivo)): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 py-2">
        <i class="fas fa-tag fa-lg"></i>
        <div>
            <strong><?= $descuentoActivo['porcentaje'] ?>% de descuento</strong>
            <?php if ($descuentoActivo['aplica_a'] === 'categoria'): ?>
            en la categoría <strong><?= htmlspecialchars($descuentoActivo['categoria_nombre'] ?? '') ?></strong>
            <?php else: ?>
            en toda la tienda
            <?php endif; ?>
            — hasta <strong><?= date('d/m/Y', strtotime($descuentoActivo['fecha_fin'])) ?></strong>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-flex gap-2 flex-wrap mb-4">
        <a href="<?= APP_URL ?>Tienda/catalogo"
           class="chip-categoria <?= $categoriaId === 0 ? 'activo' : '' ?>">
            <i class="fas fa-th me-1"></i>Todos
        </a>
        <?php foreach ($categorias as $cat): ?>
        <?php if ($cat->activo): ?>
        <a href="<?= APP_URL ?>Tienda/catalogo/<?= $cat->id ?>"
           class="chip-categoria <?= $categoriaId === $cat->id ? 'activo' : '' ?>">
            <?= htmlspecialchars($cat->nombre) ?>
        </a>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="mb-4">
        <div class="input-group" style="max-width:400px;">
            <span class="input-group-text bg-white">
                <i class="fas fa-search text-muted"></i>
            </span>
            <input type="text" class="form-control border-start-0"
                   id="buscarProducto" placeholder="Buscar producto...">
        </div>
    </div>

    <?php if (empty($productos)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-box-open fa-3x mb-3 d-block" style="opacity:0.3;"></i>
        No hay productos en esta categoría.
    </div>
    <?php else: ?>
    <div class="row g-3" id="gridProductos">
        <?php foreach ($productos as $p):
            $desc = calcDesc($p, $descuentoActivo ?? null);
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 producto-item"
             data-nombre="<?= strtolower(htmlspecialchars($p->nombre)) ?>">
            <div class="producto-card h-100 d-flex flex-column">

                <!-- Imagen + badge descuento -->
                <div style="position:relative;">
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>-<?= slugify($p->nombre) ?>">
                        <div class="producto-img"
                             style="background-image:url('<?= $p->getImageUrl() ?>');">
                        </div>
                    </a>

                    <!-- Badge descuento — esquina superior izquierda -->
                    <?php if ($desc['aplica']): ?>
                    <span style="position:absolute; top:8px; left:8px;
                                 background:#dc3545; color:#fff;
                                 padding:3px 8px; border-radius:20px;
                                 font-size:0.72rem; font-weight:700; z-index:2;">
                        -<?= $desc['pct'] ?>% OFF
                    </span>
                    <?php endif; ?>

                </div>

                <div class="p-3 flex-fill d-flex flex-column">
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>-<?= slugify($p->nombre) ?>"
                       style="text-decoration:none; color:inherit;">
                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($p->nombre) ?></h6>
                    </a>
                    <?php if ($p->descripcion): ?>
                    <small class="text-muted mb-2" style="font-size:0.8rem;">
                        <?= htmlspecialchars(substr($p->descripcion, 0, 60)) ?>...
                    </small>
                    <?php endif; ?>

                    <?php if ($desc['aplica']): ?>
                    <div class="fw-semibold mb-3 mt-auto" style="color:#1e8e4a; font-size:0.85rem;">
                        <i class="fas fa-tag me-1"></i>Producto en descuento
                    </div>
                    <?php else: ?>
                    <div class="mb-3 mt-auto"></div>
                    <?php endif; ?>

                    <?php if ($p->tieneVariantes()): ?>
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>-<?= slugify($p->nombre) ?>"
                       class="btn-rosa d-block text-center text-decoration-none">
                        <i class="fas fa-eye me-1"></i>Ver opciones
                    </a>
                    <?php elseif ($p->stock > 0): ?>
                    <button type="button" class="btn-rosa w-100"
                            onclick="agregarAlCarritoConStock(
                                <?= $p->id ?>, 0,
                                '<?= addslashes(htmlspecialchars($p->nombre)) ?>',
                                '<?= $p->getImageUrl() ?>')">
                        <i class="fas fa-cart-plus me-1"></i>Agregar al carrito
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn-rosa w-100" disabled
                            style="opacity:0.5; cursor:not-allowed;">
                        <i class="fas fa-ban me-1"></i>No disponible
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

    // ── Buscador ─────────────────────────────────
    const buscar   = document.getElementById('buscarProducto');
    const items    = document.querySelectorAll('.producto-item');
    const contador = document.getElementById('contadorProductos');

    buscar?.addEventListener('input', function () {
        const texto = this.value.toLowerCase();
        let visible = 0;
        items.forEach(item => {
            const nombre = item.dataset.nombre || '';
            if (nombre.includes(texto)) { item.style.display = ''; visible++; }
            else                        { item.style.display = 'none'; }
        });
        contador.textContent = `${visible} producto${visible !== 1 ? 's' : ''}`;
    });

});
</script>