<div class="container-fluid py-4">

    <!-- CABECERA -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-ticket-alt me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">Detalle del ticket de soporte</small>
        </div>
        <a href="<?= APP_URL ?>Soporte/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2">
        <i class="fas fa-exclamation-circle fa-lg"></i>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php elseif (empty($ticket)): ?>
    <div class="alert alert-warning">Ticket no encontrado.</div>
    <?php else:
        $estado    = $ticket['estado']    ?? 'abierto';
        $prioridad = $ticket['prioridad'] ?? 'media';
        $tipo      = $ticket['tipo']      ?? 'consulta';

        $estadoConfig = [
            'abierto'           => ['label' => 'Abierto',            'color' => '#28a745'],
            'en_proceso'        => ['label' => 'En proceso',         'color' => '#de777d'],
            'esperando_cliente' => ['label' => 'Esperando respuesta','color' => '#ffc107'],
            'resuelto'          => ['label' => 'Resuelto',           'color' => '#17a2b8'],
            'cerrado'           => ['label' => 'Cerrado',            'color' => '#6c757d'],
        ];
        $prioConfig = [
            'critica' => ['label' => 'Crítica', 'color' => '#dc3545'],
            'alta'    => ['label' => 'Alta',    'color' => '#fd7e14'],
            'media'   => ['label' => 'Media',   'color' => '#ffc107'],
            'baja'    => ['label' => 'Baja',    'color' => '#6c757d'],
        ];
        $tipoLabels = [
            'error'         => 'Error / Bug',
            'modificacion'  => 'Modificación',
            'nueva_funcion' => 'Nueva función',
            'consulta'      => 'Consulta',
        ];

        $ec = $estadoConfig[$estado]  ?? $estadoConfig['abierto'];
        $pc = $prioConfig[$prioridad] ?? $prioConfig['media'];
    ?>

    <div class="row g-4">

        <!-- COLUMNA IZQUIERDA — Detalle -->
        <div class="col-12 col-lg-7">

            <!-- Info del ticket -->
            <div class="card mb-4" style="border-left: 4px solid <?= $pc['color'] ?>;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge"
                                  style="background:<?= $ec['color'] ?>20; color:<?= $ec['color'] ?>; border:1px solid <?= $ec['color'] ?>;">
                                <?= $ec['label'] ?>
                            </span>
                            <span class="badge" style="background:<?= $pc['color'] ?>; color:#fff;">
                                <?= $pc['label'] ?>
                            </span>
                            <span class="badge bg-secondary">
                                <?= $tipoLabels[$tipo] ?? $tipo ?>
                            </span>
                        </div>
                        <small class="text-muted">
                            <?= !empty($ticket['created_at']) ? date('d/m/Y H:i', strtotime($ticket['created_at'])) : '' ?>
                        </small>
                    </div>

                    <h5 class="fw-bold mb-3"><?= htmlspecialchars($ticket['titulo'] ?? '') ?></h5>

                    <div class="p-3 rounded mb-0" style="background:rgba(222,119,125,0.05); border:1px solid rgba(222,119,125,0.15);">
                        <small class="text-muted d-block mb-1">
                            <i class="fas fa-align-left me-1"></i>Descripción:
                        </small>
                        <p class="mb-0" style="white-space:pre-wrap; font-size:0.9rem;">
                            <?= htmlspecialchars($ticket['descripcion'] ?? '') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Comentarios -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-comments me-2"></i>
                    Conversación
                    <?php if (!empty($ticket['comentarios'])): ?>
                    <span class="badge ms-1" style="background:#de777d;">
                        <?= count($ticket['comentarios']) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($ticket['comentarios'])): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-comments fa-2x mb-2 d-block" style="opacity:0.3;"></i>
                        Sin respuestas aún. El equipo de DeskCod te responderá pronto.
                    </div>
                    <?php else: ?>
                    <div class="p-3 d-flex flex-column gap-3" id="listaComentarios">
                        <?php foreach ($ticket['comentarios'] as $c):
                            $esDeskCod = ($c['tipo'] ?? '') !== 'cliente';
                        ?>
                        <div class="d-flex gap-3 <?= $esDeskCod ? '' : 'flex-row-reverse' ?>">
                            <div style="
                                width:36px; height:36px; flex-shrink:0; border-radius:50%;
                                background:<?= $esDeskCod ? '#de777d' : '#6c757d' ?>;
                                display:flex; align-items:center; justify-content:center;
                                color:#fff; font-size:0.8rem;">
                                <i class="fas <?= $esDeskCod ? 'fa-headset' : 'fa-user' ?>"></i>
                            </div>
                            <div style="max-width:80%;">
                                <div class="p-3 rounded"
                                     style="background:<?= $esDeskCod ? 'rgba(222,119,125,0.08)' : 'rgba(108,117,125,0.08)' ?>;
                                            border:1px solid <?= $esDeskCod ? 'rgba(222,119,125,0.2)' : 'rgba(108,117,125,0.2)' ?>;">
                                    <div class="fw-semibold mb-1" style="font-size:0.82rem; color:<?= $esDeskCod ? '#de777d' : '#6c757d' ?>;">
                                        <?= htmlspecialchars($c['autor'] ?? ($esDeskCod ? 'DeskCod' : 'Ana Marcol')) ?>
                                    </div>
                                    <p class="mb-0" style="font-size:0.88rem; white-space:pre-wrap;">
                                        <?= htmlspecialchars($c['comentario'] ?? '') ?>
                                    </p>
                                </div>
                                <small class="text-muted" style="font-size:0.75rem;">
                                    <?= !empty($c['created_at']) ? date('d/m/Y H:i', strtotime($c['created_at'])) : '' ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Caja de respuesta -->
                <?php if (!in_array($estado, ['cerrado', 'resuelto'])): ?>
                <div class="card-footer">
                    <div class="d-flex gap-2">
                        <textarea class="form-control form-control-sm"
                                  id="inputComentario"
                                  rows="2"
                                  placeholder="Escribe tu respuesta o comentario..."
                                  maxlength="1000"></textarea>
                        <button type="button" class="btn btn-primary btn-sm px-3"
                                id="btnComentar" style="white-space:nowrap;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- COLUMNA DERECHA — Resumen -->
        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i>Resumen
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3" style="font-size:0.88rem;">
                        <div>
                            <small class="text-muted d-block">Estado</small>
                            <span class="fw-semibold" style="color:<?= $ec['color'] ?>;">
                                <?= $ec['label'] ?>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Prioridad</small>
                            <span class="badge" style="background:<?= $pc['color'] ?>; color:#fff;">
                                <?= $pc['label'] ?>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Tipo</small>
                            <span><?= $tipoLabels[$tipo] ?? $tipo ?></span>
                        </div>
                        <?php if (!empty($ticket['empleado_nombre'])): ?>
                        <div>
                            <small class="text-muted d-block">Asignado a</small>
                            <span>
                                <i class="fas fa-user me-1" style="color:#de777d;"></i>
                                <?= htmlspecialchars($ticket['empleado_nombre']) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($ticket['created_at'])): ?>
                        <div>
                            <small class="text-muted d-block">Creado</small>
                            <span><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($ticket['fecha_limite'])): ?>
                        <div>
                            <small class="text-muted d-block">Fecha límite</small>
                            <span><?= date('d/m/Y', strtotime($ticket['fecha_limite'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php endif; ?>
</div>

<input type="hidden" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
<input type="hidden" id="appUrl"    value="<?= APP_URL ?>">
<input type="hidden" id="ticketId"  value="<?= htmlspecialchars($ticket['id'] ?? '') ?>">

<script>
document.addEventListener('DOMContentLoaded', function () {
    const APP_URL  = document.getElementById('appUrl').value;
    const csrf     = document.getElementById('csrfToken').value;
    const ticketId = document.getElementById('ticketId').value;
    const btnComentar   = document.getElementById('btnComentar');
    const inputComentario = document.getElementById('inputComentario');

    if (!btnComentar) return;

    btnComentar.addEventListener('click', function () {
        const texto = inputComentario.value.trim();
        if (!texto) return;

        btnComentar.disabled = true;
        btnComentar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        const fd = new FormData();
        fd.append('csrf_token',  csrf);
        fd.append('ticket_id',   ticketId);
        fd.append('comentario',  texto);

        fetch(`${APP_URL}Soporte/comentar`, { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                inputComentario.value = '';
                Swal.fire({
                    icon: 'success', title: 'Comentario enviado',
                    timer: 1500, showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire({ icon:'error', title:'Error', text: data.message, confirmButtonColor:'#de777d' });
            }
        })
        .finally(() => {
            btnComentar.disabled = false;
            btnComentar.innerHTML = '<i class="fas fa-paper-plane"></i>';
        });
    });

    // Enviar con Ctrl+Enter
    inputComentario?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.ctrlKey) btnComentar.click();
    });
});
</script>