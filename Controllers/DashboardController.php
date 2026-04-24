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

        $totalProductos         = $productoModel->count();
        $totalProductosActivos  = $productoModel->countActivos();
        $totalPedidosPendientes = $pedidoModel->countPendientes();
        $totalPedidosHoy        = $pedidoModel->countHoy();
        $totalClientes          = $clienteModel->count();
        $totalCitasHoy          = $citaModel->countHoy();
        $totalCitasPendientes   = $citaModel->countPendientes();

        $usuario = Auth::user();

        // ← Esta línea faltaba
        require_once VIEWS_PATH . 'Dashboard' . DS . 'index.php';
    }
}