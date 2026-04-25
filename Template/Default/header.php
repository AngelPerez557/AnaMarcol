<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Título dinámico — $pageTitle lo define cada controlador -->
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?><?= APP_NAME ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= APP_URL ?>Content/Demo/img/Logo2.png">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- CSS propio — orden obligatorio:
         1. variables.css     → variables globales de color, tipografía, espaciado
         2. custom-themes.css → componentes que consumen las variables -->
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/Custom/variables.css">
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/Custom/custom-themes.css">

    <!-- CSS adicional por vista
         El controlador inyecta estilos específicos mediante $extraCss
         Ejemplo: $extraCss = ['Content/Dist/css/Custom/calendar.css']; -->
    <?php if (!empty($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= APP_URL . htmlspecialchars($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Driver.js v1.0.1 (local, sin dependencia de CDN) -->
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Vendor/driverjs/driver.min.css">

    <style>
    /* Tour Driver.js — Ana Marcol */
    .am-driver-popover {
        border-radius: 12px !important;
        box-shadow: 0 8px 32px rgba(240,98,146,0.25) !important;
        max-width: 340px !important;
    }
    .am-driver-popover .driver-popover-title {
        color: #F06292 !important;
        font-weight: 700 !important;
        font-size: 1rem !important;
        border-bottom: 2px solid rgba(240,98,146,0.15);
        padding-bottom: 8px;
        margin-bottom: 8px;
    }
    .am-driver-popover .driver-popover-description {
        color: #555 !important;
        font-size: 0.87rem !important;
        line-height: 1.6 !important;
    }
    .am-driver-popover .driver-popover-next-btn {
        background: #F06292 !important;
        border-color: #F06292 !important;
        border-radius: 6px !important;
        font-weight: 600 !important;
    }
    .am-driver-popover .driver-popover-next-btn:hover {
        background: #e0507a !important;
    }
    .am-driver-popover .driver-popover-prev-btn {
        border-radius: 6px !important;
    }
    .am-driver-popover .driver-popover-progress-text {
        color: #F06292 !important;
        font-weight: 600 !important;
    }
    .driver-overlay { background: rgba(0,0,0,0.65) !important; }
    </style>

    <!-- PWA Panel Admin -->
    <link rel="manifest" href="<?= APP_URL ?>manifest-admin.json">
    <meta name="theme-color" content="#F06292">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="AM Admin">
    <link rel="apple-touch-icon"
          href="<?= APP_URL ?>Content/Demo/img/icons/icon-admin-192.png">

    <!-- Dark mode — se lee antes del render para evitar flash de tema incorrecto -->
    <?php $darkMode = isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] === true; ?>

</head>

<!-- Si dark mode está activo se agrega la clase al body
     El JS del footer también puede agregarla/quitarla en tiempo real -->
<body id="appBody"<?= $darkMode ? ' class="dark-mode"' : '' ?>>

    <!-- Overlay que aparece detrás del sidebar en móviles -->
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>