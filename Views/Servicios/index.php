<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-concierge-bell me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($servicios) ?> servicio<?= count($servicios) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <?php if (Auth::can('servicios.crear')): ?>
        <a href="<?= APP_URL ?>Servicios/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Servicio
        </a>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(222,119,125,0.08);">
                            <th class="ps-4">Servicio</th>
                            <th>Descripción</th>
                            <th class="text-center">Duración</th>
                            <th class="text-end">Precio base</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($servicios)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-concierge-bell fa-2x mb-3 d-block" style="opacity:0.3;"></i>
                                No hay servicios registrados.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($servicios as $s): ?>
                        <tr>
                            <td class="ps-4 fw-semibold"><?= htmlspecialchars($s->nombre) ?></td>
                            <td class="text-muted" style="font-size:0.85rem;">
                                <?= $s->descripcion ? htmlspecialchars($s->descripcion) : '<em>Sin descripción</em>' ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-clock me-1"></i><?= $s->getDuracionFormateada() ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold" style="color:#de777d;">
                                <?= $s->getPrecioFormateado() ?>
                            </td>
                            <td class="text-center">
                                <?php if (Auth::can('servicios.editar')): ?>
                                <div class="form-check form-switch d-inline-block mb-0">
                                    <input class="form-check-input toggle-activo"
                                           type="checkbox" role="switch"
                                           id="toggle-<?= $s->id ?>"
                                           data-id="<?= $s->id ?>"
                                           data-url="<?= APP_URL ?>Servicios/toggle"
                                           data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                           <?= $s->isActivo() ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="toggle-<?= $s->id ?>"></label>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <?php if (Auth::can('servicios.editar')): ?>
                                    <a href="<?= APP_URL ?>Servicios/registry/<?= $s->id ?>"
                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (Auth::can('servicios.eliminar')): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-delete"
                                            data-id="<?= $s->id ?>"
                                            data-nombre="<?= htmlspecialchars($s->nombre) ?>"
                                            data-url="<?= APP_URL ?>Servicios/delete"
                                            data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('input.toggle-activo[type="checkbox"]').forEach(function (toggle) {
        toggle.addEventListener('change', function (e) {
            e.stopPropagation();
            const id=this.dataset.id, url=this.dataset.url,
                  csrf=this.dataset.csrf, activo=this.checked?1:0, self=this;
            fetch(url,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:`id=${id}&activo=${activo}&csrf_token=${csrf}`})
            .then(r=>r.json())
            .then(data=>{
                if(data.success){
                    Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:2000})
                    .fire({icon:'success',title:activo?'Activado':'Desactivado'});
                } else { self.checked=!self.checked; }
            })
            .catch(()=>{self.checked=!self.checked;});
        });
    });

    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id=this.dataset.id, nombre=this.dataset.nombre,
                  url=this.dataset.url, csrf=this.dataset.csrf;
            Swal.fire({icon:'warning',title:'¿Desactivar servicio?',text:`"${nombre}" no aparecerá en nuevas citas.`,
                showCancelButton:true,confirmButtonColor:'#dc3545',
                confirmButtonText:'Sí',cancelButtonText:'Cancelar'})
            .then(result=>{
                if(result.isConfirmed){
                    const form=document.createElement('form');
                    form.method='POST'; form.action=url;
                    form.innerHTML=`<input type="hidden" name="id" value="${id}">
                                    <input type="hidden" name="csrf_token" value="${csrf}">`;
                    document.body.appendChild(form); form.submit();
                }
            });
        });
    });

});
</script>