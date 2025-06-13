<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

$id_animal = isset($_GET['id_animal']) ? intval($_GET['id_animal']) : 0;
if ($id_animal <= 0) {
    redirigir_con_mensaje('gestion_adopciones.php', 'danger', 'Animal no especificado');
}

// Verificar que el animal existe y está disponible para adopción
$stmt = $conn->prepare("SELECT * FROM animales_callejeros WHERE id_animal = ? AND estado = 'Adopción'");
$stmt->bind_param("i", $id_animal);
$stmt->execute();
$animal = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$animal) {
    redirigir_con_mensaje('gestion_adopciones.php', 'danger', 'Animal no disponible para adopción');
}

// Procesar formulario de búsqueda de tutor
$tutores = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buscar_tutor'])) {
    $termino = sanitizar_input($_POST['termino_busqueda'] ?? '');
    
    $sql = "SELECT * FROM tutores WHERE 
            nombre LIKE ? OR 
            apellido_paterno LIKE ? OR 
            telefono LIKE ? OR
            email LIKE ?
            LIMIT 50";
        $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $param, $param, $param, $param);
    $stmt->execute();
    $tutores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Procesar asignación de tutor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['asignar_tutor'])) {
    $id_tutor = intval($_POST['id_tutor'] ?? 0);
    $notas = sanitizar_input($_POST['notas'] ?? '');

    if ($id_tutor <= 0) {
        $errores[] = "Debe seleccionar un tutor válido";
    } else {
        try {
            $conn->begin_transaction();

            // 1. Crear registro de adopción
            $stmt = $conn->prepare("INSERT INTO adopciones 
                                  (id_animal, id_tutor, id_usuario_aprobo, fecha_adopcion, estado, notas) 
                                  VALUES (?, ?, ?, CURDATE(), 'Pendiente', ?)");
            $stmt->bind_param("iiis", $id_animal, $id_tutor, $_SESSION['user_id'], $notas);
            $stmt->execute();
            $id_adopcion = $conn->insert_id;
            $stmt->close();

            // 2. Actualizar estado del animal
            $stmt = $conn->prepare("UPDATE animales_callejeros SET estado = 'Fallecido' WHERE id_animal = ?");
            $stmt->bind_param("i", $id_animal);
            $stmt->execute();
            $stmt->close();

            $conn->commit();

            redirigir_con_mensaje('gestion_adopciones.php', 'success', 'Proceso de adopción iniciado correctamente');
        } catch (Exception $e) {
            $conn->rollback();
            $errores[] = "Error al procesar la adopción: " . $e->getMessage();
            registrar_error($e->getMessage());
        }
    }
}

$page_title = "Proceso de Adopción";
include(BASE_PATH . '/includes/header.php');
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-purple">
            <i class="fas fa-home me-2"></i> Proceso de Adopción
        </h2>
        <a href="detalle_animal.php?id=<?= $id_animal ?>" class="btn btn-outline-purple">
            <i class="fas fa-arrow-left me-1"></i> Volver
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

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-purple text-white">
            <h5 class="mb-0">Animal a Adoptar</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <?php if ($animal['foto_ruta']): ?>
                        <img src="<?= htmlspecialchars($animal['foto_ruta']) ?>" 
                             class="img-fluid rounded" 
                             alt="Foto de <?= htmlspecialchars($animal['folio']) ?>">
                    <?php else: ?>
                        <div class="text-center py-3 bg-light rounded">
                            <i class="fas fa-paw fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-10">
                    <h5><?= htmlspecialchars($animal['folio']) ?></h5>
                    <p class="mb-1">
                        <strong>Especie:</strong> <?= htmlspecialchars($animal['especie']) ?>
                        | <strong>Raza:</strong> <?= htmlspecialchars($animal['raza'] ?: 'N/A') ?>
                        | <strong>Género:</strong> <?= htmlspecialchars($animal['genero']) ?>
                    </p>
                    <p class="mb-1">
                        <strong>Edad:</strong> <?= htmlspecialchars($animal['edad_aproximada'] ?: 'N/A') ?>
                        | <strong>Tamaño:</strong> <?= htmlspecialchars($animal['tamano']) ?>
                    </p>
                    <p class="mb-0">
                        <strong>Ubicación actual:</strong> <?= htmlspecialchars($animal['ubicacion_actual']) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-purple text-white">
            <h5 class="mb-0">Seleccionar Tutor</h5>
        </div>
        <div class="card-body">
            <!-- Formulario de búsqueda de tutor -->
            <form method="post" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-9">
                        <label class="form-label">Buscar tutor existente</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="termino_busqueda" 
                                   placeholder="Nombre, apellido, teléfono o email" 
                                   value="<?= htmlspecialchars($_POST['termino_busqueda'] ?? '') ?>">
                            <button type="submit" name="buscar_tutor" class="btn btn-purple">
                                <i class="fas fa-search me-1"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <a href="agregar_tutor.php?adopcion=<?= $id_animal ?>" 
                           class="btn btn-success w-100">
                            <i class="fas fa-plus me-1"></i> Nuevo Tutor
                        </a>
                    </div>
                </div>
            </form>

            <!-- Resultados de búsqueda o selección -->
            <?php if (!empty($tutores)): ?>
                <div class="table-responsive mb-4">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Dirección</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tutores as $tutor): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($tutor['nombre'] . ' ' . $tutor['apellido_paterno'] . ' ' . ($tutor['apellido_materno'] ?? '')) ?>
                                    </td>
                                    <td><?= htmlspecialchars($tutor['telefono']) ?></td>
                                    <td><?= htmlspecialchars($tutor['email'] ?? 'N/A') ?></td>
                                    <td>
                                        <?= htmlspecialchars(
                                            ($tutor['calle'] ?? '') . ' ' . 
                                            ($tutor['numero_exterior'] ?? '') . ', ' . 
                                            ($tutor['colonia'] ?? '')
                                        ) ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-purple" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalAdopcion"
                                                data-tutor-id="<?= $tutor['id_tutor'] ?>"
                                                data-tutor-nombre="<?= htmlspecialchars($tutor['nombre'] . ' ' . $tutor['apellido_paterno']) ?>">
                                            <i class="fas fa-check me-1"></i> Seleccionar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buscar_tutor'])): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No se encontraron tutores con ese criterio de búsqueda
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para confirmar adopción -->
<div class="modal fade" id="modalAdopcion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="id_tutor" id="inputTutorId">
                <div class="modal-header bg-purple text-white">
                    <h5 class="modal-title">Confirmar Adopción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Está a punto de asignar este animal al tutor: <strong id="nombreTutor"></strong></p>
                    <div class="mb-3">
                        <label for="notasAdopcion" class="form-label">Notas adicionales</label>
                        <textarea class="form-control" id="notasAdopcion" name="notas" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="asignar_tutor" class="btn btn-purple">
                        <i class="fas fa-check me-1"></i> Confirmar Adopción
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Configurar modal con datos del tutor seleccionado
document.getElementById('modalAdopcion').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const tutorId = button.getAttribute('data-tutor-id');
    const tutorNombre = button.getAttribute('data-tutor-nombre');
    
    document.getElementById('inputTutorId').value = tutorId;
    document.getElementById('nombreTutor').textContent = tutorNombre;
});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>