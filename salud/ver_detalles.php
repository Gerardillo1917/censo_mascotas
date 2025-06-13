<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

// Validar ID de interacción
$id_interaccion = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_interaccion <= 0) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Registro no especificado');
}

// Obtener datos de la interacción
try {
    $stmt = $conn->prepare("SELECT sm.*, m.nombre AS nombre_mascota, m.id_mascota 
                          FROM salud_mascotas sm
                          JOIN mascotas m ON sm.id_mascota = m.id_mascota
                          WHERE sm.id_interaccion = ?");
    $stmt->bind_param("i", $id_interaccion);
    $stmt->execute();
    $registro = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$registro) {
        redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Registro no encontrado');
    }

    // Determinar el tipo de registro para el título
    $tipo_registro = '';
    switch($registro['tipo']) {
        case 'Consulta':
            $tipo_registro = 'Consulta Médica';
            break;
        case 'Urgencia':
            $tipo_registro = 'Urgencia Veterinaria';
            break;
        case 'Procedimiento':
            $tipo_registro = 'Procedimiento Quirúrgico';
            break;
        default:
            $tipo_registro = 'Registro Médico';
    }

    $page_title = "$tipo_registro - " . htmlspecialchars($registro['nombre_mascota']);
    include(BASE_PATH . '/includes/header.php');

} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Error al cargar datos');
}
?>

