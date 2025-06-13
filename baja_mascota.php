<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

if (!isset($_GET['tutor'])) {
    header("Location: " . BASE_URL . "/buscar.php");
    exit();
}

$id_tutor = $_GET['tutor'];

// Verificar que el tutor existe
$stmt = $conn->prepare("SELECT id_tutor FROM tutores WHERE id_tutor = ?");
$stmt->bind_param("i", $id_tutor);
$stmt->execute();
$tutor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tutor) {
    header("Location: " . BASE_URL . "/buscar.php");
    exit();
}

$page_title = "Agregar Mascota";
include(BASE_PATH . '/includes/header.php');

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = sanitizar_input($_POST['nombre']);
    $especie = sanitizar_input($_POST['especie']);
    $raza = isset($_POST['raza']) ? sanitizar_input($_POST['raza']) : null;
    $color = isset($_POST['color']) ? sanitizar_input($_POST['color']) : null;
    $edad = intval($_POST['edad']);
    $genero = sanitizar_input($_POST['genero']);
    $esterilizado = isset($_POST['esterilizado']) ? 1 : 0;
    $incapacidad = isset($_POST['incapacidad']) ? 1 : 0;
    $descripcion_incapacidad = isset($_POST['descripcion_incapacidad']) ? sanitizar_input($_POST['descripcion_incapacidad']) : null;
    $comentarios = isset($_POST['comentarios']) ? sanitizar_input($_POST['comentarios']) : null;

    try {
        // Procesar foto de la mascota
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

        // Procesar vacunas si existen
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

        $_SESSION['mensaje'] = "Mascota registrada correctamente";
        $_SESSION['tipo_mensaje'] = "success";
        header("Location: " . BASE_URL . "/perfil.php?id=$id_tutor");
        exit();

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al registrar mascota: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
}
?>

<div class="container">
    <h2 class="mb-4 text-purple">Agregar Nueva Mascota</h2>
    
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?tutor=<?= $id_tutor ?>" method="post" enctype="multipart/form-data">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-purple text-white">
                <h5 class="mb-0">Datos Básicos</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4 text-center">
                        <div class="mb-3">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center mb-2" style="height: 150px;">
                                <span class="text-muted">Previsualización</span>
                            </div>
                            <input type="file" name="foto_mascota" id="foto_mascota" accept="image/*" class="form-control form-control-sm">
                            <small class="text-muted">Formatos: JPG, PNG (máx. 2MB)</small>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Nombre*</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Especie*</label>
                                <select class="form-select" name="especie" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Perro">Perro</option>
                                    <option value="Gato">Gato</option>
                                    <option value="Ave">Ave</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Raza</label>
                                <input type="text" class="form-control" name="raza">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Color</label>
                                <input type="text" class="form-control" name="color">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Edad (meses)*</label>
                                <input type="number" class="form-control" name="edad" min="0" max="600" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Género*</label>
                                <select class="form-select" name="genero" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Macho">Macho</option>
                                    <option value="Hembra">Hembra</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mt-4 pt-1">
                                    <input class="form-check-input" type="checkbox" name="esterilizado" id="esterilizado">
                                    <label class="form-check-label" for="esterilizado">Esterilizado</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Salud -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-purple text-white">
                <h5 class="mb-0">Salud</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="incapacidad" id="incapacidad">
                            <label class="form-check-label" for="incapacidad">Tiene incapacidad</label>
                        </div>
                        <div id="incapacidad_fields" class="mt-2" style="display:none;">
                            <label class="form-label small">Descripción</label>
                            <textarea class="form-control" name="descripcion_incapacidad" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label">Comentarios</label>
                        <textarea class="form-control" name="comentarios" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Vacunas (Dinámicas) -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-purple text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Vacunas</h5>
                <button type="button" class="btn btn-sm btn-light" id="add-vacuna">
                    <i class="bi bi-plus"></i> Agregar Vacuna
                </button>
            </div>
            <div class="card-body">
                <div class="vacunas-container">
                    <!-- Vacunas se agregarán aquí dinámicamente -->
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-end gap-2">
            <a href="<?= BASE_URL ?>/perfil.php?id=<?= $id_tutor ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-purple">Registrar Mascota</button>
        </div>
    </form>
</div>

<script>
// Mostrar/ocultar campos de incapacidad
document.getElementById('incapacidad').addEventListener('change', function() {
    document.getElementById('incapacidad_fields').style.display = this.checked ? 'block' : 'none';
});

// Vacunas dinámicas
document.getElementById('add-vacuna').addEventListener('click', function() {
    const container = document.querySelector('.vacunas-container');
    const count = container.children.length + 1;
    
    const vacunaHTML = `
        <div class="vacuna-item mb-3 p-3 border rounded">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Vacuna ${count}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-vacuna">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-5">
                    <label class="form-label small">Nombre*</label>
                    <input type="text" class="form-control" name="vacunas[nombre][]" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Fecha*</label>
                    <input type="date" class="form-control" name="vacunas[fecha][]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Comentarios</label>
                    <input type="text" class="form-control" name="vacunas[comentarios][]">
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', vacunaHTML);
});

// Eliminar vacunas
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-vacuna')) {
        e.target.closest('.vacuna-item').remove();
    }
});

// Previsualización de foto
document.getElementById('foto_mascota').addEventListener('change', function(e) {
    const preview = this.previousElementSibling;
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="img-fluid" style="max-height: 150px;">`;
        }
        reader.readAsDataURL(this.files[0]);
    }
});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>