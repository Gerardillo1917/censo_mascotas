<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

if (!isset($_GET['id'])) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Registro no especificado');
}

$id_interaccion = intval($_GET['id']);

try {
    // Obtener datos del registro médico
    $stmt = $conn->prepare("SELECT sm.*, m.nombre AS nombre_mascota, 
        CONCAT(t.nombre, ' ', t.apellido_paterno) AS nombre_tutor
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

    // Obtener medicamentos si es una consulta
    $medicamentos = [];
    if ($registro['tipo'] === 'Consulta') {
        $stmt_med = $conn->prepare("SELECT * FROM medicamentos_consulta WHERE id_consulta = ?");
        $stmt_med->bind_param("i", $id_interaccion);
        $stmt_med->execute();
        $medicamentos = $stmt_med->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_med->close();
    }

    $page_title = "Detalles de " . htmlspecialchars($registro['tipo']) . " - " . htmlspecialchars($registro['nombre_mascota']);
    include(BASE_PATH . '/includes/header.php');
} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Error al cargar registro');
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-purple">
            <i class="fas fa-file-medical me-2"></i>
            Detalles de <?= htmlspecialchars($registro['tipo']) ?>
        </h2>
        <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $registro['id_mascota'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>Mascota:</strong> <?= htmlspecialchars($registro['nombre_mascota']) ?>
                    <span class="ms-3">
                        <strong>Tutor:</strong> <?= htmlspecialchars($registro['nombre_tutor']) ?>
                    </span>
                </div>
                <div class="text-muted small">
                    <?= date('d/m/Y H:i', strtotime($registro['fecha'] . ' ' . $registro['hora'])) ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5 class="text-purple">Información Principal</h5>
                    <hr class="mt-1 mb-3">
                    
                    <?php switch ($registro['tipo']):
                        case 'Consulta': ?>
                            <p><strong>Motivo:</strong> <?= htmlspecialchars($registro['motivo']) ?></p>
                            <?php if (!empty($registro['signos_clinicos'])): ?>
                                <p><strong>Signos clínicos:</strong> <?= htmlspecialchars($registro['signos_clinicos']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['diagnostico'])): ?>
                                <p><strong>Diagnóstico:</strong> <?= htmlspecialchars($registro['diagnostico']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['estado_general'])): ?>
                                <p><strong>Estado general:</strong> <?= htmlspecialchars($registro['estado_general']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['estado_hidratacion'])): ?>
                                <p><strong>Estado hidratación:</strong> <?= htmlspecialchars($registro['estado_hidratacion']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['temperatura'])): ?>
                                <p><strong>Temperatura:</strong> <?= htmlspecialchars($registro['temperatura']) ?> °C</p>
                            <?php endif; ?>
                            <?php if (!empty($registro['frecuencia_cardiaca'])): ?>
                                <p><strong>Frecuencia cardíaca:</strong> <?= htmlspecialchars($registro['frecuencia_cardiaca']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['frecuencia_respiratoria'])): ?>
                                <p><strong>Frecuencia respiratoria:</strong> <?= htmlspecialchars($registro['frecuencia_respiratoria']) ?></p>
                            <?php endif; ?>
                            <?php break;
                            
                        case 'Urgencia': ?>
                            <p><strong>Motivo:</strong> <?= htmlspecialchars($registro['motivo']) ?></p>
                            <?php if (!empty($registro['signos_clinicos'])): ?>
                                <p><strong>Signos clínicos:</strong> <?= htmlspecialchars($registro['signos_clinicos']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['primeros_auxilios'])): ?>
                                <p><strong>Primeros auxilios:</strong> <?= htmlspecialchars($registro['primeros_auxilios']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['estado_general'])): ?>
                                <p><strong>Estado general:</strong> <?= htmlspecialchars($registro['estado_general']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['estado_hidratacion'])): ?>
                                <p><strong>Estado hidratación:</strong> <?= htmlspecialchars($registro['estado_hidratacion']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['temperatura'])): ?>
                                <p><strong>Temperatura:</strong> <?= htmlspecialchars($registro['temperatura']) ?> °C</p>
                            <?php endif; ?>
                            <?php if (!empty($registro['frecuencia_cardiaca'])): ?>
                                <p><strong>Frecuencia cardíaca:</strong> <?= htmlspecialchars($registro['frecuencia_cardiaca']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['frecuencia_respiratoria'])): ?>
                                <p><strong>Frecuencia respiratoria:</strong> <?= htmlspecialchars($registro['frecuencia_respiratoria']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['medicacion_previa'])): ?>
                                <p><strong>Medicación:</strong> <?= htmlspecialchars($registro['medicacion_previa']) ?></p>
                            <?php endif; ?>
                            <?php if ($registro['referido_otro_centro']): ?>
                                <p><strong>Referido a otro centro:</strong> Sí</p>
                            <?php endif; ?>
                            <?php break;
                            
                        case 'Cirugía': ?>
                            <p><strong>Procedimiento:</strong> <?= htmlspecialchars($registro['tipo_procedimiento']) ?></p>
                            <?php if (!empty($registro['diagnostico_previo'])): ?>
                                <p><strong>Diagnóstico previo:</strong> <?= htmlspecialchars($registro['diagnostico_previo']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['riesgos_informados'])): ?>
                                <p><strong>Riesgos informados:</strong> <?= htmlspecialchars($registro['riesgos_informados']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['medicacion_previa'])): ?>
                                <p><strong>Medicación preoperatoria:</strong> <?= htmlspecialchars($registro['medicacion_previa']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['tipo_anestesia'])): ?>
                                <p><strong>Tipo de anestesia:</strong> <?= htmlspecialchars($registro['tipo_anestesia']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($registro['cuidados_postoperatorios'])): ?>
                                <p><strong>Cuidados postoperatorios:</strong> <?= htmlspecialchars($registro['cuidados_postoperatorios']) ?></p>
                            <?php endif; ?>
                            <?php break;
                            
                        case 'Vacunación': ?>
                            <p><strong>Vacuna:</strong> <?= htmlspecialchars($registro['nombre_vacuna']) ?></p>
                            <?php break;
                    endswitch; ?>
                </div>
                
                <div class="col-md-6">
                    <h5 class="text-purple">Detalles Adicionales</h5>
                    <hr class="mt-1 mb-3">
                    
                    <?php if (!empty($registro['observaciones'])): ?>
                        <p><strong>Observaciones:</strong></p>
                        <div class="p-3 bg-light rounded mb-3">
                            <?= nl2br(htmlspecialchars($registro['observaciones'])) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($medicamentos)): ?>
                        <p><strong>Medicamentos prescritos:</strong></p>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Días</th>
                                        <th>Frecuencia</th>
                                        <th>Vía</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($medicamentos as $med): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($med['nombre']) ?></td>
                                            <td><?= htmlspecialchars($med['dias']) ?></td>
                                            <td><?= htmlspecialchars($med['frecuencia']) ?></td>
                                            <td><?= htmlspecialchars($med['aplicacion']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <p class="mb-1"><strong>Responsable:</strong> <?= htmlspecialchars($registro['responsable']) ?></p>
                        <p class="mb-1"><strong>Lugar/Campaña:</strong> <?= htmlspecialchars($registro['campana_lugar']) ?></p>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($registro['firma_ruta'])): ?>
                <div class="mt-4 pt-3 border-top">
                    <h5 class="text-purple">Firma Digital</h5>
                    <img src="<?= htmlspecialchars($registro['firma_ruta']) ?>" class="img-fluid" style="max-height: 100px;">
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>