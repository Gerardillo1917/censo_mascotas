<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

$page_title = "Registro Animal Callejero";
include(BASE_PATH . '/includes/header.php');

$errores = [];
$localidades = obtener_localidades($conn);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $datos = [
            'fecha_rescate' => $_POST['fecha_rescate'] ?? '',
            'lugar_rescate' => sanitizar_input($_POST['lugar_rescate'] ?? ''),
            'coordenadas' => sanitizar_input($_POST['coordenadas'] ?? ''),
            'condicion_salud' => $_POST['condicion_salud'] ?? '',
            'tamano' => $_POST['tamano'] ?? '',
            'peso_aproximado' => $_POST['peso_aproximado'] ?? 0,
            'especie' => $_POST['especie'] ?? '',
            'raza' => sanitizar_input($_POST['raza'] ?? ''),
            'genero' => $_POST['genero'] ?? '',
            'edad_aproximada' => sanitizar_input($_POST['edad_aproximada'] ?? ''),
            'notas_medicas' => sanitizar_input($_POST['notas_medicas'] ?? ''),
            'ubicacion_actual' => $_POST['ubicacion_actual'] ?? '',
            'estado' => $_POST['estado'] ?? 'Resguardado'
        ];

        // Validaciones básicas
        if (empty($datos['fecha_rescate'])) {
            $errores[] = "La fecha de rescate es obligatoria";
        }
        if (empty($datos['lugar_rescate'])) {
            $errores[] = "El lugar de rescate es obligatorio";
        }
        if (empty($datos['especie'])) {
            $errores[] = "La especie es obligatoria";
        }

        if (empty($errores)) {
            $id_animal = registrar_animal_callejero($conn, $datos, $_FILES);
            redirigir_con_mensaje('gestion_adopciones.php', 'success', 'Animal registrado correctamente con folio: ' . generar_folio_animal($conn));
        }
    } catch (Exception $e) {
        $errores[] = "Error al registrar animal: " . $e->getMessage();
        registrar_error($e->getMessage());
    }
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-purple">
            <i class="fas fa-paw me-2"></i> Registro Animal Callejero
        </h2>
        <a href="gestion_adopciones.php" class="btn btn-outline-purple">
            <i class="fas fa-list me-1"></i> Ver Registros
        </a>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-purple text-white">
            <h5 class="mb-0">Datos del Animal</h5>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="row g-3">
                    <!-- Folio -->
                    <div class="col-md-4">
                        <label class="form-label">Folio</label>
                        <input type="text" class="form-control" value="<?= generar_folio_animal($conn) ?>" readonly>
                    </div>
                    
                    <!-- Fecha Rescate -->
                    <div class="col-md-4">
                        <label class="form-label">Fecha de Rescate*</label>
                        <input type="date" class="form-control" name="fecha_rescate" 
                               value="<?= htmlspecialchars($_POST['fecha_rescate'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    
                    <!-- Lugar Rescate -->
                    <div class="col-md-4">
                        <label class="form-label">Lugar de Rescate*</label>
                        <input type="text" class="form-control" name="lugar_rescate" 
                               value="<?= htmlspecialchars($_POST['lugar_rescate'] ?? '') ?>" required>
                    </div>
                    
                    <!-- Coordenadas -->
                    <div class="col-md-6">
                        <label class="form-label">Coordenadas (opcional)</label>
                        <input type="text" class="form-control" name="coordenadas" 
                               placeholder="Ej: 19.123456, -98.123456"
                               value="<?= htmlspecialchars($_POST['coordenadas'] ?? '') ?>">
                    </div>
                    
                    <!-- Condición Salud -->
                    <div class="col-md-6">
                        <label class="form-label">Condición de Salud*</label>
                        <select class="form-select" name="condicion_salud" required>
                            <option value="">Seleccionar...</option>
                            <option value="Buena" <?= ($_POST['condicion_salud'] ?? '') == 'Buena' ? 'selected' : '' ?>>Buena</option>
                            <option value="Herido" <?= ($_POST['condicion_salud'] ?? '') == 'Herido' ? 'selected' : '' ?>>Herido</option>
                            <option value="Desnutrido" <?= ($_POST['condicion_salud'] ?? '') == 'Desnutrido' ? 'selected' : '' ?>>Desnutrido</option>
                            <option value="Enfermo" <?= ($_POST['condicion_salud'] ?? '') == 'Enfermo' ? 'selected' : '' ?>>Enfermo</option>
                            <option value="Crítico" <?= ($_POST['condicion_salud'] ?? '') == 'Crítico' ? 'selected' : '' ?>>Crítico</option>
                        </select>
                    </div>
                    
                    <!-- Especie -->
                    <div class="col-md-4">
                        <label class="form-label">Especie*</label>
                        <select class="form-select" name="especie" required>
                            <option value="">Seleccionar...</option>
                            <option value="Perro" <?= ($_POST['especie'] ?? '') == 'Perro' ? 'selected' : '' ?>>Perro</option>
                            <option value="Gato" <?= ($_POST['especie'] ?? '') == 'Gato' ? 'selected' : '' ?>>Gato</option>
                            <option value="Ave" <?= ($_POST['especie'] ?? '') == 'Ave' ? 'selected' : '' ?>>Ave</option>
                            <option value="Otro" <?= ($_POST['especie'] ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
                        </select>
                    </div>
                    
                    <!-- Raza -->
                    <div class="col-md-4">
                        <label class="form-label">Raza (opcional)</label>
                        <input type="text" class="form-control" name="raza" 
                               value="<?= htmlspecialchars($_POST['raza'] ?? '') ?>">
                    </div>
                    
                    <!-- Género -->
                    <div class="col-md-4">
                        <label class="form-label">Género*</label>
                        <select class="form-select" name="genero" required>
                            <option value="">Seleccionar...</option>
                            <option value="Macho" <?= ($_POST['genero'] ?? '') == 'Macho' ? 'selected' : '' ?>>Macho</option>
                            <option value="Hembra" <?= ($_POST['genero'] ?? '') == 'Hembra' ? 'selected' : '' ?>>Hembra</option>
                        </select>
                    </div>
                    
                    <!-- Tamaño -->
                    <div class="col-md-3">
                        <label class="form-label">Tamaño*</label>
                        <select class="form-select" name="tamano" required>
                            <option value="">Seleccionar...</option>
                            <option value="Pequeño" <?= ($_POST['tamano'] ?? '') == 'Pequeño' ? 'selected' : '' ?>>Pequeño</option>
                            <option value="Mediano" <?= ($_POST['tamano'] ?? '') == 'Mediano' ? 'selected' : '' ?>>Mediano</option>
                            <option value="Grande" <?= ($_POST['tamano'] ?? '') == 'Grande' ? 'selected' : '' ?>>Grande</option>
                        </select>
                    </div>
                    
                    <!-- Peso -->
                    <div class="col-md-3">
                        <label class="form-label">Peso aproximado (kg)</label>
                        <input type="number" step="0.1" class="form-control" name="peso_aproximado" 
                               value="<?= htmlspecialchars($_POST['peso_aproximado'] ?? '') ?>">
                    </div>
                    
                    <!-- Edad -->
                    <div class="col-md-3">
                        <label class="form-label">Edad aproximada</label>
                        <input type="text" class="form-control" name="edad_aproximada" 
                               placeholder="Ej: 2 años, 6 meses"
                               value="<?= htmlspecialchars($_POST['edad_aproximada'] ?? '') ?>">
                    </div>
                    
                    <!-- Estado -->
                    <div class="col-md-3">
                        <label class="form-label">Estado actual*</label>
                        <select class="form-select" name="estado" required>
                            <option value="Resguardado" <?= ($_POST['estado'] ?? '') == 'Resguardado' ? 'selected' : '' ?>>Resguardado</option>
                            <option value="Tratamiento" <?= ($_POST['estado'] ?? '') == 'Tratamiento' ? 'selected' : '' ?>>En tratamiento</option>
                            <option value="Adopción" <?= ($_POST['estado'] ?? '') == 'Adopción' ? 'selected' : '' ?>>Disponible para adopción</option>
                        </select>
                    </div>
                    
                    <!-- Ubicación Actual -->
                    <div class="col-md-6">
                        <label class="form-label">Ubicación actual*</label>
                        <select class="form-select" name="ubicacion_actual" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($localidades as $loc): ?>
                                <option value="<?= htmlspecialchars($loc['nombre']) ?>" 
                                    <?= ($_POST['ubicacion_actual'] ?? '') == $loc['nombre'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loc['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Foto -->
                    <div class="col-md-6">
                        <label class="form-label">Fotografía</label>
                        <input type="file" class="form-control" name="foto" accept="image/*">
                        <small class="text-muted">Formatos: JPG, PNG (máx. 2MB)</small>
                    </div>
                    
                    <!-- Notas Médicas -->
                    <div class="col-12">
                        <label class="form-label">Notas médicas</label>
                        <textarea class="form-control" name="notas_medicas" rows="3"><?= 
                            htmlspecialchars($_POST['notas_medicas'] ?? '') 
                        ?></textarea>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" class="btn btn-purple">
                        <i class="fas fa-save me-1"></i> Guardar Registro
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>