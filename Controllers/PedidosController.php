<?php

class PedidosController
{
    private PedidoModel $pedidoModel;

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // ─────────────────────────────────────────────
    public function __construct()
    {
        Auth::check();
        $this->pedidoModel = new PedidoModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Todos los pedidos
    // URL: /Pedidos/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('pedidos.ver');

        $pageTitle = 'Todos los Pedidos';
        $pedidos   = $this->pedidoModel->findAll();

        require_once VIEWS_PATH . 'Pedidos' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // PENDIENTES
    // URL: /Pedidos/pendientes
    // ─────────────────────────────────────────────
    public function pendientes(): void
    {
        Auth::require('pedidos.ver');

        $pageTitle = 'Pedidos Pendientes';
        $pedidos   = $this->pedidoModel->findByEstado('Pendiente');

        require_once VIEWS_PATH . 'Pedidos' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // PREPARACION
    // URL: /Pedidos/preparacion
    // ─────────────────────────────────────────────
    public function preparacion(): void
    {
        Auth::require('pedidos.ver');

        $pageTitle = 'Pedidos En Preparación';
        $pedidos   = $this->pedidoModel->findByEstado('En preparacion');

        require_once VIEWS_PATH . 'Pedidos' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // LISTOS
    // URL: /Pedidos/listos
    // ─────────────────────────────────────────────
    public function listos(): void
    {
        Auth::require('pedidos.ver');

        $pageTitle = 'Pedidos Listos';
        $pedidos   = $this->pedidoModel->findByEstado('Listo');

        require_once VIEWS_PATH . 'Pedidos' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // EN CAMINO
    // URL: /Pedidos/camino
    // ─────────────────────────────────────────────
    public function camino(): void
    {
        Auth::require('pedidos.ver');

        $pageTitle = 'Pedidos En Camino';
        $pedidos   = $this->pedidoModel->findByEstado('En camino');

        require_once VIEWS_PATH . 'Pedidos' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // ENTREGADOS
    // URL: /Pedidos/entregados
    // ─────────────────────────────────────────────
    public function entregados(): void
    {
        Auth::require('pedidos.ver');

        $pageTitle = 'Pedidos Entregados';
        $pedidos   = $this->pedidoModel->findByEstado('Entregado');

        require_once VIEWS_PATH . 'Pedidos' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // DETALLE — Ver pedido completo
    // URL: /Pedidos/detalle/{id}
    // ─────────────────────────────────────────────
    public function detalle(string $id = ''): void
    {
        Auth::require('pedidos.ver');

        if (empty($id) || !is_numeric($id)) {
            header('Location: ' . APP_URL . 'Pedidos/index');
            exit();
        }

        $pedido   = $this->pedidoModel->findById((int) $id);

        if (!$pedido->Found) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'El pedido no existe.',
            ];
            header('Location: ' . APP_URL . 'Pedidos/index');
            exit();
        }

        $detalle   = $this->pedidoModel->findDetalle((int) $id);
        $historial = $this->pedidoModel->findHistorial((int) $id);
        $pageTitle = 'Pedido ' . $pedido->getCodigoFormateado();

        require_once VIEWS_PATH . 'Pedidos' . DS . 'detalle.php';
    }

    // ─────────────────────────────────────────────
    // CAMBIAR ESTADO — (POST — JSON)
    // URL: /Pedidos/cambiarEstado
    // ─────────────────────────────────────────────
    public function cambiarEstado(): void
    {
        Auth::require('pedidos.gestionar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            http_response_code(403);
            exit();
        }

        $id     = (int) ($_POST['id']     ?? 0);
        $estado = htmlspecialchars(strip_tags(trim($_POST['estado'] ?? '')));
        $nota   = htmlspecialchars(strip_tags(trim($_POST['nota']   ?? '')));

        // Validar estados permitidos
        $estadosValidos = ['Pendiente', 'En preparacion', 'Listo', 'En camino', 'Entregado', 'Cancelado'];
        if (!in_array($estado, $estadosValidos, true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Estado inválido.']);
            exit();
        }

        $ok = $this->pedidoModel->updateEstado($id, $estado, Auth::id(), $nota);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Estado actualizado.' : 'Error al actualizar.',
            'estado'  => $estado,
        ]);
        exit();
    }
}