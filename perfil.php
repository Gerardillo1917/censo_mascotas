<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

$id_tutor = $_GET['id'] ?? 0;

// Obtener tutor
$stmt_tutor = $conn->prepare("SELECT * FROM tutores WHERE id_tutor = ?");
$stmt_tutor->bind_param("i", $id_tutor);
$stmt_tutor->execute();
$tutor = $stmt_tutor->get_result()->fetch_assoc();
$stmt_tutor->close();

if (!$tutor) {
    header("Location: buscar.php");
    exit();
}

// Obtener mascotas con sus vacunas y datos de baja
$mascotas = [];
$stmt_mascotas = $conn->prepare("
    SELECT m.*, 
           v.id_vacuna, v.nombre_vacuna, v.fecha_aplicacion, v.comentarios AS comentarios_vacuna
    FROM mascotas m
    LEFT JOIN vacunas v ON m.id_mascota = v.id_mascota
    WHERE m.id_tutor = ?
    ORDER BY m.id_mascota, v.fecha_aplicacion DESC
");
$stmt_mascotas->bind_param("i", $id_tutor);
$stmt_mascotas->execute();
$result = $stmt_mascotas->get_result();

// Organizar mascotas con sus vacunas
$current_mascota = null;
while ($row = $result->fetch_assoc()) {
    if ($current_mascota === null || $current_mascota['id_mascota'] != $row['id_mascota']) {
        if ($current_mascota !== null) {
            $mascotas[] = $current_mascota;
        }
        $current_mascota = $row;
        $current_mascota['vacunas'] = [];
        unset($current_mascota['id_vacuna']);
        unset($current_mascota['nombre_vacuna']);
        unset($current_mascota['fecha_aplicacion']);
        unset($current_mascota['comentarios_vacuna']);
    }
    
    if (!empty($row['id_vacuna'])) {
        $current_mascota['vacunas'][] = [
            'id_vacuna' => $row['id_vacuna'],
            'nombre_vacuna' => $row['nombre_vacuna'],
            'fecha_aplicacion' => $row['fecha_aplicacion'],
            'comentarios' => $row['comentarios_vacuna']
        ];
    }
}

if ($current_mascota !== null) {
    $mascotas[] = $current_mascota;
}
$stmt_mascotas->close();

$page_title = "Perfil de " . $tutor['nombre'];
include(BASE_PATH . '/includes/header.php');
?>

<style>
    .bg-purple { background-color: #8b0180; }
    .text-purple { color: #8b0180; }
    .border-purple { border-color: #8b0180 !important; }
    .btn-purple { 
        background-color: #8b0180; 
        color: white;
    }
    .btn-purple:hover {
        background-color: #6a015f;
        color: white;
    }
    /* Estilos optimizados para las fichas */
    .ficha-mascota {
        transition: all 0.3s ease;
        height: 100%;
    }
    .ficha-mascota:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .ficha-mascota-img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 5px 5px 0 0;
    }
    .ficha-mascota-body {
        padding: 1.25rem;
    }
    .vacuna-item {
        border-left: 3px solid #8b0180;
        padding: 8px 12px;
        margin-bottom: 8px;
        background-color: #f9f9f9;
        border-radius: 4px;
    }
    .badge-estado {
        font-size: 0.8rem;
        padding: 5px 10px;
    }
    /* Estilo fijo para la foto del tutor */
    .foto-tutor-container {
        width: 100%;
        height: 200px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .foto-tutor {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    
    @media (max-width: 768px) {
        .ficha-mascota-img {
            height: 150px;
        }
        .ficha-mascota-body {
            padding: 1rem;
        }
    }
    /* Estilos para reportes */
.list-group-item {
    transition: all 0.2s ease;
}
.list-group-item:hover {
    background-color: #f8f9fa;
    transform: translateX(3px);
}
.btn-outline-purple {
    color: #8b0180;
    border-color: #8b0180;
}
.btn-outline-purple:hover {
    background-color: #8b0180;
    color: white;
}
</style>

<!-- Sección del Tutor -->
<div class="row mb-4">
    <div class="col-md-4 text-center mb-3">
        <?php if (!empty($tutor['foto_ruta'])): ?>
            <div class="foto-tutor-container">
                <img src="<?= htmlspecialchars($tutor['foto_ruta']) ?>" class="foto-tutor img-thumbnail border-purple mb-2">
            </div>
        <?php else: ?>
            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                <span class="text-muted">Sin foto</span>
            </div>
        <?php endif; ?>
        <a href="editar_tutor.php?id=<?= $id_tutor ?>" class="btn btn-purple w-100 mb-2">Editar Tutor</a>
        <a href="exportar_pdf.php?id=<?= $mascotas[0]['id_mascota'] ?? '' ?>" class="btn btn-info w-100">
            <i class="fas fa-file-pdf me-1"></i> Descargar Perfil
        </a>
    </div>  
    
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-purple text-white">
                <h4 class="mb-0">Datos del Tutor</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Nombre:</label>
                        <p><?= htmlspecialchars($tutor['nombre']) ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Apellido Paterno:</label>
                        <p><?= htmlspecialchars($tutor['apellido_paterno']) ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Apellido Materno:</label>
                        <p><?= !empty($tutor['apellido_materno']) ? htmlspecialchars($tutor['apellido_materno']) : 'N/A' ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="fw-bold">Edad:</label>
                        <p><?= !empty($tutor['edad']) ? htmlspecialchars($tutor['edad']) : 'N/A' ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="fw-bold">Teléfono:</label>
                        <p><?= htmlspecialchars($tutor['telefono']) ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Email:</label>
                        <p><?= !empty($tutor['email']) ? htmlspecialchars($tutor['email']) : 'N/A' ?></p>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="fw-bold">Dirección:</label>
                        <p class="mb-1"><?= htmlspecialchars($tutor['calle']) ?> <?= htmlspecialchars($tutor['numero_exterior']) ?></p>
                        <?php if (!empty($tutor['numero_interior'])): ?>
                            <p class="mb-1">Interior: <?= htmlspecialchars($tutor['numero_interior']) ?></p>
                        <?php endif; ?>
                        <p class="mb-0"><?= htmlspecialchars($tutor['colonia']) ?>, C.P. <?= htmlspecialchars($tutor['codigo_postal']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Mascotas -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-purple text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Mascotas Registradas</h4>
        <a href="agregar_mascota.php?tutor=<?= $id_tutor ?>" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-1"></i> Agregar
        </a>
    </div>
    <div class="card-body">
        <?php if (count($mascotas) > 0): ?>
            <div class="row g-4">
                <?php foreach ($mascotas as $mascota): ?>
                    <div class="col-lg-6 col-md-12">
                        <div class="card h-100 ficha-mascota">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?= htmlspecialchars($mascota['nombre']) ?></h5>
                                <span class="badge badge-estado bg-<?= $mascota['estado'] == 'Vivo' ? 'success' : ($mascota['estado'] == 'Extraviado' ? 'warning' : 'danger') ?>">
                                    <?= htmlspecialchars($mascota['estado']) ?>
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="row g-0">
                                    <div class="col-md-5">
                                        <?php if (!empty($mascota['foto_ruta'])): ?>
                                            <img src="<?= htmlspecialchars($mascota['foto_ruta']) ?>" class="ficha-mascota-img">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center ficha-mascota-img">
                                                <span class="text-muted">Sin foto</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="ficha-mascota-body">
                                            <div class="row small g-2 mb-2">
                                                <div class="col-6">
                                                    <span class="fw-bold">Especie:</span> <?= htmlspecialchars($mascota['especie']) ?>
                                                </div>
                                                <div class="col-6">
                                                    <span class="fw-bold">Raza:</span> <?= !empty($mascota['raza']) ? htmlspecialchars($mascota['raza']) : 'N/A' ?>
                                                </div>
                                                <div class="col-6">
                                                    <span class="fw-bold">Edad:</span> <?= $mascota['edad'] ?> años
                                                </div>
                                                <div class="col-6">
                                                    <span class="fw-bold">Género:</span> <?= htmlspecialchars($mascota['genero']) ?>
                                                </div>
                                                <div class="col-6">
                                                    <span class="fw-bold">Esterilizado:</span> <?= $mascota['esterilizado'] ? 'Sí' : 'No' ?>
                                                </div>
                                                <?php if ($mascota['incapacidad']): ?>
                                                    <div class="col-12">
                                                        <span class="fw-bold">Incapacidad:</span> 
                                                        <?= !empty($mascota['descripcion_incapacidad']) ? htmlspecialchars($mascota['descripcion_incapacidad']) : 'Sí' ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Información de estado -->
                                            <?php if ($mascota['estado'] != 'Vivo'): ?>
                                                <div class="col-12 mt-3">
                                                    <div class="alert alert-<?= $mascota['estado'] == 'Extraviado' ? 'warning' : 'danger' ?> small py-2 mb-2">
                                                        <div class="d-flex justify-content-between">
                                                            <strong>Estado: <?= htmlspecialchars($mascota['estado']) ?></strong>
                                                            <?php if (!empty($mascota['fecha_baja'])): ?>
                                                                <span class="text-muted"><?= date('d/m/Y', strtotime($mascota['fecha_baja'])) ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <?php if (!empty($mascota['motivo_baja'])): ?>
                                                            <div class="mt-1"><strong>Motivo:</strong> <?= htmlspecialchars($mascota['motivo_baja']) ?></div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($mascota['comentarios_baja'])): ?>
                                                            <div class="mt-1"><strong>Comentarios:</strong> <?= htmlspecialchars($mascota['comentarios_baja']) ?></div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($mascota['estado'] == 'Extraviado' && !empty($mascota['fecha_encontrado'])): ?>
                                                            <div class="mt-2 alert alert-success small py-2">
                                                                <div class="d-flex justify-content-between">
                                                                    <strong>Encontrado el:</strong>
                                                                    <span><?= date('d/m/Y', strtotime($mascota['fecha_encontrado'])) ?></span>
                                                                </div>
                                                                <?php if (!empty($mascota['comentarios_encontrado'])): ?>
                                                                    <div class="mt-1"><strong>Detalles:</strong> <?= htmlspecialchars($mascota['comentarios_encontrado']) ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Vacunas -->
                                            <?php if (!empty($mascota['vacunas'])): ?>
                                                <div class="mt-3">
                                                    <h6 class="fw-bold border-bottom pb-1">Vacunas</h6>
                                                    <div class="overflow-auto" style="max-height: 120px;">
                                                        <?php foreach ($mascota['vacunas'] as $vacuna): ?>
                                                            <div class="vacuna-item">
                                                                <div class="d-flex justify-content-between small">
                                                                    <div>
                                                                        <strong><?= htmlspecialchars($vacuna['nombre_vacuna']) ?></strong>
                                                                        <div class="text-muted"><?= date('d/m/Y', strtotime($vacuna['fecha_aplicacion'])) ?></div>
                                                                    </div>
                                                                    <?php if (!empty($vacuna['comentarios'])): ?>
                                                                        <span class="badge bg-info"><?= htmlspecialchars($vacuna['comentarios']) ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Botones de Acción -->
                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                        <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $mascota['id_mascota'] ?>" class="btn btn-sm btn-purple">
                                            <i class="fas fa-heartbeat me-1"></i> Salud
                                        </a>
                                        
                                        <?php 
                                        // Verificación mejorada del estado
                                        $estado = trim(strtolower($mascota['estado']));
                                        if ($estado === 'vivo'): ?>
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" 
                                               data-bs-target="#bajaModal<?= $mascota['id_mascota'] ?>">
                                              <i class="fas fa-exclamation-triangle me-1"></i> Baja
                                            </button>
                                        <?php elseif ($estado === 'extraviado'): ?>
                                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" 
                                               data-bs-target="#encontradoModal<?= $mascota['id_mascota'] ?>">
                                              <i class="fas fa-check me-1"></i> Encontrado
                                            </button>
                                        <?php endif; ?>
                                        
                                        <a href="editar_mascota.php?id=<?= $mascota['id_mascota'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit me-1"></i> Editar
                                        </a>
                                    </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Baja -->
                    <div class="modal fade" id="bajaModal<?= $mascota['id_mascota'] ?>" tabindex="-1" aria-labelledby="bajaModalLabel<?= $mascota['id_mascota'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-purple text-white">
                                    <h5 class="modal-title" id="bajaModalLabel<?= $mascota['id_mascota'] ?>">Dar de baja a <?= htmlspecialchars($mascota['nombre']) ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="procesar_baja.php" method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="id_mascota" value="<?= $mascota['id_mascota'] ?>">
                                        <input type="hidden" name="id_tutor" value="<?= $id_tutor ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Motivo de la baja:</label>
                                            <select class="form-select" name="motivo_baja" required>
                                                <option value="">Seleccione un motivo</option>
                                                <option value="Muerte">Muerte</option>
                                                <option value="Extraviado">Extraviado</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Comentarios:</label>
                                            <textarea class="form-control" name="comentarios_baja" rows="3" placeholder="Detalles adicionales..."></textarea>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" name="confirmacion" id="confirmacion<?= $mascota['id_mascota'] ?>" required>
                                            <label class="form-check-label" for="confirmacion<?= $mascota['id_mascota'] ?>">Confirmo que la información es correcta</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-danger">Confirmar Baja</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal de Mascota Encontrada -->
                    <div class="modal fade" id="encontradoModal<?= $mascota['id_mascota'] ?>" tabindex="-1" aria-labelledby="encontradoModalLabel<?= $mascota['id_mascota'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="encontradoModalLabel<?= $mascota['id_mascota'] ?>">Marcar como encontrado a <?= htmlspecialchars($mascota['nombre']) ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="procesar_encontrado.php" method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="id_mascota" value="<?= $mascota['id_mascota'] ?>">
                                        <input type="hidden" name="id_tutor" value="<?= $id_tutor ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Comentarios:</label>
                                            <textarea class="form-control" name="comentarios_encontrado" rows="3" placeholder="Detalles sobre cómo fue encontrado..."></textarea>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" name="confirmacion" id="confirmacionEncontrado<?= $mascota['id_mascota'] ?>" required>
                                            <label class="form-check-label" for="confirmacionEncontrado<?= $mascota['id_mascota'] ?>">Confirmo que la mascota ha sido encontrada</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-success">Confirmar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No hay mascotas registradas.</div>
        <?php endif; ?>
    </div>
</div>
<div id="reportes">
<!-- Sección de Reportes -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-purple text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Reportes Ciudadanos</h4>
        <div>
            <a href="<?= BASE_URL ?>/reportes/reportar_tutor.php?id=<?= $id_tutor ?>" class="btn btn-sm btn-light me-2">
                <i class="fas fa-exclamation-triangle me-1"></i> Reportar Tutor
            </a>
            <?php if (count($mascotas) > 0): ?>
                <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownReportarMascota" data-bs-toggle="dropdown">
                    <i class="fas fa-paw me-1"></i> Reportar Mascota
                </button>
                <ul class="dropdown-menu">
                    <?php foreach ($mascotas as $mascota): ?>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/reportes/reportar_mascota.php?id=<?= $mascota['id_mascota'] ?>">
                                <?= htmlspecialchars($mascota['nombre']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php
        // Configuración de paginación
        $reportes_por_pagina = 5;
        $pagina_actual = isset($_GET['pagina_reportes']) ? max(1, intval($_GET['pagina_reportes'])) : 1;
        $offset = ($pagina_actual - 1) * $reportes_por_pagina;
        
        // Obtener reportes paginados
        $reportes = obtener_reportes_tutor($id_tutor, $reportes_por_pagina, $offset);
        $total_reportes = obtener_total_reportes($id_tutor);
        $total_paginas = ceil($total_reportes / $reportes_por_pagina);
        
        if (empty($reportes)): ?>
            <div class="alert alert-info">No hay reportes registrados.</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($reportes as $reporte): ?>
                    <a href="<?= BASE_URL ?>/reportes/ver_reporte.php?id=<?= $reporte['id_reporte'] ?>" 
                       class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($reporte['titulo_reporte']) ?></h6>
                                <small class="text-muted">
                                    <?= date('d/m/Y H:i', strtotime($reporte['fecha_reporte'])) ?>
                                    - <?= htmlspecialchars($reporte['tipo_reporte']) ?>
                                </small>
                            </div>
                            <span class="badge bg-<?= 
                                $reporte['estado'] == 'pendiente' ? 'warning' : 
                                ($reporte['estado'] == 'investigando' ? 'info' : 'success') 
                            ?>">
                                <?= ucfirst($reporte['estado']) ?>
                            </span>
                        </div>
                        <p class="mb-1 mt-2 text-truncate"><?= htmlspecialchars($reporte['descripcion']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Paginación simple -->
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php if ($pagina_actual > 1): ?>
                        <li class="page-item">
                            <a class="page-link" 
                               href="?id=<?= $id_tutor ?>&pagina_reportes=<?= $pagina_actual - 1 ?>#reportes">
                                &laquo; Anterior
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
                            <a class="page-link" 
                               href="?id=<?= $id_tutor ?>&pagina_reportes=<?= $i ?>#reportes">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($pagina_actual < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link" 
                               href="?id=<?= $id_tutor ?>&pagina_reportes=<?= $pagina_actual + 1 ?>#reportes">
                                Siguiente &raquo;
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
</div>
<script>
// Desplazamiento suave al anchor si viene de paginación
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#reportes') {
        setTimeout(() => {
            document.querySelector('#reportes').scrollIntoView({
                behavior: 'smooth'
            });
        }, 100);
    }
});
</script>
<?php include(BASE_PATH . '/includes/footer.php'); ?>