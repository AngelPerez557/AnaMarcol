<?php

/**
 * ReportesController.php — Vistas de reportes (ventas, pedidos, inventario)
 *
 * F-20 / F-26 — Antes este controller tenía SQL directo y un helper
 * privado callSP() que duplicaba lógica del BaseModel. Ahora toda la
 * BD pasa por ReporteModel — el Controller solo orquesta datos y vista.
 */
class ReportesController
{
    private ReporteModel $reporteModel;

    public function __construct()
    {
        Auth::check();
        $this->reporteModel = new ReporteModel();
    }

    // ─────────────────────────────────────────────
    // VENTAS
    // URL: /Reportes/ventas
    // ─────────────────────────────────────────────
    public function ventas(): void
    {
        Auth::require('reportes.ver');

        $pageTitle       = 'Reporte de Ventas';
        $resumen         = $this->reporteModel->resumenVentas();
        $ventasPorDia    = $this->reporteModel->ventasPorDia();
        $ventasPorMes    = $this->reporteModel->ventasPorMes();
        $ventasPorMetodo = $this->reporteModel->ventasPorMetodo();
        $topProductos    = $this->reporteModel->topProductos();

        require_once VIEWS_PATH . 'Reportes' . DS . 'Ventas.php';
    }

    // ─────────────────────────────────────────────
    // PEDIDOS
    // URL: /Reportes/pedidos
    // ─────────────────────────────────────────────
    public function pedidos(): void
    {
        Auth::require('reportes.ver');

        $pageTitle        = 'Reporte de Pedidos';
        $resumen          = $this->reporteModel->resumenPedidos();
        $pedidosPorEstado = $this->reporteModel->pedidosPorEstado();
        $pedidosPorDia    = $this->reporteModel->pedidosPorDia();

        require_once VIEWS_PATH . 'Reportes' . DS . 'Pedidos.php';
    }

    // ─────────────────────────────────────────────
    // INVENTARIO
    // URL: /Reportes/inventario
    // ─────────────────────────────────────────────
    public function inventario(): void
    {
        Auth::require('reportes.ver');

        $pageTitle          = 'Reporte de Inventario';
        $limite             = (int) ($_GET['limite'] ?? 5);

        $resumen              = $this->reporteModel->resumenInventario();
        $stockBajo            = $this->reporteModel->stockBajo($limite);
        $variantesStockBajo   = $this->reporteModel->variantesStockBajo($limite);

        // Reporte v2 — datos del catálogo completo para el export Excel/PDF
        $inventarioCompleto   = $this->reporteModel->inventarioCompleto();
        $inventarioCategorias = $this->reporteModel->inventarioPorCategoria();

        // Valor total agregado (para mostrar en el resumen y como KPI nuevo)
        $valorTotalInventario = 0.0;
        foreach ($inventarioCategorias as $c) {
            $valorTotalInventario += (float) ($c['valor_inventario'] ?? 0);
        }

        require_once VIEWS_PATH . 'Reportes' . DS . 'Inventario.php';
    }
}
