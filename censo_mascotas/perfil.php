<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/includes/auth.php'; // Esto ya verifica la autenticación
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

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

// [Resto del código de procesamiento de bajas...]

// Obtener mascotas con sus vacunas (optimizado con JOIN)
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
    @media (max-width: 768px) {
        .ficha-mascota-img {
            height: 150px;
        }
        .ficha-mascota-body {
            padding: 1rem;
        }
    }
</style>

<div class="container mt-4">
    <!-- Mostrar mensajes -->
    <?php mostrar_mensaje(); ?>

    <!-- Sección del Tutor -->
<div class="row mb-4">
    <div class="col-md-4 text-center mb-3">
        <?php if (!empty($tutor['foto_ruta'])): ?>
            <img src="<?= htmlspecialchars($tutor['foto_ruta']) ?>" class="img-thumbnail border-purple mb-2">
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
                                    <span class="badge badge-estado bg-<?= $mascota['estado'] == 'Vivo' ? 'success' : 'danger' ?>">
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
                                                        <span class="fw-bold">Edad:</span> <?= $mascota['edad'] ?> meses
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
                                                
                                                <!-- Vacunas - Versión mejorada -->
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
                                                    <?php if ($mascota['estado'] == 'Vivo'): ?>
                                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" 
                                                           data-bs-target="#bajaModal<?= $mascota['id_mascota'] ?>">
                                                          <i class="fas fa-exclamation-triangle me-1"></i> Baja
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

                        <!-- [Mantener el modal de baja...] -->
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No hay mascotas registradas.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>