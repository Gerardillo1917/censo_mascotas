<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/includes/auth.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

//verificarAutenticacion();

if (!isset($_GET['id'])) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Mascota no especificada');
}

$id_mascota = intval($_GET['id']);
$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : 'historial';

// Validar sección
$secciones_validas = ['historial', 'consulta', 'vacunacion', 'urgencia', 'procedimiento'];
if (!in_array($seccion, $secciones_validas)) {
    redirigir_con_mensaje(BASE_URL . "/salud/salud_animal.php?id=$id_mascota", 'warning', 'Sección no válida');
}

// Obtener datos de la mascota, tutor y vacunas
try {
    // Datos básicos de mascota y tutor
    $stmt = $conn->prepare("SELECT m.*, t.nombre AS tutor_nombre, 
                          CONCAT(t.nombre, ' ', t.apellido_paterno, ' ', IFNULL(t.apellido_materno, '')) AS tutor_completo
                          FROM mascotas m 
                          JOIN tutores t ON m.id_tutor = t.id_tutor 
                          WHERE m.id_mascota = ?");
    $stmt->bind_param("i", $id_mascota);
    $stmt->execute();
    $mascota = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$mascota) {
        redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Mascota no encontrada');
    }

    // Obtener vacunas de la mascota
    $stmt_vacunas = $conn->prepare("SELECT * FROM vacunas WHERE id_mascota = ? ORDER BY fecha_aplicacion DESC");
    $stmt_vacunas->bind_param("i", $id_mascota);
    $stmt_vacunas->execute();
    $vacunas = $stmt_vacunas->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_vacunas->close();

} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Error al cargar datos de la mascota');
}

