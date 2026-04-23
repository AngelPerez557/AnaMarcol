<!-- pedido_exitoso.php -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5 text-center">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <div class="mb-4" style="font-size:4rem;">🎉</div>
                    <h3 class="fw-bold mb-2" style="color:#de777d;">¡Pedido confirmado!</h3>
                    <p class="text-muted mb-1">Tu pedido fue recibido exitosamente.</p>

                    <?php if ($pedido->Found): ?>
                    <div class="my-3 p-3 rounded" style="background:rgba(222,119,125,0.08);">
                        <div class="fw-bold" style="color:#de777d; font-size:1.3rem;">
                            <?= $pedido->getCodigoFormateado() ?>
                        </div>
                        <small class="text-muted">Código de seguimiento</small>
                    </div>

                    <p class="text-muted" style="font-size:0.85rem;">
                        Te notificaremos por WhatsApp cuando tu pedido esté listo.
                    </p>

                    <?php if ($pedido->wa_numero || $pedido->cliente_telefono): ?>
                    <a href="<?= $pedido->getWhatsAppUrl($detalle) ?>"
                       target="_blank"
                       class="btn-rosa d-block mb-3 py-2 text-decoration-none">
                        <i class="fab fa-whatsapp me-2"></i>Ver confirmación en WhatsApp
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <a href="<?= APP_URL ?>Tienda/catalogo" class="btn-rosa-outline d-block py-2">
                        <i class="fas fa-arrow-left me-1"></i>Seguir comprando
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>