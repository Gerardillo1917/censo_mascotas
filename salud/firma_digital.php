<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

// Verificar si se accede desde salud_animal.php o desde procesar_cirugia.php
if (isset($_GET['id_interaccion'])) {
    // Acceso directo desde salud_animal.php
    $id_interaccion = intval($_GET['id_interaccion']);
    $id_mascota = isset($_GET['id_mascota']) ? intval($_GET['id_mascota']) : 0;
    
    if ($id_interaccion <= 0 || $id_mascota <= 0) {
        redirigir_con_mensaje('salud_animal.php', 'danger', 'Parámetros inválidos');
    }

    // Obtener datos del registro
    try {
        $stmt = $conn->prepare("SELECT sm.tipo_procedimiento, sm.id_mascota, m.nombre, t.nombre AS tutor_nombre 
                                FROM salud_mascotas sm 
                                JOIN mascotas m ON sm.id_mascota = m.id_mascota 
                                JOIN tutores t ON m.id_tutor = t.id_tutor 
                                WHERE sm.id_interaccion = ? AND sm.id_mascota = ?");
        $stmt->bind_param("ii", $id_interaccion, $id_mascota);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        if (!$data) {
            redirigir_con_mensaje('salud_animal.php', 'danger', 'Registro no encontrado');
        }

        $tipo = $data['tipo_procedimiento'] ? 'consentimiento_procedimiento' : 'consentimiento_urgencia';
        $mascota = ['nombre' => $data['nombre'], 'tutor_nombre' => $data['tutor_nombre']];
        $info_procedimiento = $data['tipo_procedimiento'];
    } catch (Exception $e) {
        registrar_error($e->getMessage());
        redirigir_con_mensaje('salud_animal.php', 'danger', 'Error al cargar datos');
    }
} elseif (isset($_SESSION['temp_data'])) {
    // Acceso desde procesar_cirugia.php
    $temp_data = $_SESSION['temp_data'];
    $tipo = $temp_data['tipo'];
    $id_mascota = $temp_data['id_mascota'];
    $id_interaccion = $temp_data['id_interaccion'];

    // Obtener datos de mascota y tutor
    try {
        $stmt = $conn->prepare("SELECT m.nombre, t.nombre AS tutor_nombre 
                                FROM mascotas m 
                                JOIN tutores t ON m.id_tutor = t.id_tutor 
                                WHERE m.id_mascota = ?");
        $stmt->bind_param("i", $id_mascota);
        $stmt->execute();
        $result = $stmt->get_result();
        $mascota = $result->fetch_assoc();
        $stmt->close();

        // Obtener información del procedimiento si es necesario
        $info_procedimiento = '';
        if ($tipo === 'consentimiento_cirugia') {
            $stmt = $conn->prepare("SELECT tipo_procedimiento FROM salud_mascotas WHERE id_interaccion = ?");
            $stmt->bind_param("i", $id_interaccion);
            $stmt->execute();
            $result = $stmt->get_result();
            $procedimiento = $result->fetch_assoc();
            $stmt->close();
            
            $info_procedimiento = $procedimiento['tipo_procedimiento'];
        }
    } catch (Exception $e) {
        registrar_error($e->getMessage());
        redirigir_con_mensaje('salud_animal.php', 'danger', 'Error al cargar datos');
    }
} else {
    redirigir_con_mensaje('salud_animal.php', 'danger', 'No hay datos para firmar');
}

$page_title = "Firma Digital - " . htmlspecialchars($mascota['nombre']);
include(BASE_PATH . '/includes/header.php');
?>

<style>
    .card-purple-border {
        border: 2px solid #8b0180;
        border-radius: 0.5rem;
    }
    .card-header.bg-purple {
        background-color: #8b0180;
        color: #ffffff;
    }
    .card-header.bg-purple h4,
    .card-header.bg-purple h5 {
        color: #ffffff;
        margin: 0;
    }
    .text-purple {
        color: #8b0180;
    }
    .consentimiento-container {
        max-height: 300px;
        overflow-y: auto;
        border: 2px solid #e6c7eb;
        padding: 15px;
        margin-bottom: 20px;
        background-color: #f3e0f5;
        border-radius: 0.5rem;
    }
    .firma-container {
        border: 2px dashed #8b0180;
        margin: 20px 0;
        height: 200px;
        background-color: #ffffff;
        border-radius: 0.5rem;
        touch-action: none;
    }
    canvas {
        width: 100%;
        height: 100%;
        display: block;
    }
    .btn-purple {
        background-color: #8b0180;
        border-color: #8b0180;
        color: #ffffff;
    }
    .btn-purple:hover {
        background-color: #6a0160;
        border-color: #6a0160;
        color: #ffffff;
    }
    .btn-limpiar {
        margin-top: 10px;
        background-color: #6c757d;
        border: 1px solid #6c757d;
        color: #ffffff;
    }
    .btn-limpiar:hover {
        background-color: #5a6268;
        border-color: #545b62;
        color: #ffffff;
    }
    .btn-secondary {
        border: 1px solid #6c757d;
    }
    .btn-atras {
        background-color: #6c757d;
        color: #ffffff;
        border: none;
    }
    .btn-atras:hover {
        background-color: #5a6268;
    }
</style>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-purple text-white">
            <h4 class="mb-0">✍️ Firma Digital - Consentimiento de Procedimiento</h4>
        </div>
        <div class="card-body">
            <h5 class="mb-3">Mascota: <?= htmlspecialchars($mascota['nombre']) ?></h5>
            <p class="mb-4">Tutor: <?= htmlspecialchars($mascota['tutor_nombre']) ?></p>
            
            <?php if ($info_procedimiento): ?>
            <div class="info-procedimiento">
                <h6>Tipo de procedimiento:</h6>
                <p><?= htmlspecialchars($info_procedimiento) ?></p>
            </div>
            <?php endif; ?>
            
            <div class="consentimiento-container">
                <h5 class="text-purple">CONSENTIMIENTO INFORMADO PARA PROCEDIMIENTO</h5>
                <h6>Ayuntamiento de San Andrés</h6>
                
                <p class="mt-3">Yo, <strong><?= htmlspecialchars($mascota['tutor_nombre']) ?></strong>, tutor legal de <strong><?= htmlspecialchars($mascota['nombre']) ?></strong>, declaro que he sido debidamente informado/a acerca de la naturaleza del procedimiento que será realizado a mi animal por parte del servicio veterinario del Ayuntamiento de San Andrés.</p>
                
                <p>Entiendo que todo procedimiento conlleva riesgos inherentes, incluyendo pero no limitándose a reacciones adversas a medicamentos, complicaciones, y, en casos excepcionales, consecuencias graves. Acepto expresamente estos riesgos y autorizo al personal veterinario a realizar las intervenciones que consideren necesarias para el bienestar del animal.</p>
                
                <p>Declaro que toda la información proporcionada es verídica y que asumo la responsabilidad por las decisiones tomadas respecto al tratamiento de mi mascota.</p>
            </div>
            
            <form id="formFirma" method="post" action="<?= BASE_URL ?>/salud/procesar_firma.php" enctype="multipart/form-data">
                <input type="hidden" name="tipo" value="<?= $tipo ?>">
                <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
                <input type="hidden" name="id_interaccion" value="<?= $id_interaccion ?>">
                <input type="hidden" name="firma_data" id="firmaData">
                
                <div class="mb-3">
                    <label class="form-label">Firma digital*</label>
                    <div class="firma-container">
                        <canvas id="firmaPad"></canvas>
                    </div>
                    <button type="button" class="btn btn-sm btn-limpiar" id="limpiarFirma">Limpiar Firma</button>
                </div>
                
                <div class="form-group mb-3">
                    <label for="nombre_firmante" class="form-label">Nombre del firmante*</label>
                    <input type="text" class="form-control" id="nombre_firmante" name="nombre_firmante" value="<?= htmlspecialchars($mascota['tutor_nombre']) ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="dni_firmante" class="form-label">DNI del firmante*</label>
                    <input type="text" class="form-control" id="dni_firmante" name="dni_firmante" required>
                </div>
                
                <div class="form-group mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="acepto_tratamiento" name="acepto_tratamiento" value="1">
                        <label class="form-check-label" for="acepto_tratamiento">Acepto el tratamiento de mi mascota y los riesgos informados</label>
                    </div>
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
    canvas.addEventListener('touchstart', handleTouchStart, {passive: false});
    canvas.addEventListener('touchmove', handleTouchMove, {passive: false});
    canvas.addEventListener('touchend', stopDrawing);
    
    function startDrawing(e) {
        isDrawing = true;
        [lastX, lastY] = getPosition(e);
        e.preventDefault();
    }
    
    function draw(e) {
        if (!isDrawing) return;
        
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        [lastX, lastY] = getPosition(e);
        ctx.lineTo(lastX, lastY);
        ctx.stroke();
        e.preventDefault();
    }
    
    function stopDrawing() {
        isDrawing = false;
    }
    
    function handleTouchStart(e) {
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousedown', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
        e.preventDefault();
    }
    
    function handleTouchMove(e) {
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousemove', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
        e.preventDefault();
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
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    });
    
    // Validar formulario antes de enviar
    document.getElementById('formFirma').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Verificar si hay firma
        const blankCanvas = document.createElement('canvas');
        blankCanvas.width = canvas.width;
        blankCanvas.height = canvas.height;
        const blankCtx = blankCanvas.getContext('2d');
        blankCtx.fillStyle = '#FFFFFF';
        blankCtx.fillRect(0, 0, blankCanvas.width, blankCanvas.height);
        
        if (canvas.toDataURL() === blankCanvas.toDataURL()) {
            alert('Por favor, proporcione su firma');
            return;
        }
        
        // Verificar campos obligatorios
        if (!document.getElementById('nombre_firmante').value || !document.getElementById('dni_firmante').value) {
            alert('Por favor, complete todos los campos obligatorios');
            return;
        }
        
        // Verificar checkbox de aceptación
        if (!document.getElementById('acepto_tratamiento').checked) {
            alert('Debe aceptar el tratamiento de su mascota y los riesgos informados');
            return;
        }
        
        document.getElementById('firmaData').value = canvas.toDataURL('image/png');
        this.submit();
    });
});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>