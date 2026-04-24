<div class="container-fluid py-3 cash-register">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-cash-register me-2" style="color:#de777d;"></i>
                Caja / Punto de Venta
            </h4>
            <small class="text-muted">
                <i class="fas fa-user me-1"></i><?= htmlspecialchars(Auth::get('nombre')) ?>
                &nbsp;|&nbsp;
                <i class="fas fa-clock me-1"></i><?= date('d/m/Y H:i') ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Ventas/index" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-history me-1"></i>Historial
        </a>
    </div>

    <div class="row g-3">

        <!-- ═══════════════════════════════════════════
             COLUMNA IZQUIERDA — Productos
             ═══════════════════════════════════════════ -->
        <div class="col-12 col-lg-7" id="tour-grid-caja">

            <!-- Barra de búsqueda y controles -->
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="row g-2 align-items-center">

                        <!-- Buscador -->
                        <div class="col-12 col-md-5" id="tour-buscador-caja">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text"
                                       class="form-control border-start-0"
                                       id="buscarProducto"
                                       placeholder="Buscar producto o escanear código...">
                            </div>
                        </div>

                        <!-- Filtro categoría -->
                        <div class="col-6 col-md-3">
                            <select class="form-select form-select-sm" id="filtroCategoria">
                                <option value="">Todas las categorías</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat->nombre) ?>">
                                    <?= htmlspecialchars($cat->nombre) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Toggle vista -->
                        <div class="col-6 col-md-4 d-flex justify-content-end gap-2">
                            <div class="btn-group" role="group">
                                <button type="button"
                                        class="btn btn-sm btn-primary active"
                                        id="btnCards"
                                        title="Vista cards">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        id="btnLista"
                                        title="Vista lista">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                            <small class="text-muted align-self-center" id="contadorProductos">
                                <?= count($productos) ?> productos
                            </small>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ── VISTA CARDS ──────────────────────────── -->
            <div id="vistaCards">
                <?php if (empty($productos)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-boxes fa-3x mb-3 d-block" style="color:#de777d;opacity:0.4;"></i>
                    No hay productos activos.
                </div>
                <?php else: ?>
                <div class="row g-2" id="gridCards">
                    <?php foreach ($productos as $producto): ?>
                    <div class="col-6 col-sm-4 col-xl-3 producto-item"
                         data-nombre="<?= strtolower(htmlspecialchars($producto->nombre)) ?>"
                         data-categoria="<?= htmlspecialchars($producto->categoria_nombre) ?>">
                        <div class="card h-100 producto-card-caja"
                             data-id="<?= $producto->id ?>"
                             data-nombre="<?= htmlspecialchars($producto->nombre) ?>"
                             data-precio="<?= $producto->precio_base ?? 0 ?>"
                             data-tiene-variantes="<?= $producto->tieneVariantes() ? '1' : '0' ?>"
                             data-stock="<?= $producto->stock ?>"
                             style="cursor:pointer; transition: transform 0.15s, box-shadow 0.15s;">

                            <!-- Imagen -->
                            <div style="height:100px; overflow:hidden; border-radius:8px 8px 0 0;">
                                <div style="
                                    width:100%; height:100%;
                                    background-image: url('<?= $producto->getImageUrl() ?>');
                                    background-size: contain;
                                    background-position: center;
                                    background-repeat: no-repeat;
                                    background-color: #fdf8f8;">
                                </div>
                            </div>

                            <div class="card-body p-2">
                                <div class="fw-semibold" style="font-size:0.8rem; line-height:1.2;">
                                    <?= htmlspecialchars($producto->nombre) ?>
                                </div>
                                <div class="mt-1 d-flex justify-content-between align-items-center">
                                    <span style="color:#de777d; font-weight:700; font-size:0.85rem;">
                                        <?php if ($producto->tieneVariantes()): ?>
                                            <small class="text-muted">Ver variantes</small>
                                        <?php else: ?>
                                            L. <?= number_format((float)$producto->precio_base, 2) ?>
                                        <?php endif; ?>
                                    </span>
                                    <?php if (!$producto->tieneVariantes()): ?>
                                    <span class="badge <?= $producto->stock > 0 ? 'bg-success' : 'bg-danger' ?>"
                                          style="font-size:0.65rem;">
                                        <?= $producto->stock ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- ── VISTA LISTA ──────────────────────────── -->
            <div id="vistaLista" style="display:none;">
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0" id="tablaProductos">
                            <thead>
                                <tr style="background:rgba(222,119,125,0.08);">
                                    <th class="ps-3">Producto</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center">Agregar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                <tr class="producto-item"
                                    data-nombre="<?= strtolower(htmlspecialchars($producto->nombre)) ?>"
                                    data-categoria="<?= htmlspecialchars($producto->categoria_nombre) ?>">

                                    <!-- Imagen + nombre -->
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <!-- Imagen miniatura -->
                                            <div style="
                                                width:44px; height:44px; flex-shrink:0;
                                                border-radius:6px;
                                                background-image: url('<?= $producto->getImageUrl() ?>');
                                                background-size: contain;
                                                background-position: center;
                                                background-repeat: no-repeat;
                                                background-color: #fdf8f8;
                                                border: 1px solid #dee2e6;">
                                            </div>
                                            <div>
                                                <div class="fw-semibold" style="font-size:0.88rem;">
                                                    <?= htmlspecialchars($producto->nombre) ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($producto->categoria_nombre) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-end fw-bold" style="color:#de777d;">
                                        <?php if ($producto->tieneVariantes()): ?>
                                            <small class="text-muted">Ver var.</small>
                                        <?php else: ?>
                                            L. <?= number_format((float)$producto->precio_base, 2) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!$producto->tieneVariantes()): ?>
                                        <span class="badge <?= $producto->stock > 0 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $producto->stock ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="badge bg-info text-dark" style="font-size:0.7rem;">Variantes</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-primary producto-card-caja"
                                                data-id="<?= $producto->id ?>"
                                                data-nombre="<?= htmlspecialchars($producto->nombre) ?>"
                                                data-precio="<?= $producto->precio_base ?? 0 ?>"
                                                data-tiene-variantes="<?= $producto->tieneVariantes() ? '1' : '0' ?>"
                                                data-stock="<?= $producto->stock ?>">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- ═══════════════════════════════════════════
             COLUMNA DERECHA — Carrito
             ═══════════════════════════════════════════ -->
        <div class="col-12 col-lg-5" id="tour-carrito-caja">
            <div class="card" style="position:sticky; top:80px;">

                <!-- Header carrito -->
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-shopping-cart me-2"></i>
                        Carrito
                        <span class="badge ms-1" style="background:#de777d;" id="badgeItems">0</span>
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnLimpiarCarrito">
                        <i class="fas fa-trash me-1"></i>Limpiar
                    </button>
                </div>

                <div class="card-body p-0">

                    <!-- Cliente opcional -->
                    <div class="p-3 border-bottom">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent">
                                <i class="fas fa-user text-muted"></i>
                            </span>
                            <input type="text"
                                   class="form-control border-start-0"
                                   id="buscarCliente"
                                   placeholder="Cliente (opcional)..."
                                   autocomplete="off">
                        </div>
                        <div id="resultadosCliente" class="list-group mt-1" style="display:none;"></div>
                        <input type="hidden" id="clienteId" value="">
                        <div id="clienteSeleccionado" style="display:none;" class="mt-1">
                            <span class="badge" style="background:#de777d;">
                                <i class="fas fa-user me-1"></i>
                                <span id="clienteNombre"></span>
                                <button type="button" class="btn-close btn-close-white btn-sm ms-1"
                                        id="btnQuitarCliente" style="font-size:0.6rem;"></button>
                            </span>
                        </div>
                    </div>

                    <!-- Items del carrito -->
                    <div id="listaCarrito" style="max-height:280px; overflow-y:auto;">
                        <div id="carritoVacio" class="text-center py-4 text-muted">
                            <i class="fas fa-shopping-cart fa-2x mb-2 d-block" style="opacity:0.3;"></i>
                            <small>Agrega productos al carrito</small>
                        </div>
                        <table class="table table-sm mb-0 d-none" id="tablaCarrito">
                            <thead>
                                <tr style="background:rgba(222,119,125,0.06);">
                                    <th class="ps-3">Producto</th>
                                    <th class="text-center" style="width:90px;">Cant.</th>
                                    <th class="text-end">Total</th>
                                    <th style="width:30px;"></th>
                                </tr>
                            </thead>
                            <tbody id="bodyCarrito"></tbody>
                        </table>
                    </div>

                    <!-- Totales -->
                    <div class="p-3 border-top border-bottom">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Subtotal (sin ISV)</span>
                            <span id="txtSubtotalSinIsv">L. 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">ISV 15%</span>
                            <span id="txtIsv">L. 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold"
                             style="font-size:1.2rem; border-top:2px solid #de777d; padding-top:0.5rem; margin-top:0.5rem;">
                            <span>Total</span>
                            <span style="color:#de777d;" id="txtTotal">L. 0.00</span>
                        </div>
                    </div>

                    <!-- Método de pago -->
                    <div class="p-3 border-bottom" id="tour-metodo-pago">
                        <label class="form-label fw-semibold mb-2" style="font-size:0.85rem;">
                            Método de pago
                        </label>
                        <div class="d-flex gap-2 mb-2">
                            <button type="button"
                                    class="btn btn-primary btn-sm flex-fill btn-pago active"
                                    data-metodo="Efectivo">
                                <i class="fas fa-money-bill-wave me-1"></i>Efectivo
                            </button>
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm flex-fill btn-pago"
                                    data-metodo="Tarjeta">
                                <i class="fas fa-credit-card me-1"></i>Tarjeta
                            </button>
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm flex-fill btn-pago"
                                    data-metodo="Transferencia">
                                <i class="fas fa-mobile-alt me-1"></i>Transfer.
                            </button>
                        </div>
                        <input type="hidden" id="metodoPago" value="Efectivo">

                        <!-- Efectivo — monto recibido y cambio -->
                        <div id="seccionEfectivo">
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text">L.</span>
                                <input type="number"
                                       class="form-control"
                                       id="montoRecibido"
                                       placeholder="Monto recibido"
                                       min="0"
                                       step="0.01">
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted" style="font-size:0.85rem;">Cambio:</span>
                                <span class="fw-bold text-success" id="txtCambio">L. 0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Nota -->
                    <div class="p-3 border-bottom">
                        <input type="text"
                               class="form-control form-control-sm"
                               id="notaVenta"
                               placeholder="Nota (opcional)..."
                               maxlength="255">
                    </div>

                    <!-- Botón cobrar -->
                    <div class="p-3" id="tour-btn-cobrar">
                        <button type="button"
                                class="btn btn-primary w-100 fw-bold"
                                id="btnCobrar"
                                style="font-size:1.1rem; padding:0.75rem;">
                            <i class="fas fa-check-circle me-2"></i>COBRAR
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- ─────────────────────────────────────────────
     MODAL — Seleccionar variante
     ───────────────────────────────────────────── -->
