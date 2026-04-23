<?php

class TiendaController
{
    private ProductoModel      $productoModel;
    private CategoriaModel     $categoriaModel;
    private BannerModel        $bannerModel;
    private ComboModel         $comboModel;
    private PedidoModel        $pedidoModel;
    private CitaModel          $citaModel;
    private ServicioModel      $servicioModel;
    private ClienteModel       $clienteModel;
    private ZonaModel          $zonaModel;
    private NotificacionModel  $notifModel;

    public function __construct()
    {
        $this->productoModel  = new ProductoModel();
        $this->categoriaModel = new CategoriaModel();
        $this->bannerModel    = new BannerModel();
        $this->comboModel     = new ComboModel();
        $this->pedidoModel    = new PedidoModel();
        $this->citaModel      = new CitaModel();
        $this->servicioModel  = new ServicioModel();
        $this->clienteModel   = new ClienteModel();
        $this->zonaModel      = new ZonaModel();
        $this->notifModel     = new NotificacionModel();
    }

    public function index(): void
    {
        $pageTitle           = 'Inicio';
        $banners             = $this->bannerModel->findActivos();
        $productos           = $this->productoModel->findActivos();
        $combos              = $this->comboModel->findActivos();
        $categorias          = $this->categoriaModel->findAll();
        $productosDestacados = array_slice($productos, 0, 8);
        $this->render('inicio.php', compact('pageTitle','banners','productosDestacados','combos','categorias'));
    }

    public function catalogo(): void
    {
        $pageTitle   = 'Catálogo';
        $categoriaId = (int) ($_GET['categoria'] ?? 0);
        $categorias  = $this->categoriaModel->findAll();
        $productos   = $this->productoModel->findActivos();
        if ($categoriaId > 0) {
            $productos = array_values(array_filter($productos, fn($p) => (int)$p->categoria_id === $categoriaId));
        }
        $this->render('catalogo.php', compact('pageTitle','productos','categorias','categoriaId'));
    }

    public function producto(string $id = ''): void
    {
        if (empty($id) || !is_numeric($id)) {
            header('Location: ' . APP_URL . 'Tienda/catalogo'); exit();
        }
        $producto = $this->productoModel->findById((int) $id);
        if (!$producto->Found || !$producto->activo) {
            header('Location: ' . APP_URL . 'Tienda/catalogo'); exit();
        }
        $variantes = $this->productoModel->findVariantes((int) $id);
        $pageTitle = $producto->nombre;
        $this->render('producto.php', compact('pageTitle','producto','variantes'));
    }

    public function carrito(): void
    {
        $pageTitle = 'Carrito';
        $zonas     = $this->zonaModel->findActivas();
        $this->render('carrito.php', compact('pageTitle','zonas'));
    }

