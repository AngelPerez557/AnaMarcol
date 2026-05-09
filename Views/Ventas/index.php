<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-history me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($ventas) ?> venta<?= count($ventas) !== 1 ? 's' : '' ?> registrada<?= count($ventas) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Caja/index" class="btn btn-primary btn-sm">
            <i class="fas fa-cash-register me-1"></i>Ir a Caja
        </a>
    </div>

    <!-- ─────────────────────────────────────────────
         CARDS RESUMEN DEL DÍA
         ───────────────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:48px; height:48px; background:rgba(222,119,125,0.12); flex-shrink:0;">
                        <i class="fas fa-shopping-cart" style="color:#de777d;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Ventas hoy</div>
                        <div class="fw-bold fs-4"><?= $countHoy ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:48px; height:48px; background:rgba(40,167,69,0.12); flex-shrink:0;">
                        <i class="fas fa-lempira-sign" style="color:#28a745;"></i>
                        <i class="fas fa-money-bill-wave" style="color:#28a745;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Total hoy</div>
                        <div class="fw-bold fs-5" style="color:#28a745;">
                            L. <?= number_format($totalHoy, 2) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ─────────────────────────────────────────────
         FILTROS
         ───────────────────────────────────────────── -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               class="form-control border-start-0"
                               id="buscarVenta"
                               placeholder="Buscar por cliente o cajero...">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroMetodo">
                        <option value="">Todos los métodos</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta">Tarjeta</option>
                        <option value="Transferencia">Transferencia</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <input type="date" class="form-control" id="filtroFecha">
                </div>
                <div class="col-12 col-md-3 text-end">
                    <small class="text-muted" id="contadorVisible">
                        Mostrando <?= count($ventas) ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- ─────────────────────────────────────────────
         TABLA
         ───────────────────────────────────────────── -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(222,119,125,0.08);">
                            <th class="ps-4"># Venta</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Cajero</th>
                            <th>Método</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ventas)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-2x mb-3 d-block"
                                   style="color:#de777d;opacity:0.4;"></i>
                                No hay ventas registradas.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($ventas as $venta): ?>
                        <tr class="venta-row"
                            data-cliente="<?= strtolower(htmlspecialchars($venta['cliente_nombre'] ?? 'consumidor final')) ?>"
                            data-cajero="<?= strtolower(htmlspecialchars($venta['cajero_nombre'] ?? '')) ?>"
                            data-metodo="<?= htmlspecialchars($venta['metodo_pago'] ?? '') ?>"
                            data-fecha="<?= date('Y-m-d', strtotime($venta['created_at'])) ?>">

                            <td class="ps-4">
                                <span class="fw-bold" style="color:#de777d;">
                                    #<?= str_pad($venta['id'], 8, '0', STR_PAD_LEFT) ?>
                                </span>
                                <?php if ((int)($venta['anulada'] ?? 0)): ?>
                                <span class="badge bg-danger ms-1" style="font-size:0.65rem;">ANULADA</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted" style="font-size:0.85rem;">
                                <?= date('d/m/Y H:i', strtotime($venta['created_at'])) ?>
                            </td>
                            <td>
                                <div class="fw-semibold">
                                    <?= htmlspecialchars($venta['cliente_nombre'] ?? 'Consumidor final') ?>
                                </div>
                            </td>
                            <td class="text-muted">
                                <?= htmlspecialchars($venta['cajero_nombre'] ?? '—') ?>
                            </td>
                            <td>
                                <?php
                                $iconos = [
                                    'Efectivo'      => 'fa-money-bill-wave text-success',
                                    'Tarjeta'       => 'fa-credit-card text-primary',
                                    'Transferencia' => 'fa-mobile-alt text-info',
                                ];
                                $icono = $iconos[$venta['metodo_pago']] ?? 'fa-circle';
                                ?>
                                <span class="badge bg-light text-dark border">
                                    <i class="fas <?= $icono ?> me-1"></i>
                                    <?= htmlspecialchars($venta['metodo_pago'] ?? '—') ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold" style="color:#de777d;">
                                L. <?= number_format((float)$venta['total'], 2) ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="<?= APP_URL ?>Ventas/detalle/<?= $venta['id'] ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= APP_URL ?>Caja/recibo/<?= $venta['id'] ?>"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-secondary"
                                       title="Imprimir recibo">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const buscar      = document.getElementById('buscarVenta');
    const filtroMet   = document.getElementById('filtroMetodo');
    const filtroFecha = document.getElementById('filtroFecha');
    const contador    = document.getElementById('contadorVisible');
    const filas       = document.querySelectorAll('.venta-row');

    function filtrar() {
        const texto  = buscar.value.toLowerCase();
        const metodo = filtroMet.value;
        const fecha  = filtroFecha.value;
        let visible  = 0;

        filas.forEach(fila => {
            const cliente   = fila.dataset.cliente  || '';
            const cajero    = fila.dataset.cajero   || '';
            const metFila   = fila.dataset.metodo   || '';
            const fechaFila = fila.dataset.fecha    || '';

            const okTexto  = cliente.includes(texto) || cajero.includes(texto);
            const okMetodo = !metodo || metFila === metodo;
            const okFecha  = !fecha  || fechaFila === fecha;

            if (okTexto && okMetodo && okFecha) {
                fila.style.display = '';
                visible++;
            } else {
                fila.style.display = 'none';
            }
        });

        contador.textContent = `Mostrando ${visible}`;
    }

    buscar.addEventListener('input', filtrar);
    filtroMet.addEventListener('change', filtrar);
    filtroFecha.addEventListener('change', filtrar);

});
</script>