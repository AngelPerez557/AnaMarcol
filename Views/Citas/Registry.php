<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-<?= $cita->Found ? 'edit' : 'plus-circle' ?> me-2"
                   style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
        </div>
        <a href="<?= APP_URL ?>Citas/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-plus me-2"></i>Datos de la cita
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="<?= APP_URL ?>Citas/save"
                          autocomplete="off"
                          id="formCita">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <?php if ($cita->Found): ?>
                        <input type="hidden" name="id" value="<?= $cita->id ?>">
                        <?php endif; ?>

                        <!-- Servicio -->
                        <div class="mb-3">
                            <label for="servicio_id" class="form-label fw-semibold">
                                Servicio <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="servicio_id" name="servicio_id" required>
                                <option value="">Seleccionar servicio...</option>
                                <?php foreach ($servicios as $s): ?>
                                <option value="<?= $s->id ?>"
                                        data-duracion="<?= $s->duracion ?>"
                                        data-precio="<?= $s->precio_base ?>"
                                        <?= (int)($cita->servicio_id ?? 0) === $s->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s->nombre) ?>
                                    — <?= $s->getDuracionFormateada() ?>
                                    — <?= $s->getPrecioFormateado() ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Cliente (opcional) -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Cliente
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text"
                                       class="form-control border-start-0"
                                       id="buscarClienteCita"
                                       placeholder="Buscar cliente..."
                                       value="<?= htmlspecialchars($cita->cliente_nombre ?? '') ?>"
                                       autocomplete="off">
                            </div>
                            <div id="resultadosClienteCita" class="list-group mt-1" style="display:none;"></div>
                            <input type="hidden" id="cliente_id" name="cliente_id"
                                   value="<?= $cita->cliente_id ?? '' ?>">
                            <small class="text-muted">
                                Si el cliente no tiene cuenta déjalo vacío.
                            </small>
                        </div>

                        <!-- Fecha y Hora -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="fecha" class="form-label fw-semibold">
                                    Fecha <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       class="form-control"
                                       id="fecha"
                                       name="fecha"
                                       value="<?= htmlspecialchars($cita->fecha ?? '') ?>"
                                       min="<?= date('Y-m-d') ?>"
                                       required>
                            </div>
                            <div class="col-6">
                                <label for="hora_inicio" class="form-label fw-semibold">
                                    Hora <span class="text-danger">*</span>
                                </label>
                                <input type="time"
                                       class="form-control"
                                       id="hora_inicio"
                                       name="hora_inicio"
                                       value="<?= htmlspecialchars(substr($cita->hora_inicio ?? '', 0, 5)) ?>"
                                       min="<?= substr($config['horario_inicio'] ?? '08:00:00', 0, 5) ?>"
                                       max="<?= substr($config['horario_fin']    ?? '18:00:00', 0, 5) ?>"
                                       required>
                            </div>
                        </div>

                        <!-- Indicador de disponibilidad -->
                        <div id="indicadorDisponibilidad" class="mb-3" style="display:none;">
                            <div id="msgDisponibilidad" class="alert py-2 mb-0"></div>
                        </div>

                        <!-- Duración (se llena automáticamente desde el servicio) -->
                        <div class="mb-3">
                            <label for="duracion" class="form-label fw-semibold">Duración (minutos)</label>
                            <input type="number"
                                   class="form-control"
                                   id="duracion"
                                   name="duracion"
                                   min="15"
                                   value="<?= $cita->duracion ?? $config['duracion_default'] ?? 60 ?>">
                            <small class="text-muted">Se asigna automáticamente según el servicio.</small>
                        </div>

                        <!-- Precio -->
                        <div class="mb-3">
                            <label for="precio" class="form-label fw-semibold">Precio (L.)</label>
                            <div class="input-group">
                                <span class="input-group-text">L.</span>
                                <input type="number"
                                       class="form-control"
                                       id="precio"
                                       name="precio"
                                       step="0.01"
                                       min="0"
                                       value="<?= $cita->precio ?? 0 ?>">
                            </div>
                            <small class="text-muted">Se asigna automáticamente según el servicio.</small>
                        </div>

                        <!-- Nota -->
                        <div class="mb-4">
                            <label for="nota" class="form-label fw-semibold">
                                Nota
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <textarea class="form-control" id="nota" name="nota"
                                      rows="2" maxlength="500"
                                      placeholder="Observaciones, peticiones especiales..."><?= htmlspecialchars($cita->nota ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill" id="btnGuardarCita">
                                <i class="fas fa-save me-2"></i>
                                <?= $cita->Found ? 'Guardar cambios' : 'Crear cita' ?>
                            </button>
                            <a href="<?= APP_URL ?>Citas/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<input type="hidden" id="appUrl"    value="<?= APP_URL ?>">
<input type="hidden" id="citaId"    value="<?= $cita->id ?? 0 ?>">

<script>
document.addEventListener('DOMContentLoaded', function () {

    const APP_URL = document.getElementById('appUrl').value;
    const citaId  = document.getElementById('citaId').value;

    // ── Auto-fill duración y precio desde servicio ──
    document.getElementById('servicio_id').addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            document.getElementById('duracion').value = option.dataset.duracion || 60;
            document.getElementById('precio').value   = option.dataset.precio   || 0;
            verificarDisponibilidad();
        }
    });

    // ── Verificar disponibilidad al cambiar fecha/hora/duración ──
    let timerDisp = null;
    function verificarDisponibilidad() {
        clearTimeout(timerDisp);
        timerDisp = setTimeout(() => {
            const fecha    = document.getElementById('fecha').value;
            const hora     = document.getElementById('hora_inicio').value;
            const duracion = document.getElementById('duracion').value;

            if (!fecha || !hora || !duracion) return;

            const indicador = document.getElementById('indicadorDisponibilidad');
            const msg       = document.getElementById('msgDisponibilidad');

            indicador.style.display = '';
            msg.className = 'alert py-2 mb-0 alert-secondary';
            msg.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verificando disponibilidad...';

            fetch(`${APP_URL}Citas/verificar?fecha=${fecha}&hora=${hora}&duracion=${duracion}&exclude_id=${citaId}`)
            .then(r => r.json())
            .then(data => {
                if (data.disponible) {
                    msg.className = 'alert py-2 mb-0 alert-success';
                    msg.innerHTML = `<i class="fas fa-check-circle me-2"></i>${data.message}`;
                } else {
                    msg.className = 'alert py-2 mb-0 alert-danger';
                    msg.innerHTML = `<i class="fas fa-times-circle me-2"></i>${data.message}`;
                }
            });
        }, 500);
    }

    document.getElementById('fecha').addEventListener('change', verificarDisponibilidad);
    document.getElementById('hora_inicio').addEventListener('change', verificarDisponibilidad);
    document.getElementById('duracion').addEventListener('change', verificarDisponibilidad);

    // Verificar al cargar si ya hay fecha y hora
    if (document.getElementById('fecha').value && document.getElementById('hora_inicio').value) {
        verificarDisponibilidad();
    }

    // ── Buscar cliente ────────────────────────────
    let clienteTimer = null;
    document.getElementById('buscarClienteCita').addEventListener('input', function () {
        clearTimeout(clienteTimer);
        const q = this.value.trim();
        if (q.length < 2) { document.getElementById('resultadosClienteCita').style.display = 'none'; return; }

        clienteTimer = setTimeout(() => {
            fetch(`${APP_URL}Clientes/search?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(data => {
                const lista = document.getElementById('resultadosClienteCita');
                if (!data.length) { lista.style.display = 'none'; return; }
                lista.innerHTML = data.map(c => `
                    <button type="button"
                            class="list-group-item list-group-item-action py-1 btn-cliente-cita"
                            data-id="${c.id}" data-nombre="${c.nombre}">
                        <i class="fas fa-user me-2 text-muted"></i>
                        <strong>${c.nombre}</strong>
                        <small class="text-muted ms-2">${c.telefono || c.email || ''}</small>
                    </button>`).join('');
                lista.style.display = '';
            });
        }, 300);
    });

    document.getElementById('resultadosClienteCita').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-cliente-cita');
        if (!btn) return;
        document.getElementById('cliente_id').value           = btn.dataset.id;
        document.getElementById('buscarClienteCita').value    = btn.dataset.nombre;
        this.style.display = 'none';
    });

});
</script>