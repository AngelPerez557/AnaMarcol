<?php

class UsuariosController
{
    private UserModel $userModel;
    private RoleModel $roleModel;

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // ─────────────────────────────────────────────
    public function __construct()
    {
        Auth::check();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Listado de usuarios
    // URL: /Usuarios/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('usuarios.ver');

        $pageTitle = 'Usuarios';
        $usuarios  = $this->userModel->findAll();

        require_once VIEWS_PATH . 'Usuarios' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // REGISTRY — Crear o editar usuario
    // URL: /Usuarios/registry      → crear
    // URL: /Usuarios/registry/{id} → editar
    // ─────────────────────────────────────────────
    public function registry(string $id = ''): void
    {
        $esEdicion = !empty($id) && is_numeric($id);
        Auth::require($esEdicion ? 'usuarios.editar' : 'usuarios.crear');

        $pageTitle = $esEdicion ? 'Editar Usuario' : 'Nuevo Usuario';
        $usuario   = $esEdicion
            ? $this->userModel->findById((int) $id)
            : new UserEntity();

        if ($esEdicion && !$usuario->Found) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'El usuario no existe.',
            ];
            header('Location: ' . APP_URL . 'Usuarios/index');
            exit();
        }

        // Evitar que un usuario se edite a sí mismo el rol
        // para no perder acceso accidentalmente
        $esPropioUsuario = $esEdicion && (int)$id === Auth::id();
        $roles           = $this->roleModel->findAll();

