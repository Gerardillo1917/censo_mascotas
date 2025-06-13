<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

$page_title = "Gestión de Adopciones";
include(BASE_PATH . '/includes/header.php');

// Obtener parámetros de búsqueda
$busqueda = [
    'estado' => $_GET['estado'] ?? '',
    'especie' => $_GET['especie'] ?? '',
    'localidad' => $_GET['localidad'] ?? ''
];

// Construir consulta
$sql = "SELECT * FROM animales_callejeros WHERE 1=1";
$params = [];
$types = "";

if (!empty($busqueda['estado'])) {
    $sql .= " AND estado = ?";
    $params[] = $busqueda['estado'];
    $types .= "s";
}

if (!empty($busqueda['especie'])) {
    $sql .= " AND especie = ?";
    $params[] = $busqueda['especie'];
    $types .= "s";
}

if (!empty($busqueda['localidad'])) {
    $sql .= " AND ubicacion_actual = ?";
    $params[] = $busqueda['localidad'];
    $types .= "s";
}

$sql .= " ORDER BY fecha_registro DESC";

// Ejecutar consulta
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$animales = $result->fetch_all(MYSQLI_ASSOC);

$localidades = obtener_localidades($conn);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-purple">
            <i class="fas fa-paw me-2"></i> Gestión de Adopciones
        </h2>
        <a href="registro_callejero.php" class="btn btn-purple">
            <i class="fas fa-plus me-1"></i> Nuevo Animal
        </a>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" name="estado">
                        <option value="">Todos</option>
                        <option value="Resguardado" <?= $busqueda['estado'] == 'Resguardado' ? 'selected' : '' ?>>Resguardado</option>
                        <option value="Tratamiento" <?= $busqueda['estado'] == 'Tratamiento' ? 'selected' : '' ?>>Tratamiento</option>
                        <option value="Adopción" <?= $busqueda['estado'] == 'Adopción' ? 'selected' : '' ?>>Adopción</option>
                        <option value="Fallecido" <?= $busqueda['estado'] == 'Fallecido' ? 'selected' : '' ?>>Fallecido</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Especie</label>
                    <select class="form-select" name="especie">
                        <option value="">Todas</option>
                        <option value="Perro" <?= $busqueda['especie'] == 'Perro' ? 'selected' : '' ?>>Perro</option>
                        <option value="Gato" <?= $busqueda['especie'] == 'Gato' ? 'selected' : '' ?>>Gato</option>
                        <option value="Ave" <?= $busqueda['especie'] == 'Ave' ? 'selected' : '' ?>>Ave</option>
                        <option value="Otro" <?= $busqueda['especie'] == 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Ubicación</label>
                    <select class="form-select" name="localidad">
                        <option value="">Todas</option>
                        <?php foreach ($localidades as $loc): ?>
                            <option value="<?= htmlspecialchars($loc['nombre']) ?>" 
                                <?= $busqueda['localidad'] == $loc['nombre'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($loc['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-purple me-2">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                    <a href="gestion_adopciones.php" class="btn btn-secondary">
                        <i class="fas fa-eraser me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Listado de animales -->
    <div class="card shadow-sm">
        <div class="card-header bg-purple text-white">
            <h5 class="mb-0">Animales Registrados</h5>
        </div>
        <div class="card-body">
            <?php if (empty($animales)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No se encontraron animales con los filtros seleccionados
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Especie</th>
                                <th>Raza</th>
                                <th>Género</th>
                                <th>Edad</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($animales as $animal): ?>
                                <tr>
                                    <td><?= htmlspecialchars($animal['folio']) ?></td>
                                    <td><?= htmlspecialchars($animal['especie']) ?></td>
                                    <td><?= htmlspecialchars($animal['raza'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($animal['genero']) ?></td>
                                    <td><?= htmlspecialchars($animal['edad_aproximada'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($animal['ubicacion_actual']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $animal['estado'] == 'Adopción' ? 'success' : 
                                            ($animal['estado'] == 'Tratamiento' ? 'warning' : 
                                            ($animal['estado'] == 'Fallecido' ? 'danger' : 'info')) 
                                        ?>">
                                            <?= htmlspecialchars($animal['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="detalle_animal.php?id=<?= $animal['id_animal'] ?>" 
                                           class="btn btn-sm btn-info" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($animal['estado'] == 'Adopción'): ?>
                                            <a href="proceso_adopcion.php?id_animal=<?= $animal['id_animal'] ?>" 
                                               class="btn btn-sm btn-success" title="Iniciar adopción">
                                                <i class="fas fa-home"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>