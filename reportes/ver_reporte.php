<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

requerir_autenticacion();

$id_reporte = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_reporte <= 0) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Reporte no especificado');
}

// Obtener datos del reporte
$stmt = $conn->prepare("SELECT r.*, 
                        IF(r.tipo = 'tutor', 
                           CONCAT(t.nombre, ' ', t.apellido_paterno), 
                           CONCAT(m.nombre, ' (', m.especie, ')')
                        ) AS sujeto_reporte,
                        IF(r.tipo = 'tutor', t.id_tutor, m.id_tutor) AS id_tutor
                        FROM reportes r
                        LEFT JOIN tutores t ON r.tipo = 'tutor' AND r.id_referencia = t.id_tutor
                        LEFT JOIN mascotas m ON r.tipo = 'mascota' AND r.id_referencia = m.id_mascota
                        WHERE r.id_reporte = ?");
$stmt->bind_param("i", $id_reporte);
$stmt->execute();
$reporte = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reporte) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Reporte no encontrado');
}

// Convertir foto_ruta en array
$reporte['fotos'] = [];
if (!empty($reporte['foto_ruta'])) {
    $fotos = json_decode($reporte['foto_ruta'], true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $reporte['fotos'] = $fotos;
    } elseif (is_string($reporte['foto_ruta'])) {
        $reporte['fotos'] = [$reporte['foto_ruta']];
    }
}

// Preparar URLs completas para las imágenes
foreach ($reporte['fotos'] as &$foto) {
    if (strpos($foto, 'http') !== 0 && strpos($foto, '/') !== 0) {
        $foto = '/' . ltrim($foto, '/');
    }
    
    if (strpos($foto, 'http') !== 0) {
        $foto = BASE_URL . $foto;
    }
}
unset($foto);

