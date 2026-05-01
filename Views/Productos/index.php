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
        <a href="<?= APP_URL ?>Productos/registry" class="btn btn-primary" id="tour-btn-nuevo-prod">
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
                <!-- Búsqueda -->
                <div class="col-12 col-md-3">
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
                <!-- Categoría -->
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroCategoria">
                        <option value="">Todas las categorías</option>
                        <?php
                        $cats = array_unique(array_filter(array_map(fn($p) => $p->categoria_nombre, $productos)));
                        sort($cats);
                        foreach ($cats as $cat):
                        ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Estado activo -->
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroEstado">
                        <option value="">Activos e inactivos</option>
                        <option value="1">Solo activos</option>
                        <option value="0">Solo inactivos</option>
                    </select>
                </div>
                <!-- Imagen -->
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroImagen">
                        <option value="">Con y sin imagen</option>
                        <option value="1">Con imagen</option>
                        <option value="0">Sin imagen</option>
                    </select>
                </div>
                <!-- Visible tienda -->
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroVisible">
                        <option value="">Visibilidad tienda</option>
                        <option value="1">Visibles en tienda</option>
                        <option value="0">Ocultos en tienda</option>
                    </select>
                </div>
                <!-- Vista + contador -->
                <div class="col-12 col-md-1 d-flex align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnVistaTarjeta" title="Vista tarjeta">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnVistaLista" title="Vista lista">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
                <div class="col-12 text-end">
                    <small class="text-muted" id="contadorVisible">
                        Mostrando <?= count($productos) ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- ─────────────────────────────────────────────
         PRODUCTOS
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

    <div id="tour-tabla-productos">

    <!-- ── VISTA TARJETA ── -->
    <div class="row g-3" id="gridProductos">
        <?php $primerToggle = true; foreach ($productos as $producto): ?>
        <div class="col-12 col-sm-6 col-md-4 col-xl-3 producto-card"
             data-nombre="<?= strtolower(htmlspecialchars($producto->nombre)) ?>"
             data-categoria="<?= htmlspecialchars($producto->categoria_nombre ?? '') ?>"
             data-activo="<?= $producto->activo ?>"
             data-imagen="<?= empty($producto->image_url) ? '0' : '1' ?>"
             data-visible="<?= $producto->visible_tienda ?>">
            <div class="card h-100 <?= !$producto->isActivo() ? 'opacity-50' : '' ?>">

                <div class="position-relative overflow-hidden" style="height:180px; flex-shrink:0;">
                    <div style="
                        width:100%; height:100%;
                        background-image: url('<?= $producto->getImageUrl() ?>');
                        background-size: contain;
                        background-position: center;
                        background-repeat: no-repeat;
                        background-color: #fdf8f8;">
                    </div>
                    <?php if ($producto->tieneVariantes()): ?>
                    <span class="position-absolute top-0 start-0 m-2 badge" style="background:#de777d;">
                        <i class="fas fa-layer-group me-1"></i>Variantes
                    </span>
                    <?php endif; ?>
                    <span class="position-absolute top-0 end-0 m-2 badge <?= $producto->isActivo() ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $producto->isActivo() ? 'Activo' : 'Inactivo' ?>
                    </span>
                    <?php if (!$producto->isVisibleTienda()): ?>
                    <span class="position-absolute bottom-0 start-0 m-2 badge bg-warning text-dark">
                        <i class="fas fa-eye-slash me-1"></i>Oculto tienda
                    </span>
                    <?php endif; ?>
                </div>

                <div class="card-body d-flex flex-column">
                    <small class="text-muted mb-1">
                        <i class="fas fa-tag me-1" style="color:#de777d;"></i>
                        <?= htmlspecialchars($producto->categoria_nombre ?? '—') ?>
                    </small>
                    <h6 class="card-title fw-bold mb-2"><?= htmlspecialchars($producto->nombre) ?></h6>
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

                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <!-- Toggle activo -->
                        <?php if (Auth::can('productos.editar')): ?>
                        <div class="form-check form-switch mb-0" <?= $primerToggle ? 'id="tour-toggle-activo"' : '' ?>>
                            <?php $primerToggle = false; ?>
                            <input class="form-check-input toggle-activo"
                                   type="checkbox" role="switch"
                                   id="toggle-<?= $producto->id ?>"
                                   data-id="<?= $producto->id ?>"
                                   data-url="<?= APP_URL ?>Productos/toggle"
                                   data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                   <?= $producto->isActivo() ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="toggle-<?= $producto->id ?>">Activo</label>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex gap-2">
                            <?php if (Auth::can('productos.editar')): ?>
                            <a href="<?= APP_URL ?>Productos/registry/<?= $producto->id ?>"
                               class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
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

                    <!-- Toggle visible tienda -->
                    <?php if (Auth::can('productos.editar')): ?>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input toggle-visible"
                               type="checkbox" role="switch"
                               id="visible-<?= $producto->id ?>"
                               data-id="<?= $producto->id ?>"
                               data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                               <?= $producto->isVisibleTienda() ? 'checked' : '' ?>>
                        <label class="form-check-label small text-muted" for="visible-<?= $producto->id ?>">
                            <i class="fas fa-store me-1"></i>Visible en tienda
                        </label>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── VISTA LISTA ── -->
    <div class="card d-none" id="tablaProductos">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(222,119,125,0.08);">
                            <th class="ps-3">Producto</th>
                            <th>Categoría</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Activo</th>
                            <th class="text-center">Tienda</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                        <tr class="producto-fila"
                            data-nombre="<?= strtolower(htmlspecialchars($producto->nombre)) ?>"
                            data-categoria="<?= htmlspecialchars($producto->categoria_nombre ?? '') ?>"
                            data-activo="<?= $producto->activo ?>"
                            data-imagen="<?= empty($producto->image_url) ? '0' : '1' ?>"
                            data-visible="<?= $producto->visible_tienda ?>">
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= $producto->getImageUrl() ?>"
                                         style="width:40px;height:40px;object-fit:contain;border-radius:6px;background:#fdf8f8;">
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($producto->nombre) ?></div>
                                        <?php if ($producto->tieneVariantes()): ?>
                                        <small class="badge" style="background:#de777d;font-size:0.65rem;">Variantes</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($producto->categoria_nombre ?? '—') ?></td>
                            <td class="text-end fw-bold" style="color:#de777d;"><?= $producto->getPrecioFormateado() ?></td>
                            <td class="text-center">
                                <?php if (!$producto->tieneVariantes()): ?>
                                <span class="badge <?= $producto->stock > 0 ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $producto->stock ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (Auth::can('productos.editar')): ?>
                                <div class="form-check form-switch d-inline-block mb-0">
                                    <input class="form-check-input toggle-activo"
                                           type="checkbox" role="switch"
                                           data-id="<?= $producto->id ?>"
                                           data-url="<?= APP_URL ?>Productos/toggle"
                                           data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                           <?= $producto->isActivo() ? 'checked' : '' ?>>
                                </div>
                                <?php else: ?>
                                <span class="badge <?= $producto->isActivo() ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $producto->isActivo() ? 'Sí' : 'No' ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (Auth::can('productos.editar')): ?>
                                <div class="form-check form-switch d-inline-block mb-0">
                                    <input class="form-check-input toggle-visible"
                                           type="checkbox" role="switch"
                                           data-id="<?= $producto->id ?>"
                                           data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                           <?= $producto->isVisibleTienda() ? 'checked' : '' ?>>
                                </div>
                                <?php else: ?>
                                <span class="badge <?= $producto->isVisibleTienda() ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= $producto->isVisibleTienda() ? 'Sí' : 'No' ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <?php if (Auth::can('productos.editar')): ?>
                                    <a href="<?= APP_URL ?>Productos/registry/<?= $producto->id ?>"
                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (Auth::can('productos.eliminar')): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-delete"
                                            data-id="<?= $producto->id ?>"
                                            data-nombre="<?= htmlspecialchars($producto->nombre) ?>"
                                            data-url="<?= APP_URL ?>Productos/delete"
                                            data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    </div><!-- /#tour-tabla-productos -->
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const APP_URL = '<?= APP_URL ?>';
    const csrf    = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>';

    // ── Vista tarjeta / lista ────────────────────
    const gridProductos  = document.getElementById('gridProductos');
    const tablaProductos = document.getElementById('tablaProductos');
    const btnTarjeta     = document.getElementById('btnVistaTarjeta');
    const btnLista       = document.getElementById('btnVistaLista');

    const vistaGuardada = localStorage.getItem('productos_vista') || 'tarjeta';
    aplicarVista(vistaGuardada);

    btnTarjeta?.addEventListener('click', () => { aplicarVista('tarjeta'); localStorage.setItem('productos_vista','tarjeta'); });
    btnLista?.addEventListener('click',   () => { aplicarVista('lista');   localStorage.setItem('productos_vista','lista');   });

    function aplicarVista(vista) {
        if (vista === 'lista') {
            gridProductos?.classList.add('d-none');
            tablaProductos?.classList.remove('d-none');
            btnLista?.classList.replace('btn-outline-secondary','btn-secondary');
            btnTarjeta?.classList.replace('btn-secondary','btn-outline-secondary');
        } else {
            gridProductos?.classList.remove('d-none');
            tablaProductos?.classList.add('d-none');
            btnTarjeta?.classList.replace('btn-outline-secondary','btn-secondary');
            btnLista?.classList.replace('btn-secondary','btn-outline-secondary');
        }
    }

    // ── Filtros ──────────────────────────────────
    const buscar       = document.getElementById('buscarProducto');
    const filtroCat    = document.getElementById('filtroCategoria');
    const filtroEst    = document.getElementById('filtroEstado');
    const filtroImg    = document.getElementById('filtroImagen');
    const filtroVis    = document.getElementById('filtroVisible');
    const contador     = document.getElementById('contadorVisible');

    function getElementos() {
        return [
            ...document.querySelectorAll('.producto-card'),
            ...document.querySelectorAll('.producto-fila')
        ];
    }

    function filtrar() {
        const texto    = buscar.value.toLowerCase();
        const cat      = filtroCat.value;
        const estado   = filtroEst.value;
        const imagen   = filtroImg.value;
        const visible  = filtroVis.value;

        const cards  = document.querySelectorAll('.producto-card');
        const filas  = document.querySelectorAll('.producto-fila');
        let visibles = 0;

        function aplicar(el) {
            const ok =
                (!texto   || el.dataset.nombre.includes(texto)) &&
                (!cat     || el.dataset.categoria === cat) &&
                (!estado  || el.dataset.activo  === estado) &&
                (!imagen  || el.dataset.imagen   === imagen) &&
                (!visible || el.dataset.visible  === visible);

            el.style.display = ok ? '' : 'none';
            if (ok) visibles++;
        }

        cards.forEach(aplicar);
        filas.forEach(el => { aplicar(el); if (el.style.display !== 'none') visibles--; }); // evitar doble conteo
        visibles = 0;
        cards.forEach(el => { if (el.style.display !== 'none') visibles++; });
        contador.textContent = `Mostrando ${visibles}`;
    }

    buscar.addEventListener('input',    filtrar);
    filtroCat.addEventListener('change',filtrar);
    filtroEst.addEventListener('change',filtrar);
    filtroImg.addEventListener('change',filtrar);
    filtroVis.addEventListener('change',filtrar);

    // ── Toggle activo ────────────────────────────
    document.querySelectorAll('.toggle-activo').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const id     = this.dataset.id;
            const url    = this.dataset.url;
            const csrf   = this.dataset.csrf;
            const activo = this.checked ? 1 : 0;
            const self   = this;
            const card   = this.closest('.producto-card') ?? this.closest('tr');

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&activo=${activo}&csrf_token=${csrf}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (card) {
                        card.dataset.activo = activo;
                        const cardEl = card.querySelector('.card');
                        cardEl?.classList.toggle('opacity-50', activo === 0);
                        const badge = card.querySelector('.badge.bg-success, .badge.bg-secondary');
                        if (badge) {
                            badge.className = activo
                                ? 'position-absolute top-0 end-0 m-2 badge bg-success'
                                : 'position-absolute top-0 end-0 m-2 badge bg-secondary';
                            badge.textContent = activo ? 'Activo' : 'Inactivo';
                        }
                    }
                    Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:2000 })
                        .fire({ icon:'success', title: activo ? 'Producto activado' : 'Producto desactivado' });
                } else {
                    self.checked = !self.checked;
                    Swal.fire({ icon:'warning', title:'No permitido', text: data.message ?? 'Error', confirmButtonColor:'#de777d' });
                }
            })
            .catch(() => { self.checked = !self.checked; });
        });
    });

    // ── Toggle visible tienda ────────────────────
    document.querySelectorAll('.toggle-visible').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const id      = this.dataset.id;
            const csrf    = this.dataset.csrf;
            const visible = this.checked ? 1 : 0;
            const self    = this;
            const card    = this.closest('.producto-card') ?? this.closest('tr');

            fetch(`${APP_URL}Productos/toggleVisible`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&visible=${visible}&csrf_token=${csrf}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (card) card.dataset.visible = visible;
                    Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:2000 })
                        .fire({ icon:'success', title: visible ? 'Visible en tienda' : 'Oculto en tienda' });
                } else {
                    self.checked = !self.checked;
                    Swal.fire({ icon:'error', title:'Error', text:'No se pudo actualizar', confirmButtonColor:'#de777d' });
                }
            })
            .catch(() => { self.checked = !self.checked; });
        });
    });

    // ── Eliminar ─────────────────────────────────
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id    = this.dataset.id;
            const nombre = this.dataset.nombre;
            const url   = this.dataset.url;
            const csrf  = this.dataset.csrf;

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