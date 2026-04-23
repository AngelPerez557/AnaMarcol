<div class="container-fluid py-4">

    <!-- ─── CABECERA ─────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-boxes me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <label class="text-muted" style="font-size:0.85rem;">Alerta si stock ≤</label>
            <select class="form-select form-select-sm" style="width:80px;"
                    onchange="window.location='<?= APP_URL ?>Reportes/inventario?limite='+this.value">
                <?php foreach ([3,5,10,20] as $op): ?>
                <option value="<?= $op ?>" <?= $limite === $op ? 'selected' : '' ?>><?= $op ?></option>
                <?php endforeach; ?>
            </select>
            <a href="<?= APP_URL ?>Reportes/ventas"  class="btn btn-outline-secondary btn-sm">Ventas</a>
            <a href="<?= APP_URL ?>Reportes/pedidos" class="btn btn-outline-secondary btn-sm">Pedidos</a>
        </div>
    </div>

    <!-- ─── CARDS RESUMEN ────────────────────────── -->
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['label'=>'Total productos', 'valor'=>$resumen['total_productos']?? 0, 'color'=>'#de777d'],
            ['label'=>'Activos',         'valor'=>$resumen['activos']         ?? 0, 'color'=>'#28a745'],
            ['label'=>'Sin stock',       'valor'=>$resumen['sin_stock']       ?? 0, 'color'=>'#dc3545'],
            ['label'=>'Stock bajo',      'valor'=>$resumen['stock_bajo']      ?? 0, 'color'=>'#ffc107'],
        ];
        foreach ($cards as $c):
        ?>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body text-center py-3">
                    <div class="fw-bold" style="font-size:2rem; color:<?= $c['color'] ?>;">
                        <?= $c['valor'] ?>
                    </div>
                    <div class="text-muted" style="font-size:0.85rem;"><?= $c['label'] ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">

        <!-- ─── GRÁFICA STOCK BAJO ────────────────── -->
        <?php if (!empty($stockBajo)): ?>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                    Stock bajo — Productos simples
                </div>
                <div class="card-body">
                    <canvas id="chartStockBajo" style="max-height:300px;"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ─── TABLA PRODUCTOS STOCK BAJO ───────── -->
        <div class="col-12 <?= !empty($stockBajo) ? 'col-lg-6' : '' ?>">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-2"></i>Detalle — Stock ≤ <?= $limite ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($stockBajo) && empty($variantesStockBajo)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-check-circle fa-2x mb-3 d-block text-success"></i>
                        Todos los productos tienen stock suficiente.
                    </div>
                    <?php else: ?>
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr style="background:rgba(222,119,125,0.08);">
                                <th class="ps-3">Producto</th>
                                <th class="text-center">Stock actual</th>
                                <th class="text-end pe-3">Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stockBajo as $p): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold"><?= htmlspecialchars($p['nombre']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($p['categoria_nombre']) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= (int)$p['stock'] === 0 ? 'bg-danger' : 'bg-warning text-dark' ?>">
                                        <?= $p['stock'] === '0' ? 'Sin stock' : $p['stock'] . ' uds.' ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3" style="color:#de777d;">
                                    L. <?= number_format((float)$p['precio_base'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <?php foreach ($variantesStockBajo as $v): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold"><?= htmlspecialchars($v['producto_nombre']) ?></div>
                                    <small class="text-muted">
                                        <i class="fas fa-tag me-1"></i><?= htmlspecialchars($v['variante_nombre']) ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= (int)$v['stock'] === 0 ? 'bg-danger' : 'bg-warning text-dark' ?>">
                                        <?= $v['stock'] === '0' ? 'Sin stock' : $v['stock'] . ' uds.' ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3" style="color:#de777d;">
                                    L. <?= number_format((float)$v['precio'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const stockBajo = <?= json_encode(array_values($stockBajo)) ?>;

    if (stockBajo.length > 0 && document.getElementById('chartStockBajo')) {
        const ctx = document.getElementById('chartStockBajo').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: stockBajo.map(p => p.nombre.substring(0, 20)),
                datasets: [{
                    label: 'Stock actual',
                    data: stockBajo.map(p => parseInt(p.stock)),
                    backgroundColor: stockBajo.map(p =>
                        parseInt(p.stock) === 0
                            ? 'rgba(220,53,69,0.4)'
                            : 'rgba(255,193,7,0.4)'
                    ),
                    borderColor: stockBajo.map(p =>
                        parseInt(p.stock) === 0 ? '#dc3545' : '#ffc107'
                    ),
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

});
</script>