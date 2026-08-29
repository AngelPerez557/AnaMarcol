<?php

/**
 * TiendaController — Tienda pública (sin cuentas de usuario).
 *
 * Cambio de modelo (2026-08-28):
 *   • La tienda NO tiene login, registro ni perfil de cliente.
 *   • El carrito NO genera pedidos: genera COTIZACIONES que el
 *     cliente envía por WhatsApp al estudio.
 *   • El sistema NO descuenta stock ni registra ventas desde la web.
 *     AnaMarcolPOS (app local) sigue siendo el único punto de venta.
 *
 * Todo acceso a datos pasa por Models. Ninguna consulta vive aquí.
 */
class TiendaController
{
    private ProductoModel     $productoModel;
    private CategoriaModel    $categoriaModel;
    private BannerModel       $bannerModel;
    private ComboModel        $comboModel;
    private CitaModel         $citaModel;
    private ServicioModel     $servicioModel;
    private ClienteModel      $clienteModel;
    private ZonaModel         $zonaModel;
    private NotificacionModel $notifModel;
    private GaleriaModel      $galeriaModel;
    private DescuentoModel    $descuentoModel;
    private CotizacionModel   $cotizacionModel;

    // Límites defensivos del carrito — evitan payloads absurdos
    private const MAX_ITEMS    = 50;
    private const MAX_CANTIDAD = 99;

    public function __construct()
    {
        $this->productoModel   = new ProductoModel();
        $this->categoriaModel  = new CategoriaModel();
        $this->bannerModel     = new BannerModel();
        $this->comboModel      = new ComboModel();
        $this->citaModel       = new CitaModel();
        $this->servicioModel   = new ServicioModel();
        $this->clienteModel    = new ClienteModel();
        $this->zonaModel       = new ZonaModel();
        $this->notifModel      = new NotificacionModel();
        $this->galeriaModel    = new GaleriaModel();
        $this->descuentoModel  = new DescuentoModel();
        $this->cotizacionModel = new CotizacionModel();
    }

    // ─────────────────────────────────────────────
    // CATÁLOGO PÚBLICO
    // ─────────────────────────────────────────────

    public function index(): void
    {
        $pageTitle           = 'Inicio';
        $banners             = $this->bannerModel->findActivos();
        $productos           = $this->productoModel->findActivos();
        $combos              = $this->comboModel->findActivos();
        $categorias          = $this->categoriaModel->findAll();
        $galeria             = $this->galeriaModel->findActivas();
        $descuentoActivo     = $this->descuentoModel->getActivo();
        $productosDestacados = array_slice($productos, 0, 8);

        // ── Cargar productos de cada combo ──────────
        $comboProductos = [];
        foreach ($combos as $combo) {
            $comboProductos[$combo->id] = $this->comboModel->findProductos($combo->id);
        }

        $this->render('Inicio.php', compact(
            'pageTitle','banners','productosDestacados','combos',
            'categorias','galeria','descuentoActivo','comboProductos'
        ));
    }

    public function catalogo(string $catId = ''): void
    {
        $pageTitle       = 'Catálogo';
        $categoriaId     = !empty($catId) ? (int)$catId : (int)($_GET['categoria'] ?? 0);
        $categorias      = $this->categoriaModel->findAll();
        $productos       = $this->productoModel->findActivos();
        $descuentoActivo = $this->descuentoModel->getActivo();

        if ($categoriaId > 0) {
            $productos = array_values(array_filter(
                $productos, fn($p) => (int)$p->categoria_id === $categoriaId
            ));
        }

        $this->render('Catalogo.php', compact(
            'pageTitle','productos','categorias','categoriaId','descuentoActivo'
        ));
    }

    public function producto(string $id = ''): void
    {
        $descuentoActivo = $this->descuentoModel->getActivo();

        // Acepta /Tienda/producto/5 y /Tienda/producto/5-nombre-del-producto
        $idNum = (int) $id;
        if (!$idNum) {
            header('Location: ' . APP_URL . 'Tienda/catalogo'); exit();
        }

        $producto = $this->productoModel->findById($idNum);
        if (!$producto->Found || !$producto->activo) {
            header('Location: ' . APP_URL . 'Tienda/catalogo'); exit();
        }

        $variantes = $this->productoModel->findVariantes($idNum);
        $pageTitle = $producto->nombre;

        $this->render('Producto.php', compact('pageTitle','producto','variantes','descuentoActivo'));
    }

