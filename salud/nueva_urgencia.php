<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

    // Validar ID de mascota
    $id_mascota = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id_mascota <= 0) {
        redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Mascota no especificada');
    }

    // Obtener datos básicos de la mascota
    try {
        $stmt = $conn->prepare("SELECT nombre FROM mascotas WHERE id_mascota = ?");
        $stmt->bind_param("i", $id_mascota);
        $stmt->execute();
        $result = $stmt->get_result();
        $mascota = $result->fetch_assoc();
        $stmt->close();

        if (!$mascota) {
            redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Mascota no encontrada');
        }
    } catch (Exception $e) {
        registrar_error($e->getMessage());
        redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Error al cargar datos de mascota');
    }

    $page_title = "Nueva Urgencia - " . htmlspecialchars($mascota['nombre']);
    include(BASE_PATH . '/includes/header.php');

    // Datos del usuario (simulados por ahora)
    $nombre_usuario = $_SESSION['nombre_completo'] ?? 'Usuario no identificado';
    $campana_lugar = $_SESSION['campana_lugar'] ?? 'N/A';
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i', strtotime('-0 hours'));

?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-danger">
            <i class="fas fa-ambulance me-2"></i> Nueva Urgencia
        </h2>
        <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>" class="btn btn-outline-danger">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Urgencia para <?= htmlspecialchars($mascota['nombre']) ?></h5>
        </div>
        
        <div class="card-body">
            <form id="formUrgencia" method="post" action="<?= BASE_URL ?>/salud/procesar_urgencia.php">
                <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
                
<div class="row mb-4">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Fecha*</label>
            <input type="date" class="form-control" name="fecha" value="<?= $fecha_actual ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Hora*</label>
            <input type="time" class="form-control" name="hora" value="<?= $hora_actual ?>" readonly>
        </div>
    </div>
</div>

                
                <div class="mb-4">
                    <h5 class="text-danger mb-3"><i class="fas fa-asterisk me-2"></i>Datos Obligatorios</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Motivo de urgencia*</label>
                        <textarea class="form-control" name="motivo" rows="3" required placeholder="Describa el motivo de la urgencia"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Signos clínicos*</label>
                        <textarea class="form-control" name="signos_clinicos" rows="3" required placeholder="Describa los signos clínicos observados"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Primeros auxilios aplicados*</label>
                        <textarea class="form-control" name="primeros_auxilios" rows="3" required placeholder="Describa los primeros auxilios realizados"></textarea>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h5 class="text-danger mb-3"><i class="fas fa-plus-circle me-2"></i>Datos Opcionales</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estado general</label>
                                <input type="text" class="form-control" name="estado_general" placeholder="Ej: Grave, Estable, Crítico">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estado de hidratación</label>
                                <input type="text" class="form-control" name="estado_hidratacion" placeholder="Ej: Deshidratación severa">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Temperatura (°C)</label>
                                <input type="number" step="0.1" class="form-control" name="temperatura" placeholder="Ej: 39.8">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Frecuencia cardíaca</label>
                                <input type="text" class="form-control" name="frecuencia_cardiaca" placeholder="Ej: 180 lpm, arrítmico">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Frecuencia respiratoria</label>
                                <input type="text" class="form-control" name="frecuencia_respiratoria" placeholder="Ej: 50 rpm, dificultosa">
                            </div>
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
                
                <div class="card mb-4 border-light">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Responsable</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nombre*</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($nombre_usuario) ?>" readonly>
                                    <input type="hidden" class="form-control" name="responsable" value="<?= htmlspecialchars($nombre_usuario) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Campaña/Lugar*</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($campana_lugar) ?>" readonly>
                                    <input type="hidden" name="campana_lugar" value="<?= htmlspecialchars($campana_lugar) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>" class="btn btn-outline-purple">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" name="guardar_sin_firmar" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                    <button type="submit" name="guardar_y_firmar" class="btn btn-primary">
                        <i class="fas fa-signature me-1"></i> Guardar y Firmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('formUrgencia').addEventListener('submit', function(e) {
    if (e.submitter && e.submitter.name === 'guardar_y_firmar') {
        this.action = "<?= BASE_URL ?>/salud/procesar_urgencia.php?firmar=true";
    } else {
        this.action = "<?= BASE_URL ?>/salud/procesar_urgencia.php";
    }
});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>