<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Forzar autenticación
requerir_autenticacion();


ob_start();
if (!isset($_GET['tutor'])) {
    redirigir_con_mensaje('/buscar.php', 'danger', 'Tutor no especificado');
}

$id_tutor = $_GET['tutor'];

// Verificar existencia del tutor
$stmt = $conn->prepare("SELECT id_tutor FROM tutores WHERE id_tutor = ?");
$stmt->bind_param("i", $id_tutor);
$stmt->execute();
$tutor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tutor) {
    redirigir_con_mensaje('/buscar.php', 'danger', 'Tutor no encontrado');
}

// Procesar formulario - debe estar ANTES de cualquier output
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = sanitizar_input($_POST['nombre_mascota']);
    $especie = sanitizar_input($_POST['especie']);
    $raza = isset($_POST['raza']) ? sanitizar_input($_POST['raza']) : null;
    $color = isset($_POST['color']) ? sanitizar_input($_POST['color']) : null;
    $edad = intval($_POST['edad_mascota']);
    $genero = sanitizar_input($_POST['genero']);
    $esterilizado = isset($_POST['esterilizado']) ? 1 : 0;
    $incapacidad = isset($_POST['incapacidad']) ? 1 : 0;
    $descripcion_incapacidad = isset($_POST['descripcion_incapacidad']) ? sanitizar_input($_POST['descripcion_incapacidad']) : null;
    $comentarios = isset($_POST['comentarios_mascota']) ? sanitizar_input($_POST['comentarios_mascota']) : null;

    try {
        // Subir foto (opcional)
        $foto_ruta = null;
        if (!empty($_FILES['foto_mascota']['name'])) {
            $foto_ruta = subirFoto('foto_mascota', 'mascotas', 'mascota_');
        }

        // Insertar mascota
        $stmt = $conn->prepare("INSERT INTO mascotas (
            id_tutor, nombre, especie, raza, color, edad, genero, 
            esterilizado, incapacidad, descripcion_incapacidad, 
            comentarios, foto_ruta, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Vivo')");

        $stmt->bind_param(
            "issssisissss",
            $id_tutor,
            $nombre,
            $especie,
            $raza,
            $color,
            $edad,
            $genero,
            $esterilizado,
            $incapacidad,
            $descripcion_incapacidad,
            $comentarios,
            $foto_ruta
        );

        $stmt->execute();
        $id_mascota = $stmt->insert_id;
        $stmt->close();

        // Procesar vacunas (si existen)
        if (!empty($_POST['vacunas']['nombre'])) {
            $stmt_vacuna = $conn->prepare("INSERT INTO vacunas (
                id_mascota, nombre_vacuna, fecha_aplicacion, comentarios
            ) VALUES (?, ?, ?, ?)");

            foreach ($_POST['vacunas']['nombre'] as $index => $nombre_vacuna) {
                if (!empty($nombre_vacuna) && !empty($_POST['vacunas']['fecha'][$index])) {
                    $fecha = $_POST['vacunas']['fecha'][$index];
                    $comentario_vacuna = $_POST['vacunas']['comentarios'][$index] ?? null;
                    
                    $stmt_vacuna->bind_param("isss", $id_mascota, $nombre_vacuna, $fecha, $comentario_vacuna);
                    $stmt_vacuna->execute();
                }
            }
            $stmt_vacuna->close();
        }

        redirigir_con_mensaje("/perfil.php?id=$id_tutor", 'success', 'Mascota registrada correctamente');

    } catch (Exception $e) {
        redirigir_con_mensaje("/agregar_mascota.php?tutor=$id_tutor", 'danger', 'Error: ' . $e->getMessage());
    }
}

// Configuración de la página - después de posibles redirecciones
$page_title = "Agregar Mascota";

// Incluir header - después de toda la lógica PHP
include(BASE_PATH . '/includes/header.php');
?>

<div class="container">
    <h2 class="mb-4 text-purple">Agregar Mascota</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="card mb-3 shadow-sm card-purple-border">
            <div class="card-header bg-purple text-white">
                <h5 class="mb-0">Datos de la Mascota</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Nombre*</label>
                        <input type="text" class="form-control" name="nombre_mascota" required>
                    </div>
                    <div class="col-md-3">
    <label class="form-label">Especie*</label>
    <select class="form-select" name="especie" id="especie" required>
        <option value="">Seleccione...</option>
        <option value="Perro">Perro</option>
        <option value="Gato">Gato</option>
        <option value="Ave">Ave</option>
        <option value="Otro">Otro</option>
    </select>
</div>
<div class="col-md-3" id="otra_especie_container" style="display:none;">
    <label class="form-label">Especificar especie*</label>
    <input type="text" class="form-control" name="otra_especie" id="otra_especie">
