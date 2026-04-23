<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-calendar-alt me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($citasHoy) ?> cita<?= count($citasHoy) !== 1 ? 's' : '' ?> hoy
            </small>
        </div>
        <div class="d-flex gap-2">
            <?php if (Auth::can('citas.editar')): ?>
            <a href="<?= APP_URL ?>Citas/config" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-cog me-1"></i>Horarios
            </a>
            <?php endif; ?>
            <?php if (Auth::can('citas.crear')): ?>
            <a href="<?= APP_URL ?>Citas/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nueva Cita
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ─────────────────────────────────────────────
         TABS VISTA
         ───────────────────────────────────────────── -->
    <button type="button" class="btn btn-primary btn-sm active" id="btnVistaMes">
        <i class="fas fa-calendar me-1"></i>Mensual
    </button>
    <button type="button" class="btn btn-outline-primary btn-sm" id="btnVistaDia">
        <i class="fas fa-calendar-day me-1"></i>Diaria
    </button>
    <br>
    <br>

    <!-- ═══════════════════════════════════════════
         VISTA MENSUAL — Calendario
         ═══════════════════════════════════════════ -->
    <div id="vistaMes">

        <!-- Navegación del mes -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= APP_URL ?>Citas/index?mes=<?= $mes - 1 ?>&anio=<?= $anio ?>"
               class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h5 class="mb-0 fw-bold">
                <?php
                $meses = ['', 'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                        'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                echo $meses[$mes] . ' ' . $anio;
                ?>
            </h5>
            <a href="<?= APP_URL ?>Citas/index?mes=<?= $mes + 1 ?>&anio=<?= $anio ?>"
               class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        <div class="card">
    <div class="card-body p-2">
        <!-- Días de la semana -->
        <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:2px; margin-bottom:4px;">
            <?php
            $diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            foreach ($diasSemana as $dia):
            ?>
            <div class="text-center py-2" style="font-size:0.8rem; font-weight:600; color:#b05a60;">
                <?= $dia ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        $primerDia       = mktime(0, 0, 0, $mes, 1, $anio);
        $diasEnMes       = (int) date('t', $primerDia);
        $diaSemanaInicio = (int) date('w', $primerDia);
        $hoy             = date('Y-m-d');
        ?>
        <!-- Grid del calendario -->
        <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:2px;" id="gridCalendario">
            <?php for ($i = 0; $i < $diaSemanaInicio; $i++): ?>
            <div style="min-height:80px;"></div>
            <?php endfor; ?>

            <?php for ($dia = 1; $dia <= $diasEnMes; $dia++):
                $fechaDia      = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
                $esHoy         = $fechaDia === $hoy;
                $tieneCitas    = isset($citasPorFecha[$fechaDia]);
                $numCitas      = $tieneCitas ? count($citasPorFecha[$fechaDia]) : 0;
                $diaSem        = ($diaSemanaInicio + $dia - 1) % 7;
                $diasLaborales = explode(',', $config['dias_laborales'] ?? '1,2,3,4,5,6');
                $esDiaLaboral  = in_array((string)$diaSem, $diasLaborales);
            ?>
            <div style="padding:2px;">
                <div class="rounded p-1"
                     style="min-height:80px; cursor:pointer;
                            background: <?= $esHoy ? 'rgba(222,119,125,0.15)' : ($esDiaLaboral ? '' : 'rgba(0,0,0,0.03)') ?>;
                            border: <?= $esHoy ? '2px solid #de777d' : '1px solid rgba(0,0,0,0.06)' ?>;"
                     onclick="seleccionarFecha('<?= $fechaDia ?>')">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-size:0.85rem; font-weight:<?= $esHoy ? '700' : '500' ?>;
                                     color:<?= $esHoy ? '#de777d' : ($esDiaLaboral ? '' : '#aaa') ?>;">
                            <?= $dia ?>
                        </span>
                        <?php if ($numCitas > 0): ?>
                        <span class="badge rounded-pill" style="background:#de777d; font-size:0.65rem;">
                            <?= $numCitas ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($tieneCitas): ?>
                    <div class="mt-1">
                        <?php foreach (array_slice($citasPorFecha[$fechaDia], 0, 2) as $c): ?>
                        <div class="rounded px-1 mb-1 text-truncate"
                             style="font-size:0.65rem; background:rgba(222,119,125,0.2); color:#b05a60;">
                            <?= date('H:i', strtotime($c['hora_inicio'])) ?>
                            <?= htmlspecialchars($c['servicio_nombre']) ?>
                        </div>
                        <?php endforeach; ?>
                        <?php if ($numCitas > 2): ?>
                        <div style="font-size:0.65rem; color:#b05a60;">+<?= $numCitas - 2 ?> más</div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endfor; ?>
        </div>

    </div>
</div>

</div><!-- /#vistaMes -->

<!-- ═══════════════════════════════════════════
     VISTA DIARIA
     ═══════════════════════════════════════════ -->
<div id="vistaDia" style="display:none;">

    <div class="d-flex gap-2 align-items-center mb-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDiaAnterior">
            <i class="fas fa-chevron-left"></i>
        </button>
        <input type="date" class="form-control" id="fechaDia"
               value="<?= date('Y-m-d') ?>" style="max-width:200px;">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDiaSiguiente">
            <i class="fas fa-chevron-right"></i>
        </button>
        <button type="button" class="btn btn-outline-primary btn-sm" id="btnHoy">Hoy</button>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span id="tituloFechaDia" class="fw-semibold"></span>
            <?php if (Auth::can('citas.crear')): ?>
            <a href="#" id="btnNuevaCitaDia" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Nueva cita este día
            </a>
            <?php endif; ?>
        </div>
        <div class="card-body p-0" id="timelineDia">
            <div class="text-center py-5 text-muted">
                <i class="fas fa-spinner fa-spin fa-2x mb-3 d-block" style="color:#de777d;opacity:0.5;"></i>
                Cargando citas...
            </div>
        </div>
    </div>

</div>

<!-- ─────────────────────────────────────────────
     MODAL — Detalle de cita
     ───────────────────────────────────────────── -->
<div class="modal fade" id="modalCita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-check me-2" style="color:#de777d;"></i>
                    Detalle de cita
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bodyModalCita"></div>
            <div class="modal-footer" id="footerModalCita"></div>
        </div>
    </div>
</div>

<input type="hidden" id="appUrl"   value="<?= APP_URL ?>">
<input type="hidden" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
<input type="hidden" id="configHorarioInicio" value="<?= $config['horario_inicio'] ?? '08:00:00' ?>">
<input type="hidden" id="configHorarioFin"    value="<?= $config['horario_fin']    ?? '18:00:00' ?>">

<script>
document.addEventListener('DOMContentLoaded', function () {

    const APP_URL = document.getElementById('appUrl').value;
    const csrf    = document.getElementById('csrfToken').value;

    // ── Toggle vista ──────────────────────────────
    const btnMes = document.getElementById('btnVistaMes');
    const btnDia = document.getElementById('btnVistaDia');

    btnMes.addEventListener('click', function () {
        document.getElementById('vistaMes').style.display = '';
        document.getElementById('vistaDia').style.display = 'none';
        this.classList.add('active', 'btn-primary'); this.classList.remove('btn-outline-primary');
        btnDia.classList.remove('active', 'btn-primary'); btnDia.classList.add('btn-outline-primary');
    });

    btnDia.addEventListener('click', function () {
        document.getElementById('vistaMes').style.display = 'none';
        document.getElementById('vistaDia').style.display = '';
        this.classList.add('active', 'btn-primary'); this.classList.remove('btn-outline-primary');
        btnMes.classList.remove('active', 'btn-primary'); btnMes.classList.add('btn-outline-primary');
        cargarCitasDia(document.getElementById('fechaDia').value);
    });

    // ── Seleccionar fecha desde calendario ────────
    window.seleccionarFecha = function (fecha) {
        document.getElementById('fechaDia').value = fecha;
        btnDia.click();
    };

    // ── Navegación diaria ─────────────────────────
    function cambiarDia(dias) {
        const input = document.getElementById('fechaDia');
        const fecha = new Date(input.value + 'T00:00:00');
        fecha.setDate(fecha.getDate() + dias);
        input.value = fecha.toISOString().split('T')[0];
        cargarCitasDia(input.value);
    }

    document.getElementById('btnDiaAnterior').addEventListener('click', () => cambiarDia(-1));
    document.getElementById('btnDiaSiguiente').addEventListener('click', () => cambiarDia(1));
    document.getElementById('btnHoy').addEventListener('click', () => {
        document.getElementById('fechaDia').value = new Date().toISOString().split('T')[0];
        cargarCitasDia(document.getElementById('fechaDia').value);
    });

    document.getElementById('fechaDia').addEventListener('change', function () {
        cargarCitasDia(this.value);
    });

    // ── Cargar citas del día ──────────────────────
    function cargarCitasDia(fecha) {
        const titulo = document.getElementById('tituloFechaDia');
        const btnNueva = document.getElementById('btnNuevaCitaDia');
        const timeline = document.getElementById('timelineDia');

        if (btnNueva) btnNueva.href = `${APP_URL}Citas/create?fecha=${fecha}`;

        // Formatear título
        const d = new Date(fecha + 'T00:00:00');
        const opciones = { weekday:'long', year:'numeric', month:'long', day:'numeric' };
        titulo.textContent = d.toLocaleDateString('es-HN', opciones);

        timeline.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x" style="color:#de777d;opacity:0.5;"></i></div>';

        fetch(`${APP_URL}Citas/dia?fecha=${fecha}`)
        .then(r => r.json())
        .then(citas => {
            if (citas.length === 0) {
                timeline.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-calendar-times fa-2x mb-3 d-block" style="opacity:0.3;"></i>
                        <p class="mb-0">No hay citas para este día.</p>
                    </div>`;
                return;
            }

            let html = '<div class="list-group list-group-flush">';
            citas.forEach(cita => {
                html += `
                <div class="list-group-item py-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="text-center" style="min-width:60px;">
                            <div class="fw-bold" style="color:#de777d; font-size:1rem;">
                                ${cita.hora_inicio.substring(0,5)}
                            </div>
                            <div class="text-muted" style="font-size:0.75rem;">
                                ${cita.hora_fin}
                            </div>
                            <div class="text-muted" style="font-size:0.7rem;">
                                ${cita.duracion}min
                            </div>
                        </div>
                        <div class="flex-fill">
                            <div class="fw-semibold">${cita.servicio_nombre}</div>
                            <div class="text-muted" style="font-size:0.85rem;">
                                <i class="fas fa-user me-1"></i>${cita.cliente_nombre}
                            </div>
                            ${cita.nota ? `<small class="text-muted fst-italic">${cita.nota}</small>` : ''}
                        </div>
                        <div class="d-flex flex-column gap-1 align-items-end">
                            <span class="badge ${cita.badge}">${cita.estado}</span>
                            <span class="fw-bold" style="color:#de777d; font-size:0.85rem;">
                                L. ${parseFloat(cita.precio).toFixed(2)}
                            </span>
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary btn-detalle-cita"
                                    data-id="${cita.id}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
            });
            html += '</div>';
            timeline.innerHTML = html;
        })
        .catch(() => {
            timeline.innerHTML = '<div class="text-center py-4 text-danger">Error al cargar las citas.</div>';
        });
    }

    // ── Ver detalle de cita ───────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-detalle-cita');
        if (!btn) return;

        const id    = btn.dataset.id;
        const modal = new bootstrap.Modal(document.getElementById('modalCita'));
        const body  = document.getElementById('bodyModalCita');
        const footer= document.getElementById('footerModalCita');

        body.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin fa-2x" style="color:#de777d;"></i></div>';
        footer.innerHTML = '';
        modal.show();

        fetch(`${APP_URL}Citas/dia?fecha=${document.getElementById('fechaDia').value}`)
        .then(r => r.json())
        .then(citas => {
            const cita = citas.find(c => c.id == id);
            if (!cita) { body.innerHTML = '<p class="text-muted">No encontrada.</p>'; return; }

            body.innerHTML = `
                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted d-block">Servicio</small>
                        <strong>${cita.servicio_nombre}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Cliente</small>
                        <strong>${cita.cliente_nombre}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Hora</small>
                        <strong>${cita.hora_inicio.substring(0,5)} - ${cita.hora_fin}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Precio</small>
                        <strong style="color:#de777d;">L. ${parseFloat(cita.precio).toFixed(2)}</strong>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Estado</small>
                        <span class="badge ${cita.badge}">${cita.estado}</span>
                    </div>
                    ${cita.nota ? `<div class="col-12"><small class="text-muted d-block">Nota</small><em>${cita.nota}</em></div>` : ''}
                </div>
                <hr>
                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:0.85rem;">Cambiar estado:</label>
                    <div class="d-flex gap-2 flex-wrap">
                        ${['Pendiente','Confirmada','Completada','Cancelada'].map(est => `
                        <button type="button"
                                class="btn btn-sm ${cita.estado === est ? 'btn-primary' : 'btn-outline-secondary'} btn-cambiar-estado-cita"
                                data-id="${cita.id}" data-estado="${est}">
                            ${est}
                        </button>`).join('')}
                    </div>
                </div>`;

            footer.innerHTML = `
                <a href="${APP_URL}Citas/edit/${cita.id}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit me-1"></i>Editar
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    Cerrar
                </button>`;
        });
    });

    // ── Cambiar estado de cita desde modal ────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-cambiar-estado-cita');
        if (!btn) return;

        const id     = btn.dataset.id;
        const estado = btn.dataset.estado;

        const formData = new FormData();
        formData.append('csrf_token', csrf);
        formData.append('id',         id);
        formData.append('estado',     estado);

        fetch(`${APP_URL}Citas/cambiarEstadoCita`, { method:'POST', body:formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:2000 })
                .fire({ icon:'success', title:`Estado: ${estado}` });
                bootstrap.Modal.getInstance(document.getElementById('modalCita')).hide();
                cargarCitasDia(document.getElementById('fechaDia').value);
            }
        });
    });

});
</script>