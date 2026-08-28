<?php

class DashboardController
{
    public function __construct()
    {
        Auth::check();
    }

    public function index(): void
    {
        $pageTitle = 'Dashboard';

        // ── Instanciar todos los modelos ──────────────
        $userModel       = new UserModel();
        $roleModel       = new RoleModel();
        $permissionModel = new PermissionModel();
        $productoModel   = new ProductoModel();
        $cotizacionModel = new CotizacionModel();
        $clienteModel    = new ClienteModel();
        $citaModel       = new CitaModel();

        // ── Consultar datos ───────────────────────────
        $totalUsuarios = $userModel->count();
        $totalActivos  = $userModel->countActivos();
        $totalRoles    = $roleModel->count();
        $totalPermisos = $permissionModel->count();

        $totalProductos         = $productoModel->count();
        $totalProductosActivos  = $productoModel->count();
        // La tienda ya no genera pedidos: el indicador diario son las cotizaciones
        $totalCotizacionesNuevas = $cotizacionModel->countByEstado('Nueva');
        $totalCotizacionesHoy    = $cotizacionModel->countHoy();
        $totalClientes          = $clienteModel->count();
        $totalCitasHoy          = $citaModel->countHoy();
        $totalCitasPendientes   = $citaModel->countPendientes();

        $usuario = Auth::user();

        require_once VIEWS_PATH . 'Dashboard' . DS . 'index.php';
    }
}