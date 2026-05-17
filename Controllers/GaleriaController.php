<?php

class GaleriaController
{
    private GaleriaModel $model;

    public function __construct()
    {
        Auth::check();
        $this->model = new GaleriaModel();
    }

    public function index(): void
    {
        Auth::require('galeria.ver');
        $pageTitle = 'Galería de Clientes';
        $fotos     = $this->model->findAll();
        require_once VIEWS_PATH . 'Galeria' . DS . 'index.php';
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Galeria/index');
            exit();
        }

        Auth::require('galeria.gestionar');

        if (!Csrf::validate()) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error de seguridad','text'=>'Token inválido.'];
            header('Location: ' . APP_URL . 'Galeria/index');
            exit();
        }

        $id          = (int) ($_POST['id'] ?? 0);
        $esEdicion   = $id > 0;
        $descripcion = htmlspecialchars(strip_tags(trim($_POST['descripcion'] ?? '')));
        $orden       = (int) ($_POST['orden'] ?? 0);

        $imageUrl = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imageUrl = $this->subirImagen($_FILES['imagen']);
            if (!$imageUrl) {
                $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Solo JPG, PNG o WEBP. Máx. 2MB.'];
                header('Location: ' . APP_URL . 'Galeria/index');
                exit();
            }
        }

        if ($esEdicion) {
            $ok = $this->model->update(['id'=>$id,'imagen_url'=>$imageUrl,'descripcion'=>$descripcion,'orden'=>$orden]);
        } else {
            if (!$imageUrl) {
                $_SESSION['alert'] = ['icon'=>'warning','title'=>'Imagen requerida','text'=>'Sube una imagen.'];
                header('Location: ' . APP_URL . 'Galeria/index');
                exit();
            }
            $ok = $this->model->insert(['imagen_url'=>$imageUrl,'descripcion'=>$descripcion,'orden'=>$orden]) > 0;
        }

        $_SESSION['alert'] = ['icon'=>$ok?'success':'error','title'=>$ok?'Éxito':'Error',
            'text'=>$ok?'Foto guardada.':'Error al guardar.'];
        header('Location: ' . APP_URL . 'Galeria/index');
        exit();
    }

    public function toggle(): void
    {
        Auth::require('galeria.gestionar');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        Csrf::validateOrFail();
        $ok = $this->model->toggleActivo((int)($_POST['id']??0), (int)($_POST['activo']??0));
        header('Content-Type: application/json');
        echo json_encode(['success'=>$ok]);
        exit();
    }

    public function delete(): void
    {
        Auth::require('galeria.gestionar');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        Csrf::validateOrFail();
        $ok = $this->model->delete((int)($_POST['id']??0));
        $_SESSION['alert'] = ['icon'=>$ok?'success':'error','title'=>$ok?'Eliminado':'Error',
            'text'=>$ok?'Foto eliminada.':'Error al eliminar.'];
        header('Location: ' . APP_URL . 'Galeria/index');
        exit();
    }

    private function subirImagen(array $file): ?string
    {
        $destino = IMG_BASE_DIR . 'Galeria' . DS;
        if (!is_dir($destino)) mkdir($destino, 0755, true);
        return ImageOptimizer::process($file, $destino, 'foto_');
    }
}