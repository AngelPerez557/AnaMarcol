
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- PWA Panel Admin -->
    <link rel="manifest" href="<?= APP_URL ?>manifest-admin.json">
    <meta name="apple-mobile-web-app-title" content="AM Admin">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="apple-touch-icon" href="<?= APP_URL ?>Content/Demo/img/icon/icon-admin-192.png">

    <!-- Título dinámico desde Define.php -->
    <title>Iniciar sesión | <?= APP_NAME ?></title>
    <link rel="icon" type="image/png" href="<?= APP_URL ?>Content/Demo/img/Logo2.png">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Variables globales — necesarias para login.css -->
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/Custom/variables.css">

    <!-- CSS del login -->
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/login.css">
</head>

<!-- Dark mode se aplica desde PHP para evitar flash -->
<body class="login-body<?= (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode']) ? ' dark-mode' : '' ?>">

    <!-- Botón dark mode — independiente del layout principal -->
    <button type="button"
            class="btn-theme-toggle-login"
            id="themeToggleLogin"
            aria-label="Cambiar modo">
        <i class="fas <?= (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode']) ? 'fa-sun' : 'fa-moon' ?>"
           id="themeIconLogin"></i>
    </button>

    <div class="login-container">
        <div class="login-wrapper">
            <div class="login-card">

                <!-- ── Header del login ──────────────────── -->
                <div class="login-header text-center">
                    <div class="login-logo-img mb-3">
                        <img src="<?= APP_URL ?>Content/Demo/img/Logo.png"
                             alt="<?= APP_NAME ?>"
                             style="max-width:260px; width:100%; height:auto; object-fit:contain;">
                    </div>
                    <p class="login-subtitle">Ingresá tus credenciales</p>
                </div>

                <!-- ── Alerta sesión expirada ────────────── -->
                <?php if (!empty($_GET['expired'])): ?>
                <div class="alert alert-warning py-2" style="font-size:0.85rem;">
                    <i class="fas fa-clock me-2"></i>
                    Tu sesión expiró por inactividad. Inicia sesión nuevamente.
                </div>
                <?php endif; ?>

                <!-- ── Mensaje de error desde sesión ────── -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- ── Formulario de login ───────────────── -->
                <!-- POST directo al AuthController via JRouter -->
                <form id="loginForm"
                      action="<?= APP_URL ?>Auth/login"
                      method="POST"
                      class="mt-4"
                      novalidate>

                    <!-- Email -->
                    <div class="form-floating-modern mb-3">
                        <div class="input-wrapper">
                            <input type="text"
                                class="form-control form-control-modern"
                                id="email"
                                name="email"
                                placeholder=" "
                                autocomplete="username"
                                required>
                            <i class="input-icon fas fa-user"></i>
                            <label class="floating-label" for="email">Usuario o correo</label>
                            <span class="input-line"></span>
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div class="form-floating-modern mb-3">
                        <div class="input-wrapper">
                            <input type="password"
                                   class="form-control form-control-modern"
                                   id="password"
                                   name="password"
                                   placeholder=" "
                                   autocomplete="current-password"
                                   required>
                            <i class="input-icon fas fa-lock"></i>
                            <label class="floating-label" for="password">Contraseña</label>
                            <span class="input-line"></span>

                            <!-- Botón mostrar/ocultar contraseña -->
                            <button type="button"
                                    class="btn-toggle-password"
                                    id="togglePassword"
                                    aria-label="Mostrar contraseña">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Botón submit -->
                    <button type="submit" class="btn btn-login-modern w-100" id="btnLogin">
                        <span class="btn-text">Iniciar sesión</span>
                        <span class="btn-loader"></span>
                    </button>

                </form>

                <!-- ── Footer del login ──────────────────── -->
                <div class="login-footer text-center mt-4">
                    <p class="mb-0 text-muted">
                        <small>
                            &copy; <?= date('Y') ?> <?= APP_NAME ?>. Todos los derechos reservados.
                        </small>
                    </p>
                </div>

            </div>
        </div>
    </div>

    <!-- ─────────────────────────────────────────────
         JAVASCRIPT
         ───────────────────────────────────────────── -->

    <!-- Variable global APP_URL para los scripts -->
    <script>const APP_URL = '<?= APP_URL ?>';</script>

    <!-- Bootstrap bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts del login -->
    <script src="<?= APP_URL ?>Content/Dist/js/login-theme.js"></script>
    <script src="<?= APP_URL ?>Content/Dist/js/login.js"></script>

    <!-- Validación del formulario con SweetAlert -->
    <script>
(function () {
    'use strict';

    const form      = document.getElementById('loginForm');
    const btnLogin  = document.getElementById('btnLogin');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const email    = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            if (email === '') {
                Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El correo electrónico es obligatorio.', confirmButtonText: 'Entendido', allowOutsideClick: false, allowEscapeKey: false });
                return;
            }

            // Acepta usuario o correo — solo verifica que no esté vacío
            if (email.length < 3) {
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Campo inválido', 
                    text: 'Ingresa tu usuario o correo electrónico.', 
                    confirmButtonText: 'Entendido' 
                });
                return;
            }

            if (password === '') {
                Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'La contraseña es obligatoria.', confirmButtonText: 'Entendido', allowOutsideClick: false, allowEscapeKey: false });
                return;
            }

            btnLogin.disabled = true;
            btnLogin.querySelector('.btn-text').style.display = 'none';
            btnLogin.querySelector('.btn-loader').style.display = 'inline-block';
            form.submit();
        });
    }

})();
</script>
</body>
</html>