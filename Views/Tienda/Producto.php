<div class="container py-5">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>Tienda/index" style="color:#de777d;">Inicio</a></li>
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>Tienda/catalogo" style="color:#de777d;">Catálogo</a></li>
            <div class="d-flex align-items-start justify-content-between mb-2">
            <h2 class="fw-bold mb-0"><?= htmlspecialchars($producto->nombre) ?></h2>
            <button type="button"
                    class="btn-favorito ms-3"
                    data-id="<?= $producto->id ?>"
                    title="Agregar a favoritos"
                    style="background:rgba(255,255,255,0.9); border:2px solid #f0e0e1;
                        border-radius:50%; width:42px; height:42px; flex-shrink:0;
                        display:flex; align-items:center; justify-content:center;
                        cursor:pointer; transition:all 0.2s; font-size:1.1rem;">
                <i class="fas fa-heart" style="color:#ccc;"></i>
            </button>
        </div>
        </ol>
    </nav>

    <div class="row g-4">

        <!-- Imagen -->
        <div class="col-12 col-md-5">
            <div id="imagenProducto"
                 style="
                    height:380px;
                    background-image: url('<?= $producto->getImageUrl() ?>');
                    background-size: contain;
                    background-position: center;
                    background-repeat: no-repeat;
                    background-color: #fdf8f8;
                    border-radius: 16px;
                    border: 1px solid #f0e0e1;
                    transition: all 0.3s ease;">
            </div>
        </div>

        <!-- Info -->
        <div class="col-12 col-md-7">
            <h2 class="fw-bold mb-2"><?= htmlspecialchars($producto->nombre) ?></h2>

            <?php if ($producto->descripcion): ?>
            <p class="text-muted mb-3"><?= htmlspecialchars($producto->descripcion) ?></p>
            <?php endif; ?>

            <!-- Precio -->
            <div class="mb-4">
                <span class="fw-bold" style="font-size:1.8rem; color:#de777d;" id="precioMostrado">
                    L. <?= number_format((float)$producto->precio_base, 2) ?>
                </span>
            </div>

            <!-- Variantes -->
            <?php if (!empty($variantes)): ?>
            <div class="mb-4">
                <label class="form-label fw-semibold">Selecciona una opción:</label>
                <div class="d-flex gap-2 flex-wrap" id="contenedorVariantes">
                    <?php foreach ($variantes as $v): ?>
                    <?php if ($v->activo && $v->stock > 0): ?>
                    <!-- Variante disponible -->
                    <button type="button"
                            class="btn-variante"
                            data-id="<?= $v->id ?>"
                            data-nombre="<?= htmlspecialchars($v->nombre) ?>"
                            data-precio="<?= $v->precio ?? $producto->precio_base ?>"
                            data-imagen="<?= $v->image_url ? APP_URL . 'Content/Demo/img/Variantes/' . htmlspecialchars($v->image_url) : '' ?>"
                            style="padding:8px 16px; border:2px solid #dee2e6; border-radius:8px;
                                   background:#fff; cursor:pointer; font-weight:500;
                                   transition:all 0.2s;">
                        <?= htmlspecialchars($v->nombre) ?>
                        <small style="color:#de777d; display:block; font-size:0.75rem;">
                            L. <?= number_format((float)($v->precio ?? $producto->precio_base), 2) ?>
                        </small>
                    </button>
                    <?php else: ?>
                    <!-- Variante sin stock — bloqueada sin revelar motivo -->
                    <button type="button" disabled
                            style="padding:8px 16px; border:2px solid #eee; border-radius:8px;
                                   background:#f8f8f8; cursor:not-allowed; color:#aaa; font-weight:500;
                                   opacity:0.5;">
                        <?= htmlspecialchars($v->nombre) ?>
                        <small style="display:block; font-size:0.75rem;">No disponible</small>
                    </button>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Disponibilidad — sin revelar cantidad -->
            <?php if (!$producto->tieneVariantes()): ?>
            <div class="mb-3">
                <?php if ($producto->stock > 0): ?>
                <span class="badge bg-success">
                    <i class="fas fa-check me-1"></i>Disponible
                </span>
                <?php else: ?>
                <span class="badge bg-secondary">
                    <i class="fas fa-times me-1"></i>No disponible
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Botones -->
            <?php
            // Determinar si el producto está completamente sin stock
            $sinStock = false;
            if (!$producto->tieneVariantes() && $producto->stock == 0) {
                $sinStock = true;
            }
            if ($producto->tieneVariantes()) {
                $hayVarianteDisponible = false;
                foreach ($variantes as $v) {
                    if ($v->activo && $v->stock > 0) {
                        $hayVarianteDisponible = true;
                        break;
                    }
                }
                if (!$hayVarianteDisponible) $sinStock = true;
            }
            ?>
            <div class="d-flex gap-2 mt-4">

                <?php if (!$sinStock): ?>
                <!-- Selector de cantidad -->
                <div class="d-flex align-items-center gap-2 me-2">
                    <button type="button" id="btnMenos"
                            style="width:36px; height:36px; border-radius:50%; border:2px solid #de777d;
                                   background:#fff; color:#de777d; font-size:1.2rem; cursor:pointer;">−</button>
                    <span id="cantidad" style="font-size:1.1rem; font-weight:700; min-width:30px; text-align:center;">1</span>
                    <button type="button" id="btnMas"
                            style="width:36px; height:36px; border-radius:50%; border:2px solid #de777d;
                                   background:#fff; color:#de777d; font-size:1.2rem; cursor:pointer;">+</button>
                </div>

                <button type="button"
                        class="btn-rosa flex-fill"
                        id="btnAgregarCarrito">
                    <i class="fas fa-cart-plus me-2"></i>Agregar al carrito
                </button>

                <?php else: ?>
                <!-- Sin stock — botón bloqueado -->
                <button type="button" class="btn-rosa flex-fill" disabled
                        style="opacity:0.5; cursor:not-allowed;">
                    <i class="fas fa-ban me-2"></i>No disponible
                </button>
                <?php endif; ?>

            </div>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let varianteSeleccionada = null;
    let cantidad = 1;
    const imagenOriginal = '<?= $producto->getImageUrl() ?>';
    const imgDiv = document.getElementById('imagenProducto');

    // ── Selección de variante ─────────────────────
    document.querySelectorAll('.btn-variante').forEach(btn => {
        btn.addEventListener('click', function () {

            document.querySelectorAll('.btn-variante').forEach(b => {
                b.style.borderColor = '#dee2e6';
                b.style.background  = '#fff';
                b.style.color       = '#333';
            });

            this.style.borderColor = '#de777d';
            this.style.background  = '#de777d';
            this.style.color       = '#fff';

            varianteSeleccionada = {
                id:     this.dataset.id,
                nombre: this.dataset.nombre,
                precio: parseFloat(this.dataset.precio),
            };

            // Actualizar precio
            document.getElementById('precioMostrado').textContent =
                'L. ' + varianteSeleccionada.precio.toFixed(2);

            // Cambiar imagen de la variante o restaurar la del producto
            const imagenVariante = this.dataset.imagen;
            if (imagenVariante && imagenVariante.trim() !== '') {
                imgDiv.style.backgroundImage = `url('${imagenVariante}')`;
            } else {
                imgDiv.style.backgroundImage = `url('${imagenOriginal}')`;
            }
        });
    });

    // ── Cantidad ──────────────────────────────────
    const btnMenos = document.getElementById('btnMenos');
    const btnMas   = document.getElementById('btnMas');

    if (btnMenos) {
        btnMenos.addEventListener('click', () => {
            if (cantidad > 1) {
                cantidad--;
                document.getElementById('cantidad').textContent = cantidad;
            }
        });
    }

    if (btnMas) {
        btnMas.addEventListener('click', () => {
            cantidad++;
            document.getElementById('cantidad').textContent = cantidad;
        });
    }

    // ── Agregar al carrito ────────────────────────
    const btnAgregar = document.getElementById('btnAgregarCarrito');
    if (btnAgregar) {
        btnAgregar.addEventListener('click', function () {
            const tieneVariantes = <?= $producto->tieneVariantes() ? 'true' : 'false' ?>;

            if (tieneVariantes && !varianteSeleccionada) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecciona una opción',
                    text: 'Elige una variante antes de agregar al carrito.',
                    confirmButtonColor: '#de777d'
                });
                return;
            }

            const precio = varianteSeleccionada
                ? varianteSeleccionada.precio
                : <?= $producto->precio_base ?>;

            const varianteId     = varianteSeleccionada ? varianteSeleccionada.id     : null;
            const varianteNombre = varianteSeleccionada ? varianteSeleccionada.nombre : null;

            // Imagen actualmente visible
            const imgActual = imgDiv.style.backgroundImage.slice(5, -2);

            for (let i = 0; i < cantidad; i++) {
                agregarAlCarrito(
                    <?= $producto->id ?>,
                    '<?= addslashes(htmlspecialchars($producto->nombre)) ?>',
                    precio,
                    imgActual || imagenOriginal,
                    varianteId,
                    varianteNombre
                );
            }
        });
    }

});
</script>