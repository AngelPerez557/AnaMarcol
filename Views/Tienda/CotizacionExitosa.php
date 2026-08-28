<?php
/** @var CotizacionEntity $cotizacion */
/** @var array            $detalle */
$waUrl = $cotizacion->getWhatsAppUrlEstudio($detalle);
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">

            <div class="card text-center">
                <div class="card-body p-4 p-md-5">

                    <div style="width:74px; height:74px; border-radius:50%; margin:0 auto 18px;
                                background:rgba(37,211,102,0.12); display:flex;
                                align-items:center; justify-content:center;">
                        <i class="fab fa-whatsapp" style="font-size:2.4rem; color:#25d366;"></i>
                    </div>

                    <h4 class="fw-bold mb-1">¡Tu cotización está lista!</h4>
                    <p class="text-muted mb-4" style="font-size:0.92rem;">
                        Presiona el botón para enviárnosla por WhatsApp. El mensaje ya va escrito —
                        solo tienes que darle enviar.
                    </p>

                    <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener"
                       class="btn w-100 mb-3" id="btnEnviarWa"
                       style="background:#25d366; color:#fff; font-weight:600; padding:13px; font-size:1.02rem;">
                        <i class="fab fa-whatsapp me-2"></i>Enviar por WhatsApp
                    </a>

                    <!-- Resumen -->
                    <div class="text-start border rounded p-3 mb-3" style="background:#fdf8f8;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold" style="font-size:0.9rem;">
                                Cotización <?= htmlspecialchars($cotizacion->getCodigoFormateado()) ?>
                            </span>
                            <small class="text-muted"><?= htmlspecialchars($cotizacion->getFechaFormateada()) ?></small>
                        </div>

                        <?php foreach ($detalle as $item): ?>
                        <div class="d-flex justify-content-between" style="font-size:0.85rem;">
                            <span>
                                <?= htmlspecialchars($item['nombre_producto']) ?>
                                <?php if (!empty($item['variante_nombre'])): ?>
                                    <small class="text-muted">(<?= htmlspecialchars($item['variante_nombre']) ?>)</small>
                                <?php endif; ?>
                                <span class="text-muted">x<?= (int)$item['cantidad'] ?></span>
                            </span>
                            <span>L. <?= number_format((float)$item['subtotal'], 2) ?></span>
                        </div>
                        <?php endforeach; ?>

                        <hr class="my-2">

                        <div class="d-flex justify-content-between" style="font-size:0.85rem;">
                            <span class="text-muted">Subtotal</span>
                            <span>L. <?= number_format((float)$cotizacion->subtotal, 2) ?></span>
                        </div>

                        <?php if ($cotizacion->esEnvio()): ?>
                        <div class="d-flex justify-content-between" style="font-size:0.85rem;">
                            <span class="text-muted">
                                Envío<?= $cotizacion->zona_nombre ? ' — ' . htmlspecialchars($cotizacion->zona_nombre) : '' ?>
                            </span>
                            <span>L. <?= number_format((float)$cotizacion->costo_envio, 2) ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between fw-bold pt-1">
                            <span>Total estimado</span>
                            <span style="color:#de777d;"><?= htmlspecialchars($cotizacion->getTotalFormateado()) ?></span>
                        </div>
                    </div>

                    <p class="text-muted mb-3" style="font-size:0.78rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        Es una cotización, no una compra: confirmamos disponibilidad y precio final por WhatsApp.
                    </p>

                    <a href="<?= APP_URL ?>Tienda/catalogo" class="btn-rosa-outline d-inline-block px-4 py-2">
                        <i class="fas fa-arrow-left me-1"></i>Volver al catálogo
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
// La cotización ya quedó guardada en el servidor: recién aquí se
// vacía el carrito local, para no perderlo si el envío hubiera fallado.
document.addEventListener('DOMContentLoaded', function () {
    localStorage.removeItem('carrito_anamarcol');
    if (typeof actualizarBadge === 'function') actualizarBadge();
});
</script>
