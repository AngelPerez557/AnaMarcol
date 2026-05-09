<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-book me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted"><?= count($sesiones) ?> sesión<?= count($sesiones) !== 1 ? 'es' : '' ?></small>
        </div>
        <a href="<?= APP_URL ?>Caja/index" class="btn btn-primary btn-sm">
            <i class="fas fa-cash-register me-1"></i>Ir a Caja
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(222,119,125,0.08);">
                            <th class="ps-4">#</th>
                            <th>Cajero</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th class="text-end">Fondo inicial</th>
                            <th class="text-end">Total ventas</th>
                            <th class="text-end">Diferencia</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sesiones)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-book fa-2x mb-3 d-block" style="opacity:0.3;color:#de777d;"></i>
                                No hay sesiones registradas.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($sesiones as $s): ?>
                        <tr>
                            <td class="ps-4 fw-bold" style="color:#de777d;">
                                #<?= str_pad($s['id'], 4, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($s['cajero_nombre']) ?></div>
                            </td>
                            <td class="text-muted" style="font-size:0.85rem;">
                                <?= date('d/m/Y H:i', strtotime($s['abierta_at'])) ?>
                            </td>
                            <td class="text-muted" style="font-size:0.85rem;">
                                <?= $s['cerrada_at'] ? date('d/m/Y H:i', strtotime($s['cerrada_at'])) : '—' ?>
                            </td>
                            <td class="text-end">
                                L. <?= number_format((float)$s['monto_apertura'], 2) ?>
                            </td>
                            <td class="text-end fw-bold" style="color:#28a745;">
                                <?= $s['total_ventas'] !== null
                                    ? 'L. ' . number_format((float)$s['total_ventas'], 2)
                                    : '—' ?>
                            </td>
                            <td class="text-end fw-bold">
                                <?php if ($s['diferencia'] !== null): ?>
                                <?php $dif = (float)$s['diferencia']; ?>
                                <span class="<?= abs($dif) < 0.01 ? 'text-success' : ($dif > 0 ? 'text-info' : 'text-danger') ?>">
                                    <?= $dif >= 0 ? '+' : '' ?>L. <?= number_format($dif, 2) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($s['estado'] === 'abierta'): ?>
                                <span class="badge bg-success">Abierta</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Cerrada</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <?php if ($s['estado'] === 'cerrada'): ?>
                                    <a href="<?= APP_URL ?>Caja/resumen/<?= $s['id'] ?>"
                                       class="btn btn-sm btn-outline-primary" title="Ver resumen">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= APP_URL ?>Caja/resumen/<?= $s['id'] ?>"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-secondary" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <?php else: ?>
                                    <a href="<?= APP_URL ?>Caja/cierre"
                                       class="btn btn-sm btn-danger" title="Cerrar caja">
                                        <i class="fas fa-store-slash me-1"></i>Cerrar
                                    </a>
                                    <?php endif; ?>
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