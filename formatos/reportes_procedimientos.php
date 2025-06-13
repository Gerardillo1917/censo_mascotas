<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

$localidades = obtener_localidades($conn);

// Obtener parámetros de filtrado
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$id_localidad = $_GET['id_localidad'] ?? '';
$tipo_procedimiento = $_GET['tipo_procedimiento'] ?? '';

// Validar fechas
if ($fecha_inicio > $fecha_fin) {
    $fecha_inicio = $fecha_fin;
}

// Construir consulta para obtener estadísticas
$sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN m.genero = 'Macho' THEN 1 ELSE 0 END) as machos,
        SUM(CASE WHEN m.genero = 'Hembra' THEN 1 ELSE 0 END) as hembras,
        DAY(s.fecha) as dia,
        l.nombre as localidad
    FROM salud_mascotas s
    JOIN mascotas m ON s.id_mascota = m.id_mascota
    JOIN localidades l ON s.id_localidad = l.id_localidad
    WHERE s.fecha BETWEEN ? AND ?";

$params = [$fecha_inicio, $fecha_fin];
$types = "ss";

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

$sql .= " GROUP BY DAY(s.fecha), s.id_localidad
          ORDER BY s.fecha, l.nombre";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = "Reportes de Procedimientos";
include(BASE_PATH . '/includes/header.php');
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-purple">
            <i class="fas fa-chart-bar me-2"></i> Reportes de Procedimientos
        </h2>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" name="fecha_inicio" 
                           value="<?= htmlspecialchars($fecha_inicio) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" name="fecha_fin" 
                           value="<?= htmlspecialchars($fecha_fin) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Localidad</label>
                    <select class="form-select" name="id_localidad">
                        <option value="">Todas</option>
                        <?php foreach ($localidades as $loc): ?>
                            <option value="<?= $loc['id_localidad'] ?>" 
                                <?= $id_localidad == $loc['id_localidad'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($loc['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo de Procedimiento</label>
                    <select class="form-select" name="tipo_procedimiento">
                        <option value="">Todos</option>
                        <option value="Esterilización" <?= $tipo_procedimiento == 'Esterilización' ? 'selected' : '' ?>>Esterilización</option>
                        <option value="Consulta" <?= $tipo_procedimiento == 'Consulta' ? 'selected' : '' ?>>Consulta</option>
                        <option value="Vacunación" <?= $tipo_procedimiento == 'Vacunación' ? 'selected' : '' ?>>Vacunación</option>
                        <option value="Adopción" <?= $tipo_procedimiento == 'Adopción' ? 'selected' : '' ?>>Adopción</option>
                    </select>
                </div>
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-purple me-2">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="reportes_procedimientos.php" class="btn btn-secondary">
                        <i class="fas fa-eraser me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados -->
    <div class="card shadow-sm">
        <div class="card-header bg-purple text-white">
            <h5 class="mb-0">Resumen de Procedimientos</h5>
        </div>
        <div class="card-body">
            <?php if (empty($resultados)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No se encontraron procedimientos con los filtros seleccionados
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Localidad</th>
                                <th>Total</th>
                                <th>Machos</th>
                                <th>Hembras</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_general = 0;
                            $total_machos = 0;
                            $total_hembras = 0;
                            
                            foreach ($resultados as $fila): 
                                $total_general += $fila['total'];
                                $total_machos += $fila['machos'];
                                $total_hembras += $fila['hembras'];
                                
                                $fecha_completa = $fecha_inicio;
                                $fecha_completa[8] = $fila['dia'][0];
                                $fecha_completa[9] = $fila['dia'][1];
                            ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($fecha_completa)) ?></td>
                                    <td><?= htmlspecialchars($fila['localidad']) ?></td>
                                    <td><?= $fila['total'] ?></td>
                                    <td><?= $fila['machos'] ?></td>
                                    <td><?= $fila['hembras'] ?></td>
                                    <td>
                                        <a href="detalle_procedimientos.php?fecha=<?= $fecha_completa ?>&localidad=<?= $fila['id_localidad'] ?>&tipo=<?= $tipo_procedimiento ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-list me-1"></i> Detalle
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2">TOTALES</th>
                                <th><?= $total_general ?></th>
                                <th><?= $total_machos ?></th>
                                <th><?= $total_hembras ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Botón de exportación -->
                <div class="d-flex justify-content-end mt-3">
                    <a href="exportar_reporte.php?<?= http_build_query($_GET) ?>" 
                       class="btn btn-success">
                        <i class="fas fa-file-excel me-1"></i> Exportar a Excel
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>