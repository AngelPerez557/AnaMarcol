<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-images me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted"><?= count($fotos) ?> foto<?= count($fotos) !== 1 ? 's' : '' ?></small>
        </div>
        <?php if (Auth::can('galeria.gestionar')): ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSubirFoto">
            <i class="fas fa-upload me-2"></i>Subir Foto
        </button>
        <?php endif; ?>
    </div>

    <?php if (empty($fotos)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-images fa-3x mb-3 d-block" style="color:#de777d;opacity:0.4;"></i>
        No hay fotos en la galería.
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($fotos as $foto): ?>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
            <div class="card h-100 <?= !$foto['activo'] ? 'opacity-50' : '' ?>">
                <div style="height:140px; overflow:hidden; border-radius:8px 8px 0 0; cursor:pointer;"
                     onclick="verFoto('<?= APP_URL ?>Content/Demo/img/Galeria/<?= htmlspecialchars($foto['imagen_url']) ?>')">
                    <div style="
                        width:100%; height:100%;
                        background-image: url('<?= APP_URL ?>Content/Demo/img/Galeria/<?= htmlspecialchars($foto['imagen_url']) ?>');
                        background-size: cover;
                        background-position: center;">
                    </div>
                </div>
                <div class="card-body p-2">
                    <?php if ($foto['descripcion']): ?>
                    <small class="text-muted"><?= htmlspecialchars($foto['descripcion']) ?></small>
                    <?php endif; ?>
                </div>
                <?php if (Auth::can('galeria.gestionar')): ?>
                <div class="card-footer d-flex justify-content-between align-items-center py-1 px-2">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input toggle-activo" type="checkbox" role="switch"
                               id="toggle-<?= $foto['id'] ?>"
                               data-id="<?= $foto['id'] ?>"
                               data-url="<?= APP_URL ?>Galeria/toggle"
                               data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                               <?= $foto['activo'] ? 'checked' : '' ?>>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                            data-id="<?= $foto['id'] ?>"
                            data-nombre="esta foto"
                            data-url="<?= APP_URL ?>Galeria/delete"
                            data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<!-- Modal subir foto -->
<div class="modal fade" id="modalSubirFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-upload me-2" style="color:#de777d;"></i>Subir foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>Galeria/save" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Imagen <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="imagen"
                               accept="image/jpeg,image/png,image/webp" required>
                        <small class="text-muted">JPG, PNG o WEBP. Máx. 2MB.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descripción <span class="text-muted fw-normal">(opcional)</span></label>
                        <input type="text" class="form-control" name="descripcion"
                               maxlength="255" placeholder="Descripción de la foto...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Orden</label>
                        <input type="number" class="form-control" name="orden" min="0" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Subir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ver foto -->
<div class="modal fade" id="modalVerFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0 text-center">
                <img id="imgVisor" src="" alt="Foto" style="max-width:100%; border-radius:8px;">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    window.verFoto = function(url) {
        document.getElementById('imgVisor').src = url;
        new bootstrap.Modal(document.getElementById('modalVerFoto')).show();
    };

    // ── Toggle activo/inactivo ────────────────────
    document.querySelectorAll('input.toggle-activo').forEach(function (toggle) {
        toggle.addEventListener('change', function (e) {
            e.stopPropagation();
            const self   = this;
            const id     = this.dataset.id;
            const url    = this.dataset.url;
            const csrf   = this.dataset.csrf;
            const activo = this.checked ? 1 : 0;

            fetch(url, {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    `id=${id}&activo=${activo}&csrf_token=${csrf}`
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) self.checked = !self.checked;
            })
            .catch(() => { self.checked = !self.checked; });
        });
    });

    // ── Eliminar ──────────────────────────────────
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Guardar datos ANTES del .then() — this no es confiable en callbacks async
            const id   = this.dataset.id;
            const url  = this.dataset.url;
            const csrf = this.dataset.csrf;

            Swal.fire({
                icon:               'warning',
                title:              '¿Eliminar foto?',
                text:               'Esta acción no se puede deshacer.',
                showCancelButton:   true,
                confirmButtonColor: '#dc3545',
                confirmButtonText:  'Sí, eliminar',
                cancelButtonText:   'Cancelar'
            }).then(r => {
                if (r.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = `
                        <input type="hidden" name="id"         value="${id}">
                        <input type="hidden" name="csrf_token" value="${csrf}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

});
</script>