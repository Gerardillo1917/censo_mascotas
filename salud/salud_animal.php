<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Validar ID de mascota
$id_mascota = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_mascota <= 0) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Mascota no especificada');
}

// Obtener datos de mascota y procedimientos
try {
    // Datos básicos de mascota y tutor
    $stmt_mascota = $conn->prepare("SELECT m.*, t.id_tutor, t.nombre AS tutor_nombre, 
                                  t.apellido_paterno, t.apellido_materno 
                                  FROM mascotas m 
                                  JOIN tutores t ON m.id_tutor = t.id_tutor 
                                  WHERE m.id_mascota = ?");
    $stmt_mascota->bind_param("i", $id_mascota);
    $stmt_mascota->execute();
    $mascota = $stmt_mascota->get_result()->fetch_assoc();
    $stmt_mascota->close();

    if (!$mascota) {
        redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Mascota no encontrada');
    }

    // Vacunas (para dropdown)
    $stmt_vac = $conn->prepare("SELECT * FROM vacunas WHERE id_mascota = ? ORDER BY fecha_aplicacion DESC LIMIT 5");
    $stmt_vac->bind_param("i", $id_mascota);
    $stmt_vac->execute();
    $vacunas = $stmt_vac->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_vac->close();

    // Historial médico unificado (consultas + esterilizaciones)
    $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $por_pagina = 10;
    $offset = ($pagina - 1) * $por_pagina;

    $stmt_historial = $conn->prepare("(SELECT id_consulta AS id, 'Consulta' AS tipo, fecha, diagnostico 
                                     FROM consultas_medicas WHERE id_mascota = ?)
                                     UNION ALL
                                     (SELECT id_esterilizacion AS id, 'Esterilización' AS tipo, fecha_procedimiento AS fecha, tipo_procedimiento AS diagnostico 
                                     FROM esterilizaciones WHERE id_mascota = ?)
                                     ORDER BY fecha DESC LIMIT ? OFFSET ?");
    $stmt_historial->bind_param("iiii", $id_mascota, $id_mascota, $por_pagina, $offset);
    $stmt_historial->execute();
    $historial = $stmt_historial->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_historial->close();

    // Total de registros para paginación
    $stmt_total = $conn->prepare("SELECT (SELECT COUNT(*) FROM consultas_medicas WHERE id_mascota = ?) + 
                                (SELECT COUNT(*) FROM esterilizaciones WHERE id_mascota = ?) AS total");
    $stmt_total->bind_param("ii", $id_mascota, $id_mascota);
    $stmt_total->execute();
    $total_registros = $stmt_total->get_result()->fetch_assoc()['total'];
    $stmt_total->close();
    $total_paginas = ceil($total_registros / $por_pagina);

} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Error al cargar datos');
}

$page_title = "Historial - " . htmlspecialchars($mascota['nombre']);
include(BASE_PATH . '/includes/header.php');
?>

<div class="container mt-4">
    <!-- Ficha de mascota -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-purple text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= htmlspecialchars($mascota['nombre']) ?></h4>
            <div class="d-flex gap-2">
                <span class="badge bg-white text-dark">
                    <i class="fas fa-paw me-1"></i> <?= htmlspecialchars($mascota['especie']) ?>
                </span>
                <span class="badge bg-white text-dark">
                    <i class="fas fa-<?= $mascota['genero'] === 'Hembra' ? 'venus' : 'mars' ?> me-1"></i>
                    <?= htmlspecialchars($mascota['genero']) ?>
                </span>
            </div>
        </div>
        
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <h5 class="text-purple"><i class="fas fa-info-circle me-2"></i>Información Básica</h5>
                        <div class="info-label">Tutor</div>
                        <div class="info-value">
                            <?= htmlspecialchars($mascota['tutor_nombre'] . ' ' . $mascota['apellido_paterno']) ?>
                        </div>
                        
                        <div class="info-label">Edad</div>
                        <div class="info-value"><?= htmlspecialchars($mascota['edad']) ?> años</div>
                        
                        <div class="info-label">Raza</div>
                        <div class="info-value">
                            <?= !empty($mascota['raza']) ? htmlspecialchars($mascota['raza']) : 'No especificada' ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <h5 class="text-purple"><i class="fas fa-heartbeat me-2"></i>Salud</h5>
                        <div class="info-label">Estado</div>
                        <div class="info-value">
                            <span class="badge bg-<?= $mascota['estado'] == 'Vivo' ? 'success' : 'danger' ?>">
                                <?= htmlspecialchars($mascota['estado']) ?>
                            </span>
                        </div>
                        
                        <div class="info-label">Vacunación</div>
                        <div class="info-value">
                            <?php if (!empty($vacunas)): ?>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-purple dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-syringe me-1"></i> 
                                        <?= count($vacunas) ?> vacuna(s)
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($vacunas as $vacuna): ?>
                                            <li>
                                                <div class="dropdown-item">
                                                    <div class="fw-bold"><?= htmlspecialchars($vacuna['nombre_vacuna']) ?></div>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y', strtotime($vacuna['fecha_aplicacion'])) ?>
                                                        <?php if (!empty($vacuna['comentarios'])): ?>
                                                            - <?= htmlspecialchars($vacuna['comentarios']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">No registradas</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="info-label">Desparasitación</div>
                        <div class="info-value">
                            <?php if (!empty($mascota['ultima_desparasitacion_fecha'])): ?>
                                <span class="badge bg-success">Completa</span>
                                <small class="text-muted">
                                    <?= date('d/m/Y', strtotime($mascota['ultima_desparasitacion_fecha'])) ?>
                                </small>
                            <?php else: ?>
                                <span class="badge bg-warning">Pendiente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Opciones de registro médico -->
    <div class="d-flex flex-wrap gap-3 mb-4">
        <a href="<?= BASE_URL ?>/salud/nueva_consulta.php?id=<?= $id_mascota ?>" class="btn btn-purple">
            <i class="fas fa-stethoscope me-1"></i> Nueva Consulta
        </a>
        <a href="<?= BASE_URL ?>/salud/esterilizacion/esterilizacion.php?id=<?= $id_mascota ?>" class="btn btn-purple">
            <i class="fas fa-cut me-1"></i> Esterilización
        </a>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalVacuna">
            <i class="fas fa-syringe me-1"></i> Vacuna Antirrábica
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDesparasitacion">
            <i class="fas fa-bug me-1"></i> Desparasitación
        </button>
    </div>

    <!-- Modal Vacuna Antirrábica -->
    <div class="modal fade" id="modalVacuna" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="<?= BASE_URL ?>/salud/procesar_vacuna.php">
                    <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
                    <input type="hidden" name="tipo_vacuna" value="Antirrábica">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Registrar Vacuna Antirrábica</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Fecha de aplicación*</label>
                            <input type="date" class="form-control" name="fecha_aplicacion" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comentarios</label>
                            <textarea class="form-control" name="comentarios" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Desparasitación -->
    <div class="modal fade" id="modalDesparasitacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="<?= BASE_URL ?>/salud/procesar_desparasitacion.php">
                    <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Registrar Desparasitación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Producto utilizado*</label>
                            <input type="text" class="form-control" name="producto" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha*</label>
                            <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Historial Médico -->
    <div class="card shadow-sm">
        <div class="card-header bg-purple text-white">
            <h4 class="mb-0"><i class="fas fa-history me-2"></i> Historial Médico</h4>
        </div>
        <div class="card-body">
            <?php if (empty($historial)): ?>
                <div class="alert alert-info text-center py-4">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h5>No hay registros médicos</h5>
                    <p class="mb-0">Comience agregando una consulta o esterilización</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($historial as $registro): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-left-<?= $registro['tipo'] === 'Consulta' ? 'primary' : 'danger' ?> shadow-sm">
                                <div class="card-header d-flex justify-content-between align-items-center bg-<?= $registro['tipo'] === 'Consulta' ? 'info' : 'danger' ?> text-white">
                                    <div>
                                        <i class="fas fa-<?= $registro['tipo'] === 'Consulta' ? 'stethoscope' : 'cut' ?> me-2"></i>
                                        <?= $registro['tipo'] ?>
                                    </div>
                                    <small><?= date('d/m/Y', strtotime($registro['fecha'])) ?></small>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><strong>Motivo:</strong> <?= htmlspecialchars($registro['diagnostico']) ?></p>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between">
                                        <a href="ver_detalles_<?= strtolower($registro['tipo']) ?>.php?id=<?= $registro['id'] ?>" 
                                           class="btn btn-sm btn-outline-purple">
                                           <i class="fas fa-eye me-1"></i> Ver Detalles
                                        </a>
                                        <a href="eliminar_procedimiento.php?tipo=<?= $registro['tipo'] ?>&id=<?= $registro['id'] ?>&id_mascota=<?= $id_mascota ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('¿Eliminar este registro?')">
                                           <i class="fas fa-trash me-1"></i> Eliminar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagina > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?id=<?= $id_mascota ?>&pagina=<?= $pagina-1 ?>">Anterior</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                    <a class="page-link" href="?id=<?= $id_mascota ?>&pagina=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagina < $total_paginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?id=<?= $id_mascota ?>&pagina=<?= $pagina+1 ?>">Siguiente</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>