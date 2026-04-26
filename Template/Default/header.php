<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?><?= APP_NAME ?></title>

    <link rel="icon" type="image/png" href="<?= APP_URL ?>Content/Demo/img/Logo2.png">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- CSS propio -->
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/Custom/variables.css">
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/Custom/custom-themes.css">

    <?php if (!empty($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= APP_URL . htmlspecialchars($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Driver.js CSS local -->
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Vendor/driverjs/driver.min.css">

    <style>
    /* ═══════════════════════════════════════════════
       TOUR DRIVER.JS — Adaptado al sistema Ana Marcol
       Usa variables CSS del sistema → respeta dark mode
       ═══════════════════════════════════════════════ */
    .am-driver-popover {
        background: var(--card-bg) !important;
        border: 1px solid var(--card-border) !important;
        border-radius: var(--border-radius-lg) !important;
        box-shadow: var(--shadow-lg) !important;
        max-width: 360px !important;
        font-family: var(--font-family-base) !important;
        padding: 18px !important;
    }
    .am-driver-popover .driver-popover-title {
        color: var(--btn-primary-bg) !important;
        font-weight: var(--font-weight-bold) !important;
        font-size: var(--font-size-base) !important;
        border-bottom: 2px solid var(--card-border) !important;
        padding-bottom: 8px !important;
        margin-bottom: 10px !important;
        line-height: 1.4 !important;
        display: block !important;
    }
    .am-driver-popover .driver-popover-description {
        color: var(--body-text) !important;
        font-size: var(--font-size-sm) !important;
        line-height: 1.6 !important;
        margin-bottom: 0 !important;
    }
    .am-driver-popover .driver-popover-description strong {
        color: var(--btn-primary-bg) !important;
        font-weight: var(--font-weight-semibold) !important;
    }
    .am-driver-popover .driver-popover-footer {
        border-top: 1px solid var(--card-border) !important;
        padding-top: 10px !important;
        margin-top: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }
    .am-driver-popover .driver-popover-progress-text {
        color: var(--btn-primary-bg) !important;
        font-weight: var(--font-weight-semibold) !important;
        font-size: 0.78rem !important;
    }
    .am-driver-popover .driver-popover-next-btn {
        background: var(--btn-primary-bg) !important;
        border: 1px solid var(--btn-primary-bg) !important;
        border-radius: var(--btn-border-radius) !important;
        font-weight: var(--font-weight-semibold) !important;
        font-size: var(--font-size-sm) !important;
        color: #fff !important;
        padding: 6px 14px !important;
        cursor: pointer !important;
        transition: background var(--transition-speed-fast) !important;
        text-shadow: none !important;
    }
    .am-driver-popover .driver-popover-next-btn:hover {
        background: var(--btn-primary-bg-hover) !important;
        border-color: var(--btn-primary-bg-hover) !important;
    }
    .am-driver-popover .driver-popover-prev-btn {
        background: transparent !important;
        border: 1px solid var(--border-color) !important;
        border-radius: var(--btn-border-radius) !important;
        font-size: var(--font-size-sm) !important;
        color: var(--body-text-muted) !important;
        padding: 6px 14px !important;
        cursor: pointer !important;
        text-shadow: none !important;
    }
    .am-driver-popover .driver-popover-prev-btn:hover {
        background: var(--card-header-bg) !important;
        color: var(--body-text) !important;
        border-color: var(--btn-primary-bg) !important;
    }
    .am-driver-popover .driver-popover-close-btn {
        color: var(--body-text-muted) !important;
        font-size: 1.1rem !important;
    }
    .am-driver-popover .driver-popover-close-btn:hover {
        color: var(--btn-primary-bg) !important;
    }
    .am-driver-popover .driver-popover-arrow {
        border-color: var(--card-bg) !important;
    }
    .driver-overlay {
        background: var(--overlay-bg) !important;
    }
    /* Dark mode — variables ya cambian automáticamente */
    body.dark-mode .am-driver-popover .driver-popover-description {
        color: var(--body-text) !important;
    }
    body.dark-mode .am-driver-popover .driver-popover-prev-btn {
        color: var(--body-text-muted) !important;
        border-color: var(--border-color) !important;
    }
    </style>

    <!-- PWA Panel Admin -->
    <link rel="manifest" href="<?= APP_URL ?>manifest-admin.json">
    <meta name="theme-color" content="#de777d">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="AM Admin">
    <link rel="apple-touch-icon"
          href="<?= APP_URL ?>Content/Demo/img/icons/icon-admin-192.png">

    <?php $darkMode = isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] === true; ?>
</head>
<body id="appBody"<?= $darkMode ? ' class="dark-mode"' : '' ?>>
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>