</div>
                    <div class="col-md-3">
                        <label class="form-label">Edad (años)*</label>
                        <input type="number" class="form-control" name="edad_mascota" min="0" max="600" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Género*</label>
                        <select class="form-select" name="genero" required>
                            <option value="">Seleccione...</option>
                            <option value="Macho">Macho</option>
                            <option value="Hembra">Hembra</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Raza</label>
                        <input type="text" class="form-control" name="raza">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" name="color">
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-3 pt-1">
                            <input class="form-check-input" type="checkbox" name="esterilizado" id="esterilizado">
                            <label class="form-check-label" for="esterilizado">Esterilizado</label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Foto de la mascota</label>
                        <input type="file" id="foto-mascota" name="foto_mascota" accept="image/*" class="form-control form-control-sm">
                        <small class="text-muted">formatos: jpg, png (máx. 2mb)</small>
                    </div>
                    
                    <div class="col-md-12 mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="tiene_vacuna" id="tiene_vacuna">
                            <label class="form-check-label" for="tiene_vacuna">Tiene vacunas</label>
                        </div>
                        <div id="vacuna_fields" class="mt-2" style="display:none;">
                            <div class="vacunas-container">
                                </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-vacuna">
                                <i class="bi bi-plus"></i> Agregar vacuna
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="incapacidad" id="incapacidad">
                            <label class="form-check-label" for="incapacidad">Tiene discapacidad</label>
                        </div>
                        <div id="incapacidad_fields" class="mt-2" style="display:none;">
                            <label class="form-label small">Descripción incapacidad</label>
                            <textarea class="form-control" name="descripcion_incapacidad" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <div class="col-12 mt-3">
                        <label class="form-label">Comentarios adicionales</label>
                        <textarea class="form-control" name="comentarios_mascota" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="submit" class="btn btn-purple-primary">Guardar Registro</button>
            <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-red-cancel">Cancelar</a>
        </div>
    </form>
</div>   
<script>
document.getElementById('especie').addEventListener('change', function() {
    const otraEspecieContainer = document.getElementById('otra_especie_container');
    otraEspecieContainer.style.display = this.value === 'Otro' ? 'block' : 'none';
    if (this.value !== 'Otro') {
        document.getElementById('otra_especie').value = '';
    }
});
</script>
<style>
    :root {
        --purple-primary: #8b0180;
        --red-cancel: #dc3545;
        --white-text: #ffffff;
        --border-color: #dee2e6;
    }

    .text-purple {
        color: var(--purple-primary);
    }

    .card-purple-border {
        border: 1px solid var(--purple-primary);
    }

    .card-header.bg-purple {
        background-color: var(--purple-primary) !important;
        color: var(--white-text) !important;
    }

    .card-header.bg-purple h5 {
        color: var(--white-text) !important;
    }

    .btn-purple-primary {
        background-color: var(--purple-primary);
        border-color: var(--purple-primary);
        color: var(--white-text);
    }

    .btn-purple-primary:hover {
        background-color: #6a0160; /* Un tono más oscuro de morado al pasar el mouse */
        border-color: #6a0160;
        color: var(--white-text);
    }

    .btn-red-cancel {
        background-color: var(--red-cancel);
        border-color: var(--red-cancel);
        color: var(--white-text);
    }

    .btn-red-cancel:hover {
        background-color: #c82333; /* Un tono más oscuro de rojo al pasar el mouse */
        border-color: #bd2130;
        color: var(--white-text);
    }
</style>

<script>
    // Vacunas dinámicas
    document.getElementById('tiene_vacuna').addEventListener('change', function() {
        document.getElementById('vacuna_fields').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('incapacidad').addEventListener('change', function() {
        document.getElementById('incapacidad_fields').style.display = this.checked ? 'block' : 'none';
    });

    // Plantilla para vacunas
    const vacunaTemplate = (index = '') => `
        <div class="vacuna-item mb-3 p-3 border rounded">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Vacuna ${index}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-vacuna">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small">Nombre vacuna*</label>
                    <input type="text" class="form-control" name="vacunas[nombre][]" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Fecha aplicación*</label>
                    <input type="date" class="form-control" name="vacunas[fecha][]" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Comentarios</label>
                    <input type="text" class="form-control" name="vacunas[comentarios][]">
                </div>
            </div>
        </div>
    `;

    // Agregar primera vacuna
    document.getElementById('add-vacuna').addEventListener('click', function() {
        const container = document.querySelector('.vacunas-container');
        const count = container.children.length + 1;
        const newVacuna = document.createElement('div');
        newVacuna.innerHTML = vacunaTemplate(count);
        container.appendChild(newVacuna);
        
        // Agregar evento al botón de eliminar
        newVacuna.querySelector('.remove-vacuna').addEventListener('click', function() {
            this.closest('.vacuna-item').remove();
        });
    });

    // Eliminar vacunas
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-vacuna')) {
            e.target.closest('.vacuna-item').remove();
        }
    });
</script>
<?php include(BASE_PATH . '/includes/footer.php'); ?>
<?php 
// Al final del archivo
ob_end_flush();
?>