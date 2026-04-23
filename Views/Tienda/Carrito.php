<div class="container py-5">

    <h3 class="fw-bold mb-4">
        <i class="fas fa-shopping-cart me-2" style="color:#de777d;"></i>Tu carrito
    </h3>

    <!-- Carrito vacío -->
    <div id="carritoVacio" style="display:none;" class="text-center py-5">
        <i class="fas fa-shopping-cart fa-4x mb-4 d-block" style="color:#de777d; opacity:0.3;"></i>
        <h5 class="text-muted">Tu carrito está vacío</h5>
        <a href="<?= APP_URL ?>Tienda/catalogo" class="btn-rosa mt-3 d-inline-block px-4 py-2">
            <i class="fas fa-arrow-left me-2"></i>Ver catálogo
        </a>
    </div>

    <!-- Carrito con productos -->
    <div id="carritoContenido">
        <div class="row g-4">

            <!-- Lista de productos -->
            <div class="col-12 col-lg-7">
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table align-middle mb-0" id="tablaCarrito">
                            <thead>
                                <tr style="background:rgba(222,119,125,0.08);">
                                    <th class="ps-3">Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end pe-3">Subtotal</th>
                                    <th style="width:36px;"></th>
                                </tr>
                            </thead>
                            <tbody id="bodyCarrito"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Resumen y checkout -->
            <div class="col-12 col-lg-5">
                <div class="card">
                    <div class="card-header fw-semibold">
                        <i class="fas fa-receipt me-2"></i>Resumen del pedido
                    </div>
                    <div class="card-body">

                        <!-- Tipo de entrega -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipo de entrega</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn-tipo-entrega activo flex-fill"
                                        data-tipo="Retiro" id="btnRetiro">
                                    <i class="fas fa-store me-1"></i>Retiro
                                </button>
                                <button type="button" class="btn-tipo-entrega flex-fill"
                                        data-tipo="Envio" id="btnEnvio">
                                    <i class="fas fa-truck me-1"></i>Envío
                                </button>
                            </div>
                        </div>

                        <!-- Zona de envío (solo si selecciona envío) -->
                        <div id="seccionEnvio" style="display:none;">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Zona de envío</label>
                                <select class="form-select" id="selectZona">
                                    <option value="">Seleccionar zona...</option>
                                    <?php foreach ($zonas as $zona): ?>
                                    <option value="<?= $zona['id'] ?>" data-costo="<?= $zona['costo'] ?>">
                                        <?= htmlspecialchars($zona['nombre']) ?>
                                        — <?= (float)$zona['costo'] === 0.0 ? 'Gratis' : 'L. ' . number_format((float)$zona['costo'], 2) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Dirección de entrega</label>
                                <textarea class="form-control" id="inputDireccion" rows="2"
                                          placeholder="Colonia, calle, referencia..."></textarea>
                            </div>
                        </div>

                        <!-- WhatsApp -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Tu WhatsApp
                                <span class="text-muted fw-normal">(para notificaciones)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fab fa-whatsapp text-success"></i>
                                </span>
                                <input type="text" class="form-control" id="inputWa"
                                       placeholder="9999-9999"
                                       value="<?= htmlspecialchars($_SESSION['cliente']['telefono'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Nota -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nota <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <textarea class="form-control" id="inputNota" rows="2"
                                      placeholder="Instrucciones especiales..."></textarea>
                        </div>

                        <!-- Totales -->
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Subtotal</span>
                                <span id="resumenSubtotal">L. 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1" id="filaEnvio" style="display:none!important;">
                                <span class="text-muted">Envío</span>
                                <span id="resumenEnvio">L. 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold pt-2 border-top"
                                 style="font-size:1.15rem;">
                                <span>Total</span>
                                <span style="color:#de777d;" id="resumenTotal">L. 0.00</span>
                            </div>
                        </div>

                        <!-- Botón confirmar -->
                        <button type="button" class="btn-rosa w-100 mt-3" id="btnConfirmarPedido"
                                style="padding:12px; font-size:1rem;">
                            <i class="fas fa-check-circle me-2"></i>Confirmar pedido
                        </button>

                        <a href="<?= APP_URL ?>Tienda/catalogo" class="btn-rosa-outline d-block text-center mt-2">
                            <i class="fas fa-arrow-left me-1"></i>Seguir comprando
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Form oculto para enviar el pedido -->
<form id="formCheckout" method="POST" action="<?= APP_URL ?>Tienda/checkout" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <input type="hidden" name="tipo_entrega"    id="hTipoEntrega"    value="Retiro">
    <input type="hidden" name="zona_id"         id="hZonaId"         value="">
    <input type="hidden" name="direccion_envio" id="hDireccion"      value="">
    <input type="hidden" name="wa_numero"       id="hWa"             value="">
    <input type="hidden" name="nota"            id="hNota"           value="">
    <input type="hidden" name="items"           id="hItems"          value="">
</form>

<style>
.btn-tipo-entrega {
    padding: 8px 12px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}
