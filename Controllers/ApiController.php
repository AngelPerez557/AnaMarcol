<?php

/**
 * ApiController.php — API REST para sincronización con AnaMarcolPOS
 *
 * La app local (WPF, tienda) es la fuente de verdad para inventario/precio
 * de piso. Este controlador expone lo que el sitio web necesita reflejar
 * (catálogo) y recibe lo que la tienda local reporta (stock, precio, activo).
 *
 * No usa sesión/CSRF de panel — se autentica con un header propio:
 *   X-Api-Key: <POS_API_KEY>   (ver Config/Define.php)
 *
 * 'api' está en PUBLIC_ROUTES (Define.php) para que index.php no exija
 * sesión de panel admin antes de llegar aquí — la autenticación real
 * ocurre en el constructor de este controlador.
 */
class ApiController
{
    private ProductoModel     $productoModel;
    private VentaModel        $ventaModel;
    private CajaSesionModel   $cajaSesionModel;
    private CategoriaModel    $categoriaModel;

    public function __construct()
    {
        $this->autenticar();
        $this->productoModel  = new ProductoModel();
        $this->ventaModel     = new VentaModel();
        $this->cajaSesionModel = new CajaSesionModel();
        $this->categoriaModel  = new CategoriaModel();
    }

    // ─────────────────────────────────────────────
    // AUTENTICACIÓN — API key por header
    // ─────────────────────────────────────────────
    private function autenticar(): void
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $apiKey  = $headers['X-Api-Key']
            ?? $headers['x-api-key']
            ?? ($_SERVER['HTTP_X_API_KEY'] ?? '');

