<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

// Validar ID de mascota
$id_mascota = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_mascota <= 0) {
    redirigir_con_mensaje('buscar.php', 'danger', 'Mascota no especificada');
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
        redirigir_con_mensaje('buscar.php', 'danger', 'Mascota no encontrada');
    }
} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje('buscar.php', 'danger', 'Error al cargar datos de mascota');
}

$page_title = "Nueva Cirugía - " . htmlspecialchars($mascota['nombre']);
include(BASE_PATH . '/includes/header.php');

// Datos del usuario
$nombre_usuario = $_SESSION['nombre_completo'] ?? 'Usuario no identificado';
$campana_lugar = $_SESSION['campana_lugar'] ?? 'N/A';
$fecha_actual = date('Y-m-d');
$hora_actual = date('H:i', strtotime('-0 hours'));

// Restaurar datos del formulario si existen en $_SESSION
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Limpiar después de usar
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-purple">
            <i class="fas fa-cut me-2"></i> Nueva Cirugía
        </h2>
        <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>" class="btn btn-outline-purple">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-purple text-white">
            <h5 class="mb-0">Cirugía para <?= htmlspecialchars($mascota['nombre']) ?></h5>
        </div>
        
        <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/salud/procesar_cirugia.php" id="cirugiaForm">
                <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
                <input type="hidden" name="id_interaccion" id="id_interaccion" value="">
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Fecha*</label>
                            <input type="date" class="form-control" name="fecha" value="<?= htmlspecialchars($form_data['fecha'] ?? $fecha_actual) ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Hora*</label>
                            <input type="time" class="form-control" name="hora" value="<?= htmlspecialchars($form_data['hora'] ?? $hora_actual) ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="text-purple mb-3"><i class="fas fa-asterisk text-danger me-2"></i>Datos Obligatorios</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipo de procedimiento*</label>
                        <select class="form-select" name="tipo_procedimiento" id="tipo_procedimiento" required>
                            <option value="">Seleccionar...</option>
                            <option value="Esterilización" <?= isset($form_data['tipo_procedimiento']) && $form_data['tipo_procedimiento'] === 'Esterilización' ? 'selected' : '' ?>>Esterilización</option>
                            <option value="Castración" <?= isset($form_data['tipo_procedimiento']) && $form_data['tipo_procedimiento'] === 'Castración' ? 'selected' : '' ?>>Castración</option>
                            <option value="Extracción dental" <?= isset($form_data['tipo_procedimiento']) && $form_data['tipo_procedimiento'] === 'Extracción dental' ? 'selected' : '' ?>>Extracción dental</option>
                            <option value="Sutura de herida" <?= isset($form_data['tipo_procedimiento']) && $form_data['tipo_procedimiento'] === 'Sutura de herida' ? 'selected' : '' ?>>Sutura de herida</option>
                            <option value="Limpieza dental" <?= isset($form_data['tipo_procedimiento']) && $form_data['tipo_procedimiento'] === 'Limpieza dental' ? 'selected' : '' ?>>Limpieza dental</option>
                            <option value="Extirpación de tumor" <?= isset($form_data['tipo_procedimiento']) && $form_data['tipo_procedimiento'] === 'Extirpación de tumor' ? 'selected' : '' ?>>Extirpación de tumor</option>
                            <option value="otro" <?= isset($form_data['tipo_procedimiento']) && $form_data['tipo_procedimiento'] === 'otro' ? 'selected' : '' ?>>Otro (especificar)</option>
                        </select>
                    </div>
                    
                    <div id="otro_procedimiento_container" class="mb-3" style="display:<?= isset($form_data['tipo_procedimiento']) && $form_data['tipo_procedimiento'] === 'otro' ? 'block' : 'none' ?>;">
                        <label class="form-label">Especificar procedimiento*</label>
                        <input type="text" class="form-control" name="otro_procedimiento" value="<?= htmlspecialchars($form_data['otro_procedimiento'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Diagnóstico previo*</label>
                        <textarea class="form-control" name="diagnostico_previo" rows="3" required placeholder="Describa el diagnóstico que justifica el procedimiento"><?= htmlspecialchars($form_data['diagnostico_previo'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Riesgos informados*</label>
                        <textarea class="form-control" name="riesgos_informados" rows="3" required placeholder="Describa los riesgos informados al tutor"><?= htmlspecialchars($form_data['riesgos_informados'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h5 class="text-purple mb-3"><i class="fas fa-plus-circle text-secondary me-2"></i>Datos Opcionales</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Medicación preoperatoria</label>
                        <textarea class="form-control" name="medicacion_previa" rows="2" placeholder="Describa la medicación administrada antes del procedimiento"><?= htmlspecialchars($form_data['medicacion_previa'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipo de anestesia</label>
                        <input type="text" class="form-control" name="tipo_anestesia" placeholder="Ej: General inhalatoria, Local" value="<?= htmlspecialchars($form_data['tipo_anestesia'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Cuidados postoperatorios</label>
                        <textarea class="form-control" name="cuidados_postoperatorios" rows="3" placeholder="Describa los cuidados necesarios después del procedimiento"><?= htmlspecialchars($form_data['cuidados_postoperatorios'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="2" placeholder="Cualquier observación adicional sobre el procedimiento"><?= htmlspecialchars($form_data['observaciones'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="card mb-4 card-responsable">
                    <div class="card-header">
                        <h5 class="mb-0">Responsable</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-purple">Nombre*</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($nombre_usuario) ?>" readonly>
                                    <input type="hidden" name="responsable" value="<?= htmlspecialchars($nombre_usuario) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-purple">Campaña/Lugar*</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($campana_lugar) ?>" readonly>
                                    <input type="hidden" name="campana_lugar" value="<?= htmlspecialchars($campana_lugar) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>" class="btn btn-cancelar">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" name="guardar_sin_firmar" class="btn btn-outline-purple">
                        <i class="fas fa-save me-1"></i> Guardar sin firmar
                    </button>
                    <button type="submit" name="guardar_y_firmar" class="btn btn-purple">
                        <i class="fas fa-signature me-1"></i> Guardar y Firmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    :root {
        --purple-primary: #8b0180;
        --purple-dark: #6a0160;
        --purple-light: #f3e0f5;
        --purple-light-hover: #e9cceb;
        --purple-light-border: #e6c7eb;
        --purple-text-dark: #500147;
        --red-cancel: #dc3545;
        --white-text: #ffffff;
        --border-color: #dee2e6;
    }

    .card-header h5 {
        color: white !important;
    }
    .text-purple {
        color: #8b0180;
    }

    .btn-purple {
        background: linear-gradient(135deg, #8b0180, #b83eb7);
        border: none;
        color: white;
        font-weight: bold;
    }

    .btn-purple:hover {
        background: linear-gradient(135deg, #6a005f, #a5279f);
    }

    .btn-outline-purple {
        border: 2px solid #8b0180;
        color: #8b0180;
        font-weight: 500;
        background-color: transparent;
    }

    .btn-outline-purple:hover {
        background: #8b0180;
        color: white;
    }

    .card-header.bg-purple {
        background: linear-gradient(to right, #8b0180, #b83eb7);
        color: white;
        font-weight: bold;
    }

    .btn-cancelar {
        background-color: #dc3545;
        color: white;
        border: none;
        font-weight: bold;
    }

    .btn-cancelar:hover {
        background-color: #b02a37;
    }

    .card.shadow-sm {
        border: 1px solid #e0cce5;
        box-shadow: 0 0 10px rgba(139, 1, 128, 0.15);
    }

    .form-label {
        font-weight: 500;
        color: #5e005a;
    }

    .card.card-responsable {
        border: 2px solid var(--purple-primary);
        border-radius: 0.5rem;
    }

    .card.card-responsable .card-header {
        background-color: var(--purple-primary);
        color: var(--white-text);
        border-bottom: 1px solid var(--purple-primary);
    }

    .card.card-responsable .card-header h5 {
        color: var(--white-text);
        margin-bottom: 0;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar cambio en tipo de procedimiento
    document.getElementById('tipo_procedimiento').addEventListener('change', function() {
        const container = document.getElementById('otro_procedimiento_container');
        container.style.display = this.value === 'otro' ? 'block' : 'none';
        if (this.value !== 'otro') {
            document.querySelector('[name="otro_procedimiento"]').value = '';
        }
    });

    // Manejar submit del formulario
    document.getElementById('cirugiaForm').addEventListener('submit', function(e) {
        if (e.submitter && e.submitter.name === 'guardar_y_firmar') {
            this.action = "<?= BASE_URL ?>/salud/procesar_cirugia.php?firmar=1";
        } else {
            this.action = "<?= BASE_URL ?>/salud/procesar_cirugia.php";
        }
    });
});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>