$page_title = "Historial Médico - " . htmlspecialchars($mascota['nombre']);
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
    .nav-tabs .nav-link {
        color: #6c757d;
        border: 1px solid transparent;
    }
    .nav-tabs .nav-link.active {
        color: #8b0180;
        border-color: #8b0180;
        font-weight: bold;
    }
    .historial-item {
        border-left: 4px solid #8b0180;
        transition: all 0.3s ease;
        margin-bottom: 10px;
    }
    .historial-item:hover {
        background-color: #f8f9fa !important;
    }
    .ficha-mascota-img {
        max-height: 150px;
        object-fit: cover;
    }
    .historial-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .historial-compact {
        padding: 10px;
        cursor: pointer;
    }
    .historial-compact-title {
        font-weight: bold;
        color: #8b0180;
        margin-bottom: 5px;
    }
    .historial-compact-date {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .historial-compact-resumen {
        font-size: 0.9rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .vacuna-item {
        border-left: 3px solid #8b0180;
        padding: 8px 12px;
        margin-bottom: 8px;
        background-color: #f9f9f9;
        border-radius: 4px;
    }
    .vacuna-nombre {
        font-weight: bold;
    }
    .vacuna-fecha {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .vacuna-comentarios {
        font-size: 0.85rem;
        color: #495057;
    }
    @media (max-width: 768px) {
        .ficha-mascota-img {
            max-height: 100px;
        }
    }
</style>

<div class="container mt-4">
    <!-- Ficha de la mascota -->
    <div class="card shadow-sm mb-4 border-purple">
        <div class="card-header bg-purple text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Ficha de <?= htmlspecialchars($mascota['nombre']) ?></h4>
                <span class="badge bg-light text-dark">Veterinario</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <!-- Foto -->
                <div class="col-md-2 text-center mb-3 mb-md-0">
                    <?php if (!empty($mascota['foto_ruta'])): ?>
                        <img src="<?= htmlspecialchars($mascota['foto_ruta']) ?>" class="img-thumbnail ficha-mascota-img">
                    <?php else: ?>
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                            <span class="text-muted">Sin foto</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Datos básicos -->
                <div class="col-md-10">
                    <div class="row">
                        <div class="col-6 col-md-3 mb-2">
                            <label class="small text-muted mb-0">Especie</label>
                            <p class="mb-0"><?= htmlspecialchars($mascota['especie']) ?></p>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="small text-muted mb-0">Raza</label>
                            <p class="mb-0"><?= !empty($mascota['raza']) ? htmlspecialchars($mascota['raza']) : 'N/A' ?></p>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <label class="small text-muted mb-0">Edad</label>
                            <p class="mb-0"><?= $mascota['edad'] ?> meses</p>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <label class="small text-muted mb-0">Género</label>
                            <p class="mb-0"><?= htmlspecialchars($mascota['genero']) ?></p>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <label class="small text-muted mb-0">Estado</label>
                            <span class="badge bg-<?= $mascota['estado'] == 'Vivo' ? 'success' : 'danger' ?>">
                                <?= htmlspecialchars($mascota['estado']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Segunda fila de datos -->
                    <div class="row mt-2">
                        <div class="col-6 col-md-3 mb-2">
                            <label class="small text-muted mb-0">Esterilizado</label>
                            <p class="mb-0"><?= $mascota['esterilizado'] ? 'Sí' : 'No' ?></p>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="small text-muted mb-0">Vacunas</label>
                            <div class="mb-0">
                                <?php if (!empty($vacunas)): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-purple dropdown-toggle" type="button" id="dropdownVacunas" data-bs-toggle="dropdown" aria-expanded="false">
                                            <?= count($vacunas) ?> registradas
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownVacunas" style="max-height: 300px; overflow-y: auto;">
                                            <?php foreach ($vacunas as $vacuna): ?>
                                                <li>
                                                    <div class="dropdown-item">
                                                        <div class="vacuna-nombre"><?= htmlspecialchars($vacuna['nombre_vacuna']) ?></div>
                                                        <div class="vacuna-fecha"><?= date('d/m/Y', strtotime($vacuna['fecha_aplicacion'])) ?></div>
                                                        <?php if (!empty($vacuna['comentarios'])): ?>
                                                            <div class="vacuna-comentarios"><?= htmlspecialchars($vacuna['comentarios']) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Sin registrar</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="small text-muted mb-0">Tutor</label>
                            <p class="mb-0"><?= htmlspecialchars($mascota['tutor_completo']) ?></p>
                        </div>
                        <?php if ($mascota['incapacidad']): ?>
                            <div class="col-6 col-md-3 mb-2">
                                <label class="small text-muted mb-0">Incapacidad</label>
                                <p class="mb-0"><?= !empty($mascota['descripcion_incapacidad']) ? htmlspecialchars($mascota['descripcion_incapacidad']) : 'Sí' ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menú de pestañas -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= ($seccion == 'historial') ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>&seccion=historial">
               <i class="fas fa-history me-1"></i> Historial Médico
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($seccion == 'consulta') ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>&seccion=consulta">
               <i class="fas fa-stethoscope me-1"></i> Consulta
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($seccion == 'vacunacion') ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>&seccion=vacunacion">
               <i class="fas fa-syringe me-1"></i> Vacunación
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($seccion == 'urgencia') ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>&seccion=urgencia">
               <i class="fas fa-ambulance me-1"></i> Urgencia
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($seccion == 'procedimiento') ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>&seccion=procedimiento">
               <i class="fas fa-scalpel me-1"></i> Cirugía
            </a>
        </li>
    </ul>

    <!-- Contenido dinámico -->
    <div class="card shadow-sm">
        <div class="card-body">
            <?php
            switch ($seccion) {
                case 'historial':
                    // Obtener historial médico
                    $stmt = $conn->prepare("SELECT * FROM salud_mascotas WHERE id_mascota = ? ORDER BY fecha DESC");
                    $stmt->bind_param("i", $id_mascota);
                    $stmt->execute();
                    $historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();
                    
                    if (count($historial) > 0) {
                        echo '<div class="mb-4">';
                        echo '<h4 class="text-purple mb-3"><i class="fas fa-clipboard-list me-2"></i>Historial Clínico Completo</h4>';
                        
                        foreach ($historial as $registro) {
                            echo '<div class="historial-item p-3 bg-light rounded" onclick="window.location=\''.BASE_URL.'/salud/detalle_consulta.php?id='.$registro['id_interaccion'].'\'">';
                            echo '<div class="d-flex justify-content-between align-items-start">';
                            echo '<div>';
                            echo '<h5 class="text-purple">' . htmlspecialchars(ucfirst($registro['tipo'])) . '</h5>';
                            echo '<p class="text-muted small mb-1"><i class="far fa-calendar-alt me-1"></i>' . date('d/m/Y H:i', strtotime($registro['fecha'])) . '</p>';
                            
                            // Contenido específico según el tipo de registro
                            switch ($registro['tipo']) {
                                case 'Consulta':
                                    echo '<p class="mb-1"><strong><i class="fas fa-comment-medical me-1"></i>Motivo:</strong> ' . htmlspecialchars($registro['motivo']) . '</p>';
                                    if (!empty($registro['signos_clinicos'])) {
                                        echo '<p class="mb-1"><strong><i class="fas fa-notes-medical me-1"></i>Signos clínicos:</strong> ' . htmlspecialchars($registro['signos_clinicos']) . '</p>';
                                    }
                                    break;
                                    
                                case 'Vacunación':
                                    echo '<p class="mb-1"><strong><i class="fas fa-syringe me-1"></i>Vacuna:</strong> ' . htmlspecialchars($registro['tipo_vacuna']) . '</p>';
                                    break;
                                    
                                case 'Urgencia':
                                    echo '<p class="mb-1"><strong><i class="fas fa-ambulance me-1"></i>Motivo:</strong> ' . htmlspecialchars($registro['motivo']) . '</p>';
                                    break;
                                    
                                case 'Procedimiento':
                                    echo '<p class="mb-1"><strong><i class="fas fa-procedures me-1"></i>Tipo:</strong> ' . htmlspecialchars($registro['tipo_procedimiento']) . '</p>';
                                    break;
                            }
                            
                            echo '</div>';
                            echo '<div>';
                            echo '<a href="'.BASE_URL.'/salud/detalle_consulta.php?id='.$registro['id_interaccion'].'" class="btn btn-sm btn-purple">Ver Detalles</a>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    } else {
                        // Mensaje cuando no hay registros
                        echo '<div class="alert alert-info">';
                        echo '<h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Expediente vacío</h5>';
                        echo '<p class="mb-0">No se han encontrado registros médicos anteriores para esta mascota.</p>';
                        echo '<p class="mb-0 mt-2">Puedes comenzar agregando una nueva consulta, vacunación o procedimiento.</p>';
                        echo '</div>';
                    }
                    break;
                    
                case 'consulta':
                    include(BASE_PATH . '/salud/secciones/consulta.php');
                    break;
                    
                case 'vacunacion':
                    include(BASE_PATH . '/salud/secciones/vacunacion.php');
                    break;
                    
                case 'urgencia':
                    include(BASE_PATH . '/salud/secciones/urgencia.php');
                    break;
                    
                case 'procedimiento':
                    include(BASE_PATH . '/salud/secciones/procedimiento.php');
                    break;
            }
            ?>
        </div>
    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>