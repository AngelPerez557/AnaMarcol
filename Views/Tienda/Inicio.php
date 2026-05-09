<?php
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

<!-- ─── ALERTA DESCUENTO ACTIVO ──────────────── -->
<?php if (!empty($descuentoActivo)): ?>
<div style="background:linear-gradient(135deg,#dc3545,#c0392b);color:#fff;text-align:center;padding:10px 16px;font-size:0.9rem;font-weight:600;">
    <i class="fas fa-tag me-2"></i>
    <?= $descuentoActivo['aplica_a'] === 'todo'
        ? "¡{$descuentoActivo['porcentaje']}% de descuento en toda la tienda! Válido hasta " . date('d/m/Y', strtotime($descuentoActivo['fecha_fin']))
        : "¡{$descuentoActivo['porcentaje']}% de descuento en " . htmlspecialchars($descuentoActivo['categoria_nombre'] ?? '') . "! Válido hasta " . date('d/m/Y', strtotime($descuentoActivo['fecha_fin']))
    ?>
</div>
<?php endif; ?>

<!-- ─── SLIDER DE BANNERS ─────────────────────── -->
<?php if (!empty($banners)): ?>
<div id="carouselBanners" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php foreach ($banners as $i => $b): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
            <div style="height:clamp(220px,45vw,420px);background-image:url('<?= APP_URL ?>Content/Demo/img/Banners/<?= htmlspecialchars($b['imagen_url']) ?>');background-size:cover;background-position:center;">
                <?php if ($b['titulo']): ?>
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="fw-bold"><?= htmlspecialchars($b['titulo']) ?></h2>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (count($banners) > 1): ?>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselBanners" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselBanners" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
    <?php endif; ?>
</div>
<?php else: ?>
<div style="background:linear-gradient(135deg,#f5e6e7 0%,#fdf8f8 100%);padding:clamp(40px,8vw,80px) 0;text-align:center;">
    <div class="container">
        <h1 class="fw-bold mb-3" style="color:#de777d;">Ana Marcol Makeup Studio</h1>
        <p class="text-muted mb-4">Beleza profesional para cada ocasión</p>
        <a href="<?= APP_URL ?>Tienda/catalogo" class="btn-rosa px-4 py-2" style="border-radius:25px;font-size:1rem;">Ver catálogo</a>
    </div>
</div>
<?php endif; ?>

