<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-receipt me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= date('d/m/Y H:i', strtotime($venta['created_at'])) ?>
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= APP_URL ?>Caja/recibo/<?= $venta['id'] ?>"
               target="_blank"
               class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-print me-1"></i>Imprimir
            </a>
            <a href="<?= APP_URL ?>Ventas/index" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="row g-4">

        <!-- ── Columna izquierda — Info de la venta ── -->
        <div class="col-12 col-lg-4">

            <!-- Datos de la venta -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i>Información
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Cliente</td>
                                <td class="fw-semibold">
                                    <?= htmlspecialchars($venta['cliente_nombre'] ?? 'Consumidor final') ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Cajero</td>
                                <td><?= htmlspecialchars($venta['cajero_nombre'] ?? '—') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Método</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= htmlspecialchars($venta['metodo_pago'] ?? '—') ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Fecha</td>
                                <td><?= date('d/m/Y H:i', strtotime($venta['created_at'])) ?></td>
                            </tr>
                            <?php if ($venta['nota']): ?>
                            <tr>
                                <td class="text-muted">Nota</td>
                                <td class="fst-italic"><?= htmlspecialchars($venta['nota']) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totales -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calculator me-2"></i>Totales
                </div>
                <div class="card-body">
                    <?php
                    $total          = (float) $venta['total'];
                    $subtotalSinIsv = $total / 1.15;
                    $isv            = $total - $subtotalSinIsv;
                    $montoRecibido  = (float) ($venta['monto_recibido'] ?? 0);
                    $cambio         = (float) ($venta['cambio'] ?? 0);
                    ?>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Subtotal sin ISV</span>
                        <span>L. <?= number_format($subtotalSinIsv, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">ISV 15%</span>
                        <span>L. <?= number_format($isv, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold pt-2 border-top"
                         style="font-size:1.15rem;">
                        <span>Total</span>
                        <span style="color:#de777d;">L. <?= number_format($total, 2) ?></span>
                    </div>
                    <?php if ($venta['metodo_pago'] === 'Efectivo'): ?>
                    <div class="d-flex justify-content-between mt-2 text-muted">
                        <span>Recibido</span>
                        <span>L. <?= number_format($montoRecibido, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-success fw-semibold">
                        <span>Cambio</span>
                        <span>L. <?= number_format($cambio, 2) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($config && $venta['correlativo']): ?>
                    <div class="mt-3 pt-2 border-top">
                        <small class="text-muted d-block">Factura fiscal</small>
                        <code style="color:#b05a60; font-size:0.8rem;">
                            <?= htmlspecialchars(
                                ($config['establecimiento'] ?? '000') . '-' .
                                ($config['punto_emision']   ?? '001') . '-01-' .
                                str_pad($venta['correlativo'], 8, '0', STR_PAD_LEFT)
                            ) ?>
                        </code>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- ── Columna derecha — Productos vendidos ── -->
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-boxes me-2"></i>Productos vendidos
                    </span>
                    <span class="badge" style="background:#de777d;">
                        <?= count($detalle) ?> ítem<?= count($detalle) !== 1 ? 's' : '' ?>
                    </span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr style="background:rgba(222,119,125,0.08);">
                                <th class="ps-3">Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">P. Unitario</th>
                                <th class="text-end pe-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($detalle)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    Sin detalle registrado.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($detalle as $item): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($item['nombre_producto']) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        x<?= $item['cantidad'] ?>
                                    </span>
                                </td>
                                <td class="text-end text-muted">
                                    L. <?= number_format((float)$item['precio_unit'], 2) ?>
                                </td>
                                <td class="text-end pe-3 fw-bold" style="color:#de777d;">
                                    L. <?= number_format((float)$item['subtotal'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background:rgba(222,119,125,0.06);">
                                <td colspan="3" class="text-end fw-bold ps-3">Total:</td>
                                <td class="text-end pe-3 fw-bold fs-5" style="color:#de777d;">
                                    L. <?= number_format($total, 2) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>