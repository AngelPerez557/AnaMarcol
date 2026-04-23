<div class="container-fluid py-4">

    <!-- ─── CABECERA ─────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-chart-line me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= APP_URL ?>Reportes/pedidos"    class="btn btn-outline-secondary btn-sm">Pedidos</a>
            <a href="<?= APP_URL ?>Reportes/inventario" class="btn btn-outline-secondary btn-sm">Inventario</a>
        </div>
    </div>

    <!-- ─── CARDS RESUMEN ────────────────────────── -->
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['label'=>'Total ventas',    'valor'=>$resumen['total_ventas']  ?? 0,                                'icono'=>'fas fa-shopping-cart', 'color'=>'#de777d', 'formato'=>'numero'],
            ['label'=>'Ingresos totales','valor'=>$resumen['total_monto']   ?? 0,                                'icono'=>'fas fa-money-bill-wave','color'=>'#28a745','formato'=>'lempira'],
            ['label'=>'Venta promedio',  'valor'=>$resumen['promedio_venta']?? 0,                                'icono'=>'fas fa-chart-bar',      'color'=>'#007bff','formato'=>'lempira'],
            ['label'=>'Total hoy',       'valor'=>$resumen['total_hoy']     ?? 0,                                'icono'=>'fas fa-calendar-day',   'color'=>'#fd7e14','formato'=>'lempira'],
            ['label'=>'Total este mes',  'valor'=>$resumen['total_mes']     ?? 0,                                'icono'=>'fas fa-calendar-alt',   'color'=>'#6f42c1','formato'=>'lempira'],
        ];
        foreach ($cards as $card):
        ?>
        <div class="col-6 col-md-4 col-xl">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:44px; height:44px; flex-shrink:0;
                                background:<?= $card['color'] ?>22;">
                        <i class="<?= $card['icono'] ?>" style="color:<?= $card['color'] ?>;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.75rem;"><?= $card['label'] ?></div>
                        <div class="fw-bold" style="font-size:1.1rem; color:<?= $card['color'] ?>;">
                            <?= $card['formato'] === 'lempira'
                                ? 'L. ' . number_format((float)$card['valor'], 2)
                                : number_format((int)$card['valor']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4 mb-4">

        <!-- ─── GRÁFICA VENTAS POR DÍA ───────────── -->
        <div class="col-12 col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-line me-2"></i>Ventas últimos 30 días</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-primary active" id="btnDias">Por día</button>
                        <button type="button" class="btn btn-outline-primary" id="btnMeses">Por mes</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="chartVentas" style="max-height:280px;"></canvas>
                </div>
            </div>
        </div>

        <!-- ─── GRÁFICA MÉTODOS DE PAGO ──────────── -->
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i>Métodos de pago
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="chartMetodos" style="max-height:250px;"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- ─── TOP PRODUCTOS ────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-trophy me-2"></i>Top 10 productos más vendidos
        </div>
        <div class="row g-0">
            <div class="col-12 col-lg-6">
                <div class="card-body">
                    <canvas id="chartTopProductos" style="max-height:300px;"></canvas>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr style="background:rgba(222,119,125,0.08);">
                                <th class="ps-3">#</th>
                                <th>Producto</th>
                                <th class="text-center">Vendidos</th>
                                <th class="text-end pe-3">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProductos as $i => $p): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($p['nombre_producto']) ?></td>
                                <td class="text-center">
                                    <span class="badge" style="background:#de777d;">
                                        <?= $p['total_vendido'] ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3" style="color:#de777d; font-weight:600;">
                                    L. <?= number_format((float)$p['total_monto'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const colorPrimario = '#de777d';
    const colorFondo    = 'rgba(222,119,125,0.15)';

    // ── Datos desde PHP ───────────────────────────
    const datosDia = <?= json_encode(array_values($ventasPorDia)) ?>;
    const datosMes = <?= json_encode(array_values($ventasPorMes)) ?>;
    const datosMetodos = <?= json_encode(array_values($ventasPorMetodo)) ?>;
    const datosTop     = <?= json_encode(array_values($topProductos)) ?>;

    // ── Gráfica ventas por día/mes ────────────────
    const ctxVentas = document.getElementById('chartVentas').getContext('2d');
    let chartVentas = new Chart(ctxVentas, {
        type: 'line',
        data: {
            labels: datosDia.map(d => d.fecha),
            datasets: [{
                label: 'Total (L.)',
                data: datosDia.map(d => parseFloat(d.total_monto)),
                borderColor: colorPrimario,
                backgroundColor: colorFondo,
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colorPrimario,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => 'L. ' + v.toLocaleString() } }
            }
        }
    });

    // Toggle día / mes
    document.getElementById('btnDias').addEventListener('click', function () {
        chartVentas.data.labels   = datosDia.map(d => d.fecha);
        chartVentas.data.datasets[0].data = datosDia.map(d => parseFloat(d.total_monto));
        chartVentas.update();
        this.classList.add('active', 'btn-primary'); this.classList.remove('btn-outline-primary');
        document.getElementById('btnMeses').classList.remove('active','btn-primary');
        document.getElementById('btnMeses').classList.add('btn-outline-primary');
    });

    document.getElementById('btnMeses').addEventListener('click', function () {
        chartVentas.data.labels   = datosMes.map(d => d.mes_label);
        chartVentas.data.datasets[0].data = datosMes.map(d => parseFloat(d.total_monto));
        chartVentas.update();
        this.classList.add('active', 'btn-primary'); this.classList.remove('btn-outline-primary');
        document.getElementById('btnDias').classList.remove('active','btn-primary');
        document.getElementById('btnDias').classList.add('btn-outline-primary');
    });

    // ── Gráfica métodos de pago ───────────────────
    const ctxMetodos = document.getElementById('chartMetodos').getContext('2d');
    new Chart(ctxMetodos, {
        type: 'doughnut',
        data: {
            labels: datosMetodos.map(d => d.metodo_pago),
            datasets: [{
                data: datosMetodos.map(d => parseFloat(d.total_monto)),
                backgroundColor: ['#de777d','#28a745','#007bff','#fd7e14'],
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: ctx => ` L. ${ctx.raw.toLocaleString()}` } }
            }
        }
    });

    // ── Gráfica top productos ─────────────────────
    const ctxTop = document.getElementById('chartTopProductos').getContext('2d');
    new Chart(ctxTop, {
        type: 'bar',
        data: {
            labels: datosTop.map(d => d.nombre_producto.substring(0, 15)),
            datasets: [{
                label: 'Unidades vendidas',
                data: datosTop.map(d => parseInt(d.total_vendido)),
                backgroundColor: colorFondo,
                borderColor: colorPrimario,
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });

});
</script>