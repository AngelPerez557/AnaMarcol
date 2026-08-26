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
    private ProductoModel $productoModel;

    public function __construct()
    {
        $this->autenticar();
        $this->productoModel = new ProductoModel();
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
            'id'          => $p->id,
            'nombre'      => $p->nombre,
            'precio_base' => $p->precio_base,
            'stock'       => $p->stock,
            'activo'      => $p->isActivo(),
        ], $productos);

        $this->json(['success' => true, 'data' => $data]);
    }

    // ─────────────────────────────────────────────
    // POST /Api/stock — la tienda local reporta cambios (JSON body)
    // Body: { "items": [ { "id": 1, "stock": 10, "activo": true, "precio": 150.00 }, ... ] }
    // 'id' es el RemoteId (id en esta BD web). 'precio' es opcional — solo
    // viaja cuando el producto se editó en el POS (Productos > editar).
    // Cuando viene, se actualiza vía sp_productos_update (requiere también
    // categoria_id/nombre/descripcion, que se preservan tal cual están hoy
    // en la BD web — el POS no los conoce ni los toca).
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

            if (array_key_exists('precio', $item)) {
                // update() reemplaza la fila completa — hay que partir de los
                // valores actuales para no perder categoría/nombre/descripción.
                $actual = $this->productoModel->findById($id);
                if (!$actual->Found) {
                    $resultados[] = ['id' => $id, 'success' => false, 'error' => 'producto no encontrado'];
                    continue;
                }

                $ok = $ok && $this->productoModel->update([
                    'id'              => $id,
                    'categoria_id'    => $actual->categoria_id,
                    'nombre'          => $actual->nombre,
                    'descripcion'     => $actual->descripcion,
                    'precio_base'     => (float) $item['precio'],
                    'stock'           => array_key_exists('stock', $item) ? (int) $item['stock'] : $actual->stock,
                    'codigo_barras'   => $actual->codigo_barras ?? null,
                    'image_url'       => $actual->image_url,
                ]);
            } elseif (array_key_exists('stock', $item)) {
                $ok = $ok && $this->productoModel->updateStock($id, (int) $item['stock']);
            }

            if (array_key_exists('activo', $item)) {
                $ok = $ok && $this->productoModel->toggleActivo($id, $item['activo'] ? 1 : 0);
            }

            $resultados[] = ['id' => $id, 'success' => $ok];
        }

        $this->json(['success' => true, 'resultados' => $resultados]);
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
