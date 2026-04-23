<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-shopping-bag me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <span class="badge <?= $pedido->getBadgeEstado() ?> ms-1">
                <i class="<?= $pedido->getIconoEstado() ?> me-1"></i>
                <?= $pedido->estado ?>
            </span>
        </div>
        <a href="<?= APP_URL ?>Pedidos/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row g-4">

        <!-- ─────────────────────────────────────────────
             COLUMNA IZQUIERDA — Info del pedido
             ───────────────────────────────────────────── -->
        <div class="col-12 col-lg-7">

            <!-- Datos del cliente -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i>Cliente
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Nombre</small>
                            <span class="fw-semibold">
                                <?= htmlspecialchars($pedido->cliente_nombre ?? 'Consumidor final') ?>
                            </span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Teléfono</small>
                            <?php if ($pedido->cliente_telefono || $pedido->wa_numero): ?>
                            <a href="https://wa.me/504<?= preg_replace('/[^0-9]/', '', $pedido->wa_numero ?? $pedido->cliente_telefono) ?>"
                               target="_blank" class="text-decoration-none">
                                <i class="fab fa-whatsapp text-success me-1"></i>
                                <?= htmlspecialchars($pedido->wa_numero ?? $pedido->cliente_telefono) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">Sin teléfono</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Tipo de entrega</small>
                            <span>
                                <i class="fas <?= $pedido->esEnvio() ? 'fa-truck' : 'fa-store' ?> me-1 text-muted"></i>
                                <?= $pedido->esEnvio() ? 'Envío a domicilio' : 'Retiro en tienda' ?>
                            </span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Fecha del pedido</small>
                            <span><?= date('d/m/Y H:i', strtotime($pedido->created_at)) ?></span>
                        </div>
                        <?php if ($pedido->esEnvio() && $pedido->direccion_envio): ?>
                        <div class="col-12">
                            <small class="text-muted d-block">Dirección de envío</small>
                            <span><?= htmlspecialchars($pedido->direccion_envio) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($pedido->nota): ?>
                        <div class="col-12">
                            <small class="text-muted d-block">Nota del cliente</small>
                            <span class="fst-italic"><?= htmlspecialchars($pedido->nota) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Productos del pedido -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-box-open me-2"></i>Productos
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr style="background:rgba(222,119,125,0.08);">
                                <th class="ps-3">Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">P. Unit.</th>
                                <th class="text-end pe-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalle as $item): ?>
                            <tr>
                                <td class="ps-3 fw-semibold">
                                    <?= htmlspecialchars($item['nombre_producto']) ?>
                                </td>
                                <td class="text-center"><?= $item['cantidad'] ?></td>
                                <td class="text-end text-muted">
                                    L. <?= number_format((float)$item['precio_unit'], 2) ?>
                                </td>
                                <td class="text-end pe-3 fw-bold" style="color:#de777d;">
                                    L. <?= number_format((float)$item['subtotal'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background:rgba(222,119,125,0.06);">
                                <td colspan="3" class="text-end fw-bold ps-3">
                                    <?php if ($pedido->costo_envio > 0): ?>
                                    Subtotal productos:
                                    <?php else: ?>
                                    Total:
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3 fw-bold">
                                    L. <?= number_format((float)$pedido->subtotal, 2) ?>
                                </td>
                            </tr>
                            <?php if ($pedido->costo_envio > 0): ?>
                            <tr>
                                <td colspan="3" class="text-end ps-3 text-muted">Costo de envío:</td>
                                <td class="text-end pe-3 text-muted">
                                    L. <?= number_format((float)$pedido->costo_envio, 2) ?>
                                </td>
                            </tr>
                            <tr style="background:rgba(222,119,125,0.06);">
                                <td colspan="3" class="text-end fw-bold ps-3 fs-5">Total:</td>
                                <td class="text-end pe-3 fw-bold fs-5" style="color:#de777d;">
                                    L. <?= number_format((float)$pedido->total, 2) ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Botón WhatsApp con mensaje del estado actual -->
            <?php if ($pedido->cliente_telefono || $pedido->wa_numero): ?>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">Notificar al cliente</div>
                            <small class="text-muted">
                                Se abrirá WhatsApp con el mensaje del estado actual
                            </small>
                        </div>
                        <a href="<?= $pedido->getWhatsAppUrl($detalle) ?>"
                           target="_blank"
                           class="btn btn-success">
                            <i class="fab fa-whatsapp me-2"></i>Enviar por WhatsApp
                        </a>
                    </div>
                    <!-- Preview del mensaje -->
                    <div class="mt-3 p-3 rounded" style="background:rgba(37,211,102,0.08); border:1px solid rgba(37,211,102,0.2);">
                        <small class="text-muted d-block mb-1">
                            <i class="fas fa-eye me-1"></i>Preview del mensaje:
                        </small>
                        <pre style="font-size:0.8rem; margin:0; white-space:pre-wrap; font-family:inherit;"><?= htmlspecialchars($pedido->getMensajeWhatsApp($detalle)) ?></pre>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- ─────────────────────────────────────────────
             COLUMNA DERECHA — Estado e historial
             ───────────────────────────────────────────── -->
        <div class="col-12 col-lg-5">

            <!-- Cambiar estado -->
            <?php if (Auth::can('pedidos.gestionar') && $pedido->estado !== 'Entregado' && $pedido->estado !== 'Cancelado'): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-exchange-alt me-2"></i>Cambiar estado
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2" id="botonesEstado">
                        <?php foreach ($pedido->getEstadosSiguientes() as $siguienteEstado): ?>
                        <button type="button"
                                class="btn btn-primary btn-cambiar-estado"
                                data-id="<?= $pedido->id ?>"
                                data-estado="<?= $siguienteEstado ?>"
                                data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <i class="fas fa-arrow-right me-2"></i>
                            Pasar a: <strong><?= $siguienteEstado ?></strong>
                        </button>
                        <?php endforeach; ?>

                        <!-- Cancelar siempre disponible si no está entregado -->
                        <?php if ($pedido->estado !== 'Cancelado'): ?>
                        <button type="button"
                                class="btn btn-outline-danger btn-cambiar-estado"
                                data-id="<?= $pedido->id ?>"
                                data-estado="Cancelado"
                                data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <i class="fas fa-times-circle me-2"></i>Cancelar pedido
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Nota opcional al cambiar estado -->
                    <div class="mt-3">
                        <label for="notaEstado" class="form-label text-muted" style="font-size:0.85rem;">
                            Nota (opcional)
                        </label>
                        <input type="text"
                               class="form-control form-control-sm"
                               id="notaEstado"
                               placeholder="Motivo o comentario..."
                               maxlength="255">
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Historial de estados -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-2"></i>Historial de estados
                </div>
                <div class="card-body p-0">
                    <?php if (empty($historial)): ?>
                    <p class="text-muted text-center py-3 mb-0">Sin historial registrado.</p>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_reverse($historial) as $h): ?>
                        <div class="list-group-item py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div style="font-size:0.85rem;">
                                        <?php if ($h['estado_anterior']): ?>
                                        <span class="text-muted"><?= htmlspecialchars($h['estado_anterior']) ?></span>
                                        <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:0.7rem;"></i>
                                        <?php endif; ?>
                                        <strong><?= htmlspecialchars($h['estado_nuevo']) ?></strong>
                                    </div>
                                    <?php if ($h['nota']): ?>
                                    <small class="text-muted fst-italic">
                                        <?= htmlspecialchars($h['nota']) ?>
                                    </small>
                                    <?php endif; ?>
                                    <small class="text-muted d-block">
                                        <i class="fas fa-user me-1"></i>
                                        <?= htmlspecialchars($h['usuario_nombre'] ?? 'Sistema') ?>
                                    </small>
                                </div>
                                <small class="text-muted text-nowrap ms-2">
                                    <?= date('d/m H:i', strtotime($h['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- CSRF oculto -->
<input type="hidden" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
<input type="hidden" id="appUrl"    value="<?= APP_URL ?>">

<script>
document.addEventListener('DOMContentLoaded', function () {

    const APP_URL   = document.getElementById('appUrl').value;
    const csrfToken = document.getElementById('csrfToken').value;

    document.querySelectorAll('.btn-cambiar-estado').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id     = this.dataset.id;
            const estado = this.dataset.estado;
            const nota   = document.getElementById('notaEstado')?.value || '';
            const esCancelar = estado === 'Cancelado';

            Swal.fire({
                icon:  esCancelar ? 'warning' : 'question',
                title: esCancelar ? '¿Cancelar pedido?' : `¿Cambiar a "${estado}"?`,
                text:  esCancelar
                    ? 'Esta acción no se puede deshacer.'
                    : `El pedido pasará al estado "${estado}".`,
                showCancelButton:    true,
                confirmButtonColor:  esCancelar ? '#dc3545' : '#de777d',
                cancelButtonColor:   '#6c757d',
                confirmButtonText:   esCancelar ? 'Sí, cancelar' : 'Sí, cambiar',
                cancelButtonText:    'No'
            }).then(result => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('id',         id);
                formData.append('estado',     estado);
                formData.append('nota',       nota);

                fetch(`${APP_URL}Pedidos/cambiarEstado`, {
                    method: 'POST',
                    body:   formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon:  'success',
                            title: 'Estado actualizado',
                            text:  `Pedido en estado: ${data.estado}`,
                            confirmButtonColor: '#de777d',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon:  'error',
                            title: 'Error',
                            text:  data.message,
                            confirmButtonColor: '#de777d'
                        });
                    }
                });
            });
        });
    });

});
</script>