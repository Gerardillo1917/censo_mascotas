// En la sección de salud, agregar:
<div class="info-box">
    <h5 class="info-box-title"><i class="fas fa-shield-virus me-2"></i>Vacunación Antirrábica</h5>
    <?php
    $stmt_vac = $conn->prepare("SELECT * FROM vacunas 
                              WHERE id_mascota = ? AND nombre_vacuna LIKE '%rabia%' 
                              ORDER BY fecha_aplicacion DESC LIMIT 1");
    $stmt_vac->bind_param("i", $id_mascota);
    $stmt_vac->execute();
    $vacuna_rabia = $stmt_vac->get_result()->fetch_assoc();
    $stmt_vac->close();
    
    if ($vacuna_rabia): ?>
        <div class="info-label">Última vacuna antirrábica</div>
        <div class="info-value">
            <?= htmlspecialchars($vacuna_rabia['nombre_vacuna']) ?> - 
            <?= date('d/m/Y', strtotime($vacuna_rabia['fecha_aplicacion'])) ?>
            <?php if (!empty($vacuna_rabia['comentarios'])): ?>
                <br><small><?= htmlspecialchars($vacuna_rabia['comentarios']) ?></small>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No registra vacuna antirrábica</div>
    <?php endif; ?>
</div>

<div class="info-box">
    <h5 class="info-box-title"><i class="fas fa-bug me-2"></i>Desparasitación</h5>
    <?php if (!empty($mascota['ultima_desparasitacion_fecha'])): ?>
        <div class="info-label">Última desparasitación</div>
        <div class="info-value">
            <?= htmlspecialchars($mascota['ultima_desparasitacion_producto']) ?> - 
            <?= date('d/m/Y', strtotime($mascota['ultima_desparasitacion_fecha'])) ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No registra desparasitación</div>
    <?php endif; ?>
    
    <!-- Botón para registrar nueva desparasitación -->
    <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalDesparasitacion">
        <i class="fas fa-plus me-1"></i> Registrar Desparasitación
    </button>
</div>

<!-- Modal para desparasitación -->
<div class="modal fade" id="modalDesparasitacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="procesar_desparasitacion.php">
                <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Registrar Desparasitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Producto utilizado*</label>
                        <input type="text" class="form-control" name="producto" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha*</label>
                        <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>