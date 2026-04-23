<?php

class ProductosController
{
    private ProductoModel $productoModel;
    private VarianteModel $varianteModel;

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // Verifica sesión y carga modelos
    // ─────────────────────────────────────────────
    public function __construct()
    {
        Auth::check();
        $this->productoModel = new ProductoModel();
        $this->varianteModel = new VarianteModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Listado de productos
    // URL: /Productos/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('productos.ver');

        $pageTitle = 'Productos';
        $productos = $this->productoModel->findAll();

        require_once VIEWS_PATH . 'Productos' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // REGISTRY — Crear o editar producto
    // URL: /Productos/registry      → crear
    // URL: /Productos/registry/{id} → editar
    // ─────────────────────────────────────────────
    public function registry(string $id = ''): void
    {
        // Permiso diferenciado — crear o editar
        $esEdicion = !empty($id) && is_numeric($id);
        Auth::require($esEdicion ? 'productos.editar' : 'productos.crear');

        $pageTitle  = $esEdicion ? 'Editar Producto' : 'Nuevo Producto';
        $producto   = $esEdicion
            ? $this->productoModel->findById((int) $id)
            : new ProductoEntity();

        // Si viene un ID pero no existe el producto → 404
        if ($esEdicion && !$producto->Found) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'El producto no existe.',
            ];
            header('Location: ' . APP_URL . 'Productos/index');
            exit();
        }

        // Variantes del producto si las tiene
        $variantes = $esEdicion && $producto->tieneVariantes()
            ? $this->varianteModel->findByProducto((int) $id)
            : [];

        // Categorías para el select
        $categoriaModel = new CategoriaModel();
        $categorias     = $categoriaModel->findActivas();

