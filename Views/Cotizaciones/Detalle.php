<?php
/** @var CotizacionEntity $cotizacion */
/** @var array            $detalle */
$puedeGestionar = Auth::can('pedidos.gestionar');
?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-file-invoice-dollar me-2" style="color:#de777d;"></i>
                Cotización <?= htmlspecialchars($cotizacion->getCodigoFormateado()) ?>
                <span class="badge <?= $cotizacion->getBadgeEstado() ?> ms-2" id="badgeEstado">
                    <?= htmlspecialchars($cotizacion->estado) ?>
                </span>
            </h4>
            <small class="text-muted"><?= htmlspecialchars($cotizacion->getFechaFormateada()) ?></small>
        </div>
        <a href="<?= APP_URL ?>Cotizaciones/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row g-4">

        <!-- Productos cotizados -->
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header fw-semibold">
                    <i class="fas fa-boxes me-2"></i>Productos cotizados
                </div>
                <div class="card-body p-0" style="overflow-x:auto;">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr style="background:rgba(222,119,125,0.08);">
                                <th class="ps-4">Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end pe-4">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalle as $item): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold" style="font-size:0.88rem;">
                                        <?= htmlspecialchars($item['nombre_producto']) ?>
                                    </div>
                                    <?php if (!empty($item['variante_nombre'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($item['variante_nombre']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= (int)$item['cantidad'] ?></td>
                                <td class="text-end text-muted">L. <?= number_format((float)$item['precio_unit'], 2) ?></td>
                                <td class="text-end pe-4 fw-semibold">L. <?= number_format((float)$item['subtotal'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end text-muted">Subtotal</td>
                                <td class="text-end pe-4">L. <?= number_format((float)$cotizacion->subtotal, 2) ?></td>
                            </tr>
                            <?php if ($cotizacion->esEnvio()): ?>
                            <tr>
                                <td colspan="3" class="text-end text-muted">
                                    Envío<?= $cotizacion->zona_nombre ? ' — ' . htmlspecialchars($cotizacion->zona_nombre) : '' ?>
                                </td>
                                <td class="text-end pe-4">L. <?= number_format((float)$cotizacion->costo_envio, 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total estimado</td>
                                <td class="text-end pe-4 fw-bold" style="color:#de777d;">
                                    <?= htmlspecialchars($cotizacion->getTotalFormateado()) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cliente y gestión -->
        <div class="col-12 col-lg-4">

            <div class="card mb-3">
                <div class="card-header fw-semibold"><i class="fas fa-user me-2"></i>Cliente</div>
                <div class="card-body">
                    <p class="mb-1 fw-semibold"><?= htmlspecialchars($cotizacion->nombre_cliente ?? '') ?></p>
                    <?php if (!empty($cotizacion->wa_numero)): ?>
                    <p class="mb-3">
                        <a href="https://wa.me/<?= $cotizacion->getWaNumeroInternacional() ?>"
                           target="_blank" rel="noopener" class="text-decoration-none">
                            <i class="fab fa-whatsapp text-success me-1"></i>
                            <?= htmlspecialchars($cotizacion->wa_numero ?? '') ?>
                        </a>
                    </p>
                    <?php endif; ?>

                    <p class="mb-1" style="font-size:0.85rem;">
                        <i class="fas fa-<?= $cotizacion->esEnvio() ? 'truck' : 'store' ?> me-1 text-muted"></i>
                        <?= $cotizacion->esEnvio() ? 'Envío a domicilio' : 'Retiro en el estudio' ?>
                    </p>
                    <?php if ($cotizacion->esEnvio() && !empty($cotizacion->direccion_envio)): ?>
                    <p class="text-muted mb-1" style="font-size:0.82rem;">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        <?= nl2br(htmlspecialchars($cotizacion->direccion_envio)) ?>
                    </p>
                    <?php endif; ?>

                    <?php if (!empty($cotizacion->nota)): ?>
                    <div class="alert alert-light border mt-3 mb-0" style="font-size:0.82rem;">
                        <i class="fas fa-sticky-note me-1 text-muted"></i>
                        <?= nl2br(htmlspecialchars($cotizacion->nota)) ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($cotizacion->wa_numero)): ?>
                    <a href="<?= htmlspecialchars($cotizacion->getWhatsAppUrlCliente($detalle)) ?>"
                       target="_blank" rel="noopener" class="btn w-100 mt-3"
                       style="background:#25d366; color:#fff; font-weight:600;">
                        <i class="fab fa-whatsapp me-2"></i>Responder por WhatsApp
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($puedeGestionar): ?>
            <div class="card">
                <div class="card-header fw-semibold"><i class="fas fa-tasks me-2"></i>Estado</div>
                <div class="card-body">
                    <p class="text-muted mb-2" style="font-size:0.8rem;">
                        Solo cambia el seguimiento. La venta se registra en AnaMarcolPOS.
                    </p>
                    <div class="d-grid gap-2">
                        <?php foreach (CotizacionEntity::ESTADOS as $e): ?>
                        <button type="button"
                                class="btn btn-sm <?= $cotizacion->estado === $e ? 'btn-primary' : 'btn-outline-primary' ?> btn-estado"
                                data-estado="<?= htmlspecialchars($e) ?>"
                                <?= $cotizacion->estado === $e ? 'disabled' : '' ?>>
                            <?= htmlspecialchars($e) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php if ($puedeGestionar): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            const estado = this.dataset.estado;
            const fd     = new FormData();
            fd.append('csrf_token', '<?= htmlspecialchars(Csrf::token()) ?>');
            fd.append('id',         '<?= (int)$cotizacion->id ?>');
            fd.append('estado',     estado);

            fetch('<?= APP_URL ?>Cotizaciones/cambiarEstado', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo actualizar.' });
                        return;
                    }
                    Swal.fire({ icon: 'success', title: data.message, timer: 1200, showConfirmButton: false })
                        .then(() => window.location.reload());
                })
                .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión' }));
        });
    });
});
</script>
<?php endif; ?>
