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
    <div class="row g-3 mb-4" id="tour-cards">

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
                    <p class="text-muted mb-2">
                        <strong>Usuario:</strong> <?= htmlspecialchars(Auth::get('nombre') ?? 'N/A') ?>
                    </p>
                    <p class="text-muted mb-2">
                        <strong>Email:</strong> <?= htmlspecialchars(Auth::get('email') ?? 'N/A') ?>
                    </p>
                    <p class="text-muted mb-0">
                        <strong>Rol:</strong> <?= htmlspecialchars(Auth::get('rol_slug') ?? 'Sin rol') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

</div><!-- /.container-fluid -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (AM_TOUR_COMPLETADO) return;
    if (typeof window.driver === 'undefined') return;

    const driverFn = (window.driver && window.driver.js)
        ? window.driver.js.driver
        : window.driver;

    if (typeof driverFn !== 'function') return;

    function el(tourId) {
        return document.querySelector('[data-tour="' + tourId + '"]')
            ? '[data-tour="' + tourId + '"]'
            : null;
    }

    const stepsBase = [
        {
            popover: {
                title: `👋 ¡Bienvenida, ${AM_USER_NOMBRE}!`,
                description: `Hola <strong>${AM_USER_NOMBRE}</strong>, este es el panel de administración de <strong>Ana Marcol Makeup Studio</strong>. Te guiaré por los módulos principales para que puedas gestionar tu negocio de forma eficiente desde el primer día.`
            }
        },
        {
            element: el('tour-menu'),
            popover: {
                title: '📋 Menú principal',
                description: 'Desde aquí accedes a todos los módulos del sistema: Caja, Pedidos, Catálogo, Citas, Clientes, Facturación, Reportes y más. En dispositivos móviles toca el ícono ☰ para abrirlo.',
                side: 'right',
                align: 'start'
            }
        },
        {
            element: el('tour-notif'),
            popover: {
                title: '🔔 Notificaciones en tiempo real',
                description: 'Aquí recibirás alertas automáticas cuando llegue un nuevo pedido desde la tienda en línea o cuando un cliente agende una cita. El número rojo indica cuántas tienes sin leer. Se actualiza cada 30 segundos automáticamente.',
                side: 'bottom',
                align: 'end'
            }
        },
        {
            element: '#tour-cards',
            popover: {
                title: '📊 Resumen del negocio',
                description: 'Estas tarjetas muestran un vistazo rápido de tu operación diaria: ventas del día, pedidos pendientes, citas de hoy y clientes registrados. Son el punto de partida para tomar decisiones rápidas cada mañana.',
                side: 'bottom'
            }
        },
        {
            element: el('tour-caja-link'),
            popover: {
                title: '💰 Caja / Punto de Venta',
                description: 'Registra ventas presenciales en tu estudio. Busca productos por nombre o escanea el código de barras, selecciona la cantidad, elige el método de pago (Efectivo, Tarjeta o Transferencia) y cobra. El sistema descuenta el stock automáticamente y genera el recibo térmico.',
                side: 'right'
            }
        },
        {
            element: el('tour-pedidos-link'),
            popover: {
                title: '📦 Pedidos en línea',
                description: 'Cuando un cliente compra desde la tienda en línea, el pedido aparece aquí en estado "Pendiente". Cambia el estado conforme avanza: <strong>Pendiente → En preparación → Listo → En camino → Entregado</strong>. Cada cambio queda registrado en el historial.',
                side: 'right'
            }
        },
        {
            element: el('tour-citas-link'),
            popover: {
                title: '📅 Gestión de Citas',
                description: 'Administra tu calendario de citas. Los clientes agendan desde la tienda en línea y tú las ves aquí por día y mes. Al confirmar una cita puedes notificar al cliente por WhatsApp. También puedes crear citas manuales para clientes que llamen por teléfono.',
                side: 'right'
            }
        },
        {
            element: el('tour-reportes-link'),
            popover: {
                title: '📈 Reportes y estadísticas',
                description: 'Analiza el rendimiento de tu negocio con gráficas de ventas por día y mes, métodos de pago más usados, tus 10 productos más vendidos, distribución de pedidos por estado, e inventario con alertas de stock bajo.',
                side: 'right'
            }
        },
        {
            popover: {
                title: `✅ ¡Todo listo, ${AM_USER_NOMBRE}!`,
                description: `Ya conoces los módulos principales. <strong>Importante:</strong> cambia tu contraseña desde el menú de perfil (ícono de usuario arriba a la derecha). Si tienes dudas o problemas técnicos, contáctanos desde <strong>Soporte DeskCod</strong>. ¡Mucho éxito! 💄`
            }
        }
    ];

    const steps = stepsBase.filter(step => {
        if (!step.element) return true;
        return document.querySelector(step.element) !== null;
    });

    const tourDashboard = driverFn({
        showProgress:     true,
        popoverClass:     'am-driver-popover',
        nextBtnText:      'Siguiente →',
        prevBtnText:      '← Atrás',
        doneBtnText:      '¡Entendido! ✓',
        onDestroyStarted: () => {
            amMarcarTour();
            tourDashboard.destroy();
        },
        steps: steps
    });

    tourDashboard.drive();
});
</script>