<div class="container py-5">

    <h3 class="fw-bold mb-2">
        <i class="fas fa-calendar-plus me-2" style="color:#de777d;"></i>Agendar Cita
    </h3>
    <p class="text-muted mb-4">
        Selecciona un día disponible y completa el formulario para agendar tu cita.
        Te confirmaremos por WhatsApp.
    </p>

    <?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?= $_GET['error'] === 'ocupado' ? 'Ese horario ya no está disponible. Elige otro.' : 'Por favor completa todos los campos.' ?>
    </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- ── Calendario ─────────────────────────── -->
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-body">

                    <!-- Navegación -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <a href="<?= APP_URL ?>Tienda/citas?mes=<?= $mes - 1 ?>&anio=<?= $anio ?>"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <h5 class="mb-0 fw-bold">
                            <?php
                            $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                                      'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                            echo $meses[$mes] . ' ' . $anio;
                            ?>
                        </h5>
                        <a href="<?= APP_URL ?>Tienda/citas?mes=<?= $mes + 1 ?>&anio=<?= $anio ?>"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>

                    <!-- Leyenda -->
                    <div class="d-flex gap-3 mb-3" style="font-size:0.78rem;">
                        <span><span style="display:inline-block; width:12px; height:12px; border-radius:3px; background:rgba(40,167,69,0.3); border:1px solid #28a745;"></span> Disponible</span>
                        <span><span style="display:inline-block; width:12px; height:12px; border-radius:3px; background:rgba(220,53,69,0.1);"></span> Lleno</span>
                        <span><span style="display:inline-block; width:12px; height:12px; border-radius:3px; background:#f0f0f0;"></span> No laboral</span>
                    </div>

                    <!-- Días semana -->
                    <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:2px; margin-bottom:4px;">
                        <?php foreach(['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'] as $ds): ?>
                        <div class="text-center py-1" style="font-size:0.75rem; font-weight:600; color:#b05a60;">
                            <?= $ds ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php
                    $primerDia       = mktime(0,0,0,$mes,1,$anio);
                    $diasEnMes       = (int)date('t',$primerDia);
                    $diaSemanaInicio = (int)date('w',$primerDia);
                    $hoy             = date('Y-m-d');
                    $diasLaborales   = explode(',', $config['dias_laborales'] ?? '1,2,3,4,5,6');
                    $capacidad       = (int)($config['capacidad_simultanea'] ?? 1);
                    ?>

                    <!-- Grid -->
                    <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:2px;">
                        <?php for ($i=0;$i<$diaSemanaInicio;$i++): ?>
                        <div style="min-height:60px;"></div>
                        <?php endfor; ?>

                        <?php for ($dia=1;$dia<=$diasEnMes;$dia++):
                            $fechaDia  = sprintf('%04d-%02d-%02d',$anio,$mes,$dia);
                            $diaSem    = ($diaSemanaInicio+$dia-1)%7;
                            $esLaboral = in_array((string)$diaSem,$diasLaborales);
                            $esPasado  = $fechaDia < $hoy;
                            $numCitas  = isset($citasPorFecha[$fechaDia]) ? count($citasPorFecha[$fechaDia]) : 0;
                            $lleno     = $numCitas >= $capacidad;
                            $esHoy     = $fechaDia === $hoy;

                            if (!$esLaboral || $esPasado) {
                                $clase = 'dia-no-laboral';
                                $onclick = '';
                            } elseif ($lleno) {
                                $clase = 'dia-ocupado';
                                $onclick = '';
                            } else {
                                $clase = 'dia-disponible';
                                $onclick = "seleccionarDia('{$fechaDia}')";
                            }
                        ?>
                        <div style="padding:2px;">
                            <div class="rounded text-center <?= $clase ?>"
                                 style="min-height:60px; padding:6px; position:relative;
                                        border:<?= $esHoy ? '2px solid #de777d' : '1px solid transparent' ?>;"
                                 <?= $onclick ? "onclick=\"{$onclick}\" style=\"cursor:pointer;\"" : '' ?>>
                                <div style="font-size:0.85rem; font-weight:<?= $esHoy?'700':'500' ?>;
                                            color:<?= $esHoy?'#de777d':'' ?>">
                                    <?= $dia ?>
                                </div>
                                <?php if ($esLaboral && !$esPasado): ?>
                                <div style="font-size:0.6rem; margin-top:2px; color:#28a745;">
                                    <?= $lleno ? '<span style="color:#dc3545;">Lleno</span>' : 'Libre' ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>

                </div>
            </div>
        </div>

        <!-- ── Formulario ─────────────────────────── -->
        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-header fw-semibold">
                    <i class="fas fa-calendar-check me-2"></i>
                    <span id="tituloFormCita">Selecciona un día del calendario</span>
                </div>
                <div class="card-body">

                    <form method="POST" action="<?= APP_URL ?>Tienda/agendarCita"
                          autocomplete="off" id="formCita">
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="fecha"     id="inputFecha"    value="">
                        <input type="hidden" name="duracion"  id="inputDuracion" value="60">
                        <input type="hidden" name="precio"    id="inputPrecio"   value="0">

                        <!-- Servicio -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Servicio <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="servicio_id" id="selectServicio" required>
                                <option value="">Seleccionar servicio...</option>
                                <?php foreach ($servicios as $s): ?>
                                <option value="<?= $s->id ?>"
                                        data-duracion="<?= $s->duracion ?>"
                                        data-precio="<?= $s->precio_base ?>">
                                    <?= htmlspecialchars($s->nombre) ?>
                                    — <?= $s->getDuracionFormateada() ?>
                                    — L. <?= number_format((float)$s->precio_base, 2) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Hora -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Hora <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control" name="hora_inicio" id="inputHora"
                                   min="<?= substr($config['horario_inicio']??'08:00:00',0,5) ?>"
                                   max="<?= substr($config['horario_fin']   ??'18:00:00',0,5) ?>"
                                   required>
                        </div>

                        <!-- Indicador disponibilidad -->
                        <div id="indicadorDisp" style="display:none;" class="mb-3">
                            <div id="msgDisp" class="alert py-2 mb-0"></div>
                        </div>

                        <!-- WhatsApp -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Tu WhatsApp <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fab fa-whatsapp text-success"></i>
                                </span>
                                <input type="text" class="form-control" name="wa_numero"
                                       placeholder="9999-9999" required
                                       value="<?= htmlspecialchars($_SESSION['cliente']['telefono'] ?? '') ?>">
                            </div>
                            <small class="text-muted">Te confirmaremos por aquí.</small>
                        </div>

                        <!-- Nota -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Nota <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <textarea class="form-control" name="nota" rows="2"
                                      placeholder="Peticiones especiales..."></textarea>
                        </div>

                        <button type="submit" class="btn-rosa w-100 py-2" id="btnAgendar" disabled>
                            <i class="fas fa-calendar-check me-2"></i>Agendar cita
                        </button>

                        <?php if (empty($_SESSION['cliente'])): ?>
                        <p class="text-center mt-2" style="font-size:0.8rem; color:#aaa;">
                            <a href="<?= APP_URL ?>Tienda/login" style="color:#de777d;">Inicia sesión</a>
                            para asociar la cita a tu cuenta.
                        </p>
                        <?php endif; ?>

                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const APP_URL = '<?= APP_URL ?>';

    window.seleccionarDia = function(fecha) {
        document.getElementById('inputFecha').value = fecha;
        const d = new Date(fecha+'T00:00:00');
        const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                       'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        document.getElementById('tituloFormCita').textContent =
            `${d.getDate()} de ${meses[d.getMonth()]} ${d.getFullYear()}`;
        document.getElementById('btnAgendar').disabled = false;

        // Resaltar día seleccionado visualmente
        document.querySelectorAll('.dia-disponible').forEach(el => {
            el.style.border = '1px solid transparent';
        });
        event.currentTarget.style.border = '2px solid #de777d';
    };

    // Auto-llenar duración y precio desde servicio
    document.getElementById('selectServicio').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (opt.value) {
            document.getElementById('inputDuracion').value = opt.dataset.duracion;
            document.getElementById('inputPrecio').value   = opt.dataset.precio;
            verificarDisp();
        }
    });

    // Verificar disponibilidad
    let timer = null;
    function verificarDisp() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const fecha    = document.getElementById('inputFecha').value;
            const hora     = document.getElementById('inputHora').value;
            const duracion = document.getElementById('inputDuracion').value;

            if (!fecha || !hora || !duracion) return;

            const indic = document.getElementById('indicadorDisp');
            const msg   = document.getElementById('msgDisp');
            indic.style.display = '';
            msg.className = 'alert py-2 mb-0 alert-secondary';
            msg.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Verificando...';

            fetch(`${APP_URL}Citas/verificar?fecha=${fecha}&hora=${hora}&duracion=${duracion}&exclude_id=0`)
            .then(r => r.json())
            .then(data => {
                if (data.disponible) {
                    msg.className = 'alert py-2 mb-0 alert-success';
                    msg.innerHTML = `<i class="fas fa-check-circle me-1"></i>${data.message}`;
                } else {
                    msg.className = 'alert py-2 mb-0 alert-danger';
                    msg.innerHTML = `<i class="fas fa-times-circle me-1"></i>${data.message}`;
                }
            });
        }, 500);
    }

    document.getElementById('inputHora').addEventListener('change', verificarDisp);

});
</script>