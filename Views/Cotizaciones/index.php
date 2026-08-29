<?php
/** @var CotizacionEntity[] $cotizaciones */
/** @var array             $conteos */
$estadoActual = $_GET['estado'] ?? '';
?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-file-invoice-dollar me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($cotizaciones) ?> cotizacion<?= count($cotizaciones) !== 1 ? 'es' : '' ?>
                <?= $estadoActual !== '' ? 'en estado ' . htmlspecialchars($estadoActual) : 'en total' ?>
                — <?= (int)$conteos['hoy'] ?> hoy
            </small>
        </div>
    </div>

    <!-- Filtros por estado -->
    <div class="d-flex gap-2 flex-wrap mb-3">
        <a href="<?= APP_URL ?>Cotizaciones/index"
           class="btn btn-sm <?= $estadoActual === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">
            Todas
        </a>
        <?php foreach (CotizacionEntity::ESTADOS as $e): ?>
        <a href="<?= APP_URL ?>Cotizaciones/index?estado=<?= urlencode($e) ?>"
           class="btn btn-sm <?= $estadoActual === $e ? 'btn-primary' : 'btn-outline-secondary' ?>">
            <?= htmlspecialchars($e) ?>
            <span class="badge bg-light text-dark ms-1"><?= (int)($conteos[$e] ?? 0) ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="alert alert-light border d-flex align-items-start gap-2 py-2" style="font-size:0.83rem;">
        <i class="fas fa-info-circle mt-1" style="color:#de777d;"></i>
        <div>
            Una cotización <strong>no descuenta inventario ni registra venta</strong>.
            Cuando el cliente confirme, la venta se factura en AnaMarcolPOS y aquí se marca como
            <strong>Cerrada</strong>.
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0" style="overflow-x:auto;">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr style="background:rgba(222,119,125,0.08);">
                        <th class="ps-4">Código</th>
                        <th>Cliente</th>
                        <th class="text-center">Items</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Entrega</th>
                        <th class="text-center">Estado</th>
                        <th>Fecha</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cotizaciones)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-file-invoice fa-2x mb-3 d-block" style="opacity:0.3;"></i>
                            No hay cotizaciones<?= $estadoActual !== '' ? ' en este estado' : ' todavía' ?>.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($cotizaciones as $c): ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?= htmlspecialchars($c->getCodigoFormateado()) ?></td>
                        <td>
                            <div class="fw-semibold" style="font-size:0.88rem;">
                                <?= htmlspecialchars($c->nombre_cliente ?? '') ?>
                            </div>
                            <?php if (!empty($c->wa_numero)): ?>
                            <a href="https://wa.me/<?= $c->getWaNumeroInternacional() ?>"
                               target="_blank" rel="noopener" class="text-muted" style="font-size:0.8rem;">
                                <i class="fab fa-whatsapp text-success me-1"></i><?= htmlspecialchars($c->wa_numero ?? '') ?>
                            </a>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= (int)$c->total_items ?></td>
                        <td class="text-end fw-bold" style="color:#de777d;">
                            <?= htmlspecialchars($c->getTotalFormateado()) ?>
                        </td>
                        <td class="text-center">
                            <?php if ($c->esEnvio()): ?>
                            <span class="badge bg-info text-dark">
                                <i class="fas fa-truck me-1"></i><?= htmlspecialchars($c->zona_nombre ?? 'Envío') ?>
                            </span>
                            <?php else: ?>
                            <span class="badge bg-secondary"><i class="fas fa-store me-1"></i>Retiro</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge <?= $c->getBadgeEstado() ?>"><?= htmlspecialchars($c->estado) ?></span>
                        </td>
                        <td class="text-muted" style="font-size:0.82rem;">
                            <?= htmlspecialchars($c->getFechaFormateada()) ?>
                        </td>
                        <td class="text-center">
                            <a href="<?= APP_URL ?>Cotizaciones/detalle/<?= (int)$c->id ?>"
                               class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
