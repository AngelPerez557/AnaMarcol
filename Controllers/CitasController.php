<?php

class CitasController
{
    private CitaModel         $citaModel;
    private ServicioModel     $servicioModel;
    private ClienteModel      $clienteModel;
    private NotificacionModel $notifModel;

    public function __construct()
    {
        Auth::check();
        $this->citaModel     = new CitaModel();
        $this->servicioModel = new ServicioModel();
        $this->clienteModel  = new ClienteModel();
        $this->notifModel    = new NotificacionModel();
    }

    public function index(): void
    {
        Auth::require('citas.ver');
        $pageTitle     = 'Calendario de Citas';
        $anio          = (int) ($_GET['anio'] ?? date('Y'));
        $mes           = (int) ($_GET['mes']  ?? date('n'));
        if ($mes < 1)  { $mes = 12; $anio--; }
        if ($mes > 12) { $mes = 1;  $anio++; }
        $citasMes      = $this->citaModel->findByMes($anio, $mes);
        $citasPorFecha = [];
        foreach ($citasMes as $cita) { $citasPorFecha[$cita['fecha']][] = $cita; }
        $citasHoy = $this->citaModel->findByFecha(date('Y-m-d'));
        $config   = $this->citaModel->getConfig();
        require_once VIEWS_PATH . 'Citas' . DS . 'index.php';
    }

    public function dia(): void
    {
        Auth::require('citas.ver');
        $fecha     = htmlspecialchars(strip_tags(trim($_GET['fecha'] ?? date('Y-m-d'))));
        $citas     = $this->citaModel->findByFecha($fecha);
        $resultado = array_map(fn($c) => [
            'id'              => $c->id,
            'cliente_nombre'  => $c->cliente_nombre  ?? 'Sin cliente',
            'servicio_nombre' => $c->servicio_nombre,
            'hora_inicio'     => $c->hora_inicio,
            'hora_fin'        => $c->getHoraFin(),
            'duracion'        => $c->duracion,
            'precio'          => $c->precio,
            'estado'          => $c->estado,
            'badge'           => $c->getBadgeEstado(),
            'nota'            => $c->nota,
        ], $citas);
        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit();
    }

    public function verificar(): void
    {
        Auth::require('citas.ver');
        $fecha     = htmlspecialchars(strip_tags(trim($_GET['fecha']     ?? '')));
        $hora      = htmlspecialchars(strip_tags(trim($_GET['hora']      ?? '')));
        $duracion  = (int) ($_GET['duracion']   ?? 60);
        $excludeId = (int) ($_GET['exclude_id'] ?? 0);

        if (empty($fecha) || empty($hora)) {
            header('Content-Type: application/json');
            echo json_encode(['disponible' => false, 'message' => 'Fecha y hora requeridas.']);
            exit();
        }

        $config     = $this->citaModel->getConfig();
        $capacidad  = (int) ($config['capacidad_simultanea'] ?? 1);
        $ocupadas   = $this->citaModel->verificarDisponibilidad($fecha, $hora, $duracion, $excludeId);
        $disponible = $ocupadas < $capacidad;

        header('Content-Type: application/json');
        echo json_encode([
            'disponible' => $disponible,
            'ocupadas'   => $ocupadas,
            'capacidad'  => $capacidad,
            'message'    => $disponible
                ? "Disponible ✅ ({$ocupadas}/{$capacidad} citas en ese horario)"
                : "Ocupado ❌ ({$ocupadas}/{$capacidad} citas en ese horario)",
        ]);
        exit();
    }

    public function create(): void
    {
        Auth::require('citas.crear');
        $pageTitle = 'Nueva Cita';
        $cita      = new CitaEntity();
        $servicios = $this->servicioModel->findActivos();
        $config    = $this->citaModel->getConfig();
        if (!empty($_GET['fecha'])) {
            $cita->fecha = htmlspecialchars(strip_tags($_GET['fecha']));
        }
        require_once VIEWS_PATH . 'Citas' . DS . 'Registry.php';
    }

