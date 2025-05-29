<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/includes/auth.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

//verificarAutenticacion();

// Verificar rol de veterinario
if ($_SESSION['rol'] !== 'veterinario') {
    redirigir_con_mensaje(BASE_URL . '/salud/salud_animal.php', 'danger', 'Solo veterinarios pueden realizar esta acción');
}

if (!isset($_SESSION['temp_data'])) {
    redirigir_con_mensaje(BASE_URL . '/salud/salud_animal.php', 'danger', 'No hay datos para firmar');
}

$temp_data = $_SESSION['temp_data'];
$tipo = $temp_data['tipo'];
$id_mascota = $temp_data['id_mascota'];

$stmt = $conn->prepare("SELECT m.nombre, t.nombre AS tutor_nombre 
                       FROM mascotas m 
                       JOIN tutores t ON m.id_tutor = t.id_tutor 
                       WHERE m.id_mascota = ?");
$stmt->bind_param("i", $id_mascota);
$stmt->execute();
$result = $stmt->get_result();
$mascota = $result->fetch_assoc();
$stmt->close();

$page_title = "Firma Digital - " . htmlspecialchars($mascota['nombre']);
include(BASE_PATH . '/includes/header.php');
?>

<style>
    .consentimiento-container {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        padding: 15px;
        margin-bottom: 20px;
        background-color: #f8f9fa;
    }
    .firma-container {
        border: 1px dashed #8b0180;
        margin: 20px 0;
        height: 200px;
        background-color: white;
        touch-action: none; /* Importante para dispositivos táctiles */
    }
    canvas {
        width: 100%;
        height: 100%;
        display: block;
    }
    .btn-limpiar {
        margin-top: 10px;
        background-color: #6c757d;
        color: white;
    }
</style>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-purple text-white">
            <h4 class="mb-0">✍️ Firma Digital - <?= htmlspecialchars(ucfirst($tipo)) ?></h4>
        </div>
        <div class="card-body">
            <h5 class="mb-3">Mascota: <?= htmlspecialchars($mascota['nombre']) ?></h5>
            <p class="mb-4">Tutor: <?= htmlspecialchars($mascota['tutor_nombre']) ?></p>
            
            <div class="consentimiento-container">
                <h5 class="text-purple">CONSENTIMIENTO INFORMADO Y ACEPTACIÓN DE TÉRMINOS</h5>
                <h6>Procedimientos Quirúrgicos y/o de Urgencia Veterinaria</h6>
                <h6>Ayuntamiento de San Andrés</h6>
                
                <p class="mt-3">Mediante el presente documento, declaro que he sido debidamente informado/a acerca de la naturaleza del procedimiento quirúrgico o de urgencia que será realizado a mi animal por parte del servicio veterinario del Ayuntamiento de San Andrés.</p>
                
                <p>Entiendo que todo procedimiento médico conlleva riesgos inherentes, incluyendo pero no limitándose a reacciones adversas a la anestesia, hemorragias, infecciones, complicaciones postoperatorias, y, en casos excepcionales, la muerte del animal. Acepto expresamente estos riesgos y autorizo al personal veterinario a realizar las intervenciones que consideren necesarias para preservar la vida y el bienestar del animal, dentro de las posibilidades técnicas y logísticas disponibles en el momento de la atención.</p>
            </div>
            
            <form id="formFirma" method="post" action="<?= BASE_URL ?>/salud/procesamiento/procesar_firma.php">
                <input type="hidden" name="tipo" value="<?= $tipo ?>">
                <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
                <input type="hidden" name="firma_data" id="firmaData">
                
                <div class="mb-3">
                    <label class="form-label">Firma digital*</label>
                    <div class="firma-container">
                        <canvas id="firmaPad"></canvas>
                    </div>
                    <button type="button" class="btn btn-sm btn-limpiar" id="limpiarFirma">Limpiar Firma</button>
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-purple">Firmar y Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('firmaPad');
    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;
    
    // Ajustar tamaño del canvas
    function resizeCanvas() {
        const container = canvas.parentElement;
        canvas.width = container.offsetWidth;
        canvas.height = container.offsetHeight;
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000000';
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }
    
    // Inicializar canvas
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
    
    // Eventos para mouse
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    
    // Eventos para pantalla táctil
    canvas.addEventListener('touchstart', handleTouchStart);
    canvas.addEventListener('touchmove', handleTouchMove);
    canvas.addEventListener('touchend', stopDrawing);
    
    function startDrawing(e) {
        isDrawing = true;
        [lastX, lastY] = getPosition(e);
    }
    
    function draw(e) {
        if (!isDrawing) return;
        
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        [lastX, lastY] = getPosition(e);
        ctx.lineTo(lastX, lastY);
        ctx.stroke();
    }
    
    function stopDrawing() {
        isDrawing = false;
    }
    
    function handleTouchStart(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousedown', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    }
    
    function handleTouchMove(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousemove', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    }
    
    function getPosition(e) {
        const rect = canvas.getBoundingClientRect();
        let x, y;
        
        if (e.type.includes('touch')) {
            x = e.touches[0].clientX - rect.left;
            y = e.touches[0].clientY - rect.top;
        } else {
            x = e.clientX - rect.left;
            y = e.clientY - rect.top;
        }
        
        return [x, y];
    }
    
    // Limpiar firma
    document.getElementById('limpiarFirma').addEventListener('click', function() {
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    });
    
    // Guardar firma al enviar el formulario
    document.getElementById('formFirma').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (canvas.toDataURL() === canvas.toDataURL('image/png', 1.0)) {
            alert('Por favor, proporcione su firma');
            return;
        }
        
        document.getElementById('firmaData').value = canvas.toDataURL('image/png');
        this.submit();
    });
});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>