<style>
    :root {
        --color-primario: #8b0180;
        --color-secundario: #6a015f;
        --color-terciario: #f8f1f8;
    }

    .bg-purple {
        background-color: var(--color-primario);
    }

    .text-purple {
        color: #8b0180;
    }

    .border-purple {
        border-color: var(--color-primario) !important;
    }

    .btn-purple {
        background-color: var(--color-primario);
        color: white;
    }

    .btn-purple:hover {
        background-color: var(--color-secundario);
        color: white;
    }

    .btn-outline-purple {
        border-color: #8b0180;
        color: #8b0180;
    }

    .btn-outline-purple:hover {
        background-color: var(--color-primario);
        color: white;
    }

    .card-header {
        font-weight: 600;
        background: linear-gradient(to right, #8b0180, #b83eb7);
        color: white;
    }

    .info-label {
        font-weight: 500;
        color: #6c757d;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }

    .info-value {
        font-size: 1rem;
        margin-bottom: 1rem;
        padding: 0.5rem;
        background-color: #f8f9fa;
        border-radius: 4px;
        border-left: 3px solid var(--color-primario);
    }

    .firma-container {
        border: 1px dashed #ccc;
        padding: 1rem;
        margin-top: 1rem;
        text-align: center;
    }

    .firma-img {
        max-width: 300px;
        max-height: 100px;
        margin-bottom: 1rem;
    }

    .section-title {
        background: linear-gradient(to right, #8b0180, #b83eb7);
        color: white;
        border-bottom: 2px solid var(--color-terciario);
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
        font-weight: 600;
    }

    .badge-consulta {
        background-color: #0d6efd;
        color: white;
    }

    .badge-urgencia {
        background-color: #dc3545;
        color: white;
    }

    .badge-cirugia {
        background-color: var(--color-primario);
        color: white;
    }
    .bg-degradado-morado {
    background: linear-gradient(to right, #8b0180, #b83eb7);
    color: white !important;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    display: inline-block;
}
.bg-degradado-morado {
    background: linear-gradient(to right, #8b0180, #b83eb7);
    color: white !important;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.text-purple {
    color: #8b0180 !important;
}

</style>


<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-clipboard me-2"></i> <?= $tipo_registro ?>
        </h2>
        <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $registro['id_mascota'] ?>" class="btn btn-outline-purple">
            <i class="fas fa-arrow-left me-1"></i> Volver al historial
        </a>
    </div>

    <!-- Tarjeta de información básica -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span>Información básica</span>
            <span class="badge <?= $registro['tipo'] == 'Consulta' ? 'badge-consulta' : 
                               ($registro['tipo'] == 'Urgencia' ? 'badge-urgencia' : 'badge-cirugia') ?>">
                <?= $registro['tipo'] ?>
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="info-label">Mascota</div>
                    <div class="info-value"><?= htmlspecialchars($registro['nombre_mascota']) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Fecha</div>
                    <div class="info-value"><?= date('d/m/Y', strtotime($registro['fecha'])) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Hora</div>
                    <div class="info-value"><?= date('H:i', strtotime($registro['hora'])) ?></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="info-label">Responsable</div>
                    <div class="info-value"><?= htmlspecialchars($registro['responsable']) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="info-label">Campaña/Lugar</div>
                    <div class="info-value"><?= htmlspecialchars($registro['campana_lugar']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($registro['tipo'] == 'Consulta'): ?>
        <!-- Detalles específicos para Consulta -->
        <div class="card shadow-sm mb-4">
    <div class="card-header bg-light text-purple">
        Detalles de la consulta
    </div>
    <div class="card-body">
        <div class="mb-4 ps-3">
            <h5>
                <i class="fas fa-asterisk text-danger me-2"></i>
                Datos Obligatorios
            </h5>
        </div>

        <div class="info-label">Motivo de consulta</div>
        <div class="info-value"><?= nl2br(htmlspecialchars($registro['motivo'])) ?></div>

        <div class="info-label">Signos clínicos</div>
        <div class="info-value"><?= nl2br(htmlspecialchars($registro['signos_clinicos'])) ?></div>

        <div class="info-label">Diagnóstico</div>
        <div class="info-value"><?= nl2br(htmlspecialchars($registro['diagnostico'])) ?></div>
    </div>

    <?php if ($registro['estado_general'] || $registro['estado_hidratacion'] || $registro['temperatura'] || 
             $registro['frecuencia_cardiaca'] || $registro['frecuencia_respiratoria'] || $registro['medicacion']): ?>
    <div class="mb-4 ps-3">
        <h5>
            <i class="fas fa-plus-circle text-secondary me-2"></i>
            Datos Opcionales
        </h5>
    </div>

                    
                    <div class="row">
                        <?php if ($registro['estado_general']): ?>
                        <div class="col-md-6">
                            <div class="info-label">Estado general</div>
                            <div class="info-value"><?= htmlspecialchars($registro['estado_general']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($registro['estado_hidratacion']): ?>
                        <div class="col-md-6">
                            <div class="info-label">Estado de hidratación</div>
                            <div class="info-value"><?= htmlspecialchars($registro['estado_hidratacion']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <?php if ($registro['temperatura']): ?>
                        <div class="col-md-4">
                            <div class="info-label">Temperatura (°C)</div>
                            <div class="info-value"><?= htmlspecialchars($registro['temperatura']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($registro['frecuencia_cardiaca']): ?>
                        <div class="col-md-4">
                            <div class="info-label">Frecuencia cardíaca</div>
                            <div class="info-value"><?= htmlspecialchars($registro['frecuencia_cardiaca']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($registro['frecuencia_respiratoria']): ?>
                        <div class="col-md-4">
                            <div class="info-label">Frecuencia respiratoria</div>
                            <div class="info-value"><?= htmlspecialchars($registro['frecuencia_respiratoria']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($registro['medicacion']): ?>
                    <div class="info-label">Medicación</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($registro['medicacion'])) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($registro['via_administracion']): ?>
                    <div class="info-label">Vía de administración</div>
                    <div class="info-value"><?= htmlspecialchars($registro['via_administracion']) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($registro['observaciones']): ?>
                    <div class="info-label">Observaciones</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($registro['observaciones'])) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
    <?php elseif ($registro['tipo'] == 'Urgencia'): ?>
        <!-- Detalles específicos para Urgencia -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                Detalles de la urgencia
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5><i class="fas fa-asterisk text-danger me-2"></i>Datos Obligatorios</h5>
                    
                    <div class="info-label">Motivo de urgencia</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($registro['motivo'])) ?></div>
                    
                    <div class="info-label">Signos clínicos</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($registro['signos_clinicos'])) ?></div>
                    
                    <div class="info-label">Primeros auxilios aplicados</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($registro['primeros_auxilios'])) ?></div>
                </div>
                
                <?php if ($registro['estado_general'] || $registro['estado_hidratacion'] || $registro['temperatura'] || 
                         $registro['frecuencia_cardiaca'] || $registro['frecuencia_respiratoria'] || $registro['medicacion'] || 
                         $registro['referido_otro_centro'] || $registro['observaciones']): ?>
                <div class="mb-4">
                    <h5><i class="fas fa-plus-circle text-secondary me-2"></i>Datos Opcionales</h5>
                    
                    <div class="row">
                        <?php if ($registro['estado_general']): ?>
                        <div class="col-md-6">
                            <div class="info-label">Estado general</div>
                            <div class="info-value"><?= htmlspecialchars($registro['estado_general']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($registro['estado_hidratacion']): ?>
                        <div class="col-md-6">
                            <div class="info-label">Estado de hidratación</div>
                            <div class="info-value"><?= htmlspecialchars($registro['estado_hidratacion']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <?php if ($registro['temperatura']): ?>
                        <div class="col-md-4">
                            <div class="info-label">Temperatura (°C)</div>
                            <div class="info-value"><?= htmlspecialchars($registro['temperatura']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($registro['frecuencia_cardiaca']): ?>
                        <div class="col-md-4">
                            <div class="info-label">Frecuencia cardíaca</div>
                            <div class="info-value"><?= htmlspecialchars($registro['frecuencia_cardiaca']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($registro['frecuencia_respiratoria']): ?>
                        <div class="col-md-4">
                            <div class="info-label">Frecuencia respiratoria</div>
                            <div class="info-value"><?= htmlspecialchars($registro['frecuencia_respiratoria']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($registro['medicacion']): ?>
                    <div class="info-label">Medicación administrada</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($registro['medicacion'])) ?></div>
                    <?php endif; ?>
                    
                    <div class="info-label">Referido a otro centro</div>
                    <div class="info-value"><?= $registro['referido_otro_centro'] ? 'Sí' : 'No' ?></div>
                    
                    <?php if ($registro['observaciones']): ?>
                    <div class="info-label">Observaciones</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($registro['observaciones'])) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
    <?php elseif ($registro['tipo'] == 'Procedimiento'): ?>
        <!-- Detalles específicos para Procedimiento/Cirugía -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">  
                Detalles del procedimiento
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5><i class="fas fa-asterisk text-danger me-2"></i>Datos Obligatorios</h5>
                    
                    <div class="info-label">Tipo de procedimiento</div>
                    <div class="info-value"><?= htmlspecialchars($registro['tipo_procedimiento']) ?></div>
                    
                    <div class="info-label">Diagnóstico previo</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($registro['diagnostico_previo'])) ?></div>
                    
                    <div class="info-label">Riesgos informados</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($registro['riesgos_informados'])) ?></div>
                </div>
                
                <?php if ($registro['medicacion_previa'] || $registro['tipo_anestesia'] || $registro['cuidados_postoperatorios'] || $registro['observaciones']): ?>
                <div class="mb-4">
                    <h5><i class="fas fa-plus-circle text-secondary me-2"></i>Datos Opcionales</h5>
                    
                    <?php if ($registro['medicacion_previa']): ?>
                    <div class="info-label">Medicación preoperatoria</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($registro['medicacion_previa'])) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($registro['tipo_anestesia']): ?>
                    <div class="info-label">Tipo de anestesia</div>
                    <div class="info-value"><?= htmlspecialchars($registro['tipo_anestesia']) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($registro['cuidados_postoperatorios']): ?>
                    <div class="info-label">Cuidados postoperatorios</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($registro['cuidados_postoperatorios'])) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($registro['observaciones']): ?>
                    <div class="info-label">Observaciones</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($registro['observaciones'])) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Sección de firma digital -->
    <?php if ($registro['firma_imagen']): ?>
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            Consentimiento firmado
        </div>
        <div class="card-body">
            <div class="firma-container">
                <img src="<?= BASE_URL . htmlspecialchars($registro['firma_imagen']) ?>" alt="Firma digital" class="firma-img">
                <div class="mb-2"><strong>Firmado por:</strong> <?= htmlspecialchars($registro['nombre_firmante']) ?></div>
                <div class="mb-2"><strong>DNI:</strong> <?= htmlspecialchars($registro['dni_firmante']) ?></div>
                <div class="text-muted small">Fecha de firma: <?= date('d/m/Y H:i', strtotime($registro['fecha_firma'])) ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>