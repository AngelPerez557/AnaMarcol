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

    <!-- Driver.js v1.0.1 (local, sin dependencia de CDN) -->
    <script src="<?= APP_URL ?>Content/Vendor/driverjs/driver.js.iife.js"></script>
    <script>
    // ── Variables globales del tour ───────────────────────
    const AM_TOUR_COMPLETADO = <?= Auth::get('tour_completado') ? 'true' : 'false' ?>;
    const AM_APP_URL         = '<?= APP_URL ?>';
    const AM_USER_ID         = <?= Auth::id() ?? 0 ?>;
    const AM_CSRF            = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>';
    const AM_USER_NOMBRE     = '<?= htmlspecialchars(Auth::get('nombre') ?? 'Usuario') ?>';

    function amMarcarTour() {
        const fd = new FormData();
        fd.append('csrf_token', AM_CSRF);
        fd.append('id', AM_USER_ID);
        fetch(AM_APP_URL + 'Usuarios/marcarTour', { method: 'POST', body: fd })
        .catch(() => {});
    }

    function amActivarTour() {
        console.log('🔄 [TOUR] Iniciando amActivarTour()');
        
        // Verificar que las variables globales existan
        if (!AM_CSRF || !AM_USER_ID || !AM_APP_URL) {
            console.error('❌ [TOUR] Variables globales no definidas:');
            console.error('   AM_CSRF:', AM_CSRF);
            console.error('   AM_USER_ID:', AM_USER_ID);
            console.error('   AM_APP_URL:', AM_APP_URL);
            alert('Error: Variables de sesión no disponibles. Por favor recarga la página.');
            return;
        }
        
        console.log('📊 [TOUR] Variables globales OK:');
        console.log('   CSRF:', AM_CSRF.substring(0, 10) + '...');
        console.log('   USER_ID:', AM_USER_ID);
        console.log('   APP_URL:', AM_APP_URL);

        // Bandera local para forzar el tour aunque falle la actualización en servidor
        try {
            localStorage.setItem('am_force_tour', '1');
        } catch (e) {
            console.warn('No se pudo guardar am_force_tour en localStorage:', e);
        }
        
        const url = AM_APP_URL + 'Usuarios/activarTour';
        const fd = new FormData();
        fd.append('csrf_token', AM_CSRF);
        fd.append('id', AM_USER_ID);
        
        console.log('🚀 [TOUR] Enviando POST a:', url);
        
        fetch(url, { 
            method: 'POST', 
            body: fd 
        })
        .then(response => {
            console.log('📡 [TOUR] Respuesta HTTP:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ [TOUR] Respuesta JSON:', data);
            console.log('🔄 [TOUR] Redirigiendo al Dashboard con tour forzado...');
            setTimeout(() => {
                window.location.href = AM_APP_URL + 'Dashboard/index?tour=1';
            }, 500);
        })
        .catch((error) => {
            console.error('❌ [TOUR] Error al activar tour:', error);
            console.error('   Tipo:', error.name);
            console.error('   Mensaje:', error.message);
            console.warn('⚠️ [TOUR] Se forzará redirección local al Dashboard para iniciar tour.');
            window.location.href = AM_APP_URL + 'Dashboard/index?tour=1';
        });
    }
    </script>

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