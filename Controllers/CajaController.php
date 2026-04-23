<?php

class CajaController
{
    private ProductoModel $productoModel;
    private VarianteModel $varianteModel;
    private VentaModel    $ventaModel;

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // ─────────────────────────────────────────────
    public function __construct()
    {
        Auth::check();
        $this->productoModel = new ProductoModel();
        $this->varianteModel = new VarianteModel();
        $this->ventaModel    = new VentaModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Vista principal de la caja
    // URL: /Caja/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('ventas.crear');

        $pageTitle  = 'Caja / Punto de Venta';

        // Cargar todos los productos activos con sus variantes
        $productos  = $this->productoModel->findActivos();

        // Cargar categorías para el filtro
        $categoriaModel = new CategoriaModel();
        $categorias     = $categoriaModel->findActivas();

        require_once VIEWS_PATH . 'Caja' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // BUSCAR — Búsqueda de productos por nombre (AJAX)
    // URL: /Caja/buscar
    // Responde JSON
    // ─────────────────────────────────────────────
    public function buscar(): void
    {
        Auth::require('ventas.crear');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            exit();
        }

        $query = htmlspecialchars(strip_tags(trim($_GET['q'] ?? '')));

        if (strlen($query) < 1) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit();
        }

        $productos = $this->productoModel->findByNombre($query);

