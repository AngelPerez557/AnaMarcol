<?php

class CombosController
{
    private ComboModel   $comboModel;
    private ProductoModel $productoModel;

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // ─────────────────────────────────────────────
    public function __construct()
    {
        Auth::check();
        $this->comboModel   = new ComboModel();
        $this->productoModel = new ProductoModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Listado de combos
    // URL: /Combos/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('combos.ver');

        $pageTitle = 'Combos';
        $combos    = $this->comboModel->findAll();

        require_once VIEWS_PATH . 'Combos' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // REGISTRY — Crear o editar combo
    // URL: /Combos/registry      → crear
    // URL: /Combos/registry/{id} → editar
    // ─────────────────────────────────────────────
    public function registry(string $id = ''): void
    {
        $esEdicion = !empty($id) && is_numeric($id);
        Auth::require($esEdicion ? 'combos.editar' : 'combos.crear');

        $pageTitle = $esEdicion ? 'Editar Combo' : 'Nuevo Combo';
        $combo     = $esEdicion
            ? $this->comboModel->findById((int) $id)
            : new ComboEntity();

        if ($esEdicion && !$combo->Found) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'El combo no existe.',
            ];
            header('Location: ' . APP_URL . 'Combos/index');
            exit();
        }

        // Productos del combo (solo en edición)
        $productosCombo = $esEdicion
            ? $this->comboModel->findProductos((int) $id)
            : [];

        // Todos los productos activos para el selector
        $productos = $this->productoModel->findActivos();

        require_once VIEWS_PATH . 'Combos' . DS . 'Registry.php';
    }

    // ─────────────────────────────────────────────
    // SAVE — Guardar combo (POST)
    // URL: /Combos/save
    // ─────────────────────────────────────────────
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Combos/index');
            exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;

        Auth::require($esEdicion ? 'combos.editar' : 'combos.crear');

        // Validar CSRF
        if (!Csrf::validate()) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error de seguridad',
                'text'  => 'Token inválido.',
            ];
            header('Location: ' . APP_URL . 'Combos/index');
            exit();
        }

        // Sanitizar
        $nombre      = htmlspecialchars(strip_tags(trim($_POST['nombre']      ?? '')));
        $descripcion = htmlspecialchars(strip_tags(trim($_POST['descripcion'] ?? '')));
        $descuento   = $_POST['descuento'] !== '' ? (float) $_POST['descuento'] : null;

        // Validar descuento
        if ($descuento !== null && ($descuento < 0 || $descuento > 100)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Descuento inválido',
                'text'  => 'El descuento debe ser entre 0 y 100.',
            ];
            $redirect = $esEdicion
                ? APP_URL . 'Combos/registry/' . $id
                : APP_URL . 'Combos/registry';
            header('Location: ' . $redirect);
            exit();
        }

        if (empty($nombre)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Campo requerido',
                'text'  => 'El nombre del combo es obligatorio.',
            ];
            $redirect = $esEdicion
                ? APP_URL . 'Combos/registry/' . $id
                : APP_URL . 'Combos/registry';
            header('Location: ' . $redirect);
            exit();
        }

        // Manejo de imagen
        $imageUrl = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imageUrl = $this->subirImagen($_FILES['imagen']);
            if ($imageUrl === null) {
                $_SESSION['alert'] = [
                    'icon'  => 'error',
                    'title' => 'Error de imagen',
                    'text'  => 'Solo JPG, PNG o WEBP. Máximo 2MB.',
                ];
                $redirect = $esEdicion
                    ? APP_URL . 'Combos/registry/' . $id
                    : APP_URL . 'Combos/registry';
                header('Location: ' . $redirect);
                exit();
            }
        }

        $data = [
            'nombre'      => $nombre,
            'descripcion' => $descripcion ?: null,
            'imagen_url'  => $imageUrl,
            'descuento'   => $descuento,
        ];

        if ($esEdicion) {
            $data['id'] = $id;
            $ok = $this->comboModel->update($data);
            $mensaje = $ok ? 'Combo actualizado correctamente.' : 'Error al actualizar.';
        } else {
            $nuevoId = $this->comboModel->insert($data);
            $ok      = $nuevoId > 0;
            $mensaje = $ok ? 'Combo creado. Ahora agrega los productos.' : 'Error al crear el combo.';
            if ($ok) $id = $nuevoId;
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito'   : 'Error',
            'text'  => $mensaje,
        ];

        // Si se creó exitosamente redirige al formulario de edición para agregar productos
        header('Location: ' . APP_URL . 'Combos/registry/' . $id);
        exit();
    }

    // ─────────────────────────────────────────────
    // SAVE PRODUCTOS — Sincroniza productos del combo (POST — JSON)
    // URL: /Combos/saveProductos
    // ─────────────────────────────────────────────
    public function saveProductos(): void
    {
        Auth::require('combos.editar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        if (!Csrf::validate()) {
            http_response_code(403);
            exit();
        }

        $comboId   = (int) ($_POST['combo_id'] ?? 0);
        $itemsJson = $_POST['items'] ?? '[]';
        $items     = json_decode($itemsJson, true);

        if (!$comboId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Combo inválido.']);
            exit();
        }

        $ok = $this->comboModel->syncProductos($comboId, $items);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Productos actualizados.' : 'Error al actualizar productos.',
        ]);
        exit();
    }

    // ─────────────────────────────────────────────
    // TOGGLE — Activar / desactivar (POST — JSON)
    // URL: /Combos/toggle
    // ─────────────────────────────────────────────
    public function toggle(): void
    {
        Auth::require('combos.editar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        if (!Csrf::validate()) {
            http_response_code(403);
            exit();
        }

        $id     = (int) ($_POST['id']     ?? 0);
        $activo = (int) ($_POST['activo'] ?? 0);
        $ok     = $this->comboModel->toggleActivo($id, $activo);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit();
    }

    // ─────────────────────────────────────────────
    // DELETE — Eliminar combo (POST)
    // URL: /Combos/delete
    // ─────────────────────────────────────────────
    public function delete(): void
    {
        Auth::require('combos.eliminar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        if (!Csrf::validate()) {
            http_response_code(403);
            exit();
        }

        $id = (int) ($_POST['id'] ?? 0);
        $ok = $this->comboModel->delete($id);

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Eliminado' : 'Error',
            'text'  => $ok ? 'Combo desactivado correctamente.' : 'Error al eliminar.',
        ];

        header('Location: ' . APP_URL . 'Combos/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // HELPER — Subir imagen del combo
    // ─────────────────────────────────────────────
    private function subirImagen(array $file): ?string
    {
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize               = 2 * 1024 * 1024;
        $extension             = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas, true)) return null;
        if ($file['size'] > $maxSize) return null;
        if ($file['error'] !== UPLOAD_ERR_OK) return null;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $mimesPermitidos, true)) return null;

        $destino = ROOT . 'Content' . DS . 'Demo' . DS . 'img' . DS . 'Combos' . DS;
        if (!is_dir($destino)) mkdir($destino, 0755, true);

        $nombreArchivo = uniqid('combo_', true) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $destino . $nombreArchivo)) return null;

        return $nombreArchivo;
    }
}