    public function carrito(): void
    {
        $pageTitle = 'Cotizar';
        $zonas     = $this->zonaModel->findActivas();
        $this->render('Carrito.php', compact('pageTitle','zonas'));
    }

    // ─────────────────────────────────────────────
    // VERIFICAR STOCK — endpoint AJAX
    // Informativo: el stock real vive en AnaMarcolPOS.
    // URL: /Tienda/verificarStock  (POST — JSON)
    // ─────────────────────────────────────────────
    public function verificarStock(): void
    {
        header('Content-Type: application/json');

        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $varianteId = (int) ($_POST['variante_id'] ?? 0);
        $cantidad   = (int) ($_POST['cantidad']    ?? 1);

        if (!$productoId) {
            echo json_encode(['disponible' => false, 'mensaje' => 'Producto inválido']);
            exit();
        }

        if ($varianteId > 0) {
            $variante = $this->productoModel->findVarianteById($varianteId);
            if (!$variante || !$variante->activo || $variante->stock < $cantidad) {
                echo json_encode([
                    'disponible' => false,
                    'mensaje'    => 'Esta opción no está disponible en la cantidad solicitada.',
                    'stock'      => $variante ? (int)$variante->stock : 0,
                ]);
                exit();
            }
        } else {
            $producto = $this->productoModel->findById($productoId);
            if (!$producto->Found || !$producto->activo || $producto->stock < $cantidad) {
                echo json_encode([
                    'disponible' => false,
                    'mensaje'    => 'Este producto no está disponible en la cantidad solicitada.',
                    'stock'      => $producto->Found ? (int)$producto->stock : 0,
                ]);
                exit();
            }
        }

        echo json_encode(['disponible' => true]);
        exit();
    }