        $resultado = [];
        foreach ($productos as $p) {
            $item = [
                'id'              => $p->id,
                'nombre'          => $p->nombre,
                'precio_base'     => $p->precio_base,
                'tiene_variantes' => $p->tieneVariantes(),
                'stock'           => $p->stock,
                'image_url'       => $p->getImageUrl(),
                'categoria'       => $p->categoria_nombre,
                'variantes'       => [],
            ];

            // Si tiene variantes las carga
            if ($p->tieneVariantes()) {
                $variantes = $this->varianteModel->findByProducto($p->id);
                foreach ($variantes as $v) {
                    if (!$v->isActivo()) continue;
                    $item['variantes'][] = [
                        'id'     => $v->id,
                        'nombre' => $v->nombre,
                        'precio' => $v->getPrecioEfectivo(),
                        'stock'  => $v->stock,
                    ];
                }
            }

            $resultado[] = $item;
        }

        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit();
    }

    // ─────────────────────────────────────────────
    // BARRAS — Buscar producto por código de barras (AJAX)
    // URL: /Caja/barras?codigo=xxx
    // Responde JSON
    // ─────────────────────────────────────────────
    public function barras(): void
    {
        Auth::require('ventas.crear');

        $codigo = htmlspecialchars(strip_tags(trim($_GET['codigo'] ?? '')));

        if (empty($codigo)) {
            header('Content-Type: application/json');
            echo json_encode(['found' => false]);
            exit();
        }

        // Busca primero en variantes
        $resultado = $this->productoModel->findByBarras($codigo);

        if (!$resultado) {
            // Si no hay variante busca en productos simples
            $resultado = $this->productoModel->findSimpleByBarras($codigo);
        }

        header('Content-Type: application/json');
        echo json_encode($resultado
            ? ['found' => true, 'producto' => $resultado]
            : ['found' => false]
        );
        exit();
    }

    // ─────────────────────────────────────────────
    // COBRAR — Procesar venta (POST)
    // URL: /Caja/cobrar
    // ─────────────────────────────────────────────
    public function cobrar(): void
    {
        Auth::require('ventas.crear');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        // Validar CSRF
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Token inválido.']);
            exit();
        }

        // Leer datos del POST
        $clienteId     = !empty($_POST['cliente_id']) ? (int) $_POST['cliente_id'] : null;
        $metodoPago    = htmlspecialchars(strip_tags(trim($_POST['metodo_pago'] ?? 'Efectivo')));
        $montoRecibido = !empty($_POST['monto_recibido']) ? (float) $_POST['monto_recibido'] : null;
        $nota          = htmlspecialchars(strip_tags(trim($_POST['nota'] ?? '')));

        // Validar método de pago
        $metodosValidos = ['Efectivo', 'Tarjeta', 'Transferencia'];
        if (!in_array($metodoPago, $metodosValidos, true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método de pago inválido.']);
            exit();
        }

        // Leer items del carrito (JSON)
        $itemsJson = $_POST['items'] ?? '[]';
        $items     = json_decode($itemsJson, true);

        if (empty($items)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
            exit();
        }

        // Calcular totales
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (float) $item['precio'] * (int) $item['cantidad'];
        }

        $isv     = round($subtotal / 1.15 * 0.15, 2); // ISV 15% incluido en precio
        $total   = round($subtotal, 2);
        $cambio  = $metodoPago === 'Efectivo' && $montoRecibido
            ? round($montoRecibido - $total, 2)
            : null;

        // Validar que el efectivo cubra el total
        if ($metodoPago === 'Efectivo' && $montoRecibido !== null && $montoRecibido < $total) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'El monto recibido es insuficiente.']);
            exit();
        }

        try {
            // Iniciar transacción — todo o nada
            $this->ventaModel->beginTransactionPublic();

            // 1. Insertar cabecera de venta
            $ventaId = $this->ventaModel->insert([
                'cliente_id'     => $clienteId,
                'user_id'        => Auth::id(),
                'metodo_pago'    => $metodoPago,
                'subtotal'       => $subtotal,
                'descuento'      => 0,
                'total'          => $total,
                'monto_recibido' => $montoRecibido,
                'cambio'         => $cambio,
                'nota'           => $nota ?: null,
            ]);

            if (!$ventaId) {
                throw new \RuntimeException('Error al crear la venta.');
            }

            // 2. Insertar detalle y descontar stock
            foreach ($items as $item) {
                $productoId = (int) $item['producto_id'];
                $varianteId = !empty($item['variante_id']) ? (int) $item['variante_id'] : null;
                $cantidad   = (int) $item['cantidad'];
                $precio     = (float) $item['precio'];
                $nombre     = htmlspecialchars(strip_tags($item['nombre'] ?? ''));

                // Insertar línea de detalle
                $this->ventaModel->insertDetalle([
                    'venta_id'        => $ventaId,
                    'producto_id'     => $productoId,
                    'variante_id'     => $varianteId,
                    'nombre_producto' => $nombre,
                    'precio_unit'     => $precio,
                    'cantidad'        => $cantidad,
                    'subtotal'        => round($precio * $cantidad, 2),
                ]);

                // Descontar stock
                if ($varianteId) {
                    $ok = $this->varianteModel->descontarStock($varianteId, $cantidad);
                } else {
                    $ok = $this->productoModel->descontarStock($productoId, $cantidad);
                }

                if (!$ok) {
                    throw new \RuntimeException("Stock insuficiente para: {$nombre}");
                }
            }

            $this->ventaModel->commitPublic();

            header('Content-Type: application/json');
            echo json_encode([
                'success'  => true,
                'venta_id' => $ventaId,
                'total'    => $total,
                'cambio'   => $cambio,
                'message'  => 'Venta registrada correctamente.',
            ]);

        } catch (\RuntimeException $e) {
            $this->ventaModel->rollbackPublic();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        exit();
    }

    // ─────────────────────────────────────────────
    // RECIBO — Genera el recibo para imprimir
    // URL: /Caja/recibo/{venta_id}
    // ─────────────────────────────────────────────
    public function recibo(string $id = ''): void
    {
        Auth::require('ventas.ver');

        if (empty($id) || !is_numeric($id)) {
            header('Location: ' . APP_URL . 'Caja/index');
            exit();
        }

        $venta   = $this->ventaModel->findById((int) $id);
        $detalle = $this->ventaModel->findDetalle((int) $id);
        $config  = $this->ventaModel->getFacturacionConfig();

        require_once VIEWS_PATH . 'Caja' . DS . 'Recibo.php';
    }
}