<div class="container-fluid py-4">

    <!-- CABECERA -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-headset me-2" style="color:#de777d;"></i>Soporte DeskCod
            </h4>
            <small class="text-muted">Gestión de tickets con el equipo de DeskCod</small>
        </div>
        <a href="<?= APP_URL ?>Soporte/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo ticket
        </a>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
        <i class="fas fa-exclamation-circle fa-lg"></i>
        <div>
            <strong>No se pudo conectar con DeskCod</strong><br>
            <small><?= htmlspecialchars($error) ?></small>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($tickets) && empty($error)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-headset fa-3x mb-3 d-block" style="opacity:0.3; color:#de777d;"></i>
        No tienes tickets de soporte abiertos.
        <br>
        <a href="<?= APP_URL ?>Soporte/registry" class="btn btn-primary mt-3">
            <i class="fas fa-plus me-2"></i>Crear primer ticket
        </a>
    </div>
    <?php elseif (!empty($tickets)): ?>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0"
                               id="buscador" placeholder="Buscar ticket...">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select form-select-sm" id="filtroEstado">
                        <option value="">Todos los estados</option>
                        <option value="abierto">Abierto</option>
                        <option value="en_proceso">En proceso</option>
                        <option value="esperando_cliente">Esperando respuesta</option>
                        <option value="resuelto">Resuelto</option>
                        <option value="cerrado">Cerrado</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select form-select-sm" id="filtroTipo">
                        <option value="">Todos los tipos</option>
                        <option value="error">Error</option>
                        <option value="modificacion">Modificación</option>
                        <option value="nueva_funcion">Nueva función</option>
                        <option value="consulta">Consulta</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button class="btn btn-sm btn-outline-secondary w-100" onclick="limpiarFiltros()">
                        <i class="fas fa-times me-1"></i>Limpiar
                    </button>
                </div>
                <div class="col-12 col-md-2 text-end">
                    <small class="text-muted" id="contador"><?= count($tickets) ?> tickets</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista tickets -->
    <div class="row g-3" id="listaTickets">
        <?php foreach ($tickets as $t):
            $estado    = $t['estado']    ?? 'abierto';
            $prioridad = $t['prioridad'] ?? 'media';
            $tipo      = $t['tipo']      ?? 'consulta';

            $estadoConfig = [
                'abierto'           => ['label' => 'Abierto',            'color' => '#28a745', 'bg' => 'rgba(40,167,69,0.1)'],
                'en_proceso'        => ['label' => 'En proceso',         'color' => '#de777d', 'bg' => 'rgba(222,119,125,0.1)'],
                'esperando_cliente' => ['label' => 'Esperando respuesta','color' => '#ffc107', 'bg' => 'rgba(255,193,7,0.1)'],
                'resuelto'          => ['label' => 'Resuelto',           'color' => '#17a2b8', 'bg' => 'rgba(23,162,184,0.1)'],
                'cerrado'           => ['label' => 'Cerrado',            'color' => '#6c757d', 'bg' => 'rgba(108,117,125,0.1)'],
            ];
            $prioConfig = [
                'critica' => ['label' => 'Crítica', 'color' => '#dc3545'],
                'alta'    => ['label' => 'Alta',    'color' => '#fd7e14'],
                'media'   => ['label' => 'Media',   'color' => '#ffc107'],
                'baja'    => ['label' => 'Baja',    'color' => '#6c757d'],
            ];
            $tipoConfig = [
                'error'         => ['label' => 'Error',         'icon' => 'fas fa-bug'],
                'modificacion'  => ['label' => 'Modificación',  'icon' => 'fas fa-edit'],
                'nueva_funcion' => ['label' => 'Nueva función', 'icon' => 'fas fa-star'],
                'consulta'      => ['label' => 'Consulta',      'icon' => 'fas fa-question-circle'],
            ];

            $ec = $estadoConfig[$estado]    ?? $estadoConfig['abierto'];
            $pc = $prioConfig[$prioridad]   ?? $prioConfig['media'];
            $tc = $tipoConfig[$tipo]        ?? $tipoConfig['consulta'];
        ?>
        <div class="col-12 ticket-item"
             data-estado="<?= $estado ?>"
             data-tipo="<?= $tipo ?>"
             data-buscar="<?= strtolower(htmlspecialchars(($t['titulo'] ?? '') . ' ' . ($t['descripcion'] ?? ''))) ?>">
            <div class="card" style="border-left: 4px solid <?= $pc['color'] ?>;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div class="flex-fill">
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="fw-bold text-muted" style="font-size:0.85rem;">
                                    #<?= str_pad($t['id'] ?? 0, 4, '0', STR_PAD_LEFT) ?>
                                </span>
                                <span class="badge"
                                      style="background:<?= $ec['bg'] ?>; color:<?= $ec['color'] ?>; border:1px solid <?= $ec['color'] ?>;">
                                    <?= $ec['label'] ?>
                                </span>
                                <span class="badge" style="background:rgba(222,119,125,0.1); color:#de777d;">
                                    <i class="<?= $tc['icon'] ?> me-1"></i><?= $tc['label'] ?>
                                </span>
                                <span class="badge" style="background:<?= $pc['color'] ?>; color:#fff; font-size:0.7rem;">
                                    <?= $pc['label'] ?>
                                </span>
                            </div>
                            <h6 class="fw-semibold mb-1"><?= htmlspecialchars($t['titulo'] ?? '') ?></h6>
                            <small class="text-muted">
                                <?= htmlspecialchars(substr($t['descripcion'] ?? '', 0, 100)) ?>
                                <?= strlen($t['descripcion'] ?? '') > 100 ? '...' : '' ?>
                            </small>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <small class="text-muted d-block mb-2">
                                <?= !empty($t['created_at']) ? date('d/m/Y H:i', strtotime($t['created_at'])) : '' ?>
                            </small>
                            <a href="<?= APP_URL ?>Soporte/ver/<?= $t['id'] ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>Ver detalle
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($t['comentarios_count'])): ?>
                    <div class="mt-2 pt-2 border-top">
                        <small class="text-muted">
                            <i class="fas fa-comments me-1" style="color:#de777d;"></i>
                            <?= $t['comentarios_count'] ?> respuesta<?= $t['comentarios_count'] !== 1 ? 's' : '' ?>
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscador    = document.getElementById('buscador');
    const filtroEstado = document.getElementById('filtroEstado');
    const filtroTipo   = document.getElementById('filtroTipo');
    const contador     = document.getElementById('contador');

    function filtrar() {
        const txt   = buscador?.value.toLowerCase() || '';
        const est   = filtroEstado?.value || '';
        const tipo  = filtroTipo?.value  || '';
        let visibles = 0;

        document.querySelectorAll('.ticket-item').forEach(item => {
            const ok = item.dataset.buscar.includes(txt)
                && (!est  || item.dataset.estado === est)
                && (!tipo || item.dataset.tipo   === tipo);
            item.style.display = ok ? '' : 'none';
            if (ok) visibles++;
        });

        if (contador) contador.textContent = `${visibles} ticket${visibles !== 1 ? 's' : ''}`;
    }

    window.limpiarFiltros = function () {
        if (buscador)     buscador.value     = '';
        if (filtroEstado) filtroEstado.value = '';
        if (filtroTipo)   filtroTipo.value   = '';
        filtrar();
    };

    buscador?.addEventListener('input', filtrar);
    filtroEstado?.addEventListener('change', filtrar);
    filtroTipo?.addEventListener('change', filtrar);
});
</script>