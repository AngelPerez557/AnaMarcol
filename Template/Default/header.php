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
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/custom/variables.css">
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/custom/custom-themes.css">

    <!-- CSS adicional por vista
         El controlador inyecta estilos específicos mediante $extraCss
         Ejemplo: $extraCss = ['Content/Dist/css/custom/calendar.css']; -->
    <?php if (!empty($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= APP_URL . htmlspecialchars($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Dark mode — se lee antes del render para evitar flash de tema incorrecto -->
    <?php $darkMode = isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] === true; ?>

</head>

<!-- Si dark mode está activo se agrega la clase al body
     El JS del footer también puede agregarla/quitarla en tiempo real -->
<body id="appBody"<?= $darkMode ? ' class="dark-mode"' : '' ?>>

    <!-- Overlay que aparece detrás del sidebar en móviles -->
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>