.btn-tipo-entrega.activo {
    border-color: #de777d;
    background: #de777d;
    color: #fff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let tipoEntrega = 'Retiro';
    let costoEnvio  = 0;

    // ── Renderizar carrito ────────────────────────
    function renderCarrito() {
        const carrito = getCarrito();
        const body    = document.getElementById('bodyCarrito');
        const vacio   = document.getElementById('carritoVacio');
        const cont    = document.getElementById('carritoContenido');

        if (carrito.length === 0) {
            vacio.style.display = '';
            cont.style.display  = 'none';
            return;
        }

        vacio.style.display = 'none';
        cont.style.display  = '';

        body.innerHTML = carrito.map((item, idx) => `
            <tr>
                <td class="ps-3">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:40px; height:40px; flex-shrink:0; border-radius:6px;
                                    background-image:url('${item.imagen}');
                                    background-size:contain; background-position:center;
                                    background-repeat:no-repeat; background-color:#fdf8f8;"></div>
                        <div>
                            <div class="fw-semibold" style="font-size:0.85rem;">${item.nombre}</div>
                            ${item.varianteNombre ? `<small class="text-muted">${item.varianteNombre}</small>` : ''}
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                        <button onclick="cambiarCantidad(${idx}, -1)"
                                style="width:26px; height:26px; border-radius:50%; border:1px solid #de777d;
                                       background:#fff; color:#de777d; cursor:pointer; font-size:0.9rem;">−</button>
                        <span style="min-width:24px; text-align:center; font-weight:600;">${item.cantidad}</span>
                        <button onclick="cambiarCantidad(${idx}, 1)"
                                style="width:26px; height:26px; border-radius:50%; border:1px solid #de777d;
                                       background:#fff; color:#de777d; cursor:pointer; font-size:0.9rem;">+</button>
                    </div>
                </td>
                <td class="text-end text-muted" style="font-size:0.85rem;">
                    L. ${parseFloat(item.precio).toFixed(2)}
                </td>
                <td class="text-end fw-bold" style="color:#de777d; font-size:0.85rem;">
                    L. ${(item.precio * item.cantidad).toFixed(2)}
                </td>
                <td>
                    <button onclick="quitarItem(${idx})"
                            style="background:none; border:none; color:#dc3545; cursor:pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>`).join('');

        actualizarTotales();
    }

    window.cambiarCantidad = function(idx, delta) {
        const carrito = getCarrito();
        carrito[idx].cantidad += delta;
        if (carrito[idx].cantidad <= 0) carrito.splice(idx, 1);
        saveCarrito(carrito);
        renderCarrito();
    };

    window.quitarItem = function(idx) {
        const carrito = getCarrito();
        carrito.splice(idx, 1);
        saveCarrito(carrito);
        renderCarrito();
    };

    function actualizarTotales() {
        const carrito  = getCarrito();
        const subtotal = carrito.reduce((sum, i) => sum + i.precio * i.cantidad, 0);
        const total    = subtotal + costoEnvio;

        document.getElementById('resumenSubtotal').textContent = `L. ${subtotal.toFixed(2)}`;
        document.getElementById('resumenEnvio').textContent    = costoEnvio > 0 ? `L. ${costoEnvio.toFixed(2)}` : 'Gratis';
        document.getElementById('resumenTotal').textContent    = `L. ${total.toFixed(2)}`;
    }

    // ── Tipo entrega ──────────────────────────────
    document.querySelectorAll('.btn-tipo-entrega').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.btn-tipo-entrega').forEach(b => b.classList.remove('activo'));
            this.classList.add('activo');
            tipoEntrega = this.dataset.tipo;

            const secEnvio = document.getElementById('seccionEnvio');
            const filaEnvio = document.getElementById('filaEnvio');

            if (tipoEntrega === 'Envio') {
                secEnvio.style.display  = '';
                filaEnvio.style.display = '';
            } else {
                secEnvio.style.display  = 'none';
                filaEnvio.style.display = 'none';
                costoEnvio = 0;
                actualizarTotales();
            }
        });
    });

    // ── Zona de envío ─────────────────────────────
    document.getElementById('selectZona').addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        costoEnvio = parseFloat(option.dataset.costo || 0);
        document.getElementById('filaEnvio').style.display = '';
        actualizarTotales();
    });

    // ── Confirmar pedido ──────────────────────────
    document.getElementById('btnConfirmarPedido').addEventListener('click', function () {
        const carrito = getCarrito();
        
        <?php if (empty($_SESSION['cliente'])): ?>
        window.location.href = '<?= APP_URL ?>Tienda/login';
        return;
        <?php endif; ?>

        if (carrito.length === 0) {
            Swal.fire({ icon:'warning', title:'Carrito vacío',
                confirmButtonColor:'#de777d' });
            return;
        }

        if (tipoEntrega === 'Envio') {
            const zonaId = document.getElementById('selectZona').value;
            if (!zonaId) {
                Swal.fire({ icon:'warning', title:'Selecciona una zona de envío',
                    confirmButtonColor:'#de777d' });
                return;
            }
            const direccion = document.getElementById('inputDireccion').value.trim();
            if (!direccion) {
                Swal.fire({ icon:'warning', title:'Ingresa tu dirección',
                    confirmButtonColor:'#de777d' });
                return;
            }
            document.getElementById('hZonaId').value   = zonaId;
            document.getElementById('hDireccion').value = direccion;
        }

        document.getElementById('hTipoEntrega').value = tipoEntrega;
        document.getElementById('hWa').value          = document.getElementById('inputWa').value;
        document.getElementById('hNota').value         = document.getElementById('inputNota').value;
        document.getElementById('hItems').value        = JSON.stringify(carrito);

        Swal.fire({
            icon: 'question',
            title: '¿Confirmar pedido?',
            text: `Total: L. ${(carrito.reduce((s,i)=>s+i.precio*i.cantidad,0)+costoEnvio).toFixed(2)}`,
            showCancelButton: true,
            confirmButtonColor: '#de777d',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Revisar'
        }).then(result => {
            if (result.isConfirmed) {
                // Limpiar carrito antes de enviar
                localStorage.removeItem('carrito_anamarcol');
                document.getElementById('formCheckout').submit();
            }
        });
    });

    // Inicializar
    renderCarrito();
});
</script>