        if (!hash_equals(POS_API_KEY, (string) $apiKey)) {
            $this->json(['success' => false, 'error' => 'API key inválida.'], 401);
            exit();
        }
    }

    // ─────────────────────────────────────────────
    // GET /Api/productos — catálogo completo (para que la tienda local
    // se emparente con RemoteId al primer arranque / reimport)
    // ─────────────────────────────────────────────
    public function productos(): void
    {
        $productos = $this->productoModel->findAll();

        $data = array_map(fn($p) => [
            'id'            => $p->id,
            'nombre'        => $p->nombre,
            'precio_base'   => $p->precio_base,
            'stock'         => $p->stock,
            'activo'        => $p->isActivo(),
            'codigo_barras' => $p->codigo_barras,
            'categoria_id'  => $p->categoria_id,
            'image_url'     => $p->image_url,
        ], $productos);

        $this->json(['success' => true, 'data' => $data]);
    }

    // ─────────────────────────────────────────────
    // POST /Api/stock — la tienda local reporta cambios (JSON body)
    // Body: { "items": [ { "id": 1, "stock": 10, "activo": true, "precio": 150.00 }, ... ] }
    // 'id' es el RemoteId (id en esta BD web).
    //
    // IMPORTANTE: 'stock' aquí es SIEMPRE un valor ABSOLUTO ("el stock real es
    // este"), nunca una cantidad a descontar. Por eso NO se usa
    // ProductoModel::updateStock() — esa función llama a sp_productos_updateStock,
    // que es para el flujo de venta (RESTA cantidad del stock actual, ver el
    // comentario en ProductoModel::descontarStock). Usarla aquí causó un bug real:
    // el POS mandaba "stock: 20" para fijarlo, y el servidor lo interpretaba como
    // "quítale 20 unidades" — como ya estaba en 0, se quedaba en 0 sin avisar
    // error (updateStock() devuelve true pase lo que pase). Por eso 'stock' pasa
    // siempre por update() (sp_productos_update), igual que 'precio', que sí fija
    // el valor absoluto — necesita categoria_id/nombre/descripcion, que se
    // preservan tal cual están hoy en la BD web (el POS no los conoce ni los toca).
    // ─────────────────────────────────────────────
    public function stock(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método no permitido.'], 405);
            return;
        }

        $body  = json_decode(file_get_contents('php://input'), true);
        $items = $body['items'] ?? null;

        if (!is_array($items)) {
            $this->json(['success' => false, 'error' => 'Body inválido: se espera { items: [...] }.'], 400);
            return;
        }

        $resultados = [];
        foreach ($items as $item) {
            $id = (int) ($item['id'] ?? 0);
            if ($id <= 0) {
                $resultados[] = ['id' => $item['id'] ?? null, 'success' => false, 'error' => 'id inválido'];
                continue;
            }

            $ok = true;

            // isset() (no array_key_exists) a propósito: isset() da false tanto si la
            // clave no viene como si viene con valor null — un cliente que serialice
            // "precio": null (campo opcional no usado) no debe pisar el valor real con 0.
            // Ya pasó una vez: un bug en el cliente C# mandaba null explícito y esto
            // puso precio/stock en 0 para todo el catálogo.
            if (isset($item['precio']) || isset($item['stock'])) {
                // update() reemplaza la fila completa — hay que partir de los
                // valores actuales para no perder categoría/nombre/descripción.
                $actual = $this->productoModel->findById($id);
                if (!$actual->Found) {
                    $resultados[] = ['id' => $id, 'success' => false, 'error' => 'producto no encontrado'];
                    continue;
                }

                $nuevoPrecio = isset($item['precio']) ? (float) $item['precio'] : (float) $actual->precio_base;
                $nuevoStock  = isset($item['stock']) ? (int) $item['stock'] : $actual->stock;

                $ok = $this->productoModel->update([
                    'id'              => $id,
                    'categoria_id'    => $actual->categoria_id,
                    'nombre'          => $actual->nombre,
                    'descripcion'     => $actual->descripcion,
                    'precio_base'     => $nuevoPrecio,
                    'stock'           => $nuevoStock,
                    'codigo_barras'   => $actual->codigo_barras ?? null,
                    'image_url'       => $actual->image_url,
                ]);

                // MySQL reporta 0 filas afectadas cuando el UPDATE no cambia
                // ningún valor (ej. el POS reenvía el mismo precio) — eso NO
                // es un error, así que lo tratamos como éxito si los valores
                // en la BD ya coinciden con lo que se pidió guardar.
                if (!$ok) {
                    $verificacion = $this->productoModel->findById($id);
                    $ok = $verificacion->Found
                        && abs((float) $verificacion->precio_base - $nuevoPrecio) < 0.005
                        && (int) $verificacion->stock === $nuevoStock;
                }
            }

            if (isset($item['activo'])) {
                $activoDeseado = $item['activo'] ? 1 : 0;
                $okActivo = $this->productoModel->toggleActivo($id, $activoDeseado);

                // Igual que con precio/stock: 0 filas afectadas porque el
                // estado ya era ese no es un error.
                if (!$okActivo) {
                    $verificacion = $this->productoModel->findById($id);
                    $okActivo = $verificacion->Found && (int) $verificacion->activo === $activoDeseado;
                }

                $ok = $ok && $okActivo;
            }

            $resultados[] = ['id' => $id, 'success' => $ok];
        }

        $this->json(['success' => true, 'resultados' => $resultados]);
    }

    // ─────────────────────────────────────────────
    // POST /Api/crearProducto — la tienda local crea un producto nuevo
    // Body: { "nombre": "...", "precio": 100, "stock": 20, "categoria_id": 2, "codigo_barras": "..." }
    // Sin imagen a propósito — el POS local no maneja fotos.
    // Devuelve { success, id } donde "id" es el RemoteId para emparejar en el POS.
    // ─────────────────────────────────────────────
    public function crearProducto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método no permitido.'], 405);
            return;
        }

        $body = json_decode(file_get_contents('php://input'), true);

        $nombre      = trim((string) ($body['nombre'] ?? ''));
        $categoriaId = (int) ($body['categoria_id'] ?? 0);
        $precio      = isset($body['precio']) ? (float) $body['precio'] : null;
        $stock       = (int) ($body['stock'] ?? 0);
        $codigoBarras = isset($body['codigo_barras']) && $body['codigo_barras'] !== ''
            ? trim((string) $body['codigo_barras']) : null;

        if ($nombre === '' || $categoriaId <= 0) {
            $this->json(['success' => false, 'error' => 'nombre y categoria_id son obligatorios.'], 400);
            return;
        }

        try {
            $id = $this->productoModel->insert([
                'categoria_id'    => $categoriaId,
                'nombre'          => $nombre,
                'descripcion'     => null,
                'precio_base'     => $precio,
                'tiene_variantes' => 0,
                'stock'           => $stock,
                'codigo_barras'   => $codigoBarras,
                'image_url'       => null, // el POS local no maneja fotos
            ]);
        } catch (\RuntimeException $e) {
            $mensaje = str_contains($e->getMessage(), '1062')
                ? 'Ya existe un producto con ese código de barras.'
                : 'Error al crear el producto.';
            $this->json(['success' => false, 'error' => $mensaje], 409);
            return;
        }

        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'No se pudo crear el producto.'], 500);
            return;
        }

        $this->json(['success' => true, 'id' => $id]);
    }

    // ─────────────────────────────────────────────
    // POST /Api/crearVenta — el POS reporta una venta ya cobrada
    // (incluida su factura/correlativo) más su detalle de líneas.
    // Body: {
    //   "pos_venta_id": 123, "metodo_pago": "Efectivo",
    //   "subtotal": 100, "total": 115, "monto_recibido": 120, "cambio": 5,
    //   "nota": null, "correlativo": 5752, "created_at": "2026-08-28 10:00:00",
    //   "items": [ { "producto_id": 4, "nombre_producto": "...", "precio_unit": 100, "cantidad": 1, "subtotal": 100 } ]
    // }
    // Idempotente: reenviar el mismo pos_venta_id no duplica la venta.
    // 'producto_id' es el RemoteId del producto — puede venir null si ese
    // producto todavía no se sincronizó al catálogo web; la línea igual
    // se guarda, solo sin producto_id.
    // ─────────────────────────────────────────────
    public function crearVenta(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método no permitido.'], 405);
            return;
        }

        $body = json_decode(file_get_contents('php://input'), true);

        $posVentaId = (int) ($body['pos_venta_id'] ?? 0);
        $metodoPago = (string) ($body['metodo_pago'] ?? 'Efectivo');
        $total      = isset($body['total']) ? (float) $body['total'] : null;
        $items      = $body['items'] ?? [];

        if ($posVentaId <= 0 || $total === null || !is_array($items)) {
            $this->json(['success' => false, 'error' => 'pos_venta_id, total e items son obligatorios.'], 400);
            return;
        }

        $ventaId = $this->ventaModel->insertDesdePos([
            'pos_venta_id'   => $posVentaId,
            'metodo_pago'    => $metodoPago,
            'subtotal'       => (float) ($body['subtotal'] ?? $total),
            'total'          => $total,
            'monto_recibido' => isset($body['monto_recibido']) ? (float) $body['monto_recibido'] : null,
            'cambio'         => isset($body['cambio']) ? (float) $body['cambio'] : null,
            'nota'           => $body['nota'] ?? null,
            'correlativo'    => isset($body['correlativo']) ? (int) $body['correlativo'] : null,
            'created_at'     => (string) ($body['created_at'] ?? date('Y-m-d H:i:s')),
        ]);

        if ($ventaId <= 0) {
            $this->json(['success' => false, 'error' => 'No se pudo guardar la venta.'], 500);
            return;
        }

        foreach ($items as $item) {
            $this->ventaModel->insertDetalleDesdePos([
                'venta_id'        => $ventaId,
                'producto_id'     => isset($item['producto_id']) ? (int) $item['producto_id'] : null,
                'nombre_producto' => (string) ($item['nombre_producto'] ?? ''),
                'precio_unit'     => (float) ($item['precio_unit'] ?? 0),
                'cantidad'        => (int) ($item['cantidad'] ?? 1),
                'subtotal'        => (float) ($item['subtotal'] ?? 0),
            ]);
        }

        $this->json(['success' => true, 'id' => $ventaId]);
    }

    // ─────────────────────────────────────────────
    // POST /Api/crearCajaSesion — el POS reporta un turno de caja ya
    // cerrado (el POS solo sincroniza al cerrar, nunca sesiones abiertas).
    // Body: {
    //   "pos_sesion_id": 8, "monto_apertura": 500, "monto_cierre": 1200,
    //   "monto_sistema": 1180, "diferencia": 20, "total_ventas": 680,
    //   "total_efectivo": 680, "total_tarjeta": 0, "total_transferencia": 0,
    //   "nota_apertura": null, "nota_cierre": null,
    //   "abierta_at": "2026-08-28 08:00:00", "cerrada_at": "2026-08-28 17:00:00"
    // }
    // Idempotente: reenviar el mismo pos_sesion_id no duplica.
    // ─────────────────────────────────────────────
    public function crearCajaSesion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método no permitido.'], 405);
            return;
        }

        $body = json_decode(file_get_contents('php://input'), true);

        $posSesionId = (int) ($body['pos_sesion_id'] ?? 0);
        $cerradaAt   = $body['cerrada_at'] ?? null;

        if ($posSesionId <= 0 || !$cerradaAt) {
            $this->json(['success' => false, 'error' => 'pos_sesion_id y cerrada_at son obligatorios.'], 400);
            return;
        }

        $sesionId = $this->cajaSesionModel->insertDesdePos([
            'pos_sesion_id'        => $posSesionId,
            'monto_apertura'       => (float) ($body['monto_apertura'] ?? 0),
            'monto_cierre'         => (float) ($body['monto_cierre'] ?? 0),
            'monto_sistema'        => (float) ($body['monto_sistema'] ?? 0),
            'diferencia'           => (float) ($body['diferencia'] ?? 0),
            'total_ventas'         => (float) ($body['total_ventas'] ?? 0),
            'total_efectivo'       => (float) ($body['total_efectivo'] ?? 0),
            'total_tarjeta'        => (float) ($body['total_tarjeta'] ?? 0),
            'total_transferencia'  => (float) ($body['total_transferencia'] ?? 0),
            'nota_apertura'        => $body['nota_apertura'] ?? null,
            'nota_cierre'          => $body['nota_cierre'] ?? null,
            'abierta_at'           => (string) ($body['abierta_at'] ?? $cerradaAt),
            'cerrada_at'           => (string) $cerradaAt,
        ]);

        if ($sesionId <= 0) {
            $this->json(['success' => false, 'error' => 'No se pudo guardar el cierre de caja.'], 500);
            return;
        }

        $this->json(['success' => true, 'id' => $sesionId]);
    }

    // ─────────────────────────────────────────────
    // POST /Api/crearCategoria — usado una sola vez para el import masivo
    // del catálogo de "Soluciones Logísticas Integradas" (2026-08-28):
    // crea la categoría "Otros" para los productos que no encajan en las
    // 6 categorías reales de la tienda (joyería, relojes, ropa interior...).
    // Body: { "nombre": "Otros" }. Idempotente: si ya existe una categoría
    // con ese nombre, devuelve su id en vez de duplicarla.
    // ─────────────────────────────────────────────
    public function crearCategoria(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método no permitido.'], 405);
            return;
        }

        $body   = json_decode(file_get_contents('php://input'), true);
        $nombre = trim((string) ($body['nombre'] ?? ''));

        if ($nombre === '') {
            $this->json(['success' => false, 'error' => 'nombre es obligatorio.'], 400);
            return;
        }

        foreach ($this->categoriaModel->findAll() as $cat) {
            if (strcasecmp($cat->nombre ?? '', $nombre) === 0) {
                $this->json(['success' => true, 'id' => $cat->id]);
                return;
            }
        }

        $id = $this->categoriaModel->insert(['nombre' => $nombre]);
        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'No se pudo crear la categoría.'], 500);
            return;
        }

        $this->json(['success' => true, 'id' => $id]);
    }

    // ─────────────────────────────────────────────
    // Helper de respuesta JSON
    // ─────────────────────────────────────────────
    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }
}
