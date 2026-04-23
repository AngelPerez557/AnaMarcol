<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-<?= $servicio->Found ? 'edit' : 'plus-circle' ?> me-2"
                   style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
        </div>
        <a href="<?= APP_URL ?>Servicios/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-concierge-bell me-2"></i>Datos del servicio
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="<?= APP_URL ?>Servicios/save"
                          autocomplete="off">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <?php if ($servicio->Found): ?>
                        <input type="hidden" name="id" value="<?= $servicio->id ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   maxlength="100"
                                   placeholder="Ej: Maquillaje completo, Peinado..."
                                   value="<?= htmlspecialchars($servicio->nombre ?? '') ?>"
                                   required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label fw-semibold">
                                Descripción
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <textarea class="form-control" id="descripcion" name="descripcion"
                                      rows="2" placeholder="Descripción del servicio..."><?= htmlspecialchars($servicio->descripcion ?? '') ?></textarea>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label for="precio_base" class="form-label fw-semibold">
                                    Precio base (L.)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">L.</span>
                                    <input type="number" class="form-control" id="precio_base"
                                           name="precio_base" step="0.01" min="0"
                                           placeholder="0.00"
                                           value="<?= $servicio->precio_base ?? 0 ?>">
                                </div>
                            </div>
                            <div class="col-6">
                                <label for="duracion" class="form-label fw-semibold">
                                    Duración (min)
                                </label>
                                <input type="number" class="form-control" id="duracion"
                                       name="duracion" min="15" step="15"
                                       value="<?= $servicio->duracion ?? 60 ?>">
                                <small class="text-muted">
                                    <?= $servicio->Found ? $servicio->getDuracionFormateada() : '60 min = 1h' ?>
                                </small>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $servicio->Found ? 'Guardar cambios' : 'Crear servicio' ?>
                            </button>
                            <a href="<?= APP_URL ?>Servicios/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>