    // ─────────────────────────────────────────────
    // COTIZAR — reemplaza al antiguo checkout
    // Guarda la cotización y redirige a la pantalla
    // que abre WhatsApp con el mensaje precargado.
    // NO descuenta stock. NO crea venta ni factura.
    // URL: /Tienda/cotizar  (POST)
    // ─────────────────────────────────────────────
    public function cotizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Tienda/carrito'); exit();
        }
        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            $this->alertaCarrito('Sesión expirada', 'Recarga la página e intenta de nuevo.');
        }

        $nombre      = $this->limpiar($_POST['nombre_cliente'] ?? '');
        $waNumero    = $this->limpiar($_POST['wa_numero']      ?? '');
        $tipoEntrega = ($_POST['tipo_entrega'] ?? 'Retiro') === 'Envio' ? 'Envio' : 'Retiro';
        $direccion   = $this->limpiar($_POST['direccion_envio'] ?? '');
        $zonaId      = !empty($_POST['zona_id']) ? (int)$_POST['zona_id'] : null;
        $nota        = $this->limpiar($_POST['nota'] ?? '');
        $items       = json_decode($_POST['items'] ?? '[]', true);

        if (!is_array($items) || empty($items)) {
            $this->alertaCarrito('Carrito vacío', 'Agrega productos antes de cotizar.');
        }
        if (count($items) > self::MAX_ITEMS) {
            $this->alertaCarrito('Carrito demasiado grande', 'Envía la cotización en partes.');
        }

        // ── Recalcular precios contra la BD ──────────
        // El precio que manda el navegador NO se usa nunca:
        // el total de la cotización debe salir del catálogo.
        $descuento = $this->descuentoModel->getActivo();
        $detalle   = [];
        $subtotal  = 0.0;

        foreach ($items as $item) {
            $productoId = (int) ($item['id']         ?? 0);
            $varianteId = (int) ($item['varianteId'] ?? 0);
            $cantidad   = max(1, min(self::MAX_CANTIDAD, (int) ($item['cantidad'] ?? 1)));

            $producto = $this->productoModel->findById($productoId);
            if (!$producto->Found || !$producto->isActivo()) {
                continue; // producto retirado del catálogo — se ignora
            }

            $precioBase     = (float) $producto->precio_base;
            $varianteNombre = null;

            if ($varianteId > 0) {
                $variante = $this->productoModel->findVarianteById($varianteId);
                // La variante debe pertenecer al producto — evita cruzar precios
                if (!$variante || (int) $variante->producto_id !== $productoId || !$variante->activo) {
                    continue;
                }
                $variante->precio_base_producto = $precioBase;
                $precioBase     = $variante->getPrecioEfectivo();
                $varianteNombre = $variante->nombre;
            }

            $precioUnit = $this->descuentoModel->precioConDescuento(
                $precioBase, (int) $producto->categoria_id, $descuento
            );

            $subtotalItem = round($precioUnit * $cantidad, 2);
            $subtotal    += $subtotalItem;

            $detalle[] = [
                'producto_id'     => $productoId,
                'variante_id'     => $varianteId ?: null,
                'nombre_producto' => $producto->nombre,
                'variante_nombre' => $varianteNombre,
                'precio_unit'     => $precioUnit,
                'cantidad'        => $cantidad,
                'subtotal'        => $subtotalItem,
            ];
        }

        if (empty($detalle)) {
            $this->alertaCarrito('Sin productos válidos', 'Los productos de tu carrito ya no están disponibles.');
        }

        // ── Costo de envío desde la zona, nunca del POST ──
        $costoEnvio = 0.0;
        if ($tipoEntrega === 'Envio' && $zonaId) {
            $zona = $this->zonaModel->findById($zonaId);
            if (!$zona) {
                $this->alertaCarrito('Zona inválida', 'Selecciona una zona de envío de la lista.');
            }
            $costoEnvio = (float) ($zona['costo'] ?? 0);
        } else {
            $zonaId    = null;
            $direccion = '';
        }

        // ── Validar con la entidad antes de tocar la BD ──
        $cotizacion = new CotizacionEntity([
            'nombre_cliente'  => $nombre,
            'wa_numero'       => $waNumero,
            'tipo_entrega'    => $tipoEntrega,
            'direccion_envio' => $direccion ?: null,
            'subtotal'        => $subtotal,
            'costo_envio'     => $costoEnvio,
            'total'           => $subtotal + $costoEnvio,
        ]);

        if (!$cotizacion->isValid()) {
            $this->alertaCarrito('Datos incompletos', $cotizacion->getFirstError() ?? 'Revisa el formulario.');
        }

        // ── Cliente sin cuenta: se identifica por su WhatsApp ──
        $clienteId = $this->clienteModel->resolverPorTelefono($waNumero, $nombre);

        // ── Persistir ────────────────────────────────
        $codigo       = $this->cotizacionModel->generarCodigo();
        $cotizacionId = $this->cotizacionModel->insert([
            'codigo'          => $codigo,
            'cliente_id'      => $clienteId ?: null,
            'nombre_cliente'  => $nombre,
            'wa_numero'       => $waNumero,
            'tipo_entrega'    => $tipoEntrega,
            'direccion_envio' => $direccion ?: null,
            'zona_id'         => $zonaId,
            'subtotal'        => $subtotal,
            'costo_envio'     => $costoEnvio,
            'total'           => $subtotal + $costoEnvio,
            'nota'            => $nota ?: null,
        ]);

        if ($cotizacionId <= 0) {
            $this->alertaCarrito('No se pudo generar la cotización', 'Intenta de nuevo en un momento.');
        }

        foreach ($detalle as $linea) {
            $linea['cotizacion_id'] = $cotizacionId;
            $this->cotizacionModel->insertDetalle($linea);
        }

        $this->notifModel->nuevaCotizacion($codigo, $nombre, $subtotal + $costoEnvio);

        $_SESSION['cotizacion_exitosa'] = $cotizacionId;
        header('Location: ' . APP_URL . 'Tienda/cotizacionExitosa');
        exit();
    }

    // ─────────────────────────────────────────────
    // COTIZACIÓN GENERADA — pantalla de cierre
    // Muestra el resumen y el botón que abre WhatsApp
    // URL: /Tienda/cotizacionExitosa
    // ─────────────────────────────────────────────
    public function cotizacionExitosa(): void
    {
        $cotizacionId = (int) ($_SESSION['cotizacion_exitosa'] ?? 0);
        if (!$cotizacionId) {
            header('Location: ' . APP_URL . 'Tienda'); exit();
        }
        unset($_SESSION['cotizacion_exitosa']);

        $cotizacion = $this->cotizacionModel->findById($cotizacionId);
        if (!$cotizacion->Found) {
            header('Location: ' . APP_URL . 'Tienda'); exit();
        }

        $detalle   = $this->cotizacionModel->findDetalle($cotizacionId);
        $pageTitle = 'Cotización generada';

        $this->render('CotizacionExitosa.php', compact('pageTitle','cotizacion','detalle'));
    }

    // ─────────────────────────────────────────────
    // CITAS
    // ─────────────────────────────────────────────

    // El calendario de citas de la tienda quedó deshabilitado a propósito —
    // ahora "Agendar Cita" en el menú abre WhatsApp directo para cotizar
    // (ver Template/Tienda/index.php). Estas rutas siguen existiendo por si
    // alguien entra por URL directa, pero redirigen sin dejar agendar nada.
    private function bloquearCitas(): void
    {
        header('Location: https://wa.me/' . WA_NUMBER . '?text=' . urlencode('¡Hola! Quiero cotizar un servicio 💄'));
        exit();
    }

    public function citas(): void
    {
        $this->bloquearCitas();

        $pageTitle = 'Agendar Cita';
        $config    = $this->citaModel->getConfig();
        $servicios = $this->servicioModel->findActivos();
        $anio      = (int) ($_GET['anio'] ?? date('Y'));
        $mes       = (int) ($_GET['mes']  ?? date('n'));

        if ($mes < 1)  { $mes = 12; $anio--; }
        if ($mes > 12) { $mes = 1;  $anio++; }

        $citasMes      = $this->citaModel->findByMes($anio, $mes);
        $citasPorFecha = [];
        foreach ($citasMes as $cita) {
            $citasPorFecha[$cita['fecha']][] = $cita;
        }

        $this->render('Citas.php', compact(
            'pageTitle','config','servicios','anio','mes','citasPorFecha'
        ));
    }

    public function agendarCita(): void
    {
        $this->bloquearCitas();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Tienda/citas'); exit();
        }
        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            header('Location: ' . APP_URL . 'Tienda/citas?error=csrf'); exit();
        }

        // Sin login, el nombre y el WhatsApp son la única identidad del cliente
        $nombre     = $this->limpiar($_POST['nombre_cliente'] ?? '');
        $waNumero   = $this->limpiar($_POST['wa_numero']      ?? '');
        $servicioId = (int)  ($_POST['servicio_id'] ?? 0);
        $fecha      = $this->limpiar($_POST['fecha']       ?? '');
        $hora       = $this->limpiar($_POST['hora_inicio'] ?? '');
        $duracion   = (int)  ($_POST['duracion'] ?? 60);
        $precio     = (float)($_POST['precio']   ?? 0);
        $nota       = $this->limpiar($_POST['nota'] ?? '');

        if (!$servicioId || !$fecha || !$hora || $nombre === '' || $waNumero === '') {
            header('Location: ' . APP_URL . 'Tienda/citas?error=campos'); exit();
        }

        // El precio y la duración salen del servicio, no del formulario
        $servicio = $this->servicioModel->findById($servicioId);
        if (!$servicio->Found || !$servicio->isActivo()) {
            header('Location: ' . APP_URL . 'Tienda/citas?error=campos'); exit();
        }
        $precio   = (float) ($servicio->precio_base ?? $precio);
        $duracion = (int)   ($servicio->duracion    ?? $duracion);

        $config    = $this->citaModel->getConfig();
        $capacidad = (int) ($config['capacidad_simultanea'] ?? 1);
        $ocupadas  = $this->citaModel->verificarDisponibilidad($fecha, $hora, $duracion, 0);

        if ($ocupadas >= $capacidad) {
            header('Location: ' . APP_URL . 'Tienda/citas?error=ocupado'); exit();
        }

        // Límite de citas sin confirmar por dispositivo — no bloquea la IP
        // (compartida entre clientes reales con datos móviles), solo pausa
        // NUEVAS citas de este mismo origen mientras las anteriores sigan
        // pendientes. En cuanto el panel confirma/cancela alguna, se libera
        // sola en el siguiente intento — ver CitaModel::contarPendientesPorOrigen.
        $deviceId = $this->obtenerODefinirDeviceId();
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if ($this->citaModel->contarPendientesPorOrigen($deviceId, $ip) >= 2) {
            header('Location: ' . APP_URL . 'Tienda/citas?error=limite'); exit();
        }

        $clienteId = $this->clienteModel->resolverPorTelefono($waNumero, $nombre);

        $citaId = $this->citaModel->insert([
            'cliente_id'  => $clienteId ?: null,
            'servicio_id' => $servicioId,
            'user_id'     => null,
            'fecha'       => $fecha,
            'hora_inicio' => $hora,
            'duracion'    => $duracion,
            'precio'      => $precio,
            'nota'        => $nota ?: null,
        ]);

        if ($citaId > 0) {
            $this->citaModel->insertOrigen($citaId, $deviceId, $ip);

            $this->notifModel->nuevaCita(
                $nombre,
                $servicio->nombre ?? 'Servicio',
                date('d/m/Y', strtotime($fecha)),
                date('h:i A', strtotime($hora))
            );
            $_SESSION['cita_exitosa'] = $citaId;
        }

        header('Location: ' . APP_URL . 'Tienda/citaExitosa');
        exit();
    }

    public function citaExitosa(): void
    {
        $citaId = $_SESSION['cita_exitosa'] ?? null;
        if (!$citaId) {
            header('Location: ' . APP_URL . 'Tienda/citas'); exit();
        }
        unset($_SESSION['cita_exitosa']);

        $cita      = $this->citaModel->findById((int) $citaId);
        $pageTitle = 'Cita agendada';

        $this->render('CitaExitosa.php', compact('pageTitle','cita'));
    }

    // ─────────────────────────────────────────────
    // INTERNOS
    // ─────────────────────────────────────────────

    private function limpiar(string $valor): string
    {
        return htmlspecialchars(strip_tags(trim($valor)), ENT_QUOTES, 'UTF-8');
    }

    // Identifica al navegador con una cookie de larga duración, solo para
    // contar citas pendientes por origen (ver agendarCita). No es un login,
    // no identifica a la persona — si borra cookies, simplemente recibe un
    // id nuevo (y con eso solo pierde el "recuerdo" de su límite anterior,
    // no una ventaja real: la IP sigue contando también).
    private function obtenerODefinirDeviceId(): string
    {
        $existente = $_COOKIE['am_device_id'] ?? '';
        if (preg_match('/^[a-f0-9]{32}$/', $existente)) {
            return $existente;
        }

        $nuevo = bin2hex(random_bytes(16));
        setcookie('am_device_id', $nuevo, [
            'expires'  => time() + 60 * 60 * 24 * 365,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        return $nuevo;
    }

    // Corta el flujo devolviendo al carrito con una alerta.
    // Termina siempre en exit() — el tipo 'never' se evita a propósito
    // por compatibilidad con PHP 8.0.
    private function alertaCarrito(string $titulo, string $texto): void
    {
        $_SESSION['alert'] = ['icon' => 'warning', 'title' => $titulo, 'text' => $texto];
        header('Location: ' . APP_URL . 'Tienda/carrito');
        exit();
    }

    private function render(string $vista, array $vars = []): void
    {
        extract($vars);
        $urlActual = strtolower(trim($_GET['url'] ?? '', '/'));

        ob_start();
        require VIEWS_PATH . 'Tienda' . DS . $vista;
        $content = ob_get_clean();

        ob_start();
        require ROOT . 'Template' . DS . 'Tienda' . DS . 'index.php';
        $template = ob_get_clean();

        $output = str_replace('{JBODY}',    $content, $template);
        $output = str_replace('{JSCRIPTS}', '',        $output);
        echo $output;
    }
}
