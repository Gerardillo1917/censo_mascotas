<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

$fecha = $_GET['fecha'] ?? '';
$id_localidad = $_GET['localidad'] ?? '';
$tipo_procedimiento = $_GET['tipo'] ?? '';

// Validar parámetros
if (empty($fecha) || !strtotime($fecha)) {
    redirigir_con_mensaje('reportes_procedimientos.php', 'danger', 'Fecha no válida');
}

// Obtener detalles de los procedimientos
$sql = "SELECT 
        s.id_salud, s.fecha, s.hora, s.tipo, s.estado,
        m.nombre as mascota, m.genero, m.edad, m.especie, m.raza,
        t.nombre as tutor_nombre, t.apellido_paterno, t.apellido_materno, t.telefono,
        l.nombre as localidad
    FROM salud_mascotas s
    JOIN mascotas m ON s.id_mascota = m.id_mascota
    JOIN tutores t ON m.id_tutor = t.id_tutor
    JOIN localidades l ON s.id_localidad = l.id_localidad
    WHERE s.fecha = ?";

$params = [$fecha];
$types = "s";

if (!empty($id_localidad)) {
    $sql .= " AND s.id_localidad = ?";
    $params[] = $id_localidad;
    $types .= "i";
}

if (!empty($tipo_procedimiento)) {
    $sql .= " AND s.tipo = ?";
    $params[] = $tipo_procedimiento;
    $types .= "s";
}

$sql .= " ORDER BY s.hora";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$procedimientos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = "Detalle de Procedimientos";
include(BASE_PATH . '/includes/header.php');
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-purple">
            <i class="fas fa-list me-2"></i> Detalle de Procedimientos
        </h2>
        <a href="reportes_procedimientos.php" class="btn btn-outline-purple">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($fecha)) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Localidad:</strong> <?= htmlspecialchars($procedimientos[0]['localidad'] ?? 'Todas') ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Tipo:</strong> <?= htmlspecialchars($tipo_procedimiento ?: 'Todos') ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-purple text-white">
            <h5 class="mb-0">Procedimientos Realizados</h5>
        </div>
        <div class="card-body">
            <?php if (empty($procedimientos)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No se encontraron procedimientos con los filtros seleccionados
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Mascota</th>
                                <th>Especie</th>
                                <th>Género</th>
                                <th>Tutor</th>
                                <th>Teléfono</th>
                                <th>Tipo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($procedimientos as $proc): ?>
                                <tr>
                                    <td><?= date('H:i', strtotime($proc['hora'])) ?></td>
                                    <td><?= htmlspecialchars($proc['mascota']) ?></td>
                                    <td><?= htmlspecialchars($proc['especie']) ?></td>
                                    <td><?= htmlspecialchars($proc['genero']) ?></td>
                                    <td>
                                        <?= htmlspecialchars(
                                            $proc['tutor_nombre'] . ' ' . 
                                            $proc['apellido_paterno'] . ' ' . 
                                            ($proc['apellido_materno'] ?? '')
                                        ) ?>
                                    </td>
                                    <td><?= htmlspecialchars($proc['telefono']) ?></td>
                                    <td><?= htmlspecialchars($proc['tipo']) ?></td>
                                    <td>
                                        <a href="salud_animal.php?id=<?= $proc['id_mascota'] ?>" 
                                           class="btn btn-sm btn-info" title="Ver historial">
                                            <i class="fas fa-history"></i>
                                        </a>
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