<!-- ─── CATEGORÍAS ────────────────────────────── -->
<div class="container my-5">
    <h3 class="fw-bold mb-4 text-center">Categorías</h3>
    <div class="d-flex gap-2 flex-wrap justify-content-center">
        <a href="<?= APP_URL ?>Tienda/catalogo" class="chip-categoria">
            <i class="fas fa-th me-1"></i>Todos
        </a>
        <?php foreach ($categorias as $cat): ?>
        <?php if ($cat->activo): ?>
        <a href="<?= APP_URL ?>Tienda/catalogo/<?= $cat->id ?>" class="chip-categoria">
            <?= htmlspecialchars($cat->nombre) ?>
        </a>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- ─── PRODUCTOS DESTACADOS ─────────────────── -->
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Productos destacados</h3>
        <a href="<?= APP_URL ?>Tienda/catalogo" class="btn-rosa-outline">
            Ver todos <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
    <?php if (empty($productosDestacados)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-box-open fa-3x mb-3 d-block" style="opacity:0.3;"></i>
        Próximamente nuevos productos.
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($productosDestacados as $p):
            $desc = calcDesc($p, $descuentoActivo ?? null);
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="producto-card">
                <div style="position:relative;">
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>-<?= slugify($p->nombre) ?>">
                        <div class="producto-img" style="background-image:url('<?= $p->getImageUrl() ?>');"></div>
                    </a>
                    <?php if ($desc['aplica']): ?>
                    <span style="position:absolute;top:8px;left:8px;background:#dc3545;color:#fff;padding:3px 8px;border-radius:20px;font-size:0.72rem;font-weight:700;z-index:2;">
                        -<?= $desc['pct'] ?>% OFF
                    </span>
                    <?php endif; ?>
                </div>
                <div class="p-3">
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>-<?= slugify($p->nombre) ?>" style="text-decoration:none;color:inherit;">
                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($p->nombre) ?></h6>
                    </a>
                    <div class="fw-bold mb-2" style="color:#de777d;">
                        <?php if ($p->tieneVariantes()): ?>
                            <small class="text-muted">Desde</small> L. <?= number_format((float)$p->precio_base, 2) ?>
                        <?php elseif ($desc['aplica']): ?>
                            <span class="text-decoration-line-through text-muted fw-normal" style="font-size:0.82rem;">L. <?= number_format((float)$p->precio_base, 2) ?></span>
                            <span class="ms-1">L. <?= number_format($desc['precio'], 2) ?></span>
                        <?php else: ?>
                            L. <?= number_format((float)$p->precio_base, 2) ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($p->tieneVariantes()): ?>
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>-<?= slugify($p->nombre) ?>"
                       class="btn-rosa w-100 d-block text-center" style="border-radius:8px;padding:8px;">
                        <i class="fas fa-eye me-1"></i>Ver opciones
                    </a>
                    <?php elseif ((int)$p->stock <= 0): ?>
                    <button type="button" class="btn-rosa w-100" disabled style="opacity:0.5;cursor:not-allowed;background:#aaa;border:none;">
                        <i class="fas fa-times-circle me-1"></i>Agotado
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn-rosa w-100"
                            onclick="agregarAlCarrito(<?= $p->id ?>,'<?= addslashes(htmlspecialchars($p->nombre)) ?>',<?= $desc['aplica'] ? $desc['precio'] : $p->precio_base ?>,'<?= $p->getImageUrl() ?>',null,null)">
                        <i class="fas fa-cart-plus me-1"></i>Agregar
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ─── COMBOS ────────────────────────────────── -->
<?php if (!empty($combos)): ?>
<div style="background:#f5e6e7;padding:50px 0;">
    <div class="container">
        <h3 class="fw-bold mb-4 text-center">Combos especiales</h3>
        <div class="row g-3">
            <?php foreach ($combos as $combo):
                $productosCombo = $comboProductos[$combo->id] ?? [];
                $precioOriginal = array_reduce($productosCombo, fn($s,$pc) => $s + (float)$pc['precio_unitario'] * (int)$pc['cantidad'], 0);
                $precioCombo    = $combo->calcularPrecio($productosCombo);
            ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="producto-card h-100 d-flex flex-column">
                    <div style="position:relative;">
                        <div class="producto-img" style="background-image:url('<?= $combo->getImageUrl() ?>');"></div>
                        <?php if ($combo->tieneDescuento()): ?>
                        <span style="position:absolute;top:10px;left:10px;background:#dc3545;color:#fff;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:700;z-index:2;">
                            <?= $combo->getDescuentoFormateado() ?> OFF
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="p-3 flex-fill d-flex flex-column">
                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($combo->nombre) ?></h6>
                        <?php if ($combo->descripcion): ?>
                        <small class="text-muted d-block mb-2"><?= htmlspecialchars($combo->descripcion) ?></small>
                        <?php endif; ?>

                        <?php if (!empty($productosCombo)): ?>

                        <!-- Precio -->
                        <div class="fw-bold mb-2" style="color:#de777d;">
                            <?php if ($combo->tieneDescuento() && $precioOriginal > $precioCombo): ?>
                            <span class="text-decoration-line-through text-muted fw-normal" style="font-size:0.82rem;">
                                L. <?= number_format($precioOriginal, 2) ?>
                            </span>
                            <span class="ms-1">L. <?= number_format($precioCombo, 2) ?></span>
                            <?php else: ?>
                            L. <?= number_format($precioCombo, 2) ?>
                            <?php endif; ?>
                        </div>

                        <!-- Productos incluidos -->
                        <div class="mb-3">
                            <?php foreach ($productosCombo as $pc): ?>
                            <small class="text-muted d-block" style="font-size:0.75rem;">
                                <i class="fas fa-check me-1" style="color:#de777d;"></i>
                                <?= htmlspecialchars($pc['producto_nombre']) ?>
                                <?= $pc['variante_nombre'] ? '— ' . htmlspecialchars($pc['variante_nombre']) : '' ?>
                                x<?= $pc['cantidad'] ?>
                            </small>
                            <?php endforeach; ?>
                        </div>

                        <!-- Botón agregar -->
                        <?php
                        $productosJson = json_encode(array_map(fn($pc) => [
                            'id'         => $pc['producto_id'],
                            'varianteId' => $pc['variante_id'],
                            'nombre'     => $pc['producto_nombre'] . ($pc['variante_nombre'] ? ' — ' . $pc['variante_nombre'] : ''),
                            'precio'     => round((float)$pc['precio_unitario'] * (1 - (float)($combo->descuento ?? 0) / 100), 2),
                            'cantidad'   => (int)$pc['cantidad'],
                            'imagen'     => APP_URL . 'Content/Demo/img/Productos/' . ($pc['producto_imagen'] ?? ''),
                        ], $productosCombo));
                        ?>
                        <button type="button"
                                class="btn-rosa w-100 mt-auto btn-agregar-combo"
                                data-nombre="<?= addslashes(htmlspecialchars($combo->nombre)) ?>"
                                data-imagen="<?= $combo->getImageUrl() ?>"
                                data-productos='<?= htmlspecialchars($productosJson, ENT_QUOTES) ?>'>
                            <i class="fas fa-cart-plus me-1"></i>Agregar combo
                        </button>

                        <?php else: ?>
                        <button type="button" class="btn-rosa w-100 mt-auto" disabled
                                style="opacity:0.5;cursor:not-allowed;background:#aaa;border:none;">
                            <i class="fas fa-box-open me-1"></i>Sin productos
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ─── GALERÍA CLIENTES ──────────────────────── -->
<?php if (!empty($galeria)): ?>
<div style="background:#f5e6e7;padding:50px 0;">
    <div class="container">
        <h3 class="fw-bold mb-2 text-center">Nuestros trabajos</h3>
        <p class="text-center text-muted mb-4" style="font-size:0.9rem;">Resultados reales de nuestras clientas</p>
        <div class="row g-2">
            <?php foreach ($galeria as $foto): ?>
            <div class="col-6 col-sm-4 col-md-3">
                <div style="height:200px;border-radius:12px;overflow:hidden;background-image:url('<?= APP_URL ?>Content/Demo/img/Galeria/<?= htmlspecialchars($foto['imagen_url']) ?>');background-size:cover;background-position:center;transition:transform 0.3s;cursor:pointer;"
                     onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'"></div>
                <?php if (!empty($foto['descripcion'])): ?>
                <small class="text-muted d-block text-center mt-1" style="font-size:0.75rem;"><?= htmlspecialchars($foto['descripcion']) ?></small>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ─── CTA CITAS ─────────────────────────────── -->
<div class="container my-5">
    <div class="card border-0 text-center p-5"
         style="background:linear-gradient(135deg,#de777d 0%,#b05a60 100%);border-radius:16px;">
        <h3 class="fw-bold text-white mb-3">¿Lista para tu próxima sesión?</h3>
        <p class="text-white mb-4" style="opacity:0.9;">Agenda tu cita de maquillaje con Ana Marcol y luce espectacular.</p>
        <a href="<?= APP_URL ?>Tienda/citas"
           style="background:#fff;color:#de777d;font-weight:700;padding:12px 32px;border-radius:25px;text-decoration:none;display:inline-block;">
            <i class="fas fa-calendar-plus me-2"></i>Agendar ahora
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-agregar-combo').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const nombre  = this.dataset.nombre;
            const imagen  = this.dataset.imagen;
            let productos = [];
            try { productos = JSON.parse(this.dataset.productos); } catch(e) {}

            if (!productos.length) {
                Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:2500 })
                    .fire({ icon:'warning', title:'Este combo no tiene productos configurados' });
                return;
            }

            productos.forEach(function (p) {
                for (let i = 0; i < p.cantidad; i++) {
                    agregarAlCarrito(
                        p.id,
                        '[Combo] ' + nombre + ' — ' + p.nombre,
                        p.precio,
                        p.imagen || imagen,
                        p.varianteId || null,
                        null
                    );
                }
            });

            Swal.mixin({ toast:true, position:'bottom-end', showConfirmButton:false, timer:2000 })
                .fire({ icon:'success', title:'Combo "' + nombre + '" agregado al carrito' });
        });
    });
});
</script>