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

$page_title = "Nueva Consulta - " . htmlspecialchars($mascota['nombre']);
include(BASE_PATH . '/includes/header.php');

// Datos del usuario (simulados por ahora)
$nombre_usuario = $_SESSION['nombre_completo'] ?? 'Usuario no identificado';
$campana_lugar = $_SESSION['campana_lugar'] ?? 'N/A';
$fecha_actual = date('Y-m-d');
$hora_actual = date('H:i', strtotime('-0 hours'));
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-purple">
            <i class="fas fa-stethoscope me-2"></i> Nueva Consulta
        </h2>
        <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>" class="btn btn-outline-purple">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm card-purple-border">
        <div class="card-header bg-purple text-white">
            <h5 class="mb-0">Consulta para <?= htmlspecialchars($mascota['nombre']) ?></h5>
        </div>
        
        <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/salud/procesar_consulta.php">
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
                    <h5 class="text-purple mb-3"><i class="fas fa-asterisk text-danger me-2"></i>Datos Obligatorios</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Motivo de consulta*</label>
                        <textarea class="form-control" name="motivo" rows="3" required placeholder="Describa el motivo principal de la consulta"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Signos clínicos*</label>
                        <textarea class="form-control" name="signos_clinicos" rows="3" required placeholder="Describa los signos clínicos observados"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Diagnóstico*</label>
                        <textarea class="form-control" name="diagnostico" rows="3" required placeholder="Describa el diagnóstico preliminar o definitivo"></textarea>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h5 class="text-purple mb-3"><i class="fas fa-plus-circle text-secondary me-2"></i>Datos Opcionales</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estado general</label>
                                <input type="text" class="form-control" name="estado_general" placeholder="Ej: Regular, Bueno, Malo">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estado de hidratación</label>
                                <input type="text" class="form-control" name="estado_hidratacion" placeholder="Ej: Normal, Deshidratación leve">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Temperatura (°C)</label>
                                <input type="number" step="0.1" class="form-control" name="temperatura" placeholder="Ej: 38.5">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Frecuencia cardíaca</label>
                                <input type="text" class="form-control" name="frecuencia_cardiaca" placeholder="Ej: 120 lpm">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Frecuencia respiratoria</label>
                                <input type="text" class="form-control" name="frecuencia_respiratoria" placeholder="Ej: 30 rpm">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Medicación</label>
                        <textarea class="form-control" name="medicacion" rows="2" placeholder="Describa la medicación recetada"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Vía de administración</label>
                        <input type="text" class="form-control" name="via_administracion" placeholder="Ej: Oral, Intravenosa, Subcutánea">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="2" placeholder="Cualquier observación adicional"></textarea>
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
    <button type="submit" class="btn btn-purple">
        <i class="fas fa-save me-1"></i> Guardar Consulta
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
        /* Tarjeta RESPONSABLE: borde y header morado, texto blanco */
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
<?php include(BASE_PATH . '/includes/footer.php'); ?>   