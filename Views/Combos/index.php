<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-layer-group me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($combos) ?> combo<?= count($combos) !== 1 ? 's' : '' ?> registrado<?= count($combos) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <?php if (Auth::can('combos.crear')): ?>
        <a href="<?= APP_URL ?>Combos/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Combo
        </a>
        <?php endif; ?>
    </div>

    <!-- ─────────────────────────────────────────────
         CARDS
         ───────────────────────────────────────────── -->
    <?php if (empty($combos)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-layer-group fa-3x mb-3 d-block" style="color:#de777d;opacity:0.4;"></i>
        No hay combos registrados.
        <?php if (Auth::can('combos.crear')): ?>
        <br>
        <a href="<?= APP_URL ?>Combos/registry" class="btn btn-primary mt-3">
            <i class="fas fa-plus me-2"></i>Crear el primero
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($combos as $combo): ?>
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <div class="card h-100 <?= !$combo->isActivo() ? 'opacity-50' : '' ?>">

                <!-- Imagen -->
                <div class="position-relative overflow-hidden" style="height:160px; flex-shrink:0;">
                    <div style="
                        width:100%; height:100%;
                        background-image: url('<?= $combo->getImageUrl() ?>');
                        background-size: contain;
                        background-position: center;
                        background-repeat: no-repeat;
                        background-color: #fdf8f8;">
                    </div>
                    <!-- Badge descuento -->
                    <?php if ($combo->tieneDescuento()): ?>
                    <span class="position-absolute top-0 start-0 m-2 badge bg-danger">
                        <i class="fas fa-tag me-1"></i><?= $combo->getDescuentoFormateado() ?> OFF
                    </span>
                    <?php endif; ?>
                    <!-- Badge estado -->
                    <span class="position-absolute top-0 end-0 m-2 badge <?= $combo->isActivo() ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $combo->isActivo() ? 'Activo' : 'Inactivo' ?>
                    </span>
                </div>

                <div class="card-body d-flex flex-column">
                    <h6 class="card-title fw-bold mb-1">
                        <?= htmlspecialchars($combo->nombre) ?>
                    </h6>
                    <?php if ($combo->descripcion): ?>
                    <small class="text-muted mb-2">
                        <?= htmlspecialchars($combo->descripcion) ?>
                    </small>
                    <?php endif; ?>
                    <?php if ($combo->tieneDescuento()): ?>
                    <div class="mt-auto">
                        <span class="badge bg-danger">
                            <?= $combo->getDescuentoFormateado() ?> de descuento
                        </span>
                    </div>
                    <?php else: ?>
                    <div class="mt-auto">
                        <small class="text-muted">Sin descuento — suma de productos</small>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Acciones -->
                <div class="card-footer d-flex gap-2 justify-content-between align-items-center">
                    <?php if (Auth::can('combos.editar')): ?>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input toggle-activo"
                               type="checkbox"
                               role="switch"
                               id="toggle-<?= $combo->id ?>"
                               data-id="<?= $combo->id ?>"
                               data-url="<?= APP_URL ?>Combos/toggle"
                               data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                               <?= $combo->isActivo() ? 'checked' : '' ?>>
                        <label class="form-check-label" for="toggle-<?= $combo->id ?>"></label>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <?php if (Auth::can('combos.editar')): ?>
                        <a href="<?= APP_URL ?>Combos/registry/<?= $combo->id ?>"
                           class="btn btn-sm btn-outline-primary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (Auth::can('combos.eliminar')): ?>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger btn-delete"
                                data-id="<?= $combo->id ?>"
                                data-nombre="<?= htmlspecialchars($combo->nombre) ?>"
                                data-url="<?= APP_URL ?>Combos/delete"
                                data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Toggle activo
    document.querySelectorAll('input.toggle-activo[type="checkbox"]').forEach(function (toggle) {
        toggle.addEventListener('change', function (e) {
            e.stopPropagation();
            const id = this.dataset.id, url = this.dataset.url,
                  csrf = this.dataset.csrf, activo = this.checked ? 1 : 0, self = this;

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&activo=${activo}&csrf_token=${csrf}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.mixin({ toast:true, position:'top-end',
                        showConfirmButton:false, timer:2000 })
                    .fire({ icon:'success', title: activo ? 'Combo activado' : 'Combo desactivado' });
                } else {
                    self.checked = !self.checked;
                }
            })
            .catch(() => { self.checked = !self.checked; });
        });
    });

    // Eliminar
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.id, nombre = this.dataset.nombre,
                  url = this.dataset.url, csrf = this.dataset.csrf;

            Swal.fire({
                icon:'warning', title:'¿Eliminar combo?',
                text: `"${nombre}" será desactivado.`,
                showCancelButton:true, confirmButtonColor:'#dc3545',
                confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST'; form.action = url;
                    form.innerHTML = `<input type="hidden" name="id" value="${id}">
                                      <input type="hidden" name="csrf_token" value="${csrf}">`;
                    document.body.appendChild(form); form.submit();
                }
            });
        });
    });

});
</script>