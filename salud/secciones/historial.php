<?php
// Obtener historial médico completo
$stmt = $conn->prepare("SELECT * FROM salud_mascotas WHERE id_mascota = ? ORDER BY fecha DESC, hora DESC");
$stmt->bind_param("i", $id_mascota);
$stmt->execute();
$historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener vacunas por separado (para mostrar en sección especial)
$stmt_vac = $conn->prepare("SELECT * FROM vacunas WHERE id_mascota = ? ORDER BY fecha_aplicacion DESC");
$stmt_vac->bind_param("i", $id_mascota);
$stmt_vac->execute();
$vacunas = $stmt_vac->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_vac->close();
?>

<div class="mb-4">
    <h4 class="text-purple mb-3"><i class="fas fa-history me-2"></i> Historial Médico Completo</h4>
    
    <?php if (empty($historial) && empty($vacunas)): ?>
        <div class="alert alert-info">
            <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Expediente vacío</h5>
            <p class="mb-0">No se han encontrado registros médicos para esta mascota.</p>
        </div>
    <?php else: ?>
        <div class="accordion" id="historialAccordion">
            <!-- Sección de Vacunas -->
            <?php if (!empty($vacunas)): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingVacunas">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVacunas" aria-expanded="true" aria-controls="collapseVacunas">
                            <i class="fas fa-syringe me-2"></i> Vacunas (<?= count($vacunas) ?>)
                        </button>
                    </h2>
                    <div id="collapseVacunas" class="accordion-collapse collapse show" aria-labelledby="headingVacunas" data-bs-parent="#historialAccordion">
                        <div class="accordion-body">
                            <div class="list-group">
                                <?php foreach ($vacunas as $vacuna): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= htmlspecialchars($vacuna['nombre_vacuna']) ?></strong>
                                                <div class="small text-muted">
                                                    <?= date('d/m/Y', strtotime($vacuna['fecha_aplicacion'])) ?>
                                                    <?php if (!empty($vacuna['comentarios'])): ?>
                                                        - <?= htmlspecialchars($vacuna['comentarios']) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div>
                                                <small class="text-muted">
                                                    Registrada por: <?= htmlspecialchars($vacuna['responsable']) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Sección de Otros Registros -->
            <?php if (!empty($historial)): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingRegistros">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRegistros" aria-expanded="false" aria-controls="collapseRegistros">
                            <i class="fas fa-clipboard-list me-2"></i> Otros Registros (<?= count($historial) ?>)
                        </button>
                    </h2>
                    <div id="collapseRegistros" class="accordion-collapse collapse" aria-labelledby="headingRegistros" data-bs-parent="#historialAccordion">
                        <div class="accordion-body">
                            <?php foreach ($historial as $registro): ?>
                                <div class="card mb-3">
                                    <div class="card-header bg-light d-flex justify-content-between">
                                        <div>
                                            <strong><?= htmlspecialchars(ucfirst($registro['tipo'])) ?></strong>
                                            <span class="ms-2 small text-muted">
                                                <?= date('d/m/Y H:i', strtotime($registro['fecha'] . ' ' . $registro['hora'])) ?>
                                            </span>
                                        </div>
                                        <div>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($registro['responsable']) ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <?php switch ($registro['tipo']):
                                            case 'Consulta': ?>
                                                <p><strong>Motivo:</strong> <?= htmlspecialchars($registro['motivo']) ?></p>
                                                <?php if (!empty($registro['signos_clinicos'])): ?>
                                                    <p><strong>Signos clínicos:</strong> <?= htmlspecialchars($registro['signos_clinicos']) ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($registro['diagnostico'])): ?>
                                                    <p><strong>Diagnóstico:</strong> <?= htmlspecialchars($registro['diagnostico']) ?></p>
                                                <?php endif; ?>
                                                <?php break;
                                                
                                            case 'Urgencia': ?>
                                                <p><strong>Motivo:</strong> <?= htmlspecialchars($registro['motivo']) ?></p>
                                                <?php if (!empty($registro['primeros_auxilios'])): ?>
                                                    <p><strong>Primeros auxilios:</strong> <?= htmlspecialchars($registro['primeros_auxilios']) ?></p>
                                                <?php endif; ?>
                                                <?php break;
                                                
                                            case 'Cirugía': ?>
                                                <p><strong>Procedimiento:</strong> <?= htmlspecialchars($registro['tipo_procedimiento']) ?></p>
                                                <?php if (!empty($registro['diagnostico_previo'])): ?>
                                                    <p><strong>Diagnóstico previo:</strong> <?= htmlspecialchars($registro['diagnostico_previo']) ?></p>
                                                <?php endif; ?>
                                                <?php break;
                                                
                                            case 'Vacunación': ?>
                                                <p><strong>Vacuna:</strong> <?= htmlspecialchars($registro['nombre_vacuna']) ?></p>
                                                <?php break;
                                        endswitch; ?>
                                        
                                        <?php if (!empty($registro['observaciones'])): ?>
                                            <div class="mt-2 p-2 bg-light rounded">
                                                <strong>Observaciones:</strong> <?= htmlspecialchars($registro['observaciones']) ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="mt-2 text-end">
                                            <a href="<?= BASE_URL ?>/salud/detalle_consulta.php?id=<?= $registro['id_interaccion'] ?>" class="btn btn-sm btn-outline-purple">
                                                Ver detalles completos
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>