<div class="modal fade" id="modalVariantes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-layer-group me-2" style="color:#de777d;"></i>
                    Seleccionar variante
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bodyVariantes"></div>
        </div>
    </div>
</div>

<!-- ─────────────────────────────────────────────
     MODAL — Confirmación de cobro
     ───────────────────────────────────────────── -->
<div class="modal fade" id="modalConfirmar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2 text-success"></i>
                    Confirmar venta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bodyConfirmar"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnConfirmarVenta">
                    <i class="fas fa-check me-1"></i>Confirmar y cobrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CSRF y APP_URL ocultos -->
<input type="hidden" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
<input type="hidden" id="appUrl"    value="<?= APP_URL ?>">

<!-- ─────────────────────────────────────────────
     JAVASCRIPT
     ───────────────────────────────────────────── -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const APP_URL   = document.getElementById('appUrl').value;
    const csrfToken = document.getElementById('csrfToken').value;

    // ════════════════════════════════════════════
    // ESTADO DEL CARRITO
    // ════════════════════════════════════════════
    let carrito     = [];
    let vistaActual = 'cards';

    // ════════════════════════════════════════════
    // TOGGLE VISTA CARDS / LISTA
    // ════════════════════════════════════════════
    document.getElementById('btnCards').addEventListener('click', function () {
        document.getElementById('vistaCards').style.display = '';
        document.getElementById('vistaLista').style.display = 'none';
        this.classList.add('active', 'btn-primary');
        this.classList.remove('btn-outline-primary');
        const btnLista = document.getElementById('btnLista');
        btnLista.classList.remove('active', 'btn-primary');
        btnLista.classList.add('btn-outline-primary');
    });

    document.getElementById('btnLista').addEventListener('click', function () {
        document.getElementById('vistaCards').style.display = 'none';
        document.getElementById('vistaLista').style.display = '';
        this.classList.add('active', 'btn-primary');
        this.classList.remove('btn-outline-primary');
        const btnCards = document.getElementById('btnCards');
        btnCards.classList.remove('active', 'btn-primary');
        btnCards.classList.add('btn-outline-primary');
    });

    // ════════════════════════════════════════════
    // FILTROS EN TIEMPO REAL
    // ════════════════════════════════════════════
    const buscar    = document.getElementById('buscarProducto');
    const filtroCat = document.getElementById('filtroCategoria');
    const contador  = document.getElementById('contadorProductos');

    function filtrarProductos() {
        const texto = buscar.value.toLowerCase();
        const cat   = filtroCat.value;
        let visible = 0;

        document.querySelectorAll('.producto-item').forEach(item => {
            const nombre    = item.dataset.nombre    || '';
            const categoria = item.dataset.categoria || '';
            const okNombre  = nombre.includes(texto);
            const okCat     = !cat || categoria === cat;

            if (okNombre && okCat) {
                item.style.display = '';
                visible++;
            } else {
                item.style.display = 'none';
            }
        });

        contador.textContent = `${visible} productos`;
    }

    buscar.addEventListener('input', filtrarProductos);
    filtroCat.addEventListener('change', filtrarProductos);

    // Enter en buscador → buscar por código de barras
    buscar.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const codigo = this.value.trim();
            if (codigo) buscarPorBarras(codigo);
        }
    });

    // ════════════════════════════════════════════
    // BUSCAR POR CÓDIGO DE BARRAS
    // ════════════════════════════════════════════
    function buscarPorBarras(codigo) {
        fetch(`${APP_URL}Caja/barras?codigo=${encodeURIComponent(codigo)}`)
            .then(r => r.json())
            .then(data => {
                if (data.found) {
                    const p = data.producto;
                    agregarAlCarrito({
                        producto_id: p.id,
                        variante_id: p.variante_id || null,
                        nombre:      p.variante_id
                                        ? p.nombre + ' — ' + p.variante_nombre
                                        : p.nombre,
                        precio:      parseFloat(p.precio),
                        stock:       parseInt(p.stock),
                    });
                    buscar.value = '';
                    filtrarProductos();
                } else {
                    Swal.mixin({ toast:true, position:'top-end',
                        showConfirmButton:false, timer:2500 })
                    .fire({ icon:'warning', title:'Código no encontrado' });
                }
            });
    }

    // ════════════════════════════════════════════
    // CLICK EN PRODUCTO (cards y lista)
    // ════════════════════════════════════════════
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.producto-card-caja');
        if (!btn) return;

        const id             = btn.dataset.id;
        const nombre         = btn.dataset.nombre;
        const precio         = parseFloat(btn.dataset.precio) || 0;
        const tieneVariantes = btn.dataset.tieneVariantes === '1';
        const stock          = parseInt(btn.dataset.stock)  || 0;

        if (tieneVariantes) {
            mostrarModalVariantes(id, nombre);
        } else {
            if (stock <= 0) {
                Swal.fire({ icon:'warning', title:'Sin stock',
                    text:`"${nombre}" no tiene stock disponible.`,
                    confirmButtonColor:'#de777d' });
                return;
            }
            agregarAlCarrito({ producto_id:id, variante_id:null, nombre, precio, stock });
        }
    });

    // ════════════════════════════════════════════
    // MODAL VARIANTES
    // ════════════════════════════════════════════
    function mostrarModalVariantes(productoId, nombreProducto) {
        const body = document.getElementById('bodyVariantes');
        body.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin fa-2x" style="color:#de777d;"></i></div>';

        const modal = new bootstrap.Modal(document.getElementById('modalVariantes'));
        modal.show();

        fetch(`${APP_URL}Caja/buscar?q=${encodeURIComponent(nombreProducto)}`)
            .then(r => r.json())
            .then(data => {
                const producto = data.find(p => p.id == productoId);
                if (!producto || !producto.variantes.length) {
                    body.innerHTML = '<p class="text-muted text-center">No hay variantes disponibles.</p>';
                    return;
                }
                let html = `<p class="fw-semibold mb-3">${nombreProducto}</p><div class="row g-2">`;
                producto.variantes.forEach(v => {
                    const sinStock = v.stock <= 0;
                    html += `
                        <div class="col-6">
                            <button type="button"
                                    class="btn w-100 ${sinStock ? 'btn-outline-secondary' : 'btn-outline-primary'} btn-variante-select"
                                    data-producto-id="${productoId}"
                                    data-variante-id="${v.id}"
                                    data-nombre="${nombreProducto} — ${v.nombre}"
                                    data-precio="${v.precio}"
                                    data-stock="${v.stock}"
                                    ${sinStock ? 'disabled' : ''}>
                                <div class="fw-semibold">${v.nombre}</div>
                                <div style="color:#de777d; font-size:0.85rem;">L. ${parseFloat(v.precio).toFixed(2)}</div>
                                <div class="badge ${sinStock ? 'bg-danger' : 'bg-success'}" style="font-size:0.65rem;">
                                    ${sinStock ? 'Agotado' : 'Stock: ' + v.stock}
                                </div>
                            </button>
                        </div>`;
                });
                html += '</div>';
                body.innerHTML = html;
            });
    }

    document.getElementById('modalVariantes').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-variante-select');
        if (!btn) return;
        agregarAlCarrito({
            producto_id: btn.dataset.productoId,
            variante_id: btn.dataset.varianteId,
            nombre:      btn.dataset.nombre,
            precio:      parseFloat(btn.dataset.precio),
            stock:       parseInt(btn.dataset.stock),
        });
        bootstrap.Modal.getInstance(document.getElementById('modalVariantes')).hide();
    });

    // ════════════════════════════════════════════
    // CARRITO
    // ════════════════════════════════════════════
    function agregarAlCarrito(item) {
        const key = item.variante_id ? `v${item.variante_id}` : `p${item.producto_id}`;
        const existente = carrito.find(c => c.key === key);

        if (existente) {
            if (existente.cantidad >= item.stock) {
                Swal.mixin({ toast:true, position:'top-end',
                    showConfirmButton:false, timer:2500 })
                .fire({ icon:'warning', title:`Stock máximo para "${item.nombre}"` });
                return;
            }
            existente.cantidad++;
        } else {
            carrito.push({
                key, producto_id:item.producto_id,
                variante_id: item.variante_id || null,
                nombre:item.nombre, precio:item.precio,
                stock:item.stock, cantidad:1,
            });
        }

        renderCarrito();
        Swal.mixin({ toast:true, position:'bottom-end',
            showConfirmButton:false, timer:1000 })
        .fire({ icon:'success', title:`"${item.nombre}" agregado` });
    }

    function quitarDelCarrito(key) {
        carrito = carrito.filter(c => c.key !== key);
        renderCarrito();
    }

    function actualizarCantidad(key, cantidad) {
        const item = carrito.find(c => c.key === key);
        if (!item) return;
        cantidad = parseInt(cantidad);
        if (isNaN(cantidad) || cantidad < 1) { quitarDelCarrito(key); return; }
        if (cantidad > item.stock) {
            cantidad = item.stock;
            Swal.mixin({ toast:true, position:'top-end',
                showConfirmButton:false, timer:2000 })
            .fire({ icon:'warning', title:'Stock máximo alcanzado' });
        }
        item.cantidad = cantidad;
        renderCarrito();
    }

    function renderCarrito() {
        const body  = document.getElementById('bodyCarrito');
        const tabla = document.getElementById('tablaCarrito');
        const vacio = document.getElementById('carritoVacio');
        const badge = document.getElementById('badgeItems');

        if (carrito.length === 0) {
            tabla.classList.add('d-none');
            vacio.style.display = '';
            badge.textContent = '0';
            actualizarTotales();
            return;
        }

        tabla.classList.remove('d-none');
        vacio.style.display = 'none';
        badge.textContent = carrito.reduce((s, c) => s + c.cantidad, 0);

        body.innerHTML = carrito.map(item => `
            <tr>
                <td class="ps-3" style="font-size:0.82rem;">
                    <div class="fw-semibold">${item.nombre}</div>
                    <small class="text-muted">L. ${item.precio.toFixed(2)} c/u</small>
                </td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm px-1 py-0"
                                onclick="window._cajaCantidad('${item.key}', ${item.cantidad - 1})"
                                style="min-width:24px;">−</button>
                        <input type="number"
                               class="form-control form-control-sm text-center p-0"
                               value="${item.cantidad}" min="1" max="${item.stock}"
                               style="width:40px;"
                               onchange="window._cajaCantidad('${item.key}', this.value)">
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm px-1 py-0"
                                onclick="window._cajaCantidad('${item.key}', ${item.cantidad + 1})"
                                style="min-width:24px;">+</button>
                    </div>
                </td>
                <td class="text-end fw-bold" style="color:#de777d;">
                    L. ${(item.precio * item.cantidad).toFixed(2)}
                </td>
                <td>
                    <button type="button" class="btn btn-sm text-danger p-0"
                            onclick="window._cajaQuitar('${item.key}')">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>`).join('');

        actualizarTotales();
    }

    window._cajaCantidad = actualizarCantidad;
    window._cajaQuitar   = quitarDelCarrito;

    // ════════════════════════════════════════════
    // TOTALES
    // ════════════════════════════════════════════
    function actualizarTotales() {
        const total          = carrito.reduce((s, c) => s + (c.precio * c.cantidad), 0);
        const subtotalSinIsv = total / 1.15;
        const isv            = total - subtotalSinIsv;

        document.getElementById('txtSubtotalSinIsv').textContent = `L. ${subtotalSinIsv.toFixed(2)}`;
        document.getElementById('txtIsv').textContent            = `L. ${isv.toFixed(2)}`;
        document.getElementById('txtTotal').textContent          = `L. ${total.toFixed(2)}`;
        calcularCambio();
    }

    function calcularCambio() {
        const total    = carrito.reduce((s, c) => s + (c.precio * c.cantidad), 0);
        const recibido = parseFloat(document.getElementById('montoRecibido').value) || 0;
        const cambio   = recibido - total;
        const txt      = document.getElementById('txtCambio');
        txt.textContent = `L. ${Math.max(cambio, 0).toFixed(2)}`;
        txt.style.color = cambio >= 0 ? '#28a745' : '#dc3545';
    }

    document.getElementById('montoRecibido').addEventListener('input', calcularCambio);

    // ════════════════════════════════════════════
    // MÉTODO DE PAGO
    // ════════════════════════════════════════════
    document.querySelectorAll('.btn-pago').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.btn-pago').forEach(b => {
                b.classList.remove('active', 'btn-primary');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.add('active', 'btn-primary');
            this.classList.remove('btn-outline-secondary');
            document.getElementById('metodoPago').value = this.dataset.metodo;
            document.getElementById('seccionEfectivo').style.display =
                this.dataset.metodo === 'Efectivo' ? '' : 'none';
        });
    });

    // ════════════════════════════════════════════
    // LIMPIAR CARRITO
    // ════════════════════════════════════════════
    document.getElementById('btnLimpiarCarrito').addEventListener('click', function () {
        if (!carrito.length) return;
        Swal.fire({
            icon:'warning', title:'¿Limpiar carrito?',
            text:'Se eliminarán todos los productos.',
            showCancelButton:true, confirmButtonColor:'#dc3545',
            confirmButtonText:'Sí, limpiar', cancelButtonText:'Cancelar'
        }).then(r => { if (r.isConfirmed) { carrito = []; renderCarrito(); } });
    });

    // ════════════════════════════════════════════
    // BUSCAR CLIENTE
    // ════════════════════════════════════════════
    let clienteTimer = null;
    document.getElementById('buscarCliente').addEventListener('input', function () {
        clearTimeout(clienteTimer);
        const q = this.value.trim();
        if (q.length < 2) { document.getElementById('resultadosCliente').style.display = 'none'; return; }
        clienteTimer = setTimeout(() => {
            fetch(`${APP_URL}Clientes/search?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    const lista = document.getElementById('resultadosCliente');
                    if (!data.length) { lista.style.display = 'none'; return; }
                    lista.innerHTML = data.map(c => `
                        <button type="button"
                                class="list-group-item list-group-item-action py-1 btn-cliente"
                                data-id="${c.id}" data-nombre="${c.nombre}">
                            <i class="fas fa-user me-2 text-muted"></i>
                            <strong>${c.nombre}</strong>
                            <small class="text-muted ms-2">${c.telefono || c.email || ''}</small>
                        </button>`).join('');
                    lista.style.display = '';
                });
        }, 300);
    });

    document.getElementById('resultadosCliente').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-cliente');
        if (!btn) return;
        document.getElementById('clienteId').value            = btn.dataset.id;
        document.getElementById('clienteNombre').textContent  = btn.dataset.nombre;
        document.getElementById('clienteSeleccionado').style.display = '';
        document.getElementById('buscarCliente').value        = '';
        this.style.display = 'none';
    });

    document.getElementById('btnQuitarCliente').addEventListener('click', function () {
        document.getElementById('clienteId').value = '';
        document.getElementById('clienteSeleccionado').style.display = 'none';
        document.getElementById('buscarCliente').value = '';
    });

    // ════════════════════════════════════════════
    // COBRAR
    // ════════════════════════════════════════════
    document.getElementById('btnCobrar').addEventListener('click', function () {
        if (!carrito.length) {
            Swal.fire({ icon:'warning', title:'Carrito vacío',
                text:'Agrega productos antes de cobrar.',
                confirmButtonColor:'#de777d' });
            return;
        }

        const metodo   = document.getElementById('metodoPago').value;
        const recibido = parseFloat(document.getElementById('montoRecibido').value) || 0;
        const total    = carrito.reduce((s, c) => s + (c.precio * c.cantidad), 0);
        const isv      = total - (total / 1.15);
        const cambio   = recibido - total;

        if (metodo === 'Efectivo' && recibido < total) {
            Swal.fire({ icon:'warning', title:'Monto insuficiente',
                text:'El monto recibido es menor al total.',
                confirmButtonColor:'#de777d' });
            return;
        }

        document.getElementById('bodyConfirmar').innerHTML = `
            <div class="table-responsive">
                <table class="table table-sm mb-3">
                    <tbody>
                        ${carrito.map(c => `
                        <tr>
                            <td class="text-start">${c.nombre}</td>
                            <td class="text-center">x${c.cantidad}</td>
                            <td class="text-end fw-bold">L. ${(c.precio * c.cantidad).toFixed(2)}</td>
                        </tr>`).join('')}
                    </tbody>
                </table>
            </div>
            <div class="border-top pt-2">
                <div class="d-flex justify-content-between">
                    <span>Subtotal sin ISV:</span>
                    <strong>L. ${(total/1.15).toFixed(2)}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>ISV 15%:</span>
                    <strong>L. ${isv.toFixed(2)}</strong>
                </div>
                <div class="d-flex justify-content-between fs-5">
                    <span>Total:</span>
                    <strong style="color:#de777d;">L. ${total.toFixed(2)}</strong>
                </div>
                ${metodo === 'Efectivo' ? `
                <div class="d-flex justify-content-between">
                    <span>Recibido:</span><strong>L. ${recibido.toFixed(2)}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Cambio:</span>
                    <strong class="text-success">L. ${cambio.toFixed(2)}</strong>
                </div>` : ''}
                <div class="mt-2">
                    <span class="badge" style="background:#de777d; font-size:0.9rem;">
                        <i class="fas fa-credit-card me-1"></i>${metodo}
                    </span>
                </div>
            </div>`;

        new bootstrap.Modal(document.getElementById('modalConfirmar')).show();
    });

    document.getElementById('btnConfirmarVenta').addEventListener('click', function () {
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Procesando...';

        const formData = new FormData();
        formData.append('csrf_token',     csrfToken);
        formData.append('cliente_id',     document.getElementById('clienteId').value);
        formData.append('metodo_pago',    document.getElementById('metodoPago').value);
        formData.append('monto_recibido', document.getElementById('montoRecibido').value);
        formData.append('nota',           document.getElementById('notaVenta').value);
        formData.append('items',          JSON.stringify(carrito));

        fetch(`${APP_URL}Caja/cobrar`, { method:'POST', body:formData })
        .then(r => r.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmar')).hide();

            if (data.success) {
                carrito = [];
                renderCarrito();
                document.getElementById('montoRecibido').value = '';
                document.getElementById('notaVenta').value     = '';
                document.getElementById('clienteId').value     = '';
                document.getElementById('clienteSeleccionado').style.display = 'none';

                Swal.fire({
                    icon:'success', title:'¡Venta registrada!',
                    html:`<div class="fs-4 fw-bold" style="color:#de777d;">
                              Total: L. ${parseFloat(data.total).toFixed(2)}
                          </div>
                          ${data.cambio !== null
                            ? `<div class="text-success">Cambio: L. ${parseFloat(data.cambio).toFixed(2)}</div>`
                            : ''}`,
                    confirmButtonColor:'#de777d',
                    confirmButtonText:'<i class="fas fa-print me-1"></i>Imprimir recibo',
                    showCancelButton:true, cancelButtonText:'Nueva venta'
                }).then(result => {
                    if (result.isConfirmed) {
                        window.open(`${APP_URL}Caja/recibo/${data.venta_id}`, '_blank');
                    }
                });
            } else {
                Swal.fire({ icon:'error', title:'Error',
                    text: data.message || 'No se pudo procesar la venta.',
                    confirmButtonColor:'#de777d' });
            }
        })
        .catch(() => {
            Swal.fire({ icon:'error', title:'Error de conexión',
                text:'No se pudo conectar con el servidor.',
                confirmButtonColor:'#de777d' });
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-check me-1"></i>Confirmar y cobrar';
        });
    });

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (AM_TOUR_COMPLETADO) return;
    if (!document.getElementById('tour-buscador-caja')) return;
    if (typeof window.driver === 'undefined') return;

    const { driver } = window.driver.js ?? window;

    setTimeout(() => {
        const tourCaja = driver({
            showProgress: true,
            popoverClass: 'am-driver-popover',
            nextBtnText:  'Siguiente →',
            prevBtnText:  '← Atrás',
            doneBtnText:  '¡Entendido! ✓',
            steps: [
                {
                    popover: {
                        title: '💰 Caja — Punto de Venta',
                        description: `Hola <strong>${AM_USER_NOMBRE}</strong>, este es el módulo de ventas presenciales. Te explico cómo registrar una venta paso a paso.`
                    }
                },
                {
                    element: '#tour-buscador-caja',
                    popover: {
                        title: '🔍 Buscar producto',
                        description: 'Escribe el nombre del producto para buscarlo en tiempo real, o acerca el escáner de código de barras para agregarlo automáticamente. También puedes filtrar por categoría usando los botones de arriba.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#tour-grid-caja',
                    popover: {
                        title: '🛍️ Catálogo de productos',
                        description: 'Aquí aparecen los productos disponibles con su precio y stock. Haz clic en cualquier producto para agregarlo al carrito. Si tiene variantes (colores, tamaños) el sistema te pedirá que elijas una antes de agregarlo.',
                        side: 'top'
                    }
                },
                {
                    element: '#tour-carrito-caja',
                    popover: {
                        title: '🛒 Carrito de venta',
                        description: 'Aquí aparecen los productos seleccionados. Puedes aumentar o disminuir la cantidad con los botones + y −, eliminar un producto con la X, o aplicar un descuento en porcentaje. El subtotal y total se calculan automáticamente.',
                        side: 'left'
                    }
                },
                {
                    element: '#tour-metodo-pago',
                    popover: {
                        title: '💳 Método de pago',
                        description: 'Selecciona cómo paga el cliente: <strong>Efectivo</strong> (ingresa el monto recibido y el sistema calcula el cambio), <strong>Tarjeta</strong> o <strong>Transferencia</strong>. También puedes buscar y asociar el cliente a la venta.',
                        side: 'top'
                    }
                },
                {
                    element: '#tour-btn-cobrar',
                    popover: {
                        title: '✅ Cobrar y generar recibo',
                        description: 'Cuando el carrito esté listo y el método de pago seleccionado, pulsa Cobrar. El sistema registra la venta en el historial, descuenta el stock de cada producto y abre automáticamente el recibo térmico de 80mm listo para imprimir.',
                        side: 'top'
                    }
                }
            ]
        });

        tourCaja.drive();
    }, 800);
});
</script>