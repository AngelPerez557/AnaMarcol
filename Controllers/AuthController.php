<?php

class AuthController
{
    // Carga la vista del formulario de login
    public function index(): void
    {
        if (Auth::isLoggedIn()) {
            header('Location: ' . APP_URL . 'Dashboard/index');
            exit();
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        $extraCss = ['Content/Dist/css/login.css'];

        require_once VIEWS_PATH . 'Auth' . DS . 'login.php';
    }

    // Procesa el formulario de login
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }

        $email    = htmlspecialchars(strip_tags(trim($_POST['email']    ?? '')));
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Por favor completá todos los campos.';
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmailOrUsername($email);

        // Verifica que el usuario exista — Found = false si no existe
        if (!$user->Found) {
            $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }

        // Verifica la contraseña contra el hash
        if (!password_verify($password, $user->password)) {
            $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }

        // Verifica que el usuario esté activo
        if (!$user->isActivo()) {
            $_SESSION['login_error'] = 'Tu cuenta está desactivada. Contactá al administrador.';
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }

        // Obtiene los permisos del rol
        $roleModel = new RoleModel();
        $permisos  = $roleModel->getPermissionsByRole($user->rol_id);

        // Inicia la sesión
        Auth::login([
            'id'       => $user->id,
            'nombre'   => $user->nombre,
            'email'    => $user->email,
            'rol_id'   => $user->rol_id,
            'rol_slug' => $user->rol_slug,
            'permisos' => $permisos,
        ]);

        header('Location: ' . APP_URL . 'Dashboard/index');
        exit();
    }

    // Cierra la sesión
    public function logout(): void
    {
        Auth::logout();
    }

    // Sincroniza dark mode con la sesión PHP
    public function darkMode(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $_SESSION['dark_mode'] = isset($data['dark_mode']) && $data['dark_mode'] === true;

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
}