        require_once VIEWS_PATH . 'Productos' . DS . 'registry.php';
    }

    // ─────────────────────────────────────────────
    // SAVE — Guardar producto (POST)
    // URL: /Productos/save
    // ─────────────────────────────────────────────
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Productos/index');
            exit();
        }

        $id = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;

        Auth::require($esEdicion ? 'productos.editar' : 'productos.crear');

        // Validar CSRF
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error de seguridad',
                'text'  => 'Token inválido. Intenta de nuevo.',
            ];
            header('Location: ' . APP_URL . 'Productos/index');
            exit();
        }

        // Sanitizar entradas
        $nombre         = htmlspecialchars(strip_tags(trim($_POST['nombre']        ?? '')));
        $descripcion    = htmlspecialchars(strip_tags(trim($_POST['descripcion']   ?? '')));
        $categoriaId    = (int) ($_POST['categoria_id']    ?? 0);
        $precioBase     = !empty($_POST['precio_base']) ? (float) $_POST['precio_base'] : null;
        $tieneVariantes = isset($_POST['tiene_variantes']) ? 1 : 0;
        $codigoBarras = trim($_POST['codigo_barras'] ?? '') ?: null;
        $stock          = $tieneVariantes ? 0 : (int) ($_POST['stock'] ?? 0);

        // Validaciones básicas
        if (empty($nombre) || $categoriaId === 0) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Campos requeridos',
                'text'  => 'El nombre y la categoría son obligatorios.',
            ];
            $redirect = $esEdicion
                ? APP_URL . 'Productos/registry/' . $id
                : APP_URL . 'Productos/registry';
            header('Location: ' . $redirect);
            exit();
        }

        // Manejo de imagen
        $imageUrl = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imageUrl = $this->subirImagen($_FILES['imagen'], PRODUCT_IMAGE_UPLOAD_DIR);

            if ($imageUrl === null) {
                $_SESSION['alert'] = [
                    'icon'  => 'error',
                    'title' => 'Error de imagen',
                    'text'  => 'Solo se permiten imágenes JPG, PNG o WEBP menores a 2MB.',
                ];
                $redirect = $esEdicion
                    ? APP_URL . 'Productos/registry/' . $id
                    : APP_URL . 'Productos/registry';
                header('Location: ' . $redirect);
                exit();
            }
        }

        $data = [
            'categoria_id'    => $categoriaId,
            'nombre'          => $nombre,
            'descripcion'     => $descripcion,
            'precio_base'     => $precioBase,
            'tiene_variantes' => $tieneVariantes,
            'stock'           => $stock,
            'codigo_barras'   => $codigoBarras,
            'image_url'       => $imageUrl,
        ];

        if ($esEdicion) {
            $data['id'] = $id;
            $ok = $this->productoModel->update($data);
            $mensaje = $ok ? 'Producto actualizado correctamente.' : 'Error al actualizar el producto.';
        } else {
            $nuevoId = $this->productoModel->insert($data);
            $ok      = $nuevoId > 0;
            $mensaje = $ok ? 'Producto creado correctamente.' : 'Error al crear el producto.';
            if ($ok) $id = $nuevoId;
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito' : 'Error',
            'text'  => $mensaje,
        ];

        // Si tiene variantes redirige al formulario para agregarlas
        if ($ok && $tieneVariantes) {
            header('Location: ' . APP_URL . 'Productos/registry/' . $id);
        } else {
            header('Location: ' . APP_URL . 'Productos/index');
        }
        exit();
    }

    // ─────────────────────────────────────────────
    // TOGGLE — Activar / desactivar producto (POST)
    // URL: /Productos/toggle
    // ─────────────────────────────────────────────
    public function toggle(): void
    {
        Auth::require('productos.editar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        // Validar CSRF
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            http_response_code(403);
            exit();
        }

        $id     = (int) ($_POST['id']     ?? 0);
        $activo = (int) ($_POST['activo'] ?? 0);

        $ok = $this->productoModel->toggleActivo($id, $activo);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit();
    }

    // ─────────────────────────────────────────────
    // DELETE — Eliminar producto (POST)
    // URL: /Productos/delete
    // ─────────────────────────────────────────────
    public function delete(): void
    {
        Auth::require('productos.eliminar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        // Validar CSRF
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            http_response_code(403);
            exit();
        }

        $id = (int) ($_POST['id'] ?? 0);
        $ok = $this->productoModel->delete($id);

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Eliminado' : 'Error',
            'text'  => $ok ? 'Producto desactivado correctamente.' : 'Error al eliminar el producto.',
        ];

        header('Location: ' . APP_URL . 'Productos/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // SAVE VARIANTE — Guardar variante (POST)
    // URL: /Productos/saveVariante
    // ─────────────────────────────────────────────
    public function saveVariante(): void
    {
        Auth::require('productos.editar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            http_response_code(403);
            exit();
        }

        $varianteId = (int) ($_POST['variante_id']  ?? 0);
        $productoId = (int) ($_POST['producto_id']  ?? 0);
        $esEdicion  = $varianteId > 0;

        $nombre       = htmlspecialchars(strip_tags(trim($_POST['nombre']      ?? '')));
        $precio       = !empty($_POST['precio']) ? (float) $_POST['precio'] : null;
        $stock        = (int) ($_POST['stock']         ?? 0);
        $codigoBarras = trim($_POST['codigo_barras']   ?? '') ?: null;
        $orden        = (int) ($_POST['orden']         ?? 0);

        $imageUrl = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imageUrl = $this->subirImagen($_FILES['imagen'], VARIANTE_IMAGE_UPLOAD_DIR);
        }

        $data = [
            'producto_id'   => $productoId,
            'nombre'        => $nombre,
            'precio'        => $precio,
            'stock'         => $stock,
            'codigo_barras' => $codigoBarras,
            'image_url'     => $imageUrl,
            'orden'         => $orden,
        ];

        try {
            if ($esEdicion) {
                $data['id'] = $varianteId;
                $ok = $this->varianteModel->update($data);
            } else {
                $ok = $this->varianteModel->insert($data) > 0;
            }

            $_SESSION['alert'] = [
                'icon'  => $ok ? 'success' : 'error',
                'title' => $ok ? 'Éxito' : 'Error',
                'text'  => $ok
                    ? 'Variante guardada correctamente.'
                    : 'Error al guardar la variante.',
            ];

        } catch (\RuntimeException $e) {
            $mensaje = str_contains($e->getMessage(), '1062')
                ? 'El código de barras ya está registrado en otra variante.'
                : 'Error al guardar la variante.';

            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'No se pudo guardar',
                'text'  => $mensaje,
            ];
        }

        header('Location: ' . APP_URL . 'Productos/registry/' . $productoId);
        exit();
    }

    // ─────────────────────────────────────────────
    // DELETE VARIANTE — Eliminar variante (POST)
    // URL: /Productos/deleteVariante
    // ─────────────────────────────────────────────
    public function deleteVariante(): void
    {
        Auth::require('productos.editar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            http_response_code(403);
            exit();
        }

        $varianteId = (int) ($_POST['variante_id'] ?? 0);
        $productoId = (int) ($_POST['producto_id'] ?? 0);

        $ok = $this->varianteModel->delete($varianteId);

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Eliminado' : 'Error',
            'text'  => $ok ? 'Variante eliminada.' : 'Error al eliminar la variante.',
        ];

        header('Location: ' . APP_URL . 'Productos/registry/' . $productoId);
        exit();
    }

    // ─────────────────────────────────────────────
    // HELPER — Subir imagen
    // ─────────────────────────────────────────────
    private function subirImagen(array $file, string $destino): ?string
    {
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize               = 2 * 1024 * 1024; // 2MB

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validar extensión y tamaño
        if (!in_array($extension, $extensionesPermitidas, true)) return null;
        if ($file['size'] > $maxSize) return null;
        if ($file['error'] !== UPLOAD_ERR_OK) return null;

        // Verificar que es una imagen real con finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $mimesPermitidos, true)) return null;

        // Nombre único para evitar colisiones
        $nombreArchivo = uniqid('img_', true) . '.' . $extension;
        $rutaCompleta  = $destino . $nombreArchivo;

        if (!move_uploaded_file($file['tmp_name'], $rutaCompleta)) {
            return null;
        }

        return $nombreArchivo;
    }
}