    // ─────────────────────────────────────────────
    // CHECKOUT — genera pedido + notificación
    // ─────────────────────────────────────────────
    public function checkout(): void
    {
        $this->requireCliente();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Tienda/carrito'); exit();
        }
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            header('Location: ' . APP_URL . 'Tienda/carrito'); exit();
        }

        $clienteId   = !empty($_SESSION['cliente']['id']) ? (int)$_SESSION['cliente']['id'] : null;
        $waNumero    = htmlspecialchars(strip_tags(trim($_POST['wa_numero']       ?? '')));
        $tipoEntrega = htmlspecialchars(strip_tags(trim($_POST['tipo_entrega']    ?? 'Retiro')));
        $direccion   = htmlspecialchars(strip_tags(trim($_POST['direccion_envio'] ?? '')));
        $zonaId      = !empty($_POST['zona_id']) ? (int)$_POST['zona_id'] : null;
        $nota        = htmlspecialchars(strip_tags(trim($_POST['nota']            ?? '')));
        $items       = json_decode($_POST['items'] ?? '[]', true);

        if (empty($items)) {
            header('Location: ' . APP_URL . 'Tienda/carrito'); exit();
        }

        $subtotal   = array_reduce($items, fn($sum, $i) => $sum + (float)$i['precio'] * (int)$i['cantidad'], 0);
        $costoEnvio = 0;

        if ($tipoEntrega === 'Envio' && $zonaId) {
            $zona       = $this->zonaModel->findById($zonaId);
            $costoEnvio = $zona ? (float)$zona['costo'] : 0;
        }

        $total   = $subtotal + $costoEnvio;
        $codigo  = $this->pedidoModel->generarCodigo();

        $pedidoId = $this->pedidoModel->insert([
            'codigo'          => $codigo,
            'cliente_id'      => $clienteId,
            'wa_numero'       => $waNumero ?: null,
            'tipo_entrega'    => $tipoEntrega,
            'direccion_envio' => $tipoEntrega === 'Envio' ? $direccion : null,
            'zona_id'         => $zonaId,
            'subtotal'        => $subtotal,
            'costo_envio'     => $costoEnvio,
            'total'           => $total,
            'nota'            => $nota ?: null,
        ]);

        if ($pedidoId > 0) {
            foreach ($items as $item) {
                $this->pedidoModel->insertDetalle([
                    'pedido_id'       => $pedidoId,
                    'producto_id'     => $item['id'],
                    'variante_id'     => $item['varianteId'] ?? null,
                    'nombre_producto' => $item['nombre'],
                    'precio_unit'     => $item['precio'],
                    'cantidad'        => $item['cantidad'],
                    'subtotal'        => $item['precio'] * $item['cantidad'],
                ]);
            }

            // ── Notificación al panel ─────────────
            $clienteNombre = $_SESSION['cliente']['nombre'] ?? 'Cliente';
            $this->notifModel->nuevoPedido($codigo, $clienteNombre, $total);

            $_SESSION['pedido_exitoso'] = $pedidoId;
            header('Location: ' . APP_URL . 'Tienda/pedidoExitoso');
        } else {
            header('Location: ' . APP_URL . 'Tienda/carrito?error=1');
        }
        exit();
    }

    public function pedidoExitoso(): void
    {
        $pedidoId = $_SESSION['pedido_exitoso'] ?? null;
        if (!$pedidoId) { header('Location: ' . APP_URL . 'Tienda/index'); exit(); }
        unset($_SESSION['pedido_exitoso']);
        $pedido    = $this->pedidoModel->findById((int) $pedidoId);
        $detalle   = $this->pedidoModel->findDetalle((int) $pedidoId);
        $pageTitle = 'Pedido confirmado';
        $this->render('pedido_exitoso.php', compact('pageTitle','pedido','detalle'));
    }

    public function misPedidos(): void
    {
        $this->requireCliente();
        $pageTitle = 'Mis Pedidos';
        $pedidos   = $this->pedidoModel->findByCliente((int)$_SESSION['cliente']['id']);
        $this->render('mis_pedidos.php', compact('pageTitle','pedidos'));
    }

    // ─────────────────────────────────────────────
    // CITAS — Calendario público
    // ─────────────────────────────────────────────
    public function citas(): void
    {
        $pageTitle = 'Agendar Cita';
        $config    = $this->citaModel->getConfig();
        $servicios = $this->servicioModel->findActivos();
        $anio      = (int) ($_GET['anio'] ?? date('Y'));
        $mes       = (int) ($_GET['mes']  ?? date('n'));
        if ($mes < 1)  { $mes = 12; $anio--; }
        if ($mes > 12) { $mes = 1;  $anio++; }
        $citasMes      = $this->citaModel->findByMes($anio, $mes);
        $citasPorFecha = [];
        foreach ($citasMes as $cita) { $citasPorFecha[$cita['fecha']][] = $cita; }
        $this->render('citas.php', compact('pageTitle','config','servicios','anio','mes','citasPorFecha'));
    }

    // ─────────────────────────────────────────────
    // AGENDAR CITA — genera cita + notificación
    // ─────────────────────────────────────────────
    public function agendarCita(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Tienda/citas'); exit();
        }
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            header('Location: ' . APP_URL . 'Tienda/citas'); exit();
        }

        $clienteId  = !empty($_SESSION['cliente']['id']) ? (int)$_SESSION['cliente']['id'] : null;
        $servicioId = (int) ($_POST['servicio_id'] ?? 0);
        $fecha      = htmlspecialchars(strip_tags(trim($_POST['fecha']       ?? '')));
        $hora       = htmlspecialchars(strip_tags(trim($_POST['hora_inicio'] ?? '')));
        $duracion   = (int) ($_POST['duracion'] ?? 60);
        $precio     = (float) ($_POST['precio'] ?? 0);
        $nota       = htmlspecialchars(strip_tags(trim($_POST['nota']        ?? '')));

        if (!$servicioId || !$fecha || !$hora) {
            header('Location: ' . APP_URL . 'Tienda/citas?error=campos'); exit();
        }

        $config    = $this->citaModel->getConfig();
        $capacidad = (int) ($config['capacidad_simultanea'] ?? 1);
        $ocupadas  = $this->citaModel->verificarDisponibilidad($fecha, $hora, $duracion, 0);

        if ($ocupadas >= $capacidad) {
            header('Location: ' . APP_URL . 'Tienda/citas?error=ocupado'); exit();
        }

        $citaId = $this->citaModel->insert([
            'cliente_id'  => $clienteId,
            'servicio_id' => $servicioId,
            'user_id'     => null,
            'fecha'       => $fecha,
            'hora_inicio' => $hora,
            'duracion'    => $duracion,
            'precio'      => $precio,
            'nota'        => $nota ?: null,
        ]);

        if ($citaId > 0) {
            // ── Notificación al panel ─────────────
            $servicio      = $this->servicioModel->findById($servicioId);
            $clienteNombre = $_SESSION['cliente']['nombre'] ?? 'Cliente sin cuenta';
            $fechaFormato  = date('d/m/Y', strtotime($fecha));
            $horaFormato   = date('h:i A', strtotime($hora));

            $this->notifModel->nuevaCita(
                $clienteNombre,
                $servicio->nombre ?? 'Servicio',
                $fechaFormato,
                $horaFormato
            );

            $_SESSION['cita_exitosa'] = $citaId;
        }

        header('Location: ' . APP_URL . 'Tienda/citaExitosa');
        exit();
    }

    public function citaExitosa(): void
    {
        $citaId = $_SESSION['cita_exitosa'] ?? null;
        if (!$citaId) { header('Location: ' . APP_URL . 'Tienda/citas'); exit(); }
        unset($_SESSION['cita_exitosa']);
        $cita      = $this->citaModel->findById((int) $citaId);
        $pageTitle = 'Cita agendada';
        $this->render('cita_exitosa.php', compact('pageTitle','cita'));
    }

    public function misCitas(): void
    {
        $this->requireCliente();
        $pageTitle = 'Mis Citas';
        $citas     = $this->citaModel->findByCliente((int)$_SESSION['cliente']['id']);
        $this->render('mis_citas.php', compact('pageTitle','citas'));
    }

    public function registro(): void
    {
        if (!empty($_SESSION['cliente'])) { header('Location: ' . APP_URL . 'Tienda/index'); exit(); }
        $pageTitle = 'Crear cuenta';
        $error     = $_GET['error'] ?? null;
        $this->render('registro.php', compact('pageTitle','error'));
    }

    public function guardarRegistro(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'Tienda/registro'); exit(); }
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            header('Location: ' . APP_URL . 'Tienda/registro?error=csrf'); exit();
        }

        $nombre    = htmlspecialchars(strip_tags(trim($_POST['nombre']    ?? '')));
        $email     = htmlspecialchars(strip_tags(trim($_POST['email']     ?? '')));
        $telefono  = htmlspecialchars(strip_tags(trim($_POST['telefono']  ?? '')));
        $password  = trim($_POST['password']  ?? '');
        $password2 = trim($_POST['password2'] ?? '');

        if (empty($nombre) || empty($email) || empty($password)) {
            header('Location: ' . APP_URL . 'Tienda/registro?error=campos'); exit();
        }
        if ($password !== $password2) {
            header('Location: ' . APP_URL . 'Tienda/registro?error=password'); exit();
        }
        if ($this->clienteModel->emailExists($email)) {
            header('Location: ' . APP_URL . 'Tienda/registro?error=email'); exit();
        }

        $id = $this->clienteModel->insert([
            'nombre'   => $nombre,
            'email'    => $email,
            'telefono' => $telefono ?: null,
            'password' => password_hash($password, PASSWORD_BCRYPT),
        ]);

        if ($id > 0) {
            $cliente = $this->clienteModel->findById($id);
            $_SESSION['cliente'] = [
                'id'       => $cliente->id,
                'nombre'   => $cliente->nombre,
                'email'    => $cliente->email,
                'telefono' => $cliente->telefono,
            ];
            header('Location: ' . APP_URL . 'Tienda/index');
        } else {
            header('Location: ' . APP_URL . 'Tienda/registro?error=server');
        }
        exit();
    }

    public function login(): void
    {
        if (!empty($_SESSION['cliente'])) { header('Location: ' . APP_URL . 'Tienda/index'); exit(); }
        $pageTitle = 'Iniciar sesión';
        $error     = $_GET['error'] ?? null;
        $this->render('login.php', compact('pageTitle','error'));
    }

    public function procesarLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'Tienda/login'); exit(); }

        $email    = htmlspecialchars(strip_tags(trim($_POST['email']    ?? '')));
        $password = trim($_POST['password'] ?? '');
        $cliente  = $this->clienteModel->findByEmail($email);

        if (!$cliente->Found || !password_verify($password, $cliente->password ?? '')) {
            header('Location: ' . APP_URL . 'Tienda/login?error=credenciales'); exit();
        }
        if (!$cliente->isActivo()) {
            header('Location: ' . APP_URL . 'Tienda/login?error=inactivo'); exit();
        }

        $_SESSION['cliente'] = [
            'id'       => $cliente->id,
            'nombre'   => $cliente->nombre,
            'email'    => $cliente->email,
            'telefono' => $cliente->telefono,
        ];

        $redirect = $_SESSION['redirect_tienda'] ?? APP_URL . 'Tienda/index';
        unset($_SESSION['redirect_tienda']);
        header('Location: ' . $redirect);
        exit();
    }

    public function logout(): void
    {
        unset($_SESSION['cliente']);
        header('Location: ' . APP_URL . 'Tienda/index');
        exit();
    }

    private function requireCliente(): void
    {
        if (empty($_SESSION['cliente'])) {
            $_SESSION['redirect_tienda'] = APP_URL . ($_GET['url'] ?? '');
            header('Location: ' . APP_URL . 'Tienda/login');
            exit();
        }
    }

    private function render(string $vista, array $vars = []): void
    {
        extract($vars);
        $urlActual = strtolower(trim($_GET['url'] ?? '', '/'));

        ob_start();
        require_once VIEWS_PATH . 'Tienda' . DS . $vista;
        $content = ob_get_clean();

        ob_start();
        require_once ROOT . 'Template' . DS . 'Tienda' . DS . 'index.php';
        $template = ob_get_clean();

        $output = str_replace('{JBODY}',    $content, $template);
        $output = str_replace('{JSCRIPTS}', '',        $output);
        echo $output;
    }
}