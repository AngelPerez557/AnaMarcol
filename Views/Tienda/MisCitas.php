<div class="container py-5">

    <h3 class="fw-bold mb-4">
        <i class="fas fa-calendar-check me-2" style="color:#de777d;"></i>Mis Citas
    </h3>

    <?php if (empty($citas)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-calendar-times fa-3x mb-3 d-block" style="opacity:0.3;"></i>
        <p>No tienes citas agendadas.</p>
        <a href="<?= APP_URL ?>Tienda/citas" class="btn-rosa px-4 py-2 d-inline-block">
            Agendar cita
        </a>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($citas as $cita): ?>
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="fw-bold"><?= htmlspecialchars($cita->servicio_nombre ?? '—') ?></div>
                        <span class="badge <?= $cita->getBadgeEstado() ?>">
                            <i class="<?= $cita->getIconoEstado() ?> me-1"></i>
                            <?= $cita->estado ?>
                        </span>
                    </div>
                    <div class="text-muted mb-1">
                        <i class="fas fa-calendar me-2" style="color:#de777d;"></i>
                        <?= $cita->getFechaFormateada() ?>
                    </div>
                    <div class="text-muted mb-2">
                        <i class="fas fa-clock me-2" style="color:#de777d;"></i>
                        <?= $cita->getHoraFormateada() ?>
                        <small class="ms-1">(<?= $cita->duracion ?> min)</small>
                    </div>
                    <div class="fw-bold" style="color:#de777d;">
                        <?= $cita->getPrecioFormateado() ?>
                    </div>

                    <?php if ($cita->estado === 'Confirmada' && $cita->cliente_telefono): ?>
                    <a href="<?= $cita->getWhatsAppUrl() ?>" target="_blank"
                       class="btn-rosa-outline d-block text-center mt-2 text-decoration-none"
                       style="font-size:0.85rem; padding:6px;">
                        <i class="fab fa-whatsapp me-1"></i>Ver confirmación
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>