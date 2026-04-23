<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-<?= $combo->Found ? 'edit' : 'plus-circle' ?> me-2"
                   style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= $combo->Found
                    ? 'Modifica el combo y sus productos.'
                    : 'Crea el combo y luego agrega los productos.' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Combos/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row g-4">

        <!-- ─────────────────────────────────────────────
             COLUMNA IZQUIERDA — Datos del combo
             ───────────────────────────────────────────── -->
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-layer-group me-2"></i>Datos del combo
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="<?= APP_URL ?>Combos/save"
                          enctype="multipart/form-data"
                          autocomplete="off">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <?php if ($combo->Found): ?>
                        <input type="hidden" name="id" value="<?= $combo->id ?>">
                        <?php endif; ?>

                        <!-- Imagen preview -->
                        <div class="mb-3">
                            <div style="
                                height:120px;
                                background-image: url('<?= $combo->getImageUrl() ?>');
                                background-size: contain;
                                background-position: center;
                                background-repeat: no-repeat;
                                background-color: #fdf8f8;
                                border-radius:8px;
                                border:2px dashed #dee2e6;"
                                id="previewCombo">
                            </div>
                            <input type="file"
                                   class="form-control form-control-sm mt-2"
                                   id="imagen" name="imagen"
                                   accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">JPG, PNG o WEBP. Máx. 2MB.</small>
                        </div>

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   maxlength="150" placeholder="Ej: Kit Labios..."
                                   value="<?= htmlspecialchars($combo->nombre ?? '') ?>"
                                   required autofocus>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label fw-semibold">
                                Descripción
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <textarea class="form-control" id="descripcion" name="descripcion"
                                      rows="2" placeholder="Describe el combo..."><?= htmlspecialchars($combo->descripcion ?? '') ?></textarea>
                        </div>

                        <!-- Descuento -->
                        <div class="mb-4">
                            <label for="descuento" class="form-label fw-semibold">
                                Descuento (%)
                                <span class="text-muted fw-normal">(vacío = sin descuento)</span>
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="descuento"
                                       name="descuento" min="0" max="100" step="0.01"
                                       placeholder="Ej: 10"
                                       value="<?= $combo->descuento !== null ? $combo->descuento : '' ?>">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $combo->Found ? 'Guardar cambios' : 'Crear combo' ?>
                            </button>
                            <a href="<?= APP_URL ?>Combos/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>

            <?php if ($combo->Found): ?>
            <!-- Resumen de precio -->
            <div class="card mt-3">
                <div class="card-header">
                    <i class="fas fa-calculator me-2"></i>Resumen del combo
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Suma de productos:</span>
                        <span id="txtSuma" class="fw-semibold">L. 0.00</span>
                    </div>
                    <?php if ($combo->tieneDescuento()): ?>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Descuento (<?= $combo->getDescuentoFormateado() ?>):</span>
                        <span id="txtAhorro" class="fw-semibold text-danger">- L. 0.00</span>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between pt-2 border-top">
                        <span class="fw-bold">Precio combo:</span>
                        <span id="txtPrecioCombo" class="fw-bold fs-5" style="color:#de777d;">L. 0.00</span>
                    </div>
                    <!-- Botón guardar lista -->
                    <button type="button" class="btn btn-primary w-100 mt-3" id="btnGuardarProductos">
                        <i class="fas fa-save me-2"></i>Guardar productos del combo
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ─────────────────────────────────────────────
             COLUMNA DERECHA
             ───────────────────────────────────────────── -->
        <div class="col-12 col-lg-8">

            <?php if ($combo->Found): ?>

            <!-- ── Productos en el combo ─────────────── -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-boxes me-2"></i>Productos en el combo
                        <span class="badge ms-1" style="background:#de777d;" id="badgeProductos">
                            <?= count($productosCombo) ?>
                        </span>
                    </span>
                </div>
                <div class="card-body p-0">
                    <div id="listaProductosVacia"
                         class="text-center py-3 text-muted <?= !empty($productosCombo) ? 'd-none' : '' ?>">
                        <small>No hay productos en el combo todavía.</small>
                    </div>
                    <table class="table table-sm align-middle mb-0 <?= empty($productosCombo) ? 'd-none' : '' ?>"
                           id="tablaProductosCombo">
                        <thead>
                            <tr style="background:rgba(222,119,125,0.06);">
                                <th class="ps-3">Producto</th>
                                <th class="text-center" style="width:80px;">Cant.</th>
                                <th class="text-end">P. Unit.</th>
                                <th class="text-end pe-3">Subtotal</th>
                                <th style="width:36px;"></th>
                            </tr>
                        </thead>
                        <tbody id="bodyProductosCombo">
                        <?php foreach ($productosCombo as $p): ?>
                        <tr data-producto-id="<?= $p['producto_id'] ?>"
                            data-variante-id="<?= $p['variante_id'] ?? '' ?>"
                            data-precio="<?= $p['precio_unitario'] ?>">
                            <td class="ps-3">
                                <div class="fw-semibold" style="font-size:0.85rem;">
                                    <?= htmlspecialchars($p['producto_nombre']) ?>
                                </div>
                                <?php if ($p['variante_nombre']): ?>
                                <small class="text-muted"><?= htmlspecialchars($p['variante_nombre']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <input type="number"
                                       class="form-control form-control-sm text-center input-cantidad-combo"
                                       value="<?= $p['cantidad'] ?>" min="1"
                                       style="width:52px; margin:auto;">
                            </td>
                            <td class="text-end text-muted" style="font-size:0.85rem;">
                                L. <?= number_format((float)$p['precio_unitario'], 2) ?>
                            </td>
                            <td class="text-end fw-bold pe-3" style="color:#de777d; font-size:0.85rem;">
                                L. <?= number_format((float)$p['precio_unitario'] * $p['cantidad'], 2) ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm text-danger p-0 btn-quitar-producto">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── Buscador de productos ──────────────── -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-search me-2"></i>Agregar productos
                </div>
                <div class="card-body pb-2">
                    <!-- Buscador + toggle vista -->
                    <div class="d-flex gap-2 align-items-center mb-3">
                        <div class="input-group flex-fill">
                            <span class="input-group-text bg-transparent">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text"
                                   class="form-control border-start-0"
                                   id="buscarProductoCombo"
                                   placeholder="Buscar producto por nombre...">
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-primary active" id="btnCards">
                                <i class="fas fa-th"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnLista">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                        <small class="text-muted text-nowrap" id="contadorProductos">
                            <?= count($productos) ?> productos
                        </small>
                    </div>

                    <!-- ── VISTA CARDS ──────────────────── -->
                    <div id="vistaCards" style="max-height:380px; overflow-y:auto;">
                        <div class="row g-2" id="gridCardsCombo">
                            <?php foreach ($productos as $producto): ?>
                            <div class="col-6 col-sm-4 col-xl-3 producto-combo-item"
                                 data-nombre="<?= strtolower(htmlspecialchars($producto->nombre)) ?>">
                                <div class="card h-100 producto-combo-card"
                                     style="cursor:pointer;"
                                     data-id="<?= $producto->id ?>"
                                     data-nombre="<?= htmlspecialchars($producto->nombre) ?>"
                                     data-precio="<?= $producto->precio_base ?? 0 ?>"
                                     data-variantes="<?= $producto->tieneVariantes() ? '1' : '0' ?>">
                                    <!-- Imagen -->
                                    <div style="
                                        height:80px;
                                        background-image: url('<?= $producto->getImageUrl() ?>');
                                        background-size: contain;
                                        background-position: center;
                                        background-repeat: no-repeat;
                                        background-color: #fdf8f8;
                                        border-radius:6px 6px 0 0;">
                                    </div>
                                    <div class="card-body p-2 text-center">
                                        <div style="font-size:0.75rem; font-weight:600; line-height:1.2;">
                                            <?= htmlspecialchars($producto->nombre) ?>
                                        </div>
                                        <div style="color:#de777d; font-size:0.8rem; font-weight:700;">
                                            <?php if ($producto->tieneVariantes()): ?>
                                            <small class="text-muted">Ver var.</small>
                                            <?php else: ?>
                                            L. <?= number_format((float)$producto->precio_base, 2) ?>
                                            <?php endif; ?>
                                        </div>
                                        <button type="button"
                                                class="btn btn-sm btn-primary w-100 mt-1"
                                                style="font-size:0.72rem; padding:2px 4px;">
                                            <i class="fas fa-plus me-1"></i>Agregar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- ── VISTA LISTA ──────────────────── -->
                    <div id="vistaLista" style="display:none; max-height:380px; overflow-y:auto;">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead>
                                <tr style="background:rgba(222,119,125,0.08);">
                                    <th class="ps-2">Producto</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-center" style="width:50px;">Agregar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                <tr class="producto-combo-item"
                                    data-nombre="<?= strtolower(htmlspecialchars($producto->nombre)) ?>">
                                    <td class="ps-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="
                                                width:36px; height:36px; flex-shrink:0;
                                                border-radius:4px;
                                                background-image: url('<?= $producto->getImageUrl() ?>');
                                                background-size: contain;
                                                background-position: center;
                                                background-repeat: no-repeat;
                                                background-color: #fdf8f8;
                                                border:1px solid #dee2e6;">
                                            </div>
                                            <div>
                                                <div class="fw-semibold" style="font-size:0.85rem;">
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
                                        <small class="text-muted">Variantes</small>
                                        <?php else: ?>
                                        L. <?= number_format((float)$producto->precio_base, 2) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-primary producto-combo-card"
                                                data-id="<?= $producto->id ?>"
                                                data-nombre="<?= htmlspecialchars($producto->nombre) ?>"
                                                data-precio="<?= $producto->precio_base ?? 0 ?>"
                                                data-variantes="<?= $producto->tieneVariantes() ? '1' : '0' ?>">
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

            <?php else: ?>
            <div class="card border-0" style="background:rgba(222,119,125,0.06);">
                <div class="card-body text-center py-4">
                    <i class="fas fa-info-circle fa-2x mb-3 d-block" style="color:#de777d;"></i>
                    <p class="mb-0 text-muted">
                        Los productos se agregan después de crear el combo.
                    </p>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<input type="hidden" id="csrfToken"      value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
<input type="hidden" id="appUrl"         value="<?= APP_URL ?>">
<input type="hidden" id="comboId"        value="<?= $combo->id ?? '' ?>">
<input type="hidden" id="descuentoCombo" value="<?= $combo->descuento ?? '' ?>">

<script>
document.addEventListener('DOMContentLoaded', function () {

    const APP_URL   = document.getElementById('appUrl').value;
    const csrf      = document.getElementById('csrfToken').value;
    const comboId   = document.getElementById('comboId').value;
    const descuento = parseFloat(document.getElementById('descuentoCombo').value) || 0;

    // ── Preview imagen ────────────────────────────
    document.getElementById('imagen')?.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('previewCombo').style.backgroundImage = `url('${e.target.result}')`;
            };
            reader.readAsDataURL(file);
        }
    });

    // ── Toggle vista cards / lista ────────────────
    document.getElementById('btnCards')?.addEventListener('click', function () {
        document.getElementById('vistaCards').style.display = '';
        document.getElementById('vistaLista').style.display = 'none';
        this.classList.add('active', 'btn-primary');
        this.classList.remove('btn-outline-primary');
        const btnLista = document.getElementById('btnLista');
        btnLista.classList.remove('active', 'btn-primary');
        btnLista.classList.add('btn-outline-primary');
    });

    document.getElementById('btnLista')?.addEventListener('click', function () {
        document.getElementById('vistaCards').style.display = 'none';
        document.getElementById('vistaLista').style.display = '';
        this.classList.add('active', 'btn-primary');
        this.classList.remove('btn-outline-primary');
        const btnCards = document.getElementById('btnCards');
        btnCards.classList.remove('active', 'btn-primary');
        btnCards.classList.add('btn-outline-primary');
    });

    // ── Filtro en tiempo real ─────────────────────
    document.getElementById('buscarProductoCombo')?.addEventListener('input', function () {
        const texto   = this.value.toLowerCase();
        let visible   = 0;

        document.querySelectorAll('.producto-combo-item').forEach(item => {
            const nombre = item.dataset.nombre || '';
            if (nombre.includes(texto)) {
                item.style.display = '';
                visible++;
            } else {
                item.style.display = 'none';
            }
        });

        document.getElementById('contadorProductos').textContent = `${visible} productos`;
    });

    // ── Calcular totales ──────────────────────────
    function calcularTotales() {
        const filas = document.querySelectorAll('#bodyProductosCombo tr');
        let suma = 0;

        filas.forEach(fila => {
            const precio   = parseFloat(fila.dataset.precio) || 0;
            const cantidad = parseInt(fila.querySelector('.input-cantidad-combo')?.value || 1);
            const subtotal = precio * cantidad;
            suma += subtotal;

            const celdas = fila.querySelectorAll('td');
            if (celdas[3]) celdas[3].textContent = `L. ${subtotal.toFixed(2)}`;
        });

        const ahorro      = descuento > 0 ? suma * descuento / 100 : 0;
        const precioCombo = suma - ahorro;

        const txtSuma   = document.getElementById('txtSuma');
        const txtAhorro = document.getElementById('txtAhorro');
        const txtPrecio = document.getElementById('txtPrecioCombo');
        const badge     = document.getElementById('badgeProductos');

        if (txtSuma)   txtSuma.textContent   = `L. ${suma.toFixed(2)}`;
        if (txtAhorro) txtAhorro.textContent = `- L. ${ahorro.toFixed(2)}`;
        if (txtPrecio) txtPrecio.textContent = `L. ${precioCombo.toFixed(2)}`;
        if (badge)     badge.textContent     = filas.length;
    }

    calcularTotales();

    // ── Cambio de cantidad ────────────────────────
    document.getElementById('bodyProductosCombo')?.addEventListener('input', function (e) {
        if (e.target.classList.contains('input-cantidad-combo')) calcularTotales();
    });

    // ── Quitar producto ───────────────────────────
    document.getElementById('bodyProductosCombo')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-quitar-producto');
        if (!btn) return;
        btn.closest('tr').remove();
        actualizarVistaTabla();
        calcularTotales();
    });

    function actualizarVistaTabla() {
        const tabla = document.getElementById('tablaProductosCombo');
        const vacio = document.getElementById('listaProductosVacia');
        const filas = document.querySelectorAll('#bodyProductosCombo tr');
        if (filas.length === 0) {
            tabla.classList.add('d-none');
            vacio.classList.remove('d-none');
        } else {
            tabla.classList.remove('d-none');
            vacio.classList.add('d-none');
        }
    }

    // ── Agregar producto al hacer clic ────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.producto-combo-card');
        if (!btn) return;

        const productoId = btn.dataset.id;
        const nombre     = btn.dataset.nombre;
        const precio     = parseFloat(btn.dataset.precio) || 0;

        // Verificar si ya está en la lista
        const existe = document.querySelector(`#bodyProductosCombo tr[data-producto-id="${productoId}"]`);
        if (existe) {
            Swal.mixin({ toast:true, position:'top-end',
                showConfirmButton:false, timer:2000 })
            .fire({ icon:'info', title:`"${nombre}" ya está en el combo` });
            return;
        }

        const tbody = document.getElementById('bodyProductosCombo');
        const tr    = document.createElement('tr');
        tr.dataset.productoId = productoId;
        tr.dataset.varianteId = '';
        tr.dataset.precio     = precio;
        tr.innerHTML = `
            <td class="ps-3">
                <div class="fw-semibold" style="font-size:0.85rem;">${nombre}</div>
            </td>
            <td class="text-center">
                <input type="number"
                       class="form-control form-control-sm text-center input-cantidad-combo"
                       value="1" min="1" style="width:52px; margin:auto;">
            </td>
            <td class="text-end text-muted" style="font-size:0.85rem;">
                L. ${precio.toFixed(2)}
            </td>
            <td class="text-end fw-bold pe-3" style="color:#de777d; font-size:0.85rem;">
                L. ${precio.toFixed(2)}
            </td>
            <td>
                <button type="button" class="btn btn-sm text-danger p-0 btn-quitar-producto">
                    <i class="fas fa-times"></i>
                </button>
            </td>`;
        tbody.appendChild(tr);
        actualizarVistaTabla();
        calcularTotales();

        Swal.mixin({ toast:true, position:'bottom-end',
            showConfirmButton:false, timer:1000 })
        .fire({ icon:'success', title:`"${nombre}" agregado` });
    });

    // ── Guardar productos ─────────────────────────
    document.getElementById('btnGuardarProductos')?.addEventListener('click', function () {
        const filas = document.querySelectorAll('#bodyProductosCombo tr');
        const items = [];

        filas.forEach(fila => {
            items.push({
                producto_id: fila.dataset.productoId,
                variante_id: fila.dataset.varianteId || null,
                cantidad:    fila.querySelector('.input-cantidad-combo')?.value || 1,
            });
        });

        if (items.length === 0) {
            Swal.fire({ icon:'warning', title:'Sin productos',
                text:'Agrega al menos un producto al combo.',
                confirmButtonColor:'#de777d' });
            return;
        }

        const formData = new FormData();
        formData.append('csrf_token', csrf);
        formData.append('combo_id',   comboId);
        formData.append('items',      JSON.stringify(items));

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

        fetch(`${APP_URL}Combos/saveProductos`, { method:'POST', body:formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.mixin({ toast:true, position:'top-end',
                    showConfirmButton:false, timer:2000 })
                .fire({ icon:'success', title:'Productos guardados correctamente' });
            } else {
                Swal.fire({ icon:'error', title:'Error',
                    text: data.message, confirmButtonColor:'#de777d' });
            }
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-save me-2"></i>Guardar productos del combo';
        });
    });

});
</script>