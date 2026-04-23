<?php

class ServiciosController
{
    private ServicioModel $servicioModel;

    public function __construct()
    {
        Auth::check();
        $this->servicioModel = new ServicioModel();
    }

    public function index(): void
    {
        Auth::require('servicios.ver');

        $pageTitle = 'Servicios';
        $servicios = $this->servicioModel->findAll();

        require_once VIEWS_PATH . 'Servicios' . DS . 'index.php';
    }

    public function registry(string $id = ''): void
    {
        $esEdicion = !empty($id) && is_numeric($id);
        Auth::require($esEdicion ? 'servicios.editar' : 'servicios.crear');

        $pageTitle = $esEdicion ? 'Editar Servicio' : 'Nuevo Servicio';
        $servicio  = $esEdicion
            ? $this->servicioModel->findById((int) $id)
            : new ServicioEntity();

        if ($esEdicion && !$servicio->Found) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'El servicio no existe.'];
            header('Location: ' . APP_URL . 'Servicios/index');
            exit();
        }

        require_once VIEWS_PATH . 'Servicios' . DS . 'registry.php';
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Servicios/index');
            exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;

        Auth::require($esEdicion ? 'servicios.editar' : 'servicios.crear');

        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error de seguridad','text'=>'Token inválido.'];
            header('Location: ' . APP_URL . 'Servicios/index');
            exit();
        }

        $nombre      = htmlspecialchars(strip_tags(trim($_POST['nombre']      ?? '')));
        $descripcion = htmlspecialchars(strip_tags(trim($_POST['descripcion'] ?? '')));
        $precioBase  = (float) ($_POST['precio_base'] ?? 0);
        $duracion    = (int)   ($_POST['duracion']    ?? 60);

        if (empty($nombre)) {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Requerido','text'=>'El nombre es obligatorio.'];
            $redirect = $esEdicion ? APP_URL . 'Servicios/registry/' . $id : APP_URL . 'Servicios/registry';
            header('Location: ' . $redirect);
            exit();
        }

        $data = ['nombre'=>$nombre, 'descripcion'=>$descripcion?:null, 'precio_base'=>$precioBase, 'duracion'=>$duracion];

        if ($esEdicion) {
            $data['id'] = $id;
            $ok = $this->servicioModel->update($data);
            $mensaje = $ok ? 'Servicio actualizado.' : 'Error al actualizar.';
        } else {
            $nuevoId = $this->servicioModel->insert($data);
            $ok      = $nuevoId > 0;
            $mensaje = $ok ? 'Servicio creado.' : 'Error al crear.';
        }

        $_SESSION['alert'] = ['icon'=>$ok?'success':'error','title'=>$ok?'Éxito':'Error','text'=>$mensaje];
        header('Location: ' . APP_URL . 'Servicios/index');
        exit();
    }

    public function toggle(): void
    {
        Auth::require('servicios.editar');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) { http_response_code(403); exit(); }

        $id = (int)($_POST['id']??0); $activo = (int)($_POST['activo']??0);
        $ok = $this->servicioModel->toggleActivo($id, $activo);
        header('Content-Type: application/json');
        echo json_encode(['success'=>$ok]);
        exit();
    }

    public function delete(): void
    {
        Auth::require('servicios.eliminar');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) { http_response_code(403); exit(); }

        $id = (int)($_POST['id']??0);
        $ok = $this->servicioModel->delete($id);
        $_SESSION['alert'] = ['icon'=>$ok?'success':'error','title'=>$ok?'Eliminado':'Error',
            'text'=>$ok?'Servicio desactivado.':'Error al eliminar.'];
        header('Location: ' . APP_URL . 'Servicios/index');
        exit();
    }
}