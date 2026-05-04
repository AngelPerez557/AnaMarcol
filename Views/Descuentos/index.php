<div class="container-fluid py-4">

    <!-- CABECERA -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-percent me-2" style="color:#de777d;"></i>Descuentos
            </h4>
            <small class="text-muted">Gestiona los descuentos activos en tienda y caja</small>
        </div>
        <?php if (Auth::can('productos.editar')): ?>
        <a href="<?= APP_URL ?>Descuentos/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Descuento
        </a>
        <?php endif; ?>
    </div>

    <?php if (empty($descuentos)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-percent fa-3x mb-3 d-block" style="opacity:0.3; color:#de777d;"></i>
        No hay descuentos registrados.
        <?php if (Auth::can('productos.editar')): ?>
        <br>
        <a href="<?= APP_URL ?>Descuentos/registry" class="btn btn-primary mt-3">
            <i class="fas fa-plus me-2"></i>Crear el primero
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>

    <div class="row g-3">
        <?php foreach ($descuentos as $d):
            $hoy       = date('Y-m-d');
            $vigente   = $d['activo'] && $d['fecha_inicio'] <= $hoy && $d['fecha_fin'] >= $hoy;
            $expirado  = $d['fecha_fin'] < $hoy;
            $pendiente = $d['fecha_inicio'] > $hoy;
        ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100" style="border-left: 4px solid <?= $vigente ? '#28a745' : ($expirado ? '#6c757d' : '#ffc107') ?>;">
                <div class="card-body">

                    <!-- Cabecera card -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-bold mb-0"><?= htmlspecialchars($d['nombre']) ?></h5>
                        <span class="badge <?= $vigente ? 'bg-success' : ($expirado ? 'bg-secondary' : 'bg-warning text-dark') ?>">
                            <?= $vigente ? 'Vigente' : ($expirado ? 'Expirado' : 'Pendiente') ?>
                        </span>
                    </div>

                    <!-- Porcentaje destacado -->
                    <div class="mb-3" style="font-size:2.5rem; font-weight:800; color:#de777d; line-height:1;">
                        <?= $d['porcentaje'] ?>%
                        <small style="font-size:1rem; color:#888; font-weight:400;">de descuento</small>
                    </div>

                    <!-- Detalles -->
                    <div class="mb-2" style="font-size:0.85rem;">
                        <i class="fas fa-tag me-2" style="color:#de777d;"></i>
                        <?php if ($d['aplica_a'] === 'todo'): ?>
                            <strong>Toda la tienda</strong>
                        <?php else: ?>
                            Solo categoría: <strong><?= htmlspecialchars($d['categoria_nombre'] ?? '—') ?></strong>
                        <?php endif; ?>
                    </div>
                    <div class="mb-2 text-muted" style="font-size:0.85rem;">
                        <i class="fas fa-calendar me-2"></i>
                        <?= date('d/m/Y', strtotime($d['fecha_inicio'])) ?>
                        —
                        <?= date('d/m/Y', strtotime($d['fecha_fin'])) ?>
                    </div>

                    <!-- Generador texto Instagram -->
                    <?php if ($vigente): ?>
                    <div class="mt-3 p-2 rounded" style="background:rgba(222,119,125,0.08); border:1px solid rgba(222,119,125,0.2);">
                        <small class="text-muted d-block mb-1">
                            <i class="fab fa-instagram me-1"></i>Texto para redes sociales:
                        </small>
                        <div class="text-instagram" id="texto-<?= $d['id'] ?>" style="font-size:0.8rem; white-space:pre-wrap;">✨ <?= $d['aplica_a'] === 'todo' ? '¡DESCUENTO EN TODA LA TIENDA!' : '¡DESCUENTO EN ' . strtoupper($d['categoria_nombre'] ?? '') . '!' ?> ✨

🏷️ <?= $d['porcentaje'] ?>% de descuento
📅 Válido hasta el <?= date('d/m/Y', strtotime($d['fecha_fin'])) ?>

🛍️ Aprovecha ahora en nuestra tienda en línea:
<?= APP_URL ?>Tienda

#AnaMarcolMakeupStudio #Descuento #Belleza #Honduras</div>
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary mt-2 w-100"
                                onclick="copiarTexto('texto-<?= $d['id'] ?>', this)">
                            <i class="fas fa-copy me-1"></i>Copiar para Instagram / WhatsApp
                        </button>
                    </div>
                    <?php endif; ?>

                </div>

                <!-- Acciones -->
                <?php if (Auth::can('productos.editar')): ?>
                <div class="card-footer d-flex gap-2">
                    <a href="<?= APP_URL ?>Descuentos/registry/<?= $d['id'] ?>"
                       class="btn btn-sm btn-outline-primary flex-fill">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                    <button type="button"
                            class="btn btn-sm btn-outline-danger btn-delete"
                            data-id="<?= $d['id'] ?>"
                            data-nombre="<?= htmlspecialchars($d['nombre']) ?>"
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

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Copiar texto al portapapeles
    window.copiarTexto = function(id, btn) {
        const texto = document.getElementById(id).textContent;
        navigator.clipboard.writeText(texto).then(() => {
            btn.innerHTML = '<i class="fas fa-check me-1"></i>¡Copiado!';
            btn.classList.replace('btn-outline-secondary', 'btn-success');
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-copy me-1"></i>Copiar para Instagram / WhatsApp';
                btn.classList.replace('btn-success', 'btn-outline-secondary');
            }, 2500);
        });
    };

    // Eliminar
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            const id     = this.dataset.id;
            const nombre = this.dataset.nombre;
            const csrf   = this.dataset.csrf;

            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar descuento?',
                text: `"${nombre}" será eliminado permanentemente.`,
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?= APP_URL ?>Descuentos/delete';
                    form.innerHTML = `
                        <input type="hidden" name="id" value="${id}">
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