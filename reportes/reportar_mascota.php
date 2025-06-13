<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

requerir_autenticacion();

$id_mascota = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_mascota <= 0) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Mascota no especificada');
}

// Obtener datos de la mascota y tutor
$stmt = $conn->prepare("SELECT m.id_mascota, m.nombre AS nombre_mascota, m.especie, 
                        t.id_tutor, t.nombre AS nombre_tutor, t.apellido_paterno 
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

$page_title = "Reportar Mascota - " . htmlspecialchars($mascota['nombre_mascota']);
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
    .reporte-card {
        border-left: 4px solid #8b0180;
        margin-bottom: 1rem;
    }
    .reporte-tutor {
        border-left-color: #8b0180;
    }
    .reporte-mascota {
        border-left-color: #28a745;
    }
    .foto-preview {
        max-width: 200px;
        max-height: 200px;
        display: none;
    }
    .preview-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    .preview-img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    #video {
        width: 100%;
        max-height: 300px;
        display: none;
        margin-bottom: 10px;
    }
    #canvas {
        display: none;
    }
    .btn-camera {
        margin-top: 10px;
    }
</style>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-purple text-white">
                    <h4 class="mb-0">Reportar Mascota</h4>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/reportes/procesar_reporte.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="tipo" value="mascota">
                        <input type="hidden" name="id_referencia" value="<?= $mascota['id_mascota'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Mascota:</label>
                            <p class="form-control-static fw-bold"><?= htmlspecialchars($mascota['nombre_mascota']) ?> (<?= htmlspecialchars($mascota['especie']) ?>)</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tutor:</label>
                            <p class="form-control-static"><?= htmlspecialchars($mascota['nombre_tutor'] . ' ' . $mascota['apellido_paterno']) ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tipo de reporte*</label>
                            <select class="form-select" name="tipo_reporte" required>
                                <option value="">Seleccione un tipo</option>
                                <option value="Maltrato animal">Maltrato animal</option>
                                <option value="Animal en peligro">Animal en peligro</option>
                                <option value="Animal abandonado">Animal abandonado</option>
                                <option value="Animal agresivo">Animal agresivo</option>
                                <option value="Animal enfermo">Animal enfermo</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción detallada*</label>
                            <textarea class="form-control" name="descripcion" rows="5" required 
                                      placeholder="Describa el problema con el mayor detalle posible..."></textarea>
                        </div>
                        
                         <div class="mb-3">
                            <label class="form-label">Evidencia fotográfica (opcional, máx. 5)</label>
                            <input type="file" class="form-control" name="fotos_reporte[]" multiple accept="image/*" id="fotosInput">
                            <small class="text-muted">Formatos: JPG, PNG (máx. 2MB cada una)</small>
                            
                            <button type="button" class="btn btn-secondary btn-camera" id="btnTomarFoto">
                                <i class="fas fa-camera me-1"></i> Tomar foto con cámara
                            </button>
                            
                            <video id="video" autoplay></video>
                            <canvas id="canvas"></canvas>
                            
                            <div class="preview-container" id="previewContainer"></div>
                        </div>
                        
                        <input type="hidden" name="foto_capturada" id="fotoCapturada">
                        
                        <hr>
                        
                        <h5 class="mb-3">Datos del denunciante (opcional)</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre_denunciante">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" name="telefono_denunciante">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" name="email_denunciante">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= BASE_URL ?>/perfil.php?id=<?= $mascota['id_tutor'] ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-purple">
                                <i class="fas fa-paper-plane me-1"></i> Enviar Reporte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fotosInput = document.getElementById('fotosInput');
    const btnTomarFoto = document.getElementById('btnTomarFoto');
    const previewContainer = document.getElementById('previewContainer');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const fotoCapturada = document.getElementById('fotoCapturada');
    let stream = null;
    
    // Mostrar previsualización de fotos seleccionadas
    fotosInput.addEventListener('change', function() {
        previewContainer.innerHTML = '';
        if (this.files.length > 5) {
            alert('Solo puedes subir un máximo de 5 fotos');
            this.value = '';
            return;
        }
        
        for (let i = 0; i < this.files.length; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('preview-img');
                previewContainer.appendChild(img);
            }
            reader.readAsDataURL(this.files[i]);
        }
    });
    
    // Manejar captura de foto desde cámara
    btnTomarFoto.addEventListener('click', async function() {
        if (!stream) {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                video.style.display = 'block';
                btnTomarFoto.innerHTML = '<i class="fas fa-camera me-1"></i> Capturar foto';
            } catch (err) {
                console.error("Error al acceder a la cámara: ", err);
                alert('No se pudo acceder a la cámara. Asegúrate de permitir el acceso.');
            }
        } else {
            // Capturar foto
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = canvas.toDataURL('image/jpeg');
            
            // Mostrar previsualización
            const img = document.createElement('img');
            img.src = imageData;
            img.classList.add('preview-img');
            previewContainer.appendChild(img);
            
            // Guardar datos para enviar al servidor
            fotoCapturada.value = imageData;
            
            // Detener cámara
            stream.getTracks().forEach(track => track.stop());
            stream = null;
            video.style.display = 'none';
            btnTomarFoto.innerHTML = '<i class="fas fa-camera me-1"></i> Tomar foto con cámara';
        }
    });
});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>