<div class="container-fluid py-4">

    <!-- CABECERA -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-plus-circle me-2" style="color:#de777d;"></i>
                Nuevo ticket de soporte
            </h4>
            <small class="text-muted">El ticket será enviado al equipo de DeskCod</small>
        </div>
        <a href="<?= APP_URL ?>Soporte/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <form method="POST" action="<?= APP_URL ?>Soporte/registry" autocomplete="off">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="prioridad" id="inputPrioridad" value="media">

                <!-- Datos del ticket -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-ticket-alt me-2"></i>Datos del ticket
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <!-- Título -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Título <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       name="titulo"
                                       maxlength="200"
                                       placeholder="Describe brevemente el problema o solicitud"
                                       value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>"
                                       required autofocus>
                            </div>

                            <!-- Tipo -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Tipo</label>
                                <select class="form-select" name="tipo">
                                    <option value="error"         <?= ($_POST['tipo'] ?? '') === 'error'         ? 'selected' : '' ?>>
                                        🐛 Error / Bug
                                    </option>
                                    <option value="modificacion"  <?= ($_POST['tipo'] ?? '') === 'modificacion'  ? 'selected' : '' ?>>
                                        ✏️ Modificación
                                    </option>
                                    <option value="nueva_funcion" <?= ($_POST['tipo'] ?? '') === 'nueva_funcion' ? 'selected' : '' ?>>
                                        ⭐ Nueva función
                                    </option>
                                    <option value="consulta" selected <?= ($_POST['tipo'] ?? 'consulta') === 'consulta' ? 'selected' : '' ?>>
                                        ❓ Consulta
                                    </option>
                                </select>
                            </div>

                            <!-- Prioridad visual -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Prioridad</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn-prioridad flex-fill p-baja"
                                            onclick="seleccionarPrioridad('baja', this)"
                                            title="Baja">
                                        <i class="fas fa-arrow-down me-1"></i>Baja
                                    </button>
                                    <button type="button" class="btn-prioridad flex-fill p-media activo"
                                            onclick="seleccionarPrioridad('media', this)"
                                            title="Media">
                                        <i class="fas fa-minus me-1"></i>Media
                                    </button>
                                    <button type="button" class="btn-prioridad flex-fill p-alta"
                                            onclick="seleccionarPrioridad('alta', this)"
                                            title="Alta">
                                        <i class="fas fa-arrow-up me-1"></i>Alta
                                    </button>
                                    <button type="button" class="btn-prioridad flex-fill p-critica"
                                            onclick="seleccionarPrioridad('critica', this)"
                                            title="Crítica">
                                        <i class="fas fa-fire me-1"></i>Crítica
                                    </button>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Descripción <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control"
                                          name="descripcion"
                                          rows="6"
                                          placeholder="Describe el problema en detalle. Incluye pasos para reproducirlo si es un error..."
                                          required><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                                <small class="text-muted">
                                    Mientras más detalle incluyas, más rápido podremos ayudarte.
                                </small>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="<?= APP_URL ?>Soporte/index" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary" id="btnEnviar">
                        <i class="fas fa-paper-plane me-2"></i>Enviar ticket
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<style>
.btn-prioridad {
    padding: 6px 8px;
    border-radius: 6px;
    border: 2px solid #dee2e6;
    background: #fff;
    cursor: pointer;
    font-size: 0.78rem;
    font-weight: 500;
    transition: all 0.2s;
}
.btn-prioridad.p-baja    { border-color: #6c757d; }
.btn-prioridad.p-media   { border-color: #ffc107; }
.btn-prioridad.p-alta    { border-color: #fd7e14; }
.btn-prioridad.p-critica { border-color: #dc3545; }
.btn-prioridad.activo.p-baja    { background: #6c757d; color: #fff; }
.btn-prioridad.activo.p-media   { background: #ffc107; color: #000; }
.btn-prioridad.activo.p-alta    { background: #fd7e14; color: #fff; }
.btn-prioridad.activo.p-critica { background: #dc3545; color: #fff; }
</style>

<script>
function seleccionarPrioridad(valor, el) {
    document.querySelectorAll('.btn-prioridad').forEach(b => b.classList.remove('activo'));
    el.classList.add('activo');
    document.getElementById('inputPrioridad').value = valor;
}

document.querySelector('form').addEventListener('submit', function () {
    const btn = document.getElementById('btnEnviar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';
});
</script>