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
            <small class="text-muted">
                <?= count($pedidos) ?> pedido<?= count($pedidos) !== 1 ? 's' : '' ?>
            </small>
        </div>
    </div>

    <!-- ─────────────────────────────────────────────
         TABS DE ESTADOS
         ───────────────────────────────────────────── -->
    <div class="mb-4">
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= APP_URL ?>Pedidos/index"
               class="btn btn-sm <?= $pageTitle === 'Todos los Pedidos' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="fas fa-list me-1"></i>Todos
            </a>
            <a href="<?= APP_URL ?>Pedidos/pendientes"
               class="btn btn-sm <?= $pageTitle === 'Pedidos Pendientes' ? 'btn-warning' : 'btn-outline-warning' ?>">
                <i class="fas fa-clock me-1"></i>Pendientes
            </a>
            <a href="<?= APP_URL ?>Pedidos/preparacion"
               class="btn btn-sm <?= $pageTitle === 'Pedidos En Preparación' ? 'btn-info' : 'btn-outline-info' ?>">
                <i class="fas fa-box-open me-1"></i>En preparación
            </a>
            <a href="<?= APP_URL ?>Pedidos/listos"
               class="btn btn-sm <?= $pageTitle === 'Pedidos Listos' ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="fas fa-check-circle me-1"></i>Listos
            </a>
            <a href="<?= APP_URL ?>Pedidos/camino"
               class="btn btn-sm <?= $pageTitle === 'Pedidos En Camino' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                <i class="fas fa-truck me-1"></i>En camino
            </a>
            <a href="<?= APP_URL ?>Pedidos/entregados"
               class="btn btn-sm <?= $pageTitle === 'Pedidos Entregados' ? 'btn-success' : 'btn-outline-success' ?>">
                <i class="fas fa-check-double me-1"></i>Entregados
            </a>
        </div>
    </div>

    <!-- ─────────────────────────────────────────────
         BUSCADOR
         ───────────────────────────────────────────── -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               class="form-control border-start-0"
                               id="buscarPedido"
                               placeholder="Buscar por código o cliente...">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-select" id="filtroEntrega">
                        <option value="">Todos los tipos</option>
                        <option value="Retiro">Retiro en tienda</option>
                        <option value="Envio">Envío a domicilio</option>
                    </select>
                </div>
                <div class="col-6 col-md-4 text-end">
                    <small class="text-muted" id="contadorVisible">
                        Mostrando <?= count($pedidos) ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- ─────────────────────────────────────────────
         LISTADO
         ───────────────────────────────────────────── -->
    <?php if (empty($pedidos)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-shopping-bag fa-3x mb-3 d-block" style="color:#de777d;opacity:0.4;"></i>
        No hay pedidos en esta categoría.
    </div>
    <?php else: ?>
    <div class="row g-3" id="gridPedidos">
        <?php foreach ($pedidos as $pedido): ?>
        <div class="col-12 col-md-6 col-xl-4 pedido-item"
             data-codigo="<?= strtolower($pedido->codigo ?? '') ?>"
             data-cliente="<?= strtolower(htmlspecialchars($pedido->cliente_nombre ?? 'consumidor final')) ?>"
             data-entrega="<?= $pedido->tipo_entrega ?>">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <span class="fw-bold" style="color:#de777d;">
                        <?= $pedido->getCodigoFormateado() ?>
                    </span>
                    <span class="badge <?= $pedido->getBadgeEstado() ?>">
                        <i class="<?= $pedido->getIconoEstado() ?> me-1"></i>
                        <?= $pedido->estado ?>
                    </span>
                </div>
                <div class="card-body py-2">
                    <!-- Cliente -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-user text-muted" style="width:16px;"></i>
                        <span class="fw-semibold">
                            <?= htmlspecialchars($pedido->cliente_nombre ?? 'Consumidor final') ?>
                        </span>
                    </div>
                    <!-- Tipo entrega -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas <?= $pedido->esEnvio() ? 'fa-truck' : 'fa-store' ?> text-muted"
                           style="width:16px;"></i>
                        <span class="text-muted" style="font-size:0.85rem;">
                            <?= $pedido->esEnvio() ? 'Envío a domicilio' : 'Retiro en tienda' ?>
                        </span>
                    </div>
                    <!-- Fecha -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-clock text-muted" style="width:16px;"></i>
                        <span class="text-muted" style="font-size:0.85rem;">
                            <?= date('d/m/Y H:i', strtotime($pedido->created_at)) ?>
                        </span>
                    </div>
                    <!-- Total -->
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <span class="text-muted" style="font-size:0.85rem;">Total</span>
                        <span class="fw-bold" style="color:#de777d; font-size:1.1rem;">
                            <?= $pedido->getTotalFormateado() ?>
                        </span>
                    </div>
                </div>
                <div class="card-footer d-flex gap-2 justify-content-between align-items-center py-2">
                    <!-- Botón WhatsApp si tiene teléfono -->
                    <?php if ($pedido->cliente_telefono || $pedido->wa_numero): ?>
                    <a href="<?= $pedido->getWhatsAppUrl() ?>"
                       target="_blank"
                       class="btn btn-sm btn-outline-success"
                       title="Notificar por WhatsApp">
                        <i class="fab fa-whatsapp me-1"></i>WhatsApp
                    </a>
                    <?php else: ?>
                    <span></span>
                    <?php endif; ?>

                    <a href="<?= APP_URL ?>Pedidos/detalle/<?= $pedido->id ?>"
                       class="btn btn-sm btn-primary">
                        <i class="fas fa-eye me-1"></i>Ver detalle
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const buscar       = document.getElementById('buscarPedido');
    const filtroEntrega= document.getElementById('filtroEntrega');
    const contador     = document.getElementById('contadorVisible');
    const items        = document.querySelectorAll('.pedido-item');

    function filtrar() {
        const texto   = buscar.value.toLowerCase();
        const entrega = filtroEntrega.value;
        let visible   = 0;

        items.forEach(item => {
            const codigo   = item.dataset.codigo   || '';
            const cliente  = item.dataset.cliente  || '';
            const tipoEnt  = item.dataset.entrega  || '';

            const okTexto   = codigo.includes(texto) || cliente.includes(texto);
            const okEntrega = !entrega || tipoEnt === entrega;

            if (okTexto && okEntrega) {
                item.style.display = '';
                visible++;
            } else {
                item.style.display = 'none';
            }
        });

        contador.textContent = `Mostrando ${visible}`;
    }

    buscar.addEventListener('input', filtrar);
    filtroEntrega.addEventListener('change', filtrar);

});
</script>