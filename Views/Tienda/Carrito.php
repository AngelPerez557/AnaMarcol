<div class="container py-5">

    <h3 class="fw-bold mb-1">
        <i class="fas fa-file-invoice-dollar me-2" style="color:#de777d;"></i>Tu cotización
    </h3>
    <p class="text-muted mb-4" style="font-size:0.9rem;">
        Arma tu lista y envíala por WhatsApp. Te confirmamos disponibilidad y precio final por ahí mismo.
    </p>

    <!-- Carrito vacío -->
    <div id="carritoVacio" style="display:none;" class="text-center py-5">
        <i class="fas fa-shopping-cart fa-4x mb-4 d-block" style="color:#de777d; opacity:0.3;"></i>
        <h5 class="text-muted">Aún no has agregado productos</h5>
        <a href="<?= APP_URL ?>Tienda/catalogo" class="btn-rosa mt-3 d-inline-block px-4 py-2">
            <i class="fas fa-arrow-left me-2"></i>Ver catálogo
        </a>
    </div>

    <!-- Carrito con productos -->
    <div id="carritoContenido">
        <form id="formCotizacion" method="POST" action="<?= APP_URL ?>Tienda/cotizar">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <input type="hidden" name="items"        id="hItems"      value="">
            <input type="hidden" name="tipo_entrega" id="hTipoEntrega" value="Retiro">

            <div class="row g-4">

                <!-- Lista de productos -->
                <div class="col-12 col-lg-7">
                    <div class="card">
                        <div class="card-body p-0 tabla-carrito-wrap">
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
                    <p class="text-muted mt-2 mb-0" style="font-size:0.78rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        Los precios son referenciales. La disponibilidad se confirma por WhatsApp.
                    </p>
                </div>

                <!-- Datos de la cotización -->
                <div class="col-12 col-lg-5">
                    <div class="card">
                        <div class="card-header fw-semibold">
                            <i class="fas fa-receipt me-2"></i>Datos de la cotización
                        </div>
                        <div class="card-body">

                            <!-- Nombre -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="inputNombre">
                                    Tu nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="inputNombre"
                                       name="nombre_cliente" maxlength="120"
                                       placeholder="Nombre y apellido" required>
                            </div>

                            <!-- Nota -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="inputNota">
                                    Nota <span class="text-muted fw-normal">(opcional)</span>
                                </label>
                                <textarea class="form-control" id="inputNota" name="nota" rows="2"
                                          placeholder="Tono, color, fecha en que lo necesitas..."></textarea>
                            </div>

                            <!-- Totales -->
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Subtotal</span>
                                    <span id="resumenSubtotal">L. 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold pt-2 border-top"
                                     style="font-size:1.15rem;">
                                    <span>Total estimado</span>
                                    <span style="color:#de777d;" id="resumenTotal">L. 0.00</span>
                                </div>
                            </div>

                            <button type="submit" class="btn-rosa w-100 mt-3" id="btnCotizar"
                                    style="padding:12px; font-size:1rem;">
                                <i class="fab fa-whatsapp me-2"></i>Enviar cotización por WhatsApp
                            </button>

                            <a href="<?= APP_URL ?>Tienda/catalogo" class="btn-rosa-outline d-block text-center mt-2">
                                <i class="fas fa-arrow-left me-1"></i>Seguir viendo productos
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>

<style>
/* ── Tabla carrito responsive ── */
.tabla-carrito-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

@media (max-width: 575px) {
    #tablaCarrito th:nth-child(3),
    #tablaCarrito td:nth-child(3) { display: none; }
    #tablaCarrito { font-size: 0.82rem; min-width: 0; }
    #tablaCarrito td, #tablaCarrito th { padding: 8px 6px; }
    #tablaCarrito .d-flex > div:first-child { width: 36px !important; height: 36px !important; }
    #tablaCarrito button[onclick*="cambiarCantidad"] {
        width: 22px !important; height: 22px !important; font-size: 0.8rem !important;
    }
    .col-12.col-lg-5 .card-body { padding: 1rem !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const $ = id => document.getElementById(id);

    // ── Renderizar carrito ────────────────────────
    function renderCarrito() {
        const carrito = getCarrito();
        const body    = $('bodyCarrito');
        const vacio   = $('carritoVacio');
        const cont    = $('carritoContenido');

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
                        <button type="button" onclick="cambiarCantidad(${idx}, -1)"
                                style="width:26px; height:26px; border-radius:50%; border:1px solid #de777d;
                                       background:#fff; color:#de777d; cursor:pointer; font-size:0.9rem;">−</button>
                        <span style="min-width:24px; text-align:center; font-weight:600;">${item.cantidad}</span>
                        <button type="button" onclick="cambiarCantidad(${idx}, 1)"
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
                    <button type="button" onclick="quitarItem(${idx})"
                            style="background:none; border:none; color:#dc3545; cursor:pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>`).join('');

        actualizarTotales();
    }

    window.cambiarCantidad = function (idx, delta) {
        const carrito = getCarrito();
        carrito[idx].cantidad += delta;
        if (carrito[idx].cantidad <= 0) carrito.splice(idx, 1);
        saveCarrito(carrito);
        renderCarrito();
    };

    window.quitarItem = function (idx) {
        const carrito = getCarrito();
        carrito.splice(idx, 1);
        saveCarrito(carrito);
        renderCarrito();
    };

    function actualizarTotales() {
        const carrito  = getCarrito();
        const subtotal = carrito.reduce((sum, i) => sum + i.precio * i.cantidad, 0);
        $('resumenSubtotal').textContent = `L. ${subtotal.toFixed(2)}`;
        $('resumenTotal').textContent    = `L. ${subtotal.toFixed(2)}`;
    }

    // ── Enviar cotización ─────────────────────────
    // El carrito NO se limpia aquí: se limpia en la pantalla de
    // confirmación, para no perderlo si el POST falla.
    $('formCotizacion').addEventListener('submit', function (e) {
        const carrito = getCarrito();

        if (carrito.length === 0) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Tu lista está vacía', confirmButtonColor: '#de777d' });
            return;
        }

        // Solo se manda lo que el servidor necesita para reconstruir
        // el detalle: id, variante y cantidad. El precio lo pone la BD.
        $('hItems').value = JSON.stringify(carrito.map(i => ({
            id:         i.id,
            varianteId: i.varianteId || null,
            cantidad:   i.cantidad
        })));

        $('btnCotizar').disabled  = true;
        $('btnCotizar').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generando cotización...';
    });

    renderCarrito();
});
</script>