    public function edit(string $id = ''): void
    {
        Auth::require('citas.editar');
        if (empty($id) || !is_numeric($id)) { header('Location: ' . APP_URL . 'Citas/index'); exit(); }
        $cita = $this->citaModel->findById((int) $id);
        if (!$cita->Found) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'La cita no existe.'];
            header('Location: ' . APP_URL . 'Citas/index'); exit();
        }
        $pageTitle = 'Editar Cita';
        $servicios = $this->servicioModel->findActivos();
        $config    = $this->citaModel->getConfig();
        require_once VIEWS_PATH . 'Citas' . DS . 'Registry.php';
    }

    // ─────────────────────────────────────────────
    // SAVE — crea notificación solo si es nueva cita
    // ─────────────────────────────────────────────
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Citas/index'); exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;

        Auth::require($esEdicion ? 'citas.editar' : 'citas.crear');

        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error de seguridad','text'=>'Token inválido.'];
            header('Location: ' . APP_URL . 'Citas/index'); exit();
        }

        $clienteId  = !empty($_POST['cliente_id']) ? (int) $_POST['cliente_id'] : null;
        $servicioId = (int) ($_POST['servicio_id'] ?? 0);
        $fecha      = htmlspecialchars(strip_tags(trim($_POST['fecha']       ?? '')));
        $hora       = htmlspecialchars(strip_tags(trim($_POST['hora_inicio'] ?? '')));
        $duracion   = (int) ($_POST['duracion'] ?? 60);
        $precio     = (float) ($_POST['precio'] ?? 0);
        $nota       = htmlspecialchars(strip_tags(trim($_POST['nota'] ?? '')));

        if (empty($servicioId) || empty($fecha) || empty($hora)) {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Campos requeridos',
                'text'=>'Servicio, fecha y hora son obligatorios.'];
            header('Location: ' . APP_URL . ($esEdicion ? 'Citas/edit/' . $id : 'Citas/create')); exit();
        }

        $config    = $this->citaModel->getConfig();
        $capacidad = (int) ($config['capacidad_simultanea'] ?? 1);
        $ocupadas  = $this->citaModel->verificarDisponibilidad($fecha, $hora, $duracion, $esEdicion ? $id : 0);

        if ($ocupadas >= $capacidad) {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Horario ocupado',
                'text'=>"Ya hay {$ocupadas} cita(s) en ese horario. Capacidad máxima: {$capacidad}."];
            header('Location: ' . APP_URL . ($esEdicion ? 'Citas/edit/' . $id : 'Citas/create')); exit();
        }

        $data = [
            'cliente_id'  => $clienteId,
            'servicio_id' => $servicioId,
            'user_id'     => Auth::id(),
            'fecha'       => $fecha,
            'hora_inicio' => $hora,
            'duracion'    => $duracion,
            'precio'      => $precio,
            'nota'        => $nota ?: null,
        ];

        if ($esEdicion) {
            $data['id'] = $id;
            $ok         = $this->citaModel->update($data);
            $mensaje    = $ok ? 'Cita actualizada correctamente.' : 'Error al actualizar.';
        } else {
            $nuevoId = $this->citaModel->insert($data);
            $ok      = $nuevoId > 0;
            $mensaje = $ok ? 'Cita creada correctamente.' : 'Error al crear la cita.';

            // ── Notificación solo al crear desde el panel ─
            if ($ok && $clienteId) {
                $cliente  = $this->clienteModel->findById($clienteId);
                $servicio = $this->servicioModel->findById($servicioId);
                $this->notifModel->nuevaCita(
                    $cliente->nombre ?? 'Cliente',
                    $servicio->nombre ?? 'Servicio',
                    date('d/m/Y', strtotime($fecha)),
                    date('h:i A', strtotime($hora))
                );
            }
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito'   : 'Error',
            'text'  => $mensaje,
        ];

        header('Location: ' . APP_URL . 'Citas/index');
        exit();
    }

    public function cambiarEstadoCita(): void
    {
        Auth::require('citas.editar');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) { http_response_code(403); exit(); }

        $id     = (int) ($_POST['id']     ?? 0);
        $estado = htmlspecialchars(strip_tags(trim($_POST['estado'] ?? '')));

        $estadosValidos = ['Pendiente', 'Confirmada', 'Completada', 'Cancelada'];
        if (!in_array($estado, $estadosValidos, true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Estado inválido.']);
            exit();
        }

        $ok = $this->citaModel->updateEstado($id, $estado);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok, 'estado' => $estado]);
        exit();
    }

    public function delete(): void
    {
        Auth::require('citas.eliminar');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) { http_response_code(403); exit(); }

        $id = (int) ($_POST['id'] ?? 0);
        $ok = $this->citaModel->delete($id);

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Cancelada' : 'Error',
            'text'  => $ok ? 'Cita cancelada correctamente.' : 'Error al cancelar.',
        ];

        header('Location: ' . APP_URL . 'Citas/index');
        exit();
    }

    public function config(): void
    {
        Auth::require('citas.editar');
        $pageTitle = 'Configuración de Horarios';
        $config    = $this->citaModel->getConfig();
        require_once VIEWS_PATH . 'Citas' . DS . 'Config.php';
    }

    public function saveConfigCitas(): void
    {
        Auth::require('citas.editar');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'Citas/config'); exit(); }
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error de seguridad','text'=>'Token inválido.'];
            header('Location: ' . APP_URL . 'Citas/config'); exit();
        }

        $diasStr = implode(',', array_map('intval', $_POST['dias'] ?? []));
        $ok = $this->citaModel->updateConfig([
            'horario_inicio'       => htmlspecialchars(strip_tags(trim($_POST['horario_inicio']       ?? '08:00'))),
            'horario_fin'          => htmlspecialchars(strip_tags(trim($_POST['horario_fin']          ?? '18:00'))),
            'dias_laborales'       => $diasStr ?: '1,2,3,4,5,6',
            'duracion_default'     => (int) ($_POST['duracion_default']     ?? 60),
            'capacidad_simultanea' => (int) ($_POST['capacidad_simultanea'] ?? 1),
        ]);

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito'   : 'Error',
            'text'  => $ok ? 'Configuración guardada correctamente.' : 'Error al guardar.',
        ];

        header('Location: ' . APP_URL . 'Citas/config');
        exit();
    }
}