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

        $userModel       = new UserModel();
        $roleModel       = new RoleModel();
        $permissionModel = new PermissionModel();

        $totalUsuarios = $userModel->count();
        $totalActivos  = $userModel->countActivos();
        $totalRoles    = $roleModel->count();
        $totalPermisos = $permissionModel->count();

        $totalProductos         = 0;
        $totalProductosActivos  = 0;
        $totalPedidosPendientes = 0;
        $totalPedidosHoy        = 0;
        $totalClientes          = 0;
        $totalCitasHoy          = 0;
        $totalCitasPendientes   = 0;

        $usuario = Auth::user();

        // ← Esta línea faltaba
        require_once VIEWS_PATH . 'Dashboard' . DS . 'index.php';
    }
}