        require_once VIEWS_PATH . 'Usuarios' . DS . 'Registry.php';
    }

    // ─────────────────────────────────────────────
    // SAVE — Guardar usuario (POST)
    // URL: /Usuarios/save
    // ─────────────────────────────────────────────
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Usuarios/index');
            exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;

        Auth::require($esEdicion ? 'usuarios.editar' : 'usuarios.crear');

        // Validar CSRF
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error de seguridad',
                'text'  => 'Token inválido.',
            ];
            header('Location: ' . APP_URL . 'Usuarios/index');
            exit();
        }

        // Sanitizar
        $nombre   = htmlspecialchars(strip_tags(trim($_POST['nombre']   ?? '')));
        $username = preg_replace('/[^a-zA-Z0-9_]/', '', trim($_POST['username'] ?? ''));
        $email    = htmlspecialchars(strip_tags(trim($_POST['email']    ?? '')));
        $telefono = htmlspecialchars(strip_tags(trim($_POST['telefono'] ?? '')));
        $rolId    = (int) ($_POST['rol_id'] ?? 0);
        $activo   = isset($_POST['activo']) ? 1 : 0;
        $password = trim($_POST['password'] ?? '');

        // Validaciones
        if (!empty($username) && $this->userModel->usernameExists($username, $esEdicion ? $id : 0)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Usuario duplicado',
                'text'  => 'Ese nombre de usuario ya está en uso.',
            ];
            $redirect = $esEdicion
                ? APP_URL . 'Usuarios/registry/' . $id
                : APP_URL . 'Usuarios/registry';
            header('Location: ' . $redirect);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Correo inválido',
                'text'  => 'El formato del correo no es válido.',
            ];
            $redirect = $esEdicion
                ? APP_URL . 'Usuarios/registry/' . $id
                : APP_URL . 'Usuarios/registry';
            header('Location: ' . $redirect);
            exit();
        }

        // Verificar email duplicado
        if ($this->userModel->emailExists($email, $esEdicion ? $id : 0)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Correo duplicado',
                'text'  => 'Ya existe un usuario con ese correo.',
            ];
            $redirect = $esEdicion
                ? APP_URL . 'Usuarios/registry/' . $id
                : APP_URL . 'Usuarios/registry';
            header('Location: ' . $redirect);
            exit();
        }

        // Manejo de foto
        $fotoActual = null;
        if ($esEdicion) {
            $usuarioActual = $this->userModel->findById($id);
            $fotoActual    = $usuarioActual->foto;
        }

        $foto = $fotoActual;
        if (!empty($_FILES['foto']['name'])) {
            $foto = $this->subirFoto($_FILES['foto']);
            if ($foto === null) {
                $_SESSION['alert'] = [
                    'icon'  => 'error',
                    'title' => 'Error de imagen',
                    'text'  => 'Solo JPG, PNG o WEBP. Máximo 2MB.',
                ];
                $redirect = $esEdicion
                    ? APP_URL . 'Usuarios/registry/' . $id
                    : APP_URL . 'Usuarios/registry';
                header('Location: ' . $redirect);
                exit();
            }
        }

        $data = [
            'nombre'   => $nombre,
            'username' => $username ?: null,
            'email'    => $email,
            'telefono' => $telefono ?: null,
            'rol_id'   => $rolId,
            'activo'   => $activo,
            'foto'     => $foto,
        ];

        if ($esEdicion) {
            $data['id'] = $id;
            $ok = $this->userModel->update($data);

            // Actualizar contraseña solo si se ingresó una nueva
            if ($ok && !empty($password)) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $this->userModel->updatePassword($id, $hash);
            }

            $mensaje = $ok ? 'Usuario actualizado correctamente.' : 'Error al actualizar.';
        } else {
            // En creación la contraseña es obligatoria
            if (empty($password)) {
                $_SESSION['alert'] = [
                    'icon'  => 'warning',
                    'title' => 'Contraseña requerida',
                    'text'  => 'Debes ingresar una contraseña para el nuevo usuario.',
                ];
                header('Location: ' . APP_URL . 'Usuarios/registry');
                exit();
            }

            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
            $nuevoId = $this->userModel->insert($data);
            $ok      = $nuevoId > 0;
            $mensaje = $ok ? 'Usuario creado correctamente.' : 'Error al crear el usuario.';
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito'   : 'Error',
            'text'  => $mensaje,
        ];

        header('Location: ' . APP_URL . 'Usuarios/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // TOGGLE — Activar / desactivar (POST — JSON)
    // URL: /Usuarios/toggle
    // ─────────────────────────────────────────────
    public function toggle(): void
    {
        Auth::require('usuarios.editar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            http_response_code(403);
            exit();
        }

        $id = (int) ($_POST['id'] ?? 0);

        // No permitir desactivarse a sí mismo
        if ($id === Auth::id()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'No puedes desactivar tu propio usuario.'
            ]);
            exit();
        }

        $activo = (int) ($_POST['activo'] ?? 0);
        $ok     = $this->userModel->toggleActivo($id, $activo);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit();
    }

    // ─────────────────────────────────────────────
    // DELETE — Eliminar usuario (POST)
    // URL: /Usuarios/delete
    // ─────────────────────────────────────────────
    public function delete(): void
    {
        Auth::require('usuarios.eliminar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            http_response_code(403);
            exit();
        }

        $id = (int) ($_POST['id'] ?? 0);

        // No permitir eliminarse a sí mismo
        if ($id === Auth::id()) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'No permitido',
                'text'  => 'No puedes eliminar tu propio usuario.',
            ];
            header('Location: ' . APP_URL . 'Usuarios/index');
            exit();
        }

        $ok = $this->userModel->delete($id);

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Eliminado' : 'Error',
            'text'  => $ok ? 'Usuario eliminado correctamente.' : 'Error al eliminar.',
        ];

        header('Location: ' . APP_URL . 'Usuarios/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // MARCAR TOUR — marca el tour como completado
    // URL: /Usuarios/marcarTour  (POST — JSON)
    // ─────────────────────────────────────────────
    public function marcarTour(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); exit();
        }
        if (!isset($_POST['csrf_token']) ||
            $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            http_response_code(403); exit();
        }

        $id = (int) ($_POST['id'] ?? Auth::id());
        if ($id > 0) {
            $this->userModel->marcarTour($id);
            $_SESSION['user']['tour_completado'] = 1;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }

    // ─────────────────────────────────────────────
    // ACTIVAR TOUR — marca el tour como no completado
    // URL: /Usuarios/activarTour  (POST — JSON)
    // ─────────────────────────────────────────────
    public function activarTour(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); exit();
        }
        if (!isset($_POST['csrf_token']) ||
            $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            http_response_code(403); exit();
        }

        $id = (int) ($_POST['id'] ?? Auth::id());
        if ($id > 0) {
            $this->userModel->activarTour($id);
            $_SESSION['user']['tour_completado'] = 0;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }

    // ─────────────────────────────────────────────
    // HELPER — Subir foto de usuario
    // ─────────────────────────────────────────────
    private function subirFoto(array $file): ?string
    {
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize               = 2 * 1024 * 1024;

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas, true)) return null;
        if ($file['size'] > $maxSize) return null;
        if ($file['error'] !== UPLOAD_ERR_OK) return null;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $mimesPermitidos, true)) return null;

        // Crear carpeta si no existe
        $destino = ROOT . 'Content' . DS . 'Demo' . DS . 'img' . DS . 'Usuarios' . DS;
        if (!is_dir($destino)) mkdir($destino, 0755, true);

        $nombreArchivo = uniqid('usr_', true) . '.' . $extension;
        $rutaCompleta  = $destino . $nombreArchivo;

        if (!move_uploaded_file($file['tmp_name'], $rutaCompleta)) return null;

        return $nombreArchivo;
    }
    
}