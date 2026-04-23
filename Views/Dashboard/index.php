<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA DE BIENVENIDA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Dashboard</h4>
            <small class="text-muted">
                Bienvenida, <strong><?= htmlspecialchars(Auth::get('nombre') ?? 'Usuario') ?></strong>
                &nbsp;|&nbsp;
                <span class="badge" style="background-color:#de777d;">
                    <?= htmlspecialchars(Auth::get('rol_slug') ?? 'Sin rol') ?>
                </span>
            </small>
        </div>
        <small class="text-muted">
            <i class="fas fa-clock me-1"></i>
            <?= date('d/m/Y H:i') ?>
        </small>
    </div>

    <!-- ─────────────────────────────────────────────
         CARDS DE RESUMEN
         ───────────────────────────────────────────── -->
    <div class="row g-3 mb-4">

        <!-- Card Usuarios -->
        <?php if (Auth::can('usuarios.ver')): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(222,119,125,0.12);flex-shrink:0;">
                        <i class="fas fa-users fa-lg" style="color:#de777d;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Usuarios</div>
                        <div class="fw-bold" style="font-size:1.75rem;line-height:1;">
                            <?= $totalUsuarios ?>
                        </div>
                        <small class="text-muted"><?= $totalActivos ?> activos</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= APP_URL ?>Usuarios/index" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right me-1"></i>Ver
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Roles -->
        <?php if (Auth::can('roles.ver')): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(40,167,69,0.12);flex-shrink:0;">
                        <i class="fas fa-user-shield fa-lg" style="color:#28a745;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Roles</div>
                        <div class="fw-bold" style="font-size:1.75rem;line-height:1;">
                            <?= $totalRoles ?>
                        </div>
                        <small class="text-muted">registrados</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= APP_URL ?>Roles/index" class="btn btn-sm btn-success">
                        <i class="fas fa-arrow-right me-1"></i>Ver
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Permisos -->
        <?php if (Auth::can('roles.ver')): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(111,66,193,0.12);flex-shrink:0;">
                        <i class="fas fa-key fa-lg" style="color:#6f42c1;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Permisos</div>
                        <div class="fw-bold" style="font-size:1.75rem;line-height:1;">
                            <?= $totalPermisos ?>
                        </div>
                        <small class="text-muted">del sistema</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= APP_URL ?>Permisos/index" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-right me-1"></i>Ver
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Productos -->
        <?php if (Auth::can('productos.ver')): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(253,126,20,0.12);flex-shrink:0;">
                        <i class="fas fa-boxes fa-lg" style="color:#fd7e14;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Productos</div>
                        <div class="fw-bold" style="font-size:1.75rem;line-height:1;">
                            <?= $totalProductos ?>
                        </div>
                        <small class="text-muted"><?= $totalProductosActivos ?> activos</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= APP_URL ?>Productos/index" class="btn btn-sm btn-warning">
                        <i class="fas fa-arrow-right me-1"></i>Ver
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Pedidos pendientes -->
        <?php if (Auth::can('pedidos.ver')): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(220,53,69,0.12);flex-shrink:0;">
                        <i class="fas fa-shopping-bag fa-lg" style="color:#dc3545;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Pedidos pendientes</div>
                        <div class="fw-bold" style="font-size:1.75rem;line-height:1;">
                            <?= $totalPedidosPendientes ?>
                        </div>
                        <small class="text-muted"><?= $totalPedidosHoy ?> hoy</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= APP_URL ?>Pedidos/pendientes" class="btn btn-sm btn-danger">
                        <i class="fas fa-arrow-right me-1"></i>Ver
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Clientes -->
        <?php if (Auth::can('clientes.ver')): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(23,162,184,0.12);flex-shrink:0;">
                        <i class="fas fa-users fa-lg" style="color:#17a2b8;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Clientes</div>
                        <div class="fw-bold" style="font-size:1.75rem;line-height:1;">
                            <?= $totalClientes ?>
                        </div>
                        <small class="text-muted">registrados</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= APP_URL ?>Clientes/index" class="btn btn-sm btn-info">
                        <i class="fas fa-arrow-right me-1"></i>Ver
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Citas hoy -->
        <?php if (Auth::can('citas.ver')): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(102,16,242,0.12);flex-shrink:0;">
                        <i class="fas fa-calendar-alt fa-lg" style="color:#6610f2;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Citas hoy</div>
                        <div class="fw-bold" style="font-size:1.75rem;line-height:1;">
                            <?= $totalCitasHoy ?>
                        </div>
                        <small class="text-muted"><?= $totalCitasPendientes ?> pendientes</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= APP_URL ?>Citas/index" class="btn btn-sm" style="background:#6610f2;color:#fff;">
                        <i class="fas fa-arrow-right me-1"></i>Ver
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- ─────────────────────────────────────────────
         INFORMACIÓN DEL SISTEMA Y USUARIO
         ───────────────────────────────────────────── -->
    <div class="row g-3">

        <!-- Info del usuario autenticado -->
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-user-circle me-2"></i>Mi información
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="40%">Nombre</td>
                                <td><strong><?= htmlspecialchars(Auth::get('nombre') ?? '—') ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Correo</td>
                                <td><?= htmlspecialchars(Auth::get('email') ?? '—') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Rol</td>
                                <td>
                                    <span class="badge" style="background-color:#de777d;">
                                        <?= htmlspecialchars(Auth::get('rol_slug') ?? '—') ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Permisos</td>
                                <td><?= count(Auth::user()['permisos'] ?? []) ?> asignados</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Info del sistema -->
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i>Información del sistema
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="40%">Sistema</td>
                                <td><strong><?= APP_NAME ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Versión</td>
                                <td><?= APP_VERSION ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Entorno</td>
                                <td>
                                    <?php if (APP_ENV === 'development'): ?>
                                        <span class="badge bg-warning text-dark">Desarrollo</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Producción</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div><!-- /.container-fluid -->