$page_title = "Reporte #" . $id_reporte;
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
    .reporte-header {
        border-left: 4px solid #8b0180;
        padding-left: 1rem;
    }
    .reporte-tutor {
        border-left-color: #8b0180;
    }
    .reporte-mascota {
        border-left-color: #28a745;
    }
    .estado-pendiente { background-color: #ffc107; color: #000; }
    .estado-investigando { background-color: #17a2b8; color: #fff; }
    .estado-resuelto { background-color: #28a745; color: #fff; }
    .foto-reporte {
        max-width: 100%;
        max-height: 400px;
        cursor: pointer;
        transition: transform 0.3s;
    }
    .foto-reporte:hover {
        transform: scale(1.02);
    }
    .galeria-fotos {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
    }
    .miniatura {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border: 2px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .miniatura:hover {
        border-color: #8b0180;
    }
    .modal-img {
        max-width: 100%;
        max-height: 80vh;
    }
</style>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-purple text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Reporte #<?= $id_reporte ?></h4>
                    <span class="badge estado-<?= $reporte['estado'] ?>">
                        <?= ucfirst($reporte['estado']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="reporte-header <?= $reporte['tipo'] === 'tutor' ? 'reporte-tutor' : 'reporte-mascota' ?>">
                            <?= $reporte['tipo'] === 'tutor' ? 'Tutor reportado' : 'Mascota reportada' ?>:
                            <a href="<?= BASE_URL ?>/perfil.php?id=<?= $reporte['id_tutor'] ?>" class="text-decoration-none">
                                <?= htmlspecialchars($reporte['sujeto_reporte']) ?>
                            </a>
                        </h5>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de reporte:</label>
                            <p><?= htmlspecialchars($reporte['tipo_reporte']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha:</label>
                            <p><?= date('d/m/Y H:i', strtotime($reporte['fecha_reporte'])) ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción:</label>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(htmlspecialchars($reporte['descripcion'])) ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($reporte['fotos'])): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Evidencia fotográfica:</label>
                        
                        <!-- Mostrar primera foto como principal -->
                        <div class="text-center mb-3">
                            <img src="<?= htmlspecialchars($reporte['fotos'][0]) ?>" 
                                 class="img-thumbnail foto-reporte" 
                                 id="fotoPrincipal"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#modalGaleria">
                        </div>
                        
                        <!-- Miniaturas para todas las fotos -->
                        <div class="galeria-fotos">
                            <?php foreach ($reporte['fotos'] as $index => $foto): ?>
                                <img src="<?= htmlspecialchars($foto) ?>" 
                                     class="miniatura" 
                                     onclick="document.getElementById('fotoPrincipal').src = this.src">
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($reporte['nombre_denunciante']) || !empty($reporte['telefono_denunciante']) || !empty($reporte['email_denunciante'])): ?>
                    <hr>
                    <h5 class="mb-3">Información del denunciante</h5>
                    <div class="row">
                        <?php if (!empty($reporte['nombre_denunciante'])): ?>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nombre:</label>
                            <p><?= htmlspecialchars($reporte['nombre_denunciante']) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($reporte['telefono_denunciante'])): ?>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Teléfono:</label>
                            <p><?= htmlspecialchars($reporte['telefono_denunciante']) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($reporte['email_denunciante'])): ?>
                        <div class="col-12 mb-2">
                            <label class="form-label">Correo electrónico:</label>
                            <p><?= htmlspecialchars($reporte['email_denunciante']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($reporte['comentarios_resolucion'])): ?>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Resolución:</label>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(htmlspecialchars($reporte['comentarios_resolucion'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= BASE_URL ?>/perfil.php?id=<?= $reporte['id_tutor'] ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver al perfil
                        </a>
                        
                        <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'veterinario'])): ?>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog me-1"></i> Acciones
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#cambiarEstadoModal">
                                        <i class="fas fa-sync-alt me-1"></i> Cambiar estado
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="confirmarEliminacion(<?= $id_reporte ?>)">
                                        <i class="fas fa-trash me-1"></i> Eliminar reporte
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar estado -->
<div class="modal fade" id="cambiarEstadoModal" tabindex="-1" aria-labelledby="cambiarEstadoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-purple text-white">
                <h5 class="modal-title" id="cambiarEstadoModalLabel">Cambiar estado del reporte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/reportes/procesar_estado_reporte.php" method="post">
                <input type="hidden" name="id_reporte" value="<?= $id_reporte ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nuevo estado:</label>
                        <select class="form-select" name="nuevo_estado" required>
                            <option value="pendiente" <?= $reporte['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="investigando" <?= $reporte['estado'] === 'investigando' ? 'selected' : '' ?>>Investigando</option>
                            <option value="resuelto" <?= $reporte['estado'] === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comentarios (opcional):</label>
                        <textarea class="form-control" name="comentarios_resolucion" rows="3"><?= htmlspecialchars($reporte['comentarios_resolucion'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-purple">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para galería de fotos -->
<div class="modal fade" id="modalGaleria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" class="modal-img" id="imagenModal">
            </div>
            <div class="modal-footer justify-content-center">
                <?php if (!empty($reporte['fotos'])): ?>
                    <?php foreach ($reporte['fotos'] as $index => $foto): ?>
                        <button type="button" class="btn btn-sm btn-outline-purple" 
                                onclick="document.getElementById('imagenModal').src = '<?= htmlspecialchars($foto) ?>'">
                            <?= $index + 1 ?>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminacion(id) {
    if (confirm('¿Está seguro de que desea eliminar este reporte? Esta acción no se puede deshacer.')) {
        window.location.href = '<?= BASE_URL ?>/reportes/eliminar_reporte.php?id=' + id;
    }
}

// Inicializar modal de galería
document.addEventListener('DOMContentLoaded', function() {
    const modalGaleria = document.getElementById('modalGaleria');
    if (modalGaleria) {
        modalGaleria.addEventListener('show.bs.modal', function(event) {
            const imagenModal = document.getElementById('imagenModal');
            imagenModal.src = document.getElementById('fotoPrincipal').src;
        });
    }
});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>