<?php
ob_start();
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

requerir_autenticacion_admin();
iniciar_sesion_segura();

$page_title = "Agregar Nueva Campaña";
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validación y sanitización
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $fecha_inicio = filter_input(INPUT_POST, 'fecha_inicio', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $fecha_fin = filter_input(INPUT_POST, 'fecha_fin', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $localidades = filter_input(INPUT_POST, 'localidades', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $activa = isset($_POST['activa']) ? 1 : 0;

    // Validar campos
    if (empty($nombre)) {
        $errores[] = "El nombre de la campaña es requerido.";
    }
    if (empty($tipo) || !in_array($tipo, ['vacunacion', 'esterilizacion', 'consulta', 'otro'])) {
        $errores[] = "El tipo de campaña no es válido.";
    }
    if (empty($fecha_inicio) || !strtotime($fecha_inicio)) {
        $errores[] = "La fecha de inicio no es válida.";
    }
    if (!empty($fecha_fin) && !strtotime($fecha_fin)) {
        $errores[] = "La fecha de fin no es válida.";
    }
    if (empty($localidades)) {
        $errores[] = "Debe especificar al menos una localidad.";
    }

    if (empty($errores)) {
        try {
            $stmt = $conn->prepare("INSERT INTO campanas (nombre, tipo, descripcion, fecha_inicio, fecha_fin, localidades, activa) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $nombre, $tipo, $descripcion, $fecha_inicio, $fecha_fin, $localidades, $activa);
            $stmt->execute();
            $id_campana = $stmt->insert_id;
            $stmt->close();

            registrar_acceso($_SESSION['panel_admin_id'], true, "Campaña creada: $nombre (ID: $id_campana)");
            redirigir_con_mensaje('/admin/gestion_campanas.php', 'success', "Campaña creada con éxito");
        } catch (Exception $e) {
            $errores[] = "Error al crear la campaña: " . $e->getMessage();
            registrar_error("Error en form_agregar_campana.php: " . $e->getMessage());
        }
    }
}

include(BASE_PATH . '/admin/includes_panel/header_panel.php');
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-plus-circle"></i> Agregar Nueva Campaña</h2>
        <a href="gestion_campanas.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm card-purple-border">
        <div class="card-header bg-purple">
            <h5 class="mb-0">Datos de la Campaña</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre de la Campaña</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required
                               value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="tipo" class="form-label">Tipo de Campaña</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="vacunacion" <?= (isset($_POST['tipo']) && $_POST['tipo'] === 'vacunacion') ? 'selected' : '' ?>>Vacunación</option>
                            <option value="esterilizacion" <?= (isset($_POST['tipo']) && $_POST['tipo'] === 'esterilizacion') ? 'selected' : '' ?>>Esterilización</option>
                            <option value="consulta" <?= (isset($_POST['tipo']) && $_POST['tipo'] === 'consulta') ? 'selected' : '' ?>>Consulta</option>
                            <option value="otro" <?= (isset($_POST['tipo']) && $_POST['tipo'] === 'otro') ? 'selected' : '' ?>>Otro</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required
                               value="<?= htmlspecialchars($_POST['fecha_inicio'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="fecha_fin" class="form-label">Fecha de Fin (Opcional)</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                               value="<?= htmlspecialchars($_POST['fecha_fin'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label for="localidades" class="form-label">Localidades que cubre (separadas por comas)</label>
                        <textarea class="form-control" id="localidades" name="localidades" rows="2" required><?= htmlspecialchars($_POST['localidades'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="activa" name="activa" <?= isset($_POST['activa']) ? 'checked' : 'checked' ?>>
                            <label class="form-check-label" for="activa">Campaña activa</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-purple-primary">Guardar Campaña</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
:root {
    --purple-primary: #8b0180;
    --purple-secondary: #6a015f;
    --white: #ffffff;
}

.card-purple-border {
    border: 1px solid var(--purple-primary);
}

.card-header.bg-purple {
    background-color: var(--purple-primary) !important;
    color: var(--white) !important;
}

.btn-purple-primary {
    background-color: var(--purple-primary);
    border-color: var(--purple-primary);
    color: var(--white);
}

.btn-purple-primary:hover {
    background-color: var(--purple-secondary);
    border-color: var(--purple-secondary);
    color: var(--white);
}
</style>

<?php
include(BASE_PATH . '/admin/includes_panel/footer_panel.php');
ob_end_flush();
?>