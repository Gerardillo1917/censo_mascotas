<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/includes/auth.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

//verificarAutenticacion();

if (!isset($_GET['id'])) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Registro no especificado');
}

$id_interaccion = intval($_GET['id']);

// Obtener datos del registro médico
try {
    $stmt = $conn->prepare("SELECT sm.*, m.nombre AS nombre_mascota, 
                           CONCAT(t.nombre, ' ', t.apellido_paterno, ' ', IFNULL(t.apellido_materno, '')) AS tutor_completo
                           FROM salud_mascotas sm
                           JOIN mascotas m ON sm.id_mascota = m.id_mascota
                           JOIN tutores t ON m.id_tutor = t.id_tutor
                           WHERE sm.id_interaccion = ?");
    $stmt->bind_param("i", $id_interaccion);
    $stmt->execute();
    $registro = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$registro) {
        redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Registro no encontrado');
    }
} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Error al cargar el registro');
}

$page_title = "Detalle de " . htmlspecialchars($registro['tipo']) . " - " . htmlspecialchars($registro['nombre_mascota']);
include(BASE_PATH . '/includes/header.php');
?>

<style>
    .bg-purple { background-color: #8b0180; }
    .text-purple { color: #8b0180; }
    .border-purple { border-color: #8b0180 !important; }
    .btn-purple { 
        background-color: #8b0180; 
        color: white;
    }
    .btn-purple:hover {
        background-color: #6a015f;
        color: white;
    }
    .detalle-container {
        border-left: 4px solid #8b0180;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 5px;
    }
    .detalle-header {
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    .detalle-item {
        margin-bottom: 15px;
    }
    .detalle-label {
        font-weight: bold;
        color: #495057;
    }
    .firma-container {
        margin-top: 30px;
        text-align: center;
    }
    .firma-img {
        max-width: 300px;
        max-height: 150px;
        border: 1px solid #dee2e6;
    }
</style>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-purple text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Detalle de <?= htmlspecialchars(ucfirst($registro['tipo'])) ?></h4>
            <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $registro['id_mascota'] ?>&seccion=historial" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
        <div class="card-body">
            <div class="detalle-container">
                <div class="detalle-header">
                    <h5 class="text-purple"><?= htmlspecialchars($registro['nombre_mascota']) ?></h5>
                    <p class="text-muted"><i class="far fa-calendar-alt me-1"></i><?= date('d/m/Y H:i', strtotime($registro['fecha'])) ?></p>
                    <p><strong>Responsable:</strong> <?= htmlspecialchars($registro['responsable']) ?></p>
                </div>
                
                <div class="detalle-item">
                    <div class="detalle-label">Tutor:</div>
                    <p><?= htmlspecialchars($registro['tutor_completo']) ?></p>
                </div>
                
                <?php if ($registro['tipo'] == 'Consulta'): ?>
                    <div class="detalle-item">
                        <div class="detalle-label">Motivo:</div>
                        <p><?= htmlspecialchars($registro['motivo']) ?></p>
                    </div>
                    
                    <?php if (!empty($registro['signos_clinicos'])): ?>
                        <div class="detalle-item">
                            <div class="detalle-label">Signos clínicos:</div>
                            <p><?= htmlspecialchars($registro['signos_clinicos']) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($registro['estado_general'])): ?>
                        <div class="detalle-item">
                            <div class="detalle-label">Estado general:</div>
                            <p><?= htmlspecialchars($registro['estado_general']) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($registro['temperatura'])): ?>
                        <div class="detalle-item">
                            <div class="detalle-label">Temperatura:</div>
                            <p><?= htmlspecialchars($registro['temperatura']) ?> °C</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($registro['recomendaciones'])): ?>
                        <div class="detalle-item">
                            <div class="detalle-label">Recomendaciones:</div>
                            <p><?= htmlspecialchars($registro['recomendaciones']) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($registro['medicamento_nombre'])): ?>
                        <div class="detalle-item">
                            <div class="detalle-label">Medicación:</div>
                            <div class="card">
                                <div class="card-body">
                                    <p><strong>Nombre:</strong> <?= htmlspecialchars($registro['medicamento_nombre']) ?></p>
                                    <div class="row">
                                        <div class="col-md-4"><strong>Días:</strong> <?= htmlspecialchars($registro['medicamento_dias']) ?></div>
                                        <div class="col-md-4"><strong>Frecuencia:</strong> <?= htmlspecialchars($registro['medicamento_frecuencia']) ?></div>
                                        <div class="col-md-4"><strong>Aplicación:</strong> <?= htmlspecialchars($registro['medicamento_aplicacion']) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                
                <?php elseif ($registro['tipo'] == 'Vacunación'): ?>
                    <div class="detalle-item">
                        <div class="detalle-label">Vacuna:</div>
                        <p><?= htmlspecialchars($registro['tipo_vacuna']) ?></p>
                    </div>
                
                <?php elseif ($registro['tipo'] == 'Urgencia'): ?>
                    <div class="detalle-item">
                        <div class="detalle-label">Motivo:</div>
                        <p><?= htmlspecialchars($registro['motivo']) ?></p>
                    </div>
                    
                    <?php if (!empty($registro['primeros_auxilios'])): ?>
                        <div class="detalle-item">
                            <div class="detalle-label">Primeros auxilios:</div>
                            <p><?= htmlspecialchars($registro['primeros_auxilios']) ?></p>
                        </div>
                    <?php endif; ?>
                
                <?php elseif ($registro['tipo'] == 'Procedimiento'): ?>
                    <div class="detalle-item">
                        <div class="detalle-label">Tipo de procedimiento:</div>
                        <p><?= htmlspecialchars($registro['tipo_procedimiento']) ?></p>
                    </div>
                    
                    <?php if (!empty($registro['diagnostico'])): ?>
                        <div class="detalle-item">
                            <div class="detalle-label">Diagnóstico:</div>
                            <p><?= htmlspecialchars($registro['diagnostico']) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($registro['medicacion_previa'])): ?>
                        <div class="detalle-item">
                            <div class="detalle-label">Medicación previa:</div>
                            <p><?= htmlspecialchars($registro['medicacion_previa']) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($registro['medicacion_postoperatoria'])): ?>
                        <div class="detalle-item">
                            <div class="detalle-label">Medicación postoperatoria:</div>
                            <p><?= htmlspecialchars($registro['medicacion_postoperatoria']) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($registro['peso'])): ?>
                        <div class="detalle-item">
                            <div class="detalle-label">Peso:</div>
                            <p><?= htmlspecialchars($registro['peso']) ?> kg</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if (!empty($registro['notas'])): ?>
                    <div class="detalle-item">
                        <div class="detalle-label">Notas adicionales:</div>
                        <p><?= htmlspecialchars($registro['notas']) ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($registro['firma_ruta'])): ?>
                    <div class="firma-container">
                        <div class="detalle-label">Firma digital:</div>
                        <img src="<?= htmlspecialchars($registro['firma_ruta']) ?>" class="firma-img">
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="mt-4 text-center">
                <button onclick="window.print()" class="btn btn-purple me-2">
                    <i class="fas fa-print me-1"></i> Imprimir
                </button>
                <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $registro['id_mascota'] ?>&seccion=historial" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver al historial
                </a>
            </div>
        </div>
    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>