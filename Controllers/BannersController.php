<?php

class BannersController
{
    private BannerModel $model;

    public function __construct()
    {
        Auth::check();
        $this->model = new BannerModel();
    }

    public function index(): void
    {
        Auth::require('banners.ver');
        $pageTitle = 'Banners';
        $banners   = $this->model->findAll();
        require_once VIEWS_PATH . 'Banners' . DS . 'index.php';
    }

    public function registry(string $id = ''): void
    {
        $esEdicion = !empty($id) && is_numeric($id);
        Auth::require('banners.gestionar');

        $pageTitle = $esEdicion ? 'Editar Banner' : 'Nuevo Banner';
        $banner    = $esEdicion ? $this->model->findById((int) $id) : null;

        if ($esEdicion && !$banner) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Banner no encontrado.'];
            header('Location: ' . APP_URL . 'Banners/index');
            exit();
        }

        require_once VIEWS_PATH . 'Banners' . DS . 'Registry.php';
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Banners/index');
            exit();
        }

        Auth::require('banners.gestionar');

        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error de seguridad','text'=>'Token inválido.'];
            header('Location: ' . APP_URL . 'Banners/index');
            exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;
        $titulo    = htmlspecialchars(strip_tags(trim($_POST['titulo'] ?? '')));
        $enlace    = htmlspecialchars(strip_tags(trim($_POST['enlace'] ?? '')));
        $orden     = (int) ($_POST['orden'] ?? 0);

        $imageUrl = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imageUrl = $this->subirImagen($_FILES['imagen']);
            if (!$imageUrl) {
                $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Solo JPG, PNG o WEBP. Máx. 2MB.'];
                header('Location: ' . APP_URL . ($esEdicion ? 'Banners/registry/' . $id : 'Banners/registry'));
                exit();
            }
        }

        if ($esEdicion) {
            $ok = $this->model->update(['id'=>$id,'titulo'=>$titulo,'imagen_url'=>$imageUrl,'enlace'=>$enlace,'orden'=>$orden]);
        } else {
            if (!$imageUrl) {
                $_SESSION['alert'] = ['icon'=>'warning','title'=>'Imagen requerida','text'=>'Sube una imagen para el banner.'];
                header('Location: ' . APP_URL . 'Banners/registry');
                exit();
            }
            $ok = $this->model->insert(['titulo'=>$titulo,'imagen_url'=>$imageUrl,'enlace'=>$enlace,'orden'=>$orden]) > 0;
        }

        $_SESSION['alert'] = ['icon'=>$ok?'success':'error','title'=>$ok?'Éxito':'Error',
            'text'=>$ok?'Banner guardado.':'Error al guardar.'];
        header('Location: ' . APP_URL . 'Banners/index');
        exit();
    }

    public function toggle(): void
    {
        Auth::require('banners.gestionar');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) { http_response_code(403); exit(); }
        $ok = $this->model->toggleActivo((int)($_POST['id']??0), (int)($_POST['activo']??0));
        header('Content-Type: application/json');
        echo json_encode(['success'=>$ok]);
        exit();
    }

    public function delete(): void
    {
        Auth::require('banners.gestionar');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) { http_response_code(403); exit(); }
        $ok = $this->model->delete((int)($_POST['id']??0));
        $_SESSION['alert'] = ['icon'=>$ok?'success':'error','title'=>$ok?'Eliminado':'Error',
            'text'=>$ok?'Banner eliminado.':'Error al eliminar.'];
        header('Location: ' . APP_URL . 'Banners/index');
        exit();
    }

    private function subirImagen(array $file): ?string
    {
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) return null;
        if ($file['size'] > 2 * 1024 * 1024) return null;
        if ($file['error'] !== UPLOAD_ERR_OK) return null;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) return null;

        $destino = ROOT . 'Content' . DS . 'Demo' . DS . 'img' . DS . 'Banners' . DS;
        if (!is_dir($destino)) mkdir($destino, 0755, true);

        $nombre = uniqid('banner_', true) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $destino . $nombre)) return null;
        return $nombre;
    }
}