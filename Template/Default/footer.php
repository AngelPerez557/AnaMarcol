</div><!-- /.container-fluid -->
    </main><!-- /#mainContent -->

    <!-- FOOTER -->
    <footer class="footer py-4 text-center">
        <p class="mb-0">
            &copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash;
            Desarrollado por
            <a href="https://wa.me/50493429641"
               target="_blank"
               rel="noopener noreferrer"
               style="color:inherit; font-weight:600;">
                DeskCod
            </a>
        </p>
    </footer>

    <!-- ─────────────────────────────────────────────
         JAVASCRIPT — Orden obligatorio:
         1. APP_URL        → variable global para todos los scripts
         2. Bootstrap      → requerido por sidebar (collapse, dropdown)
         3. SweetAlert2    → alertas en vistas
         4. sidebar.js     → lógica del sidebar y collapse
         5. theme-switcher → dark mode toggle
         6. $extraJs       → scripts específicos de cada vista
         7. Alertas sesión → mensajes flash desde controladores
         ───────────────────────────────────────────── -->

    <!-- 1. Variable global APP_URL — debe ir primero -->
    <script>const APP_URL = '<?= APP_URL ?>';</script>

    <!-- 2. Bootstrap bundle — incluye Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- 3. SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- 4. Sidebar -->
    <script src="<?= APP_URL ?>Content/Dist/js/sidebar.js"></script>

    <!-- 5. Dark mode toggle -->
    <script src="<?= APP_URL ?>Content/Dist/js/theme-switcher.js"></script>

    <!-- 6. JS adicional por vista
         El controlador inyecta scripts específicos mediante $extraJs
         Ejemplo: $extraJs = ['Content/Dist/js/calendar.js']; -->
    <?php if (!empty($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= APP_URL . htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- 7. Alertas flash desde controladores
         El controlador guarda mensajes en $_SESSION['flash']
         y el footer los muestra automáticamente con SweetAlert2
         Ejemplo en controlador:
         $_SESSION['flash'] = ['type' => 'success', 'message' => 'Guardado correctamente']; -->
    <?php if (!empty($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon:              '<?= htmlspecialchars($flash['type']    ?? 'info',    ENT_QUOTES) ?>',
                    title:             '<?= htmlspecialchars($flash['title']   ?? 'Aviso',   ENT_QUOTES) ?>',
                    text:              '<?= htmlspecialchars($flash['message'] ?? '',         ENT_QUOTES) ?>',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#de777d',
                    allowOutsideClick: false
                });
            });
        </script>
    <?php endif; ?>

</body>
</html>