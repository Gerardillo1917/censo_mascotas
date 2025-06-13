<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Verificar que tenemos el ID de mascota
if (!isset($id_mascota) || $id_mascota <= 0) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Mascota no especificada');
}

// Datos del usuario
$nombre_usuario = $_SESSION['nombre_completo'] ?? 'Usuario no identificado';
$campana_lugar = $_SESSION['campana_lugar'] ?? 'N/A';
$rol_usuario = $_SESSION['rol'] ?? 'Usuario';

$fecha_actual = date('Y-m-d');
$hora_actual = date('H:i');
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/salud/procesamiento/procesar_cirugia.php">
                <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
                
                <h4 class="text-purple mb-3"><i class="fas fa-scalpel me-2"></i> Formulario de Procedimiento Quirúrgico</h4>
                
                <div class="card mb-4 border-purple">
                    <div class="card-header bg-purple text-white">
                        <h5 class="mb-0"><i class="fas fa-asterisk me-2"></i> Campos Obligatorios</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha*</label>
                                <input type="date" class="form-control" name="fecha" value="<?= $fecha_actual ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hora*</label>
                                <input type="time" class="form-control" name="hora" value="<?= $hora_actual ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tipo de procedimiento*</label>
                            <select class="form-select" name="tipo_procedimiento" id="tipo_procedimiento" required>
                                <option value="">Seleccionar...</option>
                                <option value="Esterilización">Esterilización</option>
                                <option value="Castración">Castración</option>
                                <option value="Extracción dental">Extracción dental</option>
                                <option value="Sutura de herida">Sutura de herida</option>
                                <option value="Limpieza dental">Limpieza dental</option>
                                <option value="Extirpación de tumor">Extirpación de tumor</option>
                                <option value="otro">Otro (especificar)</option>
                            </select>
                        </div>
                        
                        <div id="otro_procedimiento_container" class="mb-3" style="display:none;">
                            <label class="form-label">Especificar procedimiento*</label>
                            <input type="text" class="form-control" name="otro_procedimiento">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Diagnóstico previo*</label>
                            <textarea class="form-control" name="diagnostico_previo" rows="2" required placeholder="Describa el diagnóstico que justifica el procedimiento"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Riesgos informados*</label>
                            <textarea class="form-control" name="riesgos_informados" rows="2" required placeholder="Describa los riesgos informados al tutor"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-asterisk me-2"></i> Campos Opcionales</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Medicación preoperatoria</label>
                            <textarea class="form-control" name="medicacion_previa" rows="2" placeholder="Describa la medicación administrada antes del procedimiento"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tipo de anestesia</label>
                            <input type="text" class="form-control" name="tipo_anestesia" placeholder="Ej: General inhalatoria, Local">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Cuidados postoperatorios</label>
                            <textarea class="form-control" name="cuidados_postoperatorios" rows="2" placeholder="Describa los cuidados necesarios después del procedimiento"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" name="observaciones" rows="2" placeholder="Cualquier observación adicional sobre el procedimiento"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-user-md me-2"></i> Responsable</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre*</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($nombre_usuario) ?>" readonly>
                                <input type="hidden" name="responsable" value="<?= htmlspecialchars($nombre_usuario) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Campaña/Lugar*</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($campana_lugar) ?>" readonly>
                                <input type="hidden" name="campana_lugar" value="<?= htmlspecialchars($campana_lugar) ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-purple">
                        <i class="fas fa-save me-1"></i> Guardar Procedimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mostrar/ocultar campo para otro procedimiento
document.getElementById('tipo_procedimiento').addEventListener('change', function() {
    const container = document.getElementById('otro_procedimiento_container');
    container.style.display = this.value === 'otro' ? 'block' : 'none';
    if (this.value !== 'otro') {
        document.querySelector('[name="otro_procedimiento"]').value = '';
    }
});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>