<?php

/**
 * CotizacionesController — Bandeja de cotizaciones de la tienda.
 *
 * Sustituye al módulo Pedidos en el flujo diario: la tienda ya no
 * gestiona pedidos. Aquí solo se consulta lo que pidió el cliente y
 * se marca en qué estado quedó la conversación de WhatsApp.
 *
 * Deliberadamente NO hay: cambio de stock, cobro, ni factura.
 * La venta se registra en AnaMarcolPOS cuando se concreta.
 *
 * Permisos: reutiliza los de pedidos (pedidos.ver / pedidos.gestionar)
 * para no obligar a un alta de permisos y reasignación de roles.
 */
class CotizacionesController
{
    private CotizacionModel $model;

    public function __construct()
    {
        Auth::check();
        $this->model = new CotizacionModel();
    }

    // ─────────────────────────────────────────────
    // LISTADO — URL: /Cotizaciones/index
    // Filtro opcional: ?estado=Nueva
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('pedidos.ver');

        $estado = $_GET['estado'] ?? '';
        $estado = in_array($estado, CotizacionEntity::ESTADOS, true) ? $estado : '';

        $pageTitle    = 'Cotizaciones';
        $cotizaciones = $estado !== ''
            ? $this->model->findByEstado($estado)
            : $this->model->findAll();

        $conteos = [
            'Nueva'      => $this->model->countByEstado('Nueva'),
            'Atendida'   => $this->model->countByEstado('Atendida'),
            'Cerrada'    => $this->model->countByEstado('Cerrada'),
            'Descartada' => $this->model->countByEstado('Descartada'),
            'hoy'        => $this->model->countHoy(),
        ];

        require_once VIEWS_PATH . 'Cotizaciones' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // DETALLE — URL: /Cotizaciones/detalle/5
    // ─────────────────────────────────────────────
    public function detalle(string $id = ''): void
    {
        Auth::require('pedidos.ver');

        $cotizacionId = (int) $id;
        $cotizacion   = $this->model->findById($cotizacionId);

        if (!$cotizacion->Found) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Cotización no encontrada.'];
            header('Location: ' . APP_URL . 'Cotizaciones/index');
            exit();
        }

        $pageTitle = 'Cotización ' . $cotizacion->getCodigoFormateado();
        $detalle   = $this->model->findDetalle($cotizacionId);

        require_once VIEWS_PATH . 'Cotizaciones' . DS . 'Detalle.php';
    }

    // ─────────────────────────────────────────────
    // CAMBIAR ESTADO — URL: /Cotizaciones/cambiarEstado (POST — JSON)
    // Único cambio de datos del módulo: en qué quedó la conversación.
    // ─────────────────────────────────────────────
    public function cambiarEstado(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        Auth::require('pedidos.gestionar');

        if (!Csrf::validate()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token inválido.']);
            exit();
        }

        $id     = (int) ($_POST['id']     ?? 0);
        $estado = (string) ($_POST['estado'] ?? '');

        if (!$id || !in_array($estado, CotizacionEntity::ESTADOS, true)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            exit();
        }

        $ok = $this->model->updateEstado($id, $estado);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? "Cotización marcada como {$estado}." : 'No se pudo actualizar.',
        ]);
        exit();
    }
}
