<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? APP_NAME ?> | Ana Marcol Makeup Studio</title>
    <link rel="icon" type="image/png" href="<?= APP_URL ?>Content/Demo/img/Logo2.png">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Tienda -->
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/tienda.css">

    <!-- PWA Tienda -->
    <link rel="manifest" href="<?= APP_URL ?>manifest-tienda.json">
    <meta name="theme-color" content="#F48FB1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Ana Marcol">
    <link rel="apple-touch-icon"
          href="<?= APP_URL ?>Content/Demo/img/icons/icon-tienda-192.png">

    <style>
        :root {
            --rosa:       #de777d;
            --rosa-hover: #c56d71;
            --rosa-dark:  #b05a60;
            --rosa-soft:  #f5e6e7;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #fdf8f8;
            color: #333;
        }

        /* ── NAVBAR ── */
        .tienda-navbar {
            background: #fff;
            border-bottom: 2px solid var(--rosa-soft);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(222,119,125,0.1);
        }

        .tienda-brand {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--rosa);
            text-decoration: none;
        }

        .tienda-brand:hover { color: var(--rosa-hover); }

        .nav-tienda .nav-link {
            color: #555;
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 20px;
            transition: all 0.2s;
        }

        .nav-tienda .nav-link:hover,
        .nav-tienda .nav-link.active {
            background: var(--rosa-soft);
            color: var(--rosa);
        }

        .btn-carrito {
            position: relative;
            background: var(--rosa);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 8px 16px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-carrito:hover { background: var(--rosa-hover); color: #fff; }

        .badge-carrito {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #dc3545;
            color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        /* ── CARDS PRODUCTO ── */
        .producto-card {
            border: 1px solid #f0e0e1;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            background: #fff;
        }

        .producto-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(222,119,125,0.2);
        }

        .producto-img {
            height: 200px;
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #fdf8f8;
        }

        .btn-rosa {
            background: var(--rosa);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            transition: background 0.2s;
            cursor: pointer;
        }

        .btn-rosa:hover { background: var(--rosa-hover); color: #fff; }

        .btn-rosa-outline {
            background: transparent;
            color: var(--rosa);
            border: 2px solid var(--rosa);
            border-radius: 8px;
            padding: 6px 14px;
            font-weight: 600;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-rosa-outline:hover {
            background: var(--rosa);
            color: #fff;
        }

        /* ── FOOTER ── */
        .tienda-footer {
            background: #3d3d3d;
            color: #ccc;
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        .tienda-footer a { color: #de777d; text-decoration: none; }
        .tienda-footer a:hover { color: #f5a0a5; }

        /* ── TOAST CARRITO ── */
        .toast-carrito {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            background: #333;
            color: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            display: none;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        /* ── CHIPS CATEGORÍAS ── */
        .chip-categoria {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            border: 2px solid var(--rosa-soft);
            background: #fff;
            color: #555;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
        }

        .chip-categoria:hover,
        .chip-categoria.activo {
            background: var(--rosa);
            border-color: var(--rosa);
            color: #fff;
        }

        /* ── Responsive móvil navbar tienda ── */
        @media (max-width: 767px) {
            .tienda-navbar .container {
                padding-left: 12px;
                padding-right: 12px;
            }
            .tienda-brand img {
                height: 36px !important;
                max-width: 140px !important;
            }
            .btn-carrito {
                padding: 6px 12px !important;
                font-size: 0.85rem;
            }
            .btn-rosa-outline {
                padding: 5px 10px !important;
                font-size: 0.78rem !important;
            }
            .d-flex.align-items-center.gap-3 {
                gap: 8px !important;
            }
            /* Hero section más compacto en móvil */
            .tienda-footer {
                padding: 30px 0 15px;
                margin-top: 30px;
            }
        }

        /* ── DISPONIBILIDAD CITAS ── */
        .dia-disponible  { background: rgba(40,167,69,0.15) !important; border-color: #28a745 !important; cursor: pointer; }
        .dia-ocupado     { background: rgba(220,53,69,0.08) !important; color: #aaa !important; cursor: not-allowed; }
        .dia-no-laboral  { background: rgba(0,0,0,0.03) !important; color: #ccc !important; cursor: not-allowed; }
    </style>
</head>
<body>

    <!-- ─── NAVBAR ─────────────────────────────────── -->
    <nav class="tienda-navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="<?= APP_URL ?>Tienda/index" class="tienda-brand">
                    <img src="<?= APP_URL ?>Content/Demo/img/Logo.png"
                         alt="<?= APP_NAME ?>"
                         style="height:40px; width:auto; object-fit:contain;
                                max-width:160px;">
                </a>

                <!-- Menú desktop -->
                <ul class="nav nav-tienda d-none d-md-flex align-items-center">
                    <li class="nav-item">
                        <a href="<?= APP_URL ?>Tienda/index"
                           class="nav-link <?= str_contains($urlActual ?? '', 'tienda/index') ? 'active' : '' ?>">
                            Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= APP_URL ?>Tienda/catalogo"
                           class="nav-link <?= str_contains($urlActual ?? '', 'tienda/catalogo') ? 'active' : '' ?>">
                            Catálogo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= APP_URL ?>Tienda/citas"
                           class="nav-link <?= str_contains($urlActual ?? '', 'tienda/citas') ? 'active' : '' ?>">
                            Agendar Cita
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <!-- Carrito -->
                    <a href="<?= APP_URL ?>Tienda/carrito" class="btn-carrito">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge-carrito" id="badgeCarrito">0</span>
                    </a>

                    <!-- Cliente logueado -->
                    <?php if (!empty($_SESSION['cliente'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?= htmlspecialchars($_SESSION['cliente']['nombre']) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?= APP_URL ?>Tienda/misPedidos">
                                    <i class="fas fa-box me-2"></i>Mis pedidos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= APP_URL ?>Tienda/misCitas">
                                    <i class="fas fa-calendar me-2"></i>Mis citas
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= APP_URL ?>Tienda/logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <a href="<?= APP_URL ?>Tienda/login" class="btn-rosa-outline" style="font-size:0.85rem;">
                        <i class="fas fa-sign-in-alt me-1"></i>Ingresar
                    </a>
                    <?php endif; ?>

                    <!-- Menú móvil -->
                    <button class="btn btn-outline-secondary btn-sm d-md-none" type="button"
                            data-bs-toggle="collapse" data-bs-target="#menuMobile">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>

            <!-- Menú móvil colapsable -->
            <div class="collapse d-md-none mt-2" id="menuMobile">
                <ul class="nav flex-column">
                    <li><a href="<?= APP_URL ?>Tienda/index"   class="nav-link">Inicio</a></li>
                    <li><a href="<?= APP_URL ?>Tienda/catalogo" class="nav-link">Catálogo</a></li>
                    <li><a href="<?= APP_URL ?>Tienda/citas"    class="nav-link">Agendar Cita</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ─── CONTENIDO ──────────────────────────────── -->
    <main>
        {JBODY}
    </main>

    <!-- ─── FOOTER ─────────────────────────────────── -->
    <footer class="tienda-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <h5 class="text-white mb-3">
                        <i class="fas fa-spa me-2" style="color:#de777d;"></i>
                        Ana Marcol Makeup Studio
                    </h5>
                    <p style="font-size:0.85rem;">
                        Tu estudio de maquillaje de confianza en Santa Barbara. Belleza profesional para cada ocasión.
                    </p>
                </div>
                <div class="col-6 col-md-2">
                    <h6 class="text-white mb-3">Tienda</h6>
                    <ul class="list-unstyled" style="font-size:0.85rem;">
                        <li><a href="<?= APP_URL ?>Tienda/catalogo">Catálogo</a></li>
                        <li><a href="<?= APP_URL ?>Tienda/citas">Agendar cita</a></li>
                        <li><a href="<?= APP_URL ?>Tienda/carrito">Carrito</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-3">
                    <h6 class="text-white mb-3">Contacto</h6>
                    <ul class="list-unstyled" style="font-size:0.85rem;">
                        <li><i class="fas fa-phone me-2" style="color:#de777d;"></i>9987-3125</li>
                        <li class="mt-1">
                            <i class="fab fa-whatsapp me-2" style="color:#25d366;"></i>
                            <a href="https://wa.me/<?= WA_NUMBER ?>" target="_blank">WhatsApp</a>
                        </li>
                        <li class="mt-1">
                            <i class="fas fa-map-marker-alt me-2" style="color:#de777d;"></i>
                            Barrio Abajo, Av. La Libertad
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-md-3">
                    <h6 class="text-white mb-3">Síguenos</h6>
                    <div class="d-flex gap-3" style="font-size:1.5rem;">
                        <a href="#" title="Instagram"><i class="fab fa-instagram" style="color:#de777d;"></i></a>
                        <a href="#" title="Facebook"><i class="fab fa-facebook" style="color:#de777d;"></i></a>
                        <a href="https://wa.me/<?= WA_NUMBER ?>" target="_blank" title="WhatsApp">
                            <i class="fab fa-whatsapp" style="color:#25d366;"></i>
                        </a>
                    </div>
                </div>
            </div>
            <hr style="border-color:#555; margin: 20px 0;">
            <div class="text-center" style="font-size:0.8rem;">
                &copy; <?= date('Y') ?> Ana Marcol Makeup Studio.
                Desarrollado por <a href="#">DeskCod</a>
            </div>
        </div>
    </footer>

    <!-- Toast carrito -->
    <div class="toast-carrito" id="toastCarrito">
        <i class="fas fa-check-circle text-success"></i>
        <span id="toastCarritoMsg">Producto agregado</span>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    const APP_URL = '<?= APP_URL ?>';

    // ── Carrito localStorage ──────────────────────
    function getCarrito() {
        return JSON.parse(localStorage.getItem('carrito_anamarcol') || '[]');
    }

    function saveCarrito(carrito) {
        localStorage.setItem('carrito_anamarcol', JSON.stringify(carrito));
        actualizarBadge();
    }

    function actualizarBadge() {
        const carrito = getCarrito();
        const total   = carrito.reduce((sum, item) => sum + item.cantidad, 0);
        const badge   = document.getElementById('badgeCarrito');
        if (badge) badge.textContent = total;
    }

    function agregarAlCarrito(id, nombre, precio, imagen, varianteId, varianteNombre) {
        const carrito = getCarrito();
        const key     = `${id}-${varianteId || ''}`;
        const existe  = carrito.find(i => i.key === key);

        if (existe) {
            existe.cantidad++;
        } else {
            carrito.push({
                key, id, nombre, precio, imagen,
                varianteId:     varianteId     || null,
                varianteNombre: varianteNombre || null,
                cantidad: 1
            });
        }

        saveCarrito(carrito);
        mostrarToast(`"${nombre}" agregado al carrito`);
    }

    function mostrarToast(msg) {
        const toast = document.getElementById('toastCarrito');
        const texto = document.getElementById('toastCarritoMsg');
        if (!toast) return;
        texto.textContent = msg;
        toast.style.display = 'flex';
        setTimeout(() => { toast.style.display = 'none'; }, 2500);
    }

    // Inicializar badge al cargar
    document.addEventListener('DOMContentLoaded', actualizarBadge);
    </script>

    {JSCRIPTS}
</body>
</html>