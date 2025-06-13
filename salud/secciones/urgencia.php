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
            <form method="post" action="<?= BASE_URL ?>/salud/procesamiento/procesar_urgencia.php">
                <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
                
                <h4 class="text-danger mb-3"><i class="fas fa-ambulance me-2"></i> Formulario de Urgencia Veterinaria</h4>
                
                <div class="card mb-4 border-danger">
                    <div class="card-header bg-danger text-white">
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
                            <label class="form-label">Motivo de urgencia*</label>
                            <textarea class="form-control" name="motivo" rows="2" required placeholder="Describa el motivo de la urgencia"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Signos clínicos*</label>
                            <textarea class="form-control" name="signos_clinicos" rows="3" required placeholder="Describa los signos clínicos observados"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Primeros auxilios aplicados*</label>
                            <textarea class="form-control" name="primeros_auxilios" rows="2" required placeholder="Describa los primeros auxilios realizados"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-asterisk me-2"></i> Campos Opcionales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado general</label>
                                <input type="text" class="form-control" name="estado_general" placeholder="Ej: Grave, Estable, Crítico">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado de hidratación</label>
                                <input type="text" class="form-control" name="estado_hidratacion" placeholder="Ej: Deshidratación severa">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Temperatura (°C)</label>
                                <input type="number" step="0.1" class="form-control" name="temperatura" placeholder="Ej: 39.8">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Frecuencia cardíaca</label>
                                <input type="text" class="form-control" name="frecuencia_cardiaca" placeholder="Ej: 180 lpm, arrítmico">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Frecuencia respiratoria</label>
                                <input type="text" class="form-control" name="frecuencia_respiratoria" placeholder="Ej: 50 rpm, dificultosa">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Medicación administrada</label>
                            <textarea class="form-control" name="medicacion" rows="2" placeholder="Describa la medicación administrada"></textarea>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="referido_otro_centro" id="referido">
                            <label class="form-check-label" for="referido">Referido a otro centro</label>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" name="observaciones" rows="2" placeholder="Cualquier observación adicional"></textarea>
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
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save me-1"></i> Guardar Urgencia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>