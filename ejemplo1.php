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
    $stmt = $conn->prepare("SELECT nombre, especie, genero FROM mascotas WHERE id_mascota = ?");
    $stmt->bind_param("i", $id_mascota);
    $stmt->execute();
    $result = $stmt->get_result();
    $mascota = $result->fetch_assoc();
    $stmt->close();

    if (!$mascota) {
        redirigir_con_mensaje('buscar.php', 'danger', 'Mascota no encontrada');
    }

    // Obtener localidades
    $localidades = $conn->query("SELECT * FROM localidades WHERE activa = TRUE ORDER BY nombre");
} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje('buscar.php', 'danger', 'Error al cargar datos');
}

$page_title = "Esterilización - " . htmlspecialchars($mascota['nombre']);
include(BASE_PATH . '/includes/header.php');

// Datos del usuario
$nombre_usuario = $_SESSION['nombre_completo'] ?? 'Usuario no identificado';
$fecha_actual = date('Y-m-d');
$hora_actual = date('H:i');

// Restaurar datos del formulario si existen en $_SESSION
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-purple">
            <i class="fas fa-cut me-2"></i> Registro de Esterilización
        </h2>
        <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>" class="btn btn-outline-purple">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-purple text-white">
            <h5 class="mb-0"><?= htmlspecialchars($mascota['nombre']) ?> - <?= htmlspecialchars($mascota['especie']) ?> <?= htmlspecialchars($mascota['genero']) ?></h5>
        </div>
        
        <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/salud/procesar_cirugia.php" id="cirugiaForm">
                <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
                <input type="hidden" name="tipo_procedimiento" value="Esterilización">
                
                <!-- Sección 1: Datos básicos -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">1. Información del Procedimiento</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Fecha*</label>
                                    <input type="date" class="form-control" name="fecha" value="<?= htmlspecialchars($form_data['fecha'] ?? $fecha_actual) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Hora*</label>
                                    <input type="time" class="form-control" name="hora" value="<?= htmlspecialchars($form_data['hora'] ?? $hora_actual) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Localidad*</label>
                                    <select class="form-select" name="id_localidad" required>
                                        <option value="">Seleccionar...</option>
                                        <?php while($localidad = $localidades->fetch_assoc()): ?>
                                            <option value="<?= $localidad['id_localidad'] ?>" <?= isset($form_data['id_localidad']) && $form_data['id_localidad'] == $localidad['id_localidad'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($localidad['nombre']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Constantes fisiológicas -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">2. Constantes Fisiológicas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">FR (resp/min)</label>
                                <input type="text" class="form-control" name="fr" value="<?= htmlspecialchars($form_data['fr'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">FC (lat/min)</label>
                                <input type="text" class="form-control" name="fc" value="<?= htmlspecialchars($form_data['fc'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">CC (1-5)</label>
                                <input type="text" class="form-control" name="cc" value="<?= htmlspecialchars($form_data['cc'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">TLLC (seg)</label>
                                <input type="text" class="form-control" name="tllc" value="<?= htmlspecialchars($form_data['tllc'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reflejo Tusígeno</label>
                                <select class="form-select" name="reflejo_tusigeno">
                                    <option value="">Seleccionar...</option>
                                    <option value="Presente" <?= isset($form_data['reflejo_tusigeno']) && $form_data['reflejo_tusigeno'] == 'Presente' ? 'selected' : '' ?>>Presente</option>
                                    <option value="Ausente" <?= isset($form_data['reflejo_tusigeno']) && $form_data['reflejo_tusigeno'] == 'Ausente' ? 'selected' : '' ?>>Ausente</option>
                                    <option value="Disminuido" <?= isset($form_data['reflejo_tusigeno']) && $form_data['reflejo_tusigeno'] == 'Disminuido' ? 'selected' : '' ?>>Disminuido</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reflejo Deglutorio</label>
                                <select class="form-select" name="reflejo_deglutorio">
                                    <option value="">Seleccionar...</option>
                                    <option value="Presente" <?= isset($form_data['reflejo_deglutorio']) && $form_data['reflejo_deglutorio'] == 'Presente' ? 'selected' : '' ?>>Presente</option>
                                    <option value="Ausente" <?= isset($form_data['reflejo_deglutorio']) && $form_data['reflejo_deglutorio'] == 'Ausente' ? 'selected' : '' ?>>Ausente</option>
                                    <option value="Disminuido" <?= isset($form_data['reflejo_deglutorio']) && $form_data['reflejo_deglutorio'] == 'Disminuido' ? 'selected' : '' ?>>Disminuido</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mucosas</label>
                                    <select class="form-select" name="mucosas">
                                        <option value="">Seleccionar...</option>
                                        <option value="Rosadas" <?= isset($form_data['mucosas']) && $form_data['mucosas'] == 'Rosadas' ? 'selected' : '' ?>>Rosadas</option>
                                        <option value="Pálidas" <?= isset($form_data['mucosas']) && $form_data['mucosas'] == 'Pálidas' ? 'selected' : '' ?>>Pálidas</option>
                                        <option value="Ictéricas" <?= isset($form_data['mucosas']) && $form_data['mucosas'] == 'Ictéricas' ? 'selected' : '' ?>>Ictéricas</option>
                                        <option value="Cianóticas" <?= isset($form_data['mucosas']) && $form_data['mucosas'] == 'Cianóticas' ? 'selected' : '' ?>>Cianóticas</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Temperatura (°C)</label>
                                    <input type="text" class="form-control" name="temperatura" value="<?= htmlspecialchars($form_data['temperatura'] ?? '') ?>">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Nódulos Linfáticos</label>
                                    <input type="text" class="form-control" name="nodulos_linfaticos" placeholder="Especificar ganglios palpables" value="<?= htmlspecialchars($form_data['nodulos_linfaticos'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Historial Clínico -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">3. Historial Clínico</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="vacunacion_rabia" name="vacunacion_rabia" value="1" <?= isset($form_data['vacunacion_rabia']) && $form_data['vacunacion_rabia'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="vacunacion_rabia">Vacunación contra Rabia</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="vacunacion_basica" name="vacunacion_basica" value="1" <?= isset($form_data['vacunacion_basica']) && $form_data['vacunacion_basica'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="vacunacion_basica">Cuadro Básico de Vacunación</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Desparasitación (Producto)</label>
                                        <input type="text" class="form-control" name="desparasitacion_producto" value="<?= htmlspecialchars($form_data['desparasitacion_producto'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Fecha Desparasitación</label>
                                        <input type="date" class="form-control" name="desparasitacion_fecha" value="<?= htmlspecialchars($form_data['desparasitacion_fecha'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Antecedentes</label>
                                    <textarea class="form-control" name="antecedentes" rows="3" placeholder="Enfermedades anteriores, cirugías, procedencia..."><?= htmlspecialchars($form_data['antecedentes'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 4: Datos del Procedimiento -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">4. Datos de la Esterilización</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Plan Anestésico*</label>
                                    <select class="form-select" name="plan_anestesico" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="Intramuscular" <?= isset($form_data['plan_anestesico']) && $form_data['plan_anestesico'] == 'Intramuscular' ? 'selected' : '' ?>>Intramuscular</option>
                                        <option value="Intravenoso" <?= isset($form_data['plan_anestesico']) && $form_data['plan_anestesico'] == 'Intravenoso' ? 'selected' : '' ?>>Intravenoso</option>
                                        <option value="Inhalatorio" <?= isset($form_data['plan_anestesico']) && $form_data['plan_anestesico'] == 'Inhalatorio' ? 'selected' : '' ?>>Inhalatorio</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Medicación Preoperatoria</label>
                                    <input type="text" class="form-control" name="medicacion_previa" value="<?= htmlspecialchars($form_data['medicacion_previa'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Observaciones Quirúrgicas</label>
                                    <textarea class="form-control" name="observaciones" rows="3"><?= htmlspecialchars($form_data['observaciones'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Responsable -->
                    <div class="card mb-4">
                        <div class="card-header bg-purple text-white">
                            <h5 class="mb-0">Responsable del Procedimiento</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre*</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($nombre_usuario) ?>" readonly>
                                        <input type="hidden" name="responsable" value="<?= htmlspecialchars($nombre_usuario) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Fecha de Registro</label>
                                        <input type="text" class="form-control" value="<?= date('d/m/Y H:i') ?>" readonly>
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
        .form-check-input:checked {
            background-color: #8b0180;
            border-color: #8b0180;
        }
        .card-header.bg-light {
            background-color: #f8f9fa !important;
        }
    </style>

    <?php include(BASE_PATH . '/includes/footer.php'); ?>