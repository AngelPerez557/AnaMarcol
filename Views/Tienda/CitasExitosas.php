<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5 text-center">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <div class="mb-4" style="font-size:4rem;">💄</div>
                    <h3 class="fw-bold mb-2" style="color:#de777d;">¡Cita agendada!</h3>
                    <p class="text-muted mb-3">
                        Tu cita fue registrada exitosamente. Ana te confirmará por WhatsApp en breve.
                    </p>

                    <?php if ($cita->Found): ?>
                    <div class="p-3 rounded mb-3" style="background:rgba(222,119,125,0.08);">
                        <div class="fw-semibold"><?= htmlspecialchars($cita->servicio_nombre ?? '—') ?></div>
                        <div style="color:#de777d; font-size:1.1rem; font-weight:700;">
                            <?= $cita->getFechaFormateada() ?> — <?= $cita->getHoraFormateada() ?>
                        </div>
                        <span class="badge bg-warning text-dark mt-1">Pendiente de confirmación</span>
                    </div>

                    <?php if ($cita->cliente_telefono): ?>
                    <a href="https://wa.me/<?= WA_NUMBER ?>?text=<?= urlencode('Hola Ana, acabo de agendar una cita de ' . ($cita->servicio_nombre ?? '') . ' para el ' . $cita->getFechaFormateada() . ' a las ' . $cita->getHoraFormateada()) ?>"
                       target="_blank"
                       class="btn-rosa d-block mb-3 py-2 text-decoration-none">
                        <i class="fab fa-whatsapp me-2"></i>Confirmar por WhatsApp
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <a href="<?= APP_URL ?>Tienda/citas" class="btn-rosa-outline d-block py-2">
                        <i class="fas fa-calendar me-1"></i>Ver disponibilidad
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>