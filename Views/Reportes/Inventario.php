<div class="container-fluid py-4">

    <!-- ─── CABECERA ─────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-boxes me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <button onclick="descargarPDFInventario()" class="btn btn-sm btn-danger">
                <i class="fas fa-file-pdf me-1"></i>PDF
            </button>
            <button onclick="descargarExcelInventario()" class="btn btn-sm btn-success">
                <i class="fas fa-file-excel me-1"></i>Excel
            </button>
            <label class="text-muted" style="font-size:0.85rem;">Alerta si stock ≤</label>
            <select class="form-select form-select-sm" style="width:80px;"
                    onchange="window.location='<?= APP_URL ?>Reportes/inventario?limite='+this.value">
                <?php foreach ([3,5,10,20] as $op): ?>
                <option value="<?= $op ?>" <?= $limite === $op ? 'selected' : '' ?>><?= $op ?></option>
                <?php endforeach; ?>
            </select>
            <a href="<?= APP_URL ?>Reportes/ventas"    class="btn btn-outline-secondary btn-sm">Ventas</a>
            <a href="<?= APP_URL ?>Reportes/pedidos"   class="btn btn-outline-secondary btn-sm">Pedidos</a>
        </div>
    </div>

    <!-- ─── CARDS RESUMEN ────────────────────────── -->
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['label'=>'Total productos','valor'=>$resumen['total_productos']??0,'color'=>'#de777d'],
            ['label'=>'Activos',        'valor'=>$resumen['activos']        ??0,'color'=>'#28a745'],
            ['label'=>'Sin stock',      'valor'=>$resumen['sin_stock']      ??0,'color'=>'#dc3545'],
            ['label'=>'Stock bajo',     'valor'=>$resumen['stock_bajo']     ??0,'color'=>'#ffc107'],
        ];
        foreach ($cards as $c):
        ?>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body text-center py-3">
                    <div class="fw-bold" style="font-size:2rem;color:<?= $c['color'] ?>;">
                        <?= $c['valor'] ?>
                    </div>
                    <div class="text-muted" style="font-size:0.85rem;"><?= $c['label'] ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">

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

        <!-- ─── TABLA CON PAGINACIÓN ─────────────── -->
        <div class="col-12 <?= !empty($stockBajo) ? 'col-lg-6' : '' ?>">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-list me-2"></i>Detalle — Stock ≤ <?= $limite ?>
                    </span>
                    <?php if (!empty($stockBajo) || !empty($variantesStockBajo)): ?>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted">Por página:</small>
                        <select class="form-select form-select-sm" id="porPaginaInv" style="width:auto;">
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($stockBajo) && empty($variantesStockBajo)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-check-circle fa-2x mb-3 d-block text-success"></i>
                        Todos los productos tienen stock suficiente.
                    </div>
                    <?php else: ?>

                    <!-- Buscador interno -->
                    <div class="px-3 pt-3 pb-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0"
                                   id="buscarInventario"
                                   placeholder="Filtrar por nombre...">
                        </div>
                    </div>

                    <table class="table table-sm align-middle mb-0" id="tablaInventario">
                        <thead>
                            <tr style="background:rgba(222,119,125,0.08);">
                                <th class="ps-3">Producto</th>
                                <th class="text-center">Stock actual</th>
                                <th class="text-end pe-3">Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stockBajo as $p): ?>
                            <tr class="inv-row" data-nombre="<?= strtolower(htmlspecialchars($p['nombre'])) ?>">
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
                            <tr class="inv-row" data-nombre="<?= strtolower(htmlspecialchars($v['producto_nombre'] . ' ' . $v['variante_nombre'])) ?>">
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

                    <!-- Paginación de la tabla -->
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top flex-wrap gap-2">
                        <small class="text-muted" id="infoInv"></small>
                        <nav><ul class="pagination pagination-sm mb-0" id="navInv"></ul></nav>
                    </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Gráfica stock bajo ────────────────────────
    const stockBajo = <?= json_encode(array_values($stockBajo)) ?>;
    if (stockBajo.length > 0 && document.getElementById('chartStockBajo')) {
        const ctx = document.getElementById('chartStockBajo').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: stockBajo.map(p => p.nombre.substring(0, 20)),
                datasets: [{
                    label: 'Stock actual',
                    data:  stockBajo.map(p => parseInt(p.stock)),
                    backgroundColor: stockBajo.map(p => parseInt(p.stock) === 0 ? 'rgba(220,53,69,0.4)' : 'rgba(255,193,7,0.4)'),
                    borderColor:     stockBajo.map(p => parseInt(p.stock) === 0 ? '#dc3545' : '#ffc107'),
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

    // ── Paginación tabla inventario ───────────────
    const filas      = [...document.querySelectorAll('.inv-row')];
    const infoEl     = document.getElementById('infoInv');
    const navEl      = document.getElementById('navInv');
    const porPagSel  = document.getElementById('porPaginaInv');
    const buscarEl   = document.getElementById('buscarInventario');

    if (!filas.length || !navEl) return; // sin filas → no paginar

    let porPagina  = 10;
    let pagActual  = 1;
    let filtradas  = filas.map((_, i) => i);

    // Filtro por nombre
    buscarEl?.addEventListener('input', function () {
        const txt = this.value.toLowerCase().trim();
        filtradas = [];
        filas.forEach((fila, i) => {
            if (!txt || fila.dataset.nombre.includes(txt)) filtradas.push(i);
        });
        pagActual = 1;
        render();
    });

    // Por página
    porPagSel?.addEventListener('change', function () {
        porPagina = parseInt(this.value);
        pagActual = 1;
        render();
    });

    function render() {
        const total   = filtradas.length;
        const paginas = Math.max(1, Math.ceil(total / porPagina));
        if (pagActual > paginas) pagActual = paginas;

        const inicio  = (pagActual - 1) * porPagina;
        const fin     = Math.min(inicio + porPagina, total);
        const vis     = new Set(filtradas.slice(inicio, fin));

        filas.forEach((el, i) => { el.style.display = vis.has(i) ? '' : 'none'; });

        infoEl.textContent = total > 0
            ? `Página ${pagActual} de ${paginas} — ${inicio + 1}–${fin} de ${total} productos`
            : 'Sin resultados';

        renderNav(paginas);
    }

    function renderNav(paginas) {
        navEl.innerHTML = '';
        if (paginas <= 1) return;

        const btn = (lbl, page, dis, act) => {
            const li = document.createElement('li');
            li.className = `page-item${dis ? ' disabled' : ''}${act ? ' active' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link'; a.href = '#'; a.innerHTML = lbl;
            if (!dis && !act) {
                a.addEventListener('click', e => {
                    e.preventDefault();
                    pagActual = page;
                    render();
                    document.getElementById('tablaInventario')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }
            li.appendChild(a); return li;
        };

        navEl.appendChild(btn('&laquo;', pagActual - 1, pagActual === 1, false));

        let nums = paginas <= 7
            ? Array.from({ length: paginas }, (_, i) => i + 1)
            : [1];

        if (paginas > 7) {
            if (pagActual > 3) nums.push('…');
            for (let i = Math.max(2, pagActual - 1); i <= Math.min(paginas - 1, pagActual + 1); i++) nums.push(i);
            if (pagActual < paginas - 2) nums.push('…');
            nums.push(paginas);
        }

        nums.forEach(n => {
            if (n === '…') {
                const li = document.createElement('li');
                li.className = 'page-item disabled';
                li.innerHTML = '<a class="page-link">…</a>';
                navEl.appendChild(li);
            } else {
                navEl.appendChild(btn(n, n, false, n === pagActual));
            }
        });

        navEl.appendChild(btn('&raquo;', pagActual + 1, pagActual === paginas, false));
    }

    // Init
    render();
});

// ── Descarga PDF ──────────────────────────────────
function descargarPDFInventario() {
    const { jsPDF } = window.jspdf;
    const doc  = new jsPDF();
    const fecha = new Date().toLocaleDateString('es-HN');

    doc.setFillColor(222, 119, 125);
    doc.rect(0, 0, 210, 28, 'F');
    doc.setTextColor(255,255,255);
    doc.setFontSize(16); doc.setFont('helvetica','bold');
    doc.text('Ana Marcol Makeup Studio', 14, 12);
    doc.setFontSize(11); doc.setFont('helvetica','normal');
    doc.text('Reporte de Inventario — ' + fecha, 14, 22);
    doc.setTextColor(0,0,0);

    const resumenData = <?= json_encode($resumen) ?>;
    doc.autoTable({
        startY: 36,
        head: [['Indicador', 'Valor']],
        body: [
            ['Total productos', resumenData.total_productos ?? 0],
            ['Activos',         resumenData.activos         ?? 0],
            ['Sin stock',       resumenData.sin_stock       ?? 0],
            ['Stock bajo',      resumenData.stock_bajo      ?? 0],
        ],
        styles: { fontSize: 10 },
        headStyles: { fillColor: [222,119,125], textColor: 255 },
        alternateRowStyles: { fillColor: [253,240,241] },
        margin: { left: 14, right: 14 },
    });

    // El PDF exporta TODOS los productos sin importar la página actual
    const stockBajo          = <?= json_encode(array_values($stockBajo)) ?>;
    const variantesStockBajo = <?= json_encode(array_values($variantesStockBajo)) ?>;
    const todosBajos = [
        ...stockBajo.map(p => [p.nombre, p.categoria_nombre, p.stock + ' uds.', 'L. ' + parseFloat(p.precio_base).toFixed(2)]),
        ...variantesStockBajo.map(v => [v.producto_nombre + ' — ' + v.variante_nombre, 'Variante', v.stock + ' uds.', 'L. ' + parseFloat(v.precio).toFixed(2)]),
    ];

    if (todosBajos.length > 0) {
        doc.autoTable({
            startY: doc.lastAutoTable.finalY + 10,
            head: [['Producto', 'Categoría', 'Stock', 'Precio']],
            body: todosBajos,
            styles: { fontSize: 9 },
            headStyles: { fillColor: [222,119,125], textColor: 255 },
            alternateRowStyles: { fillColor: [253,240,241] },
            margin: { left: 14, right: 14 },
            columnStyles: { 2: { halign: 'center' }, 3: { halign: 'right' } }
        });
    }

    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8); doc.setTextColor(150,150,150);
        doc.text(`Generado el ${fecha} | Ana Marcol Makeup Studio | Página ${i} de ${pageCount}`, 14, 290);
    }

    doc.save(`reporte-inventario-${fecha.replace(/\//g,'-')}.pdf`);
}

// ── Descarga Excel — Reporte de Inventario v2 ────────────────
//
// Hojas incluidas:
//   1. Resumen general (KPIs + valor total inventario)
//   2. Catálogo completo  — TODOS los productos con stock y valor
//   3. Por categoría      — totalizado agrupado
//   4. Sin stock          — solo productos en cero
//   5. Stock bajo         — productos críticos (limite configurable)
//   6. Variantes          — variantes con stock por debajo del límite
function descargarExcelInventario() {
    const wb    = XLSX.utils.book_new();
    const fecha = new Date().toLocaleDateString('es-HN');
    const hora  = new Date().toLocaleTimeString('es-HN');

    const resumenData            = <?= json_encode($resumen ?? []) ?>;
    const stockBajo              = <?= json_encode(array_values($stockBajo ?? [])) ?>;
    const variantesStockBajo     = <?= json_encode(array_values($variantesStockBajo ?? [])) ?>;
    const inventarioCompleto     = <?= json_encode(array_values($inventarioCompleto ?? [])) ?>;
    const inventarioCategorias   = <?= json_encode(array_values($inventarioCategorias ?? [])) ?>;
    const valorTotalInventario   = <?= (float) ($valorTotalInventario ?? 0) ?>;

    // Helper — ancho de columnas para mejor legibilidad
    const setColWidths = (ws, widths) => {
        ws['!cols'] = widths.map(w => ({ wch: w }));
    };

    // ─── HOJA 1: Resumen ───────────────────────────
    const wsResumen = XLSX.utils.aoa_to_sheet([
        ['ANA MARCOL MAKEUP STUDIO — REPORTE DE INVENTARIO'],
        ['Generado:', fecha + ' ' + hora],
        [],
        ['RESUMEN GENERAL'],
        ['Indicador', 'Valor'],
        ['Total productos',              resumenData.total_productos ?? 0],
        ['Productos activos',            resumenData.activos         ?? 0],
        ['Sin stock',                    resumenData.sin_stock       ?? 0],
        ['Stock bajo (≤ 5)',             resumenData.stock_bajo      ?? 0],
        ['Categorías',                   inventarioCategorias.length],
        ['Valor total del inventario',   'L. ' + valorTotalInventario.toFixed(2)],
    ]);
    setColWidths(wsResumen, [32, 22]);
    XLSX.utils.book_append_sheet(wb, wsResumen, 'Resumen');

    // ─── HOJA 2: Catálogo completo ─────────────────
    const wsCatalogo = XLSX.utils.aoa_to_sheet([
        ['CATÁLOGO COMPLETO DE PRODUCTOS'],
        [],
        ['ID', 'Código de barras', 'Producto', 'Categoría', 'Precio base (L.)', 'Stock', 'Valor del stock (L.)', 'Estado', 'Visible tienda'],
        ...inventarioCompleto.map(p => [
            parseInt(p.id),
            p.codigo_barras || '—',
            p.nombre,
            p.categoria_nombre || '—',
            parseFloat(p.precio_base ?? 0),
            parseInt(p.stock_total ?? 0),
            parseFloat(p.valor_inventario ?? 0),
            p.estado,
            parseInt(p.visible_tienda) === 1 ? 'Sí' : 'No',
        ])
    ]);
    setColWidths(wsCatalogo, [6, 18, 40, 22, 14, 8, 18, 12, 12]);
    XLSX.utils.book_append_sheet(wb, wsCatalogo, 'Catálogo completo');

    // ─── HOJA 3: Por categoría ─────────────────────
    const wsCategorias = XLSX.utils.aoa_to_sheet([
        ['INVENTARIO POR CATEGORÍA'],
        [],
        ['Categoría', 'Total productos', 'Activos', 'Stock total (uds.)', 'Valor inventario (L.)'],
        ...inventarioCategorias.map(c => [
            c.categoria_nombre,
            parseInt(c.total_productos ?? 0),
            parseInt(c.activos         ?? 0),
            parseInt(c.stock_total     ?? 0),
            parseFloat(c.valor_inventario ?? 0),
        ]),
        [],
        ['TOTAL',
            inventarioCategorias.reduce((sum, c) => sum + parseInt(c.total_productos ?? 0), 0),
            inventarioCategorias.reduce((sum, c) => sum + parseInt(c.activos ?? 0), 0),
            inventarioCategorias.reduce((sum, c) => sum + parseInt(c.stock_total ?? 0), 0),
            valorTotalInventario,
        ],
    ]);
    setColWidths(wsCategorias, [25, 16, 12, 18, 22]);
    XLSX.utils.book_append_sheet(wb, wsCategorias, 'Por categoría');

    // ─── HOJA 4: Sin stock ─────────────────────────
    const sinStock = inventarioCompleto.filter(p => parseInt(p.stock_total ?? 0) === 0);
    const wsSinStock = XLSX.utils.aoa_to_sheet([
        ['PRODUCTOS SIN STOCK'],
        ['Total:', sinStock.length],
        [],
        ['Código', 'Producto', 'Categoría', 'Precio (L.)', 'Estado'],
        ...sinStock.map(p => [
            p.codigo_barras || '—',
            p.nombre,
            p.categoria_nombre || '—',
            parseFloat(p.precio_base ?? 0),
            p.estado,
        ])
    ]);
    setColWidths(wsSinStock, [18, 40, 22, 14, 12]);
    XLSX.utils.book_append_sheet(wb, wsSinStock, 'Sin stock');

    // ─── HOJA 5: Stock bajo (productos simples) ────
    const wsStockBajo = XLSX.utils.aoa_to_sheet([
        ['PRODUCTOS CON STOCK BAJO'],
        [],
        ['Producto', 'Categoría', 'Stock actual', 'Precio (L.)'],
        ...stockBajo.map(p => [
            p.nombre,
            p.categoria_nombre,
            parseInt(p.stock),
            parseFloat(p.precio_base)
        ]),
    ]);
    setColWidths(wsStockBajo, [40, 22, 14, 14]);
    XLSX.utils.book_append_sheet(wb, wsStockBajo, 'Stock bajo');

    // ─── HOJA 6: Variantes con stock bajo ──────────
    if (variantesStockBajo.length > 0) {
        const wsVar = XLSX.utils.aoa_to_sheet([
            ['VARIANTES CON STOCK BAJO'],
            [],
            ['Producto', 'Variante', 'Stock', 'Precio (L.)'],
            ...variantesStockBajo.map(v => [
                v.producto_nombre,
                v.variante_nombre,
                parseInt(v.stock),
                parseFloat(v.precio),
            ])
        ]);
        setColWidths(wsVar, [40, 22, 10, 14]);
        XLSX.utils.book_append_sheet(wb, wsVar, 'Variantes');
    }

    XLSX.writeFile(wb, `reporte-inventario-${fecha.replace(/\//g,'-')}.xlsx`);
}
</script>