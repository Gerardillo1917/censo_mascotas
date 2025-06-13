<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

$id_animal = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_animal <= 0) {
    redirigir_con_mensaje('gestion_adopciones.php', 'danger', 'Animal no especificado');
}

// Obtener datos del animal
$stmt = $conn->prepare("SELECT * FROM animales_callejeros WHERE id_animal = ?");
$stmt->bind_param("i", $id_animal);
$stmt->execute();
$animal = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$animal) {
    redirigir_con_mensaje('gestion_adopciones.php', 'danger', 'Animal no encontrado');
}

$page_title = "Detalle: " . htmlspecialchars($animal['folio']);
include(BASE_PATH . '/includes/header.php');
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-purple">
            <i class="fas fa-paw me-2"></i> <?= htmlspecialchars($animal['folio']) ?>
        </h2>
        <a href="gestion_adopciones.php" class="btn btn-outline-purple">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="row">
        <!-- Columna izquierda - Foto -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <?php if ($animal['foto_ruta']): ?>
                    <img src="<?= htmlspecialchars($animal['foto_ruta']) ?>" 
                         class="card-img-top" 
                         alt="Foto de <?= htmlspecialchars($animal['folio']) ?>">
                <?php else: ?>
                    <div class="text-center py-5 bg-light">
                        <i class="fas fa-paw fa-5x text-muted"></i>
                        <p class="mt-2 mb-0">Sin imagen</p>
                    </div>
                <?php endif; ?>
                <div class="card-body text-center">
                    <h5 class="card-title mb-1"><?= htmlspecialchars($animal['especie']) ?></h5>
                    <p class="card-text text-muted mb-2">
                        <?= htmlspecialchars($animal['raza'] ?: 'Raza no especificada') ?>
                    </p>
                    <span class="badge bg-<?= 
                        $animal['estado'] == 'Adopción' ? 'success' : 
                        ($animal['estado'] == 'Tratamiento' ? 'warning' : 
                        ($animal['estado'] == 'Fallecido' ? 'danger' : 'info')) 
                    ?>">
                        <?= htmlspecialchars($animal['estado']) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Columna derecha - Detalles -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-purple text-white">
                    <h5 class="mb-0">Información del Animal</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-purple">Datos Básicos</h6>
                            <ul class="list-unstyled">
                                <li><strong>Folio:</strong> <?= htmlspecialchars($animal['folio']) ?></li>
                                <li><strong>Fecha Rescate:</strong> <?= date('d/m/Y', strtotime($animal['fecha_rescate'])) ?></li>
                                <li><strong>Género:</strong> <?= htmlspecialchars($animal['genero']) ?></li>
                                <li><strong>Edad:</strong> <?= htmlspecialchars($animal['edad_aproximada'] ?: 'N/A') ?></li>
                                <li><strong>Tamaño:</strong> <?= htmlspecialchars($animal['tamano']) ?></li>
                                <li><strong>Peso:</strong> <?= $animal['peso_aproximado'] ? htmlspecialchars($animal['peso_aproximado']) . ' kg' : 'N/A' ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-purple">Ubicación</h6>
                            <ul class="list-unstyled">
                                <li><strong>Lugar Rescate:</strong> <?= htmlspecialchars($animal['lugar_rescate']) ?></li>
                                <li><strong>Coordenadas:</strong> <?= htmlspecialchars($animal['coordenadas'] ?: 'N/A') ?></li>
                                <li><strong>Ubicación Actual:</strong> <?= htmlspecialchars($animal['ubicacion_actual']) ?></li>
                                <li><strong>Condición:</strong> <?= htmlspecialchars($animal['condicion_salud']) ?></li>
                            </ul>
                        </div>
                    </div>

                    <h6 class="text-purple mt-4">Notas Médicas</h6>
                    <div class="border p-3 rounded bg-light">
                        <?= $animal['notas_medicas'] ? nl2br(htmlspecialchars($animal['notas_medicas'])) : 'No hay notas médicas registradas' ?>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <?php if ($animal['estado'] == 'Adopción'): ?>
                            <a href="proceso_adopcion.php?id_animal=<?= $animal['id_animal'] ?>" 
                               class="btn btn-success">
                                <i class="fas fa-home me-1"></i> Iniciar Adopción
                            </a>
                        <?php endif; ?>
                        <a href="editar_animal.php?id=<?= $animal['id_animal'] ?>" 
                           class="btn btn-purple">
                            <i class="fas fa-edit me-1"></i> Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>