<?php

class ReportesController
{
    private VentaModel $ventaModel;

    public function __construct()
    {
        Auth::check();
        $this->ventaModel = new VentaModel();
    }

    // ─────────────────────────────────────────────
    // VENTAS
    // URL: /Reportes/ventas
    // ─────────────────────────────────────────────
    public function ventas(): void
    {
        Auth::require('reportes.ver');

        $pageTitle      = 'Reporte de Ventas';
        $pdo            = Conexion::getInstance();

        $resumen        = $this->callSP($pdo, 'sp_reportes_resumenVentas',   [], true);
        $ventasPorDia   = $this->callSP($pdo, 'sp_reportes_ventasPorDia',   []);
        $ventasPorMes   = $this->callSP($pdo, 'sp_reportes_ventasPorMes',   []);
        $ventasPorMetodo= $this->callSP($pdo, 'sp_reportes_ventasPorMetodo',[]);
        $topProductos   = $this->callSP($pdo, 'sp_reportes_topProductos',   []);

        require_once VIEWS_PATH . 'Reportes' . DS . 'ventas.php';
    }

    // ─────────────────────────────────────────────
    // PEDIDOS
    // URL: /Reportes/pedidos
    // ─────────────────────────────────────────────
    public function pedidos(): void
    {
        Auth::require('reportes.ver');

        $pageTitle       = 'Reporte de Pedidos';
        $pdo             = Conexion::getInstance();

        $resumen         = $this->callSP($pdo, 'sp_reportes_resumenPedidos',  [], true);
        $pedidosPorEstado= $this->callSP($pdo, 'sp_reportes_pedidosPorEstado',[]);
        $pedidosPorDia   = $this->callSP($pdo, 'sp_reportes_pedidosPorDia',  []);

        require_once VIEWS_PATH . 'Reportes' . DS . 'pedidos.php';
    }

    // ─────────────────────────────────────────────
    // INVENTARIO
    // URL: /Reportes/inventario
    // ─────────────────────────────────────────────
    public function inventario(): void
    {
        Auth::require('reportes.ver');

        $pageTitle       = 'Reporte de Inventario';
        $pdo             = Conexion::getInstance();
        $limite          = (int) ($_GET['limite'] ?? 5);

        $resumen         = $this->callSP($pdo, 'sp_reportes_resumenInventario',  [], true);
        $stockBajo       = $this->callSP($pdo, 'sp_reportes_stockBajo',          [$limite]);
        $variantesStockBajo = $this->callSP($pdo, 'sp_reportes_variantesStockBajo', [$limite]);

        require_once VIEWS_PATH . 'Reportes' . DS . 'inventario.php';
    }

    // ─────────────────────────────────────────────
    // HELPER — Ejecutar SP directo
    // ─────────────────────────────────────────────
    private function callSP(PDO $pdo, string $sp, array $params = [], bool $single = false): mixed
    {
        $placeholders = implode(',', array_fill(0, count($params), '?'));
        $sql          = "CALL {$sp}(" . ($placeholders ?: '') . ")";
        $stmt         = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $single ? ($rows[0] ?? null) : $rows;
    }
}