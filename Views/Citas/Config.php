<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-cog me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">Define los horarios de atención y la capacidad de citas.</small>
        </div>
        <a href="<?= APP_URL ?>Citas/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <form method="POST" action="<?= APP_URL ?>Citas/saveConfigCitas" autocomplete="off">

                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <!-- Horario de atención -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-clock me-2"></i>Horario de atención
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label for="horario_inicio" class="form-label fw-semibold">
                                    Apertura
                                </label>
                                <input type="time"
                                       class="form-control"
                                       id="horario_inicio"
                                       name="horario_inicio"
                                       value="<?= substr($config['horario_inicio'] ?? '08:00:00', 0, 5) ?>">
                            </div>
                            <div class="col-6">
                                <label for="horario_fin" class="form-label fw-semibold">
                                    Cierre
                                </label>
                                <input type="time"
                                       class="form-control"
                                       id="horario_fin"
                                       name="horario_fin"
                                       value="<?= substr($config['horario_fin'] ?? '18:00:00', 0, 5) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Días laborales -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-calendar-week me-2"></i>Días de atención
                    </div>
                    <div class="card-body">
                        <?php
                        $diasConfig    = explode(',', $config['dias_laborales'] ?? '1,2,3,4,5,6');
                        $nombresDias   = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
                        ?>
                        <div class="row g-2">
                            <?php for ($i = 0; $i <= 6; $i++): ?>
                            <div class="col-6 col-sm-4 col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="dias[]"
                                           value="<?= $i ?>"
                                           id="dia-<?= $i ?>"
                                           <?= in_array((string)$i, $diasConfig) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="dia-<?= $i ?>">
                                        <?= $nombresDias[$i] ?>
                                    </label>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Duración y capacidad -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-sliders-h me-2"></i>Configuración de citas
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label for="duracion_default" class="form-label fw-semibold">
                                    Duración por defecto (min)
                                </label>
                                <input type="number"
                                       class="form-control"
                                       id="duracion_default"
                                       name="duracion_default"
                                       min="15"
                                       step="15"
                                       value="<?= $config['duracion_default'] ?? 60 ?>">
                                <small class="text-muted">Se usa si el servicio no tiene duración propia.</small>
                            </div>
                            <div class="col-6">
                                <label for="capacidad_simultanea" class="form-label fw-semibold">
                                    Citas simultáneas
                                </label>
                                <input type="number"
                                       class="form-control"
                                       id="capacidad_simultanea"
                                       name="capacidad_simultanea"
                                       min="1"
                                       max="10"
                                       value="<?= $config['capacidad_simultanea'] ?? 1 ?>">
                                <small class="text-muted">
                                    1 = Solo.  2 = Ayudantes. Etc.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-save me-2"></i>Guardar configuración
                    </button>
                    <a href="<?= APP_URL ?>Citas/index" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>

</div>