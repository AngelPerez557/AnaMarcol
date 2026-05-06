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

        $pedido = $this->pedidoModel->findById((int) $id);

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

        require_once VIEWS_PATH . 'Pedidos' . DS . 'Detalle.php';
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

    // ─────────────────────────────────────────────
    // CONFIRMAR PAGO — (POST — JSON)
    // URL: /Pedidos/confirmarPago
    // Crea la venta en caja cuando el pago es verificado
    // ─────────────────────────────────────────────
    public function confirmarPago(): void
    {
        // Header JSON siempre primero — evita que cualquier error
        // devuelva HTML y rompa el JSON.parse() del frontend
        header('Content-Type: application/json');

        if (!Auth::can('pedidos.gestionar')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Sin permiso.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token inválido.']);
            exit();
        }

        $pedidoId = (int)($_POST['pedido_id'] ?? 0);
        if (!$pedidoId) {
            echo json_encode(['success' => false, 'message' => 'Pedido inválido.']);
            exit();
        }

        $pedido = $this->pedidoModel->findById($pedidoId);
        if (!$pedido->Found) {
            echo json_encode(['success' => false, 'message' => 'Pedido no encontrado.']);
            exit();
        }

        if ($pedido->pagado) {
            echo json_encode(['success' => false, 'message' => 'Este pedido ya fue pagado.']);
            exit();
        }

        $detalle    = $this->pedidoModel->findDetalle($pedidoId);
        $ventaModel = new VentaModel();

        try {
            $ventaModel->beginTransactionPublic();

            // 1 — Crear cabecera de venta
            $ventaId = $ventaModel->insert([
                'cliente_id'     => $pedido->cliente_id,
                'user_id'        => Auth::id(),
                'metodo_pago'    => $pedido->metodo_pago ?? 'Transferencia',
                'subtotal'       => $pedido->subtotal,
                'descuento'      => 0,
                'total'          => $pedido->total,
                'monto_recibido' => $pedido->total,
                'cambio'         => 0,
                'nota'           => "Pedido tienda en línea #{$pedido->codigo}",
            ]);

            if (!$ventaId) {
                throw new \RuntimeException('Error al crear la venta.');
            }

            // 2 — Insertar detalle de venta
            foreach ($detalle as $item) {
                $ventaModel->insertDetalle([
                    'venta_id'        => $ventaId,
                    'producto_id'     => $item['producto_id'],
                    'variante_id'     => $item['variante_id'] ?? null,
                    'nombre_producto' => $item['nombre_producto'],
                    'precio_unit'     => $item['precio_unit'],
                    'cantidad'        => $item['cantidad'],
                    'subtotal'        => $item['subtotal'],
                ]);
            }

            // 3 — Marcar pedido como pagado
            $this->pedidoModel->marcarPagado($pedidoId, Auth::id());

            $ventaModel->commitPublic();

            echo json_encode([
                'success'  => true,
                'message'  => 'Pago confirmado. Venta registrada en caja.',
                'venta_id' => $ventaId,
            ]);

        } catch (\RuntimeException $e) {
            $ventaModel->rollbackPublic();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

        exit();
    }
}