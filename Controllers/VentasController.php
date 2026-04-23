<?php

class VentasController
{
    private VentaModel $ventaModel;

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // ─────────────────────────────────────────────
    public function __construct()
    {
        Auth::check();
        $this->ventaModel = new VentaModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Historial de ventas
    // URL: /Ventas/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('ventas.ver');

        $pageTitle = 'Historial de Ventas';
        $ventas    = $this->ventaModel->findAll();

        // Totales del día
        $totalHoy  = $this->ventaModel->totalHoy();
        $countHoy  = $this->ventaModel->countHoy();

        require_once VIEWS_PATH . 'Ventas' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // DETALLE — Ver detalle de una venta
    // URL: /Ventas/detalle/{id}
    // ─────────────────────────────────────────────
    public function detalle(string $id = ''): void
    {
        Auth::require('ventas.ver');

        if (empty($id) || !is_numeric($id)) {
            header('Location: ' . APP_URL . 'Ventas/index');
            exit();
        }

        $venta   = $this->ventaModel->findById((int) $id);
        $detalle = $this->ventaModel->findDetalle((int) $id);
        $config  = $this->ventaModel->getFacturacionConfig();

        if (!$venta) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'La venta no existe.',
            ];
            header('Location: ' . APP_URL . 'Ventas/index');
            exit();
        }

        $pageTitle = 'Detalle de Venta #' . str_pad($id, 8, '0', STR_PAD_LEFT);

        require_once VIEWS_PATH . 'Ventas' . DS . 'Detalle.php';
    }
}