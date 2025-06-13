<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Validar ID de interacción
$id_interaccion = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_interaccion <= 0) {
    redirigir_con_mensaje('buscar.php', 'danger', 'Procedimiento no especificado');
}

try {
    // Obtener datos del procedimiento
    $stmt = $conn->prepare("
        SELECT sm.*, m.nombre AS nombre_mascota, m.especie, m.genero, 
               l.nombre AS localidad, CONCAT(t.nombre, ' ', t.apellido_paterno) AS tutor
        FROM salud_mascotas sm
        JOIN mascotas m ON sm.id_mascota = m.id_mascota
        JOIN tutores t ON m.id_tutor = t.id_tutor
        JOIN localidades l ON sm.id_localidad = l.id_localidad
        WHERE sm.id_interaccion = ?
    ");
    $stmt->bind_param("i", $id_interaccion);
    $stmt->execute();
    $procedimiento = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$procedimiento) {
        redirigir_con_mensaje('buscar.php', 'danger', 'Procedimiento no encontrado');
    }

    // Obtener firma digital si existe
    $firma_img = '';
    if (!empty($procedimiento['firma_imagen'])) {
        $firma_img = 'data:image/png;base64,' . base64_encode($procedimiento['firma_imagen']);
    }

} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje('buscar.php', 'danger', 'Error al cargar datos');
}

$page_title = "Detalles de " . htmlspecialchars($procedimiento['tipo']) . " - " . htmlspecialchars($procedimiento['nombre_mascota']);
include(BASE_PATH . '/includes/header.php');
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-purple mb-0">
                <i class="fas fa-<?= $procedimiento['tipo'] === 'Esterilización' ? 'cut' : 'stethoscope' ?> me-2"></i>
                <?= htmlspecialchars($procedimiento['tipo']) ?>
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/buscar.php">Buscar</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $procedimiento['id_mascota'] ?>">Historial</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalles</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $procedimiento['id_mascota'] ?>" class="btn btn-outline-purple">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Tarjeta resumen -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-purple text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Resumen del Procedimiento</h5>
                <div>
                    <span class="badge bg-light text-dark">
                        <?= date('d/m/Y', strtotime($procedimiento['fecha'])) ?> 
                        <?= !empty($procedimiento['hora']) ? 'a las ' . date('H:i', strtotime($procedimiento['hora'])) : '' ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <h6 class="text-purple">Mascota</h6>
                        <p class="mb-1">
                            <strong><?= htmlspecialchars($procedimiento['nombre_mascota']) ?></strong> 
                            (<?= htmlspecialchars($procedimiento['especie']) ?>, <?= htmlspecialchars($procedimiento['genero']) ?>)
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <h6 class="text-purple">Tutor</h6>
                        <p class="mb-1"><?= htmlspecialchars($procedimiento['tutor']) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <h6 class="text-purple">Localidad</h6>
                        <p class="mb-1"><?= htmlspecialchars($procedimiento['localidad']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección 1: Constantes fisiológicas -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">1. Constantes Fisiológicas</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php if ($procedimiento['fr']): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block">FR (resp/min)</small>
                        <strong><?= htmlspecialchars($procedimiento['fr']) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($procedimiento['fc']): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block">FC (lat/min)</small>
                        <strong><?= htmlspecialchars($procedimiento['fc']) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($procedimiento['cc']): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block">CC (1-5)</small>
                        <strong><?= htmlspecialchars($procedimiento['cc']) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($procedimiento['tllc']): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block">TLLC (seg)</small>
                        <strong><?= htmlspecialchars($procedimiento['tllc']) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($procedimiento['reflejo_tusigeno']): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block">Reflejo Tusígeno</small>
                        <strong><?= htmlspecialchars($procedimiento['reflejo_tusigeno']) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($procedimiento['reflejo_deglutorio']): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block">Reflejo Deglutorio</small>
                        <strong><?= htmlspecialchars($procedimiento['reflejo_deglutorio']) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($procedimiento['mucosas']): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block">Coloración de mucosas</small>
                        <strong><?= htmlspecialchars($procedimiento['mucosas']) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($procedimiento['temperatura']): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block">Temperatura (°C)</small>
                        <strong><?= htmlspecialchars($procedimiento['temperatura']) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($procedimiento['nodulos_linfaticos']): ?>
                <div class="col-12">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block">Nódulos Linfáticos</small>
                        <strong><?= htmlspecialchars($procedimiento['nodulos_linfaticos']) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sección 2: Historial Clínico -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">2. Historial Clínico</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-purple mb-3">Vacunación</h6>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" disabled <?= $procedimiento['vacunacion_rabia'] ? 'checked' : '' ?>>
                            <label class="form-check-label">Rabia</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" disabled <?= $procedimiento['vacunacion_basica'] ? 'checked' : '' ?>>
                            <label class="form-check-label">Cuadro básico</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-purple mb-3">Desparasitación</h6>
                    <?php if ($procedimiento['desparasitacion_producto']): ?>
                        <p>
                            <strong>Producto:</strong> <?= htmlspecialchars($procedimiento['desparasitacion_producto']) ?><br>
                            <?php if ($procedimiento['desparasitacion_fecha']): ?>
                                <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($procedimiento['desparasitacion_fecha'])) ?>
                            <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <p class="text-muted">No registrada</p>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <h6 class="text-purple mb-3">Antecedentes</h6>
                    <?php if ($procedimiento['antecedentes']): ?>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(htmlspecialchars($procedimiento['antecedentes'])) ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No se registraron antecedentes</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección 3: Datos del Procedimiento -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">3. Datos del Procedimiento</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6 class="text-purple">Plan Anestésico</h6>
                        <p><?= htmlspecialchars($procedimiento['plan_anestesico']) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6 class="text-purple">Medicación Preoperatoria</h6>
                        <p><?= $procedimiento['medicacion_previa'] ? htmlspecialchars($procedimiento['medicacion_previa']) : 'No registrada' ?></p>
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <h6 class="text-purple">Observaciones Quirúrgicas</h6>
                        <?php if ($procedimiento['observaciones']): ?>
                            <div class="border rounded p-3 bg-light">
                                <?= nl2br(htmlspecialchars($procedimiento['observaciones'])) ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No se registraron observaciones</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección 4: Responsable y Firma -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">4. Responsable</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6 class="text-purple">Veterinario Responsable</h6>
                        <p><?= htmlspecialchars($procedimiento['responsable']) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6 class="text-purple">Estado</h6>
                        <span class="badge bg-<?= $procedimiento['estado'] === 'completado' ? 'success' : 'warning' ?>">
                            <?= ucfirst($procedimiento['estado']) ?>
                        </span>
                    </div>
                </div>
                <?php if ($firma_img): ?>
                <div class="col-12">
                    <h6 class="text-purple mb-3">Consentimiento Firmado</h6>
                    <div class="border rounded p-3 text-center">
                        <img src="<?= $firma_img ?>" alt="Firma digital" style="max-width: 300px; height: auto;">
                        <p class="mt-2 mb-0 text-muted">Firmado el <?= date('d/m/Y H:i', strtotime($procedimiento['firma_fecha'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $procedimiento['id_mascota'] ?>" class="btn btn-outline-purple">
            <i class="fas fa-arrow-left me-1"></i> Volver al historial
        </a>
        <?php if (empty($firma_img) && ($_SESSION['rol'] === 'veterinario' || $_SESSION['rol'] === 'admin')): ?>
            <a href="<?= BASE_URL ?>/salud/firma_digital.php?id_interaccion=<?= $procedimiento['id_interaccion'] ?>&id_mascota=<?= $procedimiento['id_mascota'] ?>" class="btn btn-purple">
                <i class="fas fa-signature me-1"></i> Firmar consentimiento
            </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/salud/generar_reporte.php?id=<?= $procedimiento['id_interaccion'] ?>" class="btn btn-success" target="_blank">
            <i class="fas fa-file-pdf me-1"></i> Generar PDF
        </a>
    </div>
</div>

<style>
    .form-check-input:checked {
        background-color: #8b0180;
        border-color: #8b0180;
    }
    .form-check-input:disabled {
        opacity: 1;
    }
    .card-header.bg-light {
        background-color: #f8f9fa !important;
    }
</style>

<?php include(BASE_PATH . '/includes/footer.php'); ?>