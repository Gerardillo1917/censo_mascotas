<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
// Obtener todas las vacunas de la mascota
try {
    $stmt_vac = $conn->prepare("SELECT * FROM vacunas WHERE id_mascota = ? ORDER BY fecha_aplicacion DESC");
    $stmt_vac->bind_param("i", $id_mascota);
    $stmt_vac->execute();
    $vacunas = $stmt_vac->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_vac->close();
} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje(
        "/salud/salud_animal.php?id=$id_mascota",
        'danger',
        'Error al cargar las vacunas'
    );
}

$fecha_actual = date('Y-m-d');
$hora_actual = date('H:i');
?>

<div class="row">
    <!-- Formulario de vacunación -->
    <div class="col-md-6">
        <div class="card mb-4 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-syringe me-2"></i>Registrar Nueva Vacuna</h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?= BASE_URL ?>/salud/procesamiento/procesar_vacunacion.php">
                    <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Fecha*</label>
                        <input type="date" class="form-control" name="fecha" value="<?= $fecha_actual ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipo de vacuna*</label>
                        <select class="form-select" name="tipo_vacuna" required>
                            <option value="">Seleccionar...</option>
                            <option value="Rabia">Rabia</option>
                            <option value="Moquillo">Moquillo</option>
                            <option value="Parvovirus">Parvovirus</option>
                            <option value="Leptospirosis">Leptospirosis</option>
                            <option value="Polivalente">Polivalente</option>
                            <option value="otro">Otra (especificar)</option>
                        </select>
                    </div>
                    
                    <div id="otra_vacuna_container" class="mb-3" style="display:none;">
                        <label class="form-label">Nombre de la vacuna*</label>
                        <input type="text" class="form-control" name="otra_vacuna">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="notas" rows="2"></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Guardar Vacuna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Listado de vacunas existentes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Vacunas Registradas</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($vacunas)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Vacuna</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vacunas as $vacuna): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($vacuna['nombre_vacuna']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($vacuna['fecha_aplicacion'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detalleVacunaModal"
                                                    onclick="mostrarDetalleVacuna(
                                                        '<?= htmlspecialchars($vacuna['nombre_vacuna']) ?>',
                                                        '<?= date('d/m/Y', strtotime($vacuna['fecha_aplicacion'])) ?>',
                                                        '<?= htmlspecialchars($vacuna['comentarios'] ?? '') ?>'
                                                    )">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>No hay vacunas registradas
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles de vacuna -->
<div class="modal fade" id="detalleVacunaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Detalles de Vacuna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre:</label>
                    <p id="modal-vacuna-nombre" class="fw-bold"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha:</label>
                    <p id="modal-vacuna-fecha"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones:</label>
                    <p id="modal-vacuna-observaciones" class="text-muted"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar/ocultar campo para otra vacuna
document.querySelector('[name="tipo_vacuna"]').addEventListener('change', function() {
    const container = document.getElementById('otra_vacuna_container');
    container.style.display = this.value === 'otro' ? 'block' : 'none';
    if (this.value !== 'otro') {
        document.querySelector('[name="otra_vacuna"]').value = '';
    }
});

// Función para mostrar detalles en el modal
function mostrarDetalleVacuna(nombre, fecha, observaciones) {
    document.getElementById('modal-vacuna-nombre').textContent = nombre;
    document.getElementById('modal-vacuna-fecha').textContent = fecha;
    document.getElementById('modal-vacuna-observaciones').textContent = 
        observaciones || 'No hay observaciones registradas';
}
</script>