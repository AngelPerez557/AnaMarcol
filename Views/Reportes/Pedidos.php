<div class="container-fluid py-4">

    <!-- ─── CABECERA ─────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-shopping-bag me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= APP_URL ?>Reportes/ventas"     class="btn btn-outline-secondary btn-sm">Ventas</a>
            <a href="<?= APP_URL ?>Reportes/inventario" class="btn btn-outline-secondary btn-sm">Inventario</a>
        </div>
    </div>

    <!-- ─── CARDS RESUMEN ────────────────────────── -->
    <div class="row g-3 mb-4">
        <?php
        $estados = [
            ['label'=>'Total pedidos', 'valor'=>$resumen['total']       ?? 0, 'color'=>'#de777d'],
            ['label'=>'Pendientes',    'valor'=>$resumen['pendientes']   ?? 0, 'color'=>'#ffc107'],
            ['label'=>'Preparación',   'valor'=>$resumen['preparacion']  ?? 0, 'color'=>'#17a2b8'],
            ['label'=>'Listos',        'valor'=>$resumen['listos']       ?? 0, 'color'=>'#007bff'],
            ['label'=>'En camino',     'valor'=>$resumen['en_camino']    ?? 0, 'color'=>'#6f42c1'],
            ['label'=>'Entregados',    'valor'=>$resumen['entregados']   ?? 0, 'color'=>'#28a745'],
            ['label'=>'Cancelados',    'valor'=>$resumen['cancelados']   ?? 0, 'color'=>'#dc3545'],
        ];
        foreach ($estados as $e):
        ?>
        <div class="col-6 col-md-4 col-xl">
            <div class="card h-100">
                <div class="card-body text-center py-3">
                    <div class="fw-bold" style="font-size:1.8rem; color:<?= $e['color'] ?>;">
                        <?= $e['valor'] ?>
                    </div>
                    <div class="text-muted" style="font-size:0.8rem;"><?= $e['label'] ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">

        <!-- ─── GRÁFICA DONUT ESTADOS ─────────────── -->
        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i>Pedidos por estado
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="chartEstados" style="max-height:280px;"></canvas>
                </div>
            </div>
        </div>

        <!-- ─── GRÁFICA LÍNEA PEDIDOS POR DÍA ─────── -->
        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i>Pedidos últimos 30 días
                </div>
                <div class="card-body">
                    <canvas id="chartPedidosDia" style="max-height:280px;"></canvas>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const datosEstados = <?= json_encode(array_values($pedidosPorEstado)) ?>;
    const datosDia     = <?= json_encode(array_values($pedidosPorDia)) ?>;

    const coloresEstados = {
        'Pendiente':      '#ffc107',
        'En preparacion': '#17a2b8',
        'Listo':          '#007bff',
        'En camino':      '#6f42c1',
        'Entregado':      '#28a745',
        'Cancelado':      '#dc3545',
    };

    // ── Donut estados ─────────────────────────────
    const ctxEstados = document.getElementById('chartEstados').getContext('2d');
    new Chart(ctxEstados, {
        type: 'doughnut',
        data: {
            labels: datosEstados.map(d => d.estado),
            datasets: [{
                data: datosEstados.map(d => parseInt(d.total)),
                backgroundColor: datosEstados.map(d => coloresEstados[d.estado] ?? '#aaa'),
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: ctx => ` ${ctx.raw} pedidos` } }
            }
        }
    });

    // ── Línea pedidos por día ─────────────────────
    const ctxDia = document.getElementById('chartPedidosDia').getContext('2d');
    new Chart(ctxDia, {
        type: 'bar',
        data: {
            labels: datosDia.map(d => d.fecha),
            datasets: [{
                label: 'Pedidos',
                data: datosDia.map(d => parseInt(d.total_pedidos)),
                backgroundColor: 'rgba(222,119,125,0.3)',
                borderColor: '#de777d',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

});
</script>