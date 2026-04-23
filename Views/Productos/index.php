<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-boxes me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($productos) ?> producto<?= count($productos) !== 1 ? 's' : '' ?> registrado<?= count($productos) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <?php if (Auth::can('productos.crear')): ?>
        <a href="<?= APP_URL ?>Productos/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Producto
        </a>
        <?php endif; ?>
    </div>

    <!-- ─────────────────────────────────────────────
         FILTROS
         ───────────────────────────────────────────── -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               class="form-control border-start-0"
                               id="buscarProducto"
                               placeholder="Buscar por nombre...">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-select" id="filtroCategoria">
                        <option value="">Todas las categorías</option>
                        <?php
                        $cats = array_unique(array_map(fn($p) => $p->categoria_nombre, $productos));
                        sort($cats);
                        foreach ($cats as $cat):
                        ?>
                        <option value="<?= htmlspecialchars($cat) ?>">
                            <?= htmlspecialchars($cat) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 text-end">
                    <small class="text-muted" id="contadorVisible">
                        Mostrando <?= count($productos) ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- ─────────────────────────────────────────────
         CARDS DE PRODUCTOS
         ───────────────────────────────────────────── -->
    <?php if (empty($productos)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-boxes fa-3x mb-3 d-block" style="color:#de777d;opacity:0.4;"></i>
        No hay productos registrados.
        <?php if (Auth::can('productos.crear')): ?>
        <br><a href="<?= APP_URL ?>Productos/registry" class="btn btn-primary mt-3">
            <i class="fas fa-plus me-2"></i>Crear el primero
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>

    <div class="row g-3" id="gridProductos">
        <?php foreach ($productos as $producto): ?>
        <div class="col-12 col-sm-6 col-md-4 col-xl-3 producto-card"
             data-nombre="<?= strtolower(htmlspecialchars($producto->nombre)) ?>"
             data-categoria="<?= htmlspecialchars($producto->categoria_nombre) ?>"
             data-activo="<?= $producto->activo ?>">
            <div class="card h-100 <?= !$producto->isActivo() ? 'opacity-50' : '' ?>">

                <!-- Imagen del producto -->
                <div class="position-relative overflow-hidden" style="height:180px; flex-shrink:0;">
                    <div style="
                        width:100%; 
                        height:100%; 
                        background-image: url('<?= $producto->getImageUrl() ?>');
                        background-size: contain;
                        background-position: center;
                        background-repeat: no-repeat;
                        background-color: #fdf8f8;
                    "></div>

                    <!-- Badge variantes -->
                    <?php if ($producto->tieneVariantes()): ?>
                    <span class="position-absolute top-0 start-0 m-2 badge"
                        style="background:#de777d;">
                        <i class="fas fa-layer-group me-1"></i>Variantes
                    </span>
                    <?php endif; ?>

                    <!-- Badge estado -->
                    <span class="position-absolute top-0 end-0 m-2 badge <?= $producto->isActivo() ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $producto->isActivo() ? 'Activo' : 'Inactivo' ?>
                    </span>
                </div>

                <div class="card-body d-flex flex-column">
                    <!-- Categoría -->
                    <small class="text-muted mb-1">
                        <i class="fas fa-tag me-1" style="color:#de777d;"></i>
                        <?= htmlspecialchars($producto->categoria_nombre) ?>
                    </small>

                    <!-- Nombre -->
                    <h6 class="card-title fw-bold mb-2">
                        <?= htmlspecialchars($producto->nombre) ?>
                    </h6>

                    <!-- Precio y stock -->
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <span class="fw-bold" style="color:#de777d; font-size:1.1rem;">
                            <?= $producto->getPrecioFormateado() ?>
                        </span>
                        <?php if (!$producto->tieneVariantes()): ?>
                        <span class="badge <?= $producto->stock > 0 ? 'bg-success' : 'bg-danger' ?>">
                            <i class="fas fa-cubes me-1"></i><?= $producto->stock ?> uds.
                        </span>
                        <?php else: ?>
                        <span class="badge bg-info text-dark">
                            <i class="fas fa-layer-group me-1"></i>Ver variantes
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="card-footer d-flex gap-2 justify-content-between align-items-center">
                    <!-- Toggle activo -->
                    <?php if (Auth::can('productos.editar')): ?>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input toggle-activo"
                               type="checkbox"
                               role="switch"
                               id="toggle-<?= $producto->id ?>"
                               data-id="<?= $producto->id ?>"
                               data-url="<?= APP_URL ?>Productos/toggle"
                               data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                               <?= $producto->isActivo() ? 'checked' : '' ?>>
                        <label class="form-check-label" for="toggle-<?= $producto->id ?>"></label>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <!-- Editar -->
                        <?php if (Auth::can('productos.editar')): ?>
                        <a href="<?= APP_URL ?>Productos/registry/<?= $producto->id ?>"
                           class="btn btn-sm btn-outline-primary"
                           title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php endif; ?>

                        <!-- Eliminar -->
                        <?php if (Auth::can('productos.eliminar')): ?>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger btn-delete"
                                data-id="<?= $producto->id ?>"
                                data-nombre="<?= htmlspecialchars($producto->nombre) ?>"
                                data-url="<?= APP_URL ?>Productos/delete"
                                data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>

</div>

<!-- ─────────────────────────────────────────────
     JAVASCRIPT
     ───────────────────────────────────────────── -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Filtros en tiempo real ───────────────────
    const buscar    = document.getElementById('buscarProducto');
    const filtroCat = document.getElementById('filtroCategoria');
    const filtroEst = document.getElementById('filtroEstado');
    const contador  = document.getElementById('contadorVisible');
    const cards     = document.querySelectorAll('.producto-card');

    function filtrar() {
        const texto    = buscar.value.toLowerCase();
        const categoria = filtroCat.value;
        const estado    = filtroEst.value;
        let visible     = 0;

        cards.forEach(card => {
            const nombre    = card.dataset.nombre;
            const cat       = card.dataset.categoria;
            const activo    = card.dataset.activo;

            const okNombre   = nombre.includes(texto);
            const okCat      = !categoria || cat === categoria;
            const okEstado   = !estado || activo === estado;

            if (okNombre && okCat && okEstado) {
                card.style.display = '';
                visible++;
            } else {
                card.style.display = 'none';
            }
        });

        contador.textContent = `Mostrando ${visible}`;
    }

    buscar.addEventListener('input', filtrar);
    filtroCat.addEventListener('change', filtrar);
    filtroEst.addEventListener('change', filtrar);

    // ── Toggle activo ────────────────────────────
    document.querySelectorAll('input.toggle-activo[type="checkbox"]').forEach(function (toggle) {
        toggle.addEventListener('change', function (e) {
            e.stopPropagation();

            const id     = this.dataset.id;
            const url    = this.dataset.url;
            const csrf   = this.dataset.csrf;
            const activo = this.checked ? 1 : 0;
            const self   = this;
            const card   = this.closest('.producto-card');

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&activo=${activo}&csrf_token=${csrf}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Actualizar badge de estado en la card
                    const badge = card.querySelector('.badge.bg-success, .badge.bg-secondary');
                    if (badge) {
                        badge.className = activo
                            ? 'position-absolute top-0 end-0 m-2 badge bg-success'
                            : 'position-absolute top-0 end-0 m-2 badge bg-secondary';
                        badge.textContent = activo ? 'Activo' : 'Inactivo';
                    }
                    // Opacidad de la card
                    const cardEl = card.querySelector('.card');
                    if (cardEl) {
                        cardEl.classList.toggle('opacity-50', activo === 0);
                    }
                    // Actualizar data-activo para el filtro
                    card.dataset.activo = activo;

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                    Toast.fire({
                        icon: 'success',
                        title: activo ? 'Producto activado' : 'Producto desactivado'
                    });
                } else {
                    self.checked = !self.checked;
                    Swal.fire({
                        icon: 'warning',
                        title: 'No permitido',
                        text: data.message ?? 'No se pudo cambiar el estado.',
                        confirmButtonColor: '#de777d'
                    });
                }
            })
            .catch(() => { self.checked = !self.checked; });
        });
    });

    // ── Eliminar ─────────────────────────────────
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id     = this.dataset.id;
            const nombre = this.dataset.nombre;
            const url    = this.dataset.url;
            const csrf   = this.dataset.csrf;

            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar producto?',
                text: `"${nombre}" será desactivado.`,
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = `
                        <input type="hidden" name="id" value="${id}">
                        <input type="hidden" name="csrf_token" value="${csrf}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

});
</script>