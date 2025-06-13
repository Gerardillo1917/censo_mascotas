<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Forzar autenticación
requerir_autenticacion();

if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "/buscar.php");
    exit();
}

$id_mascota = $_GET['id'];


// Obtener datos actuales de la mascota
$stmt = $conn->prepare("SELECT * FROM mascotas WHERE id_mascota = ?");
$stmt->bind_param("i", $id_mascota);
$stmt->execute();
$mascota = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$mascota) {
    header("Location: " . BASE_URL . "/buscar.php");
    exit();
}

// Obtener vacunas de la mascota
$stmt_vacunas = $conn->prepare("SELECT * FROM vacunas WHERE id_mascota = ?");
$stmt_vacunas->bind_param("i", $id_mascota);
$stmt_vacunas->execute();
$vacunas = $stmt_vacunas->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_vacunas->close();

$page_title = "Editar Mascota";
include(BASE_PATH . '/includes/header.php');

// Procesar actualización
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
        // Procesar foto si se subió una nueva
        $foto_ruta = $mascota['foto_ruta'];
        if (!empty($_FILES['foto_mascota']['name'])) {
            if (!empty($foto_ruta)) {
                // Eliminar foto anterior si existe
                $ruta_anterior = str_replace(BASE_URL, BASE_PATH, $foto_ruta);
                if (file_exists($ruta_anterior)) {
                    unlink($ruta_anterior);
                }
            }
            $foto_ruta = subirFoto('foto_mascota', 'mascotas', 'mascota_');
        }

        // Actualizar mascota
        $stmt = $conn->prepare("UPDATE mascotas SET 
            nombre = ?,
            especie = ?,
            raza = ?,
            color = ?,
            edad = ?,
            genero = ?,
            esterilizado = ?,
            incapacidad = ?,
            descripcion_incapacidad = ?,
            comentarios = ?,
            foto_ruta = ?
            WHERE id_mascota = ?
        ");

        $stmt->bind_param(
            "ssssisiiissi",
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
            $foto_ruta,
            $id_mascota
        );

        $stmt->execute();
        $stmt->close();

        // Procesar vacunas (eliminar las existentes y agregar las nuevas)
        $conn->query("DELETE FROM vacunas WHERE id_mascota = $id_mascota");

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

        $_SESSION['mensaje'] = "Mascota actualizada correctamente";
        $_SESSION['tipo_mensaje'] = "success";
        header("Location: " . BASE_URL . "/perfil.php?id=" . $mascota['id_tutor']);
        exit();

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al actualizar mascota: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
}
?>

<div class="container">
    <h2 class="mb-4 text-purple">Editar Mascota</h2>
    
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?id=<?= $id_mascota ?>" method="post" enctype="multipart/form-data">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-purple text-white">
                <h5 class="mb-0">Datos Básicos</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4 text-center">
                        <div class="mb-3">
                            <?php if (!empty($mascota['foto_ruta'])): ?>
                                <img src="<?= htmlspecialchars($mascota['foto_ruta']) ?>" id="preview-foto" class="img-thumbnail mb-2" style="max-height: 200px;">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center mb-2" style="height: 200px;">
                                    <span class="text-muted">Sin foto</span>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="foto_mascota" id="foto_mascota" accept="image/*" class="form-control form-control-sm">
                            <small class="text-muted">Formatos: JPG, PNG (máx. 2MB)</small>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Nombre*</label>
                                <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($mascota['nombre']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Especie*</label>
                                <select class="form-select" name="especie" required>
                                    <option value="Perro" <?= $mascota['especie'] == 'Perro' ? 'selected' : '' ?>>Perro</option>
                                    <option value="Gato" <?= $mascota['especie'] == 'Gato' ? 'selected' : '' ?>>Gato</option>
                                    <option value="Ave" <?= $mascota['especie'] == 'Ave' ? 'selected' : '' ?>>Ave</option>
                                    <option value="Otro" <?= $mascota['especie'] == 'Otro' ? 'selected' : '' ?>>Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Raza</label>
                                <input type="text" class="form-control" name="raza" value="<?= htmlspecialchars($mascota['raza'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Color</label>
                                <input type="text" class="form-control" name="color" value="<?= htmlspecialchars($mascota['color'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Edad (años)*</label>
                                <input type="number" class="form-control" name="edad" min="0" max="600" value="<?= $mascota['edad'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Género*</label>
                                <select class="form-select" name="genero" required>
                                    <option value="Macho" <?= $mascota['genero'] == 'Macho' ? 'selected' : '' ?>>Macho</option>
                                    <option value="Hembra" <?= $mascota['genero'] == 'Hembra' ? 'selected' : '' ?>>Hembra</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mt-4 pt-1">
                                    <input class="form-check-input" type="checkbox" name="esterilizado" id="esterilizado" <?= $mascota['esterilizado'] ? 'checked' : '' ?>>
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
        <div class="card-header bg-purple text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Salud</h4>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="incapacidad" id="incapacidad" <?= $mascota['incapacidad'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="incapacidad">Tiene incapacidad</label>
                        </div>
                        <div id="incapacidad_fields" style="<?= $mascota['incapacidad'] ? 'display:block;' : 'display:none;' ?>">
                            <label class="form-label small">Descripción</label>
                            <textarea class="form-control" name="descripcion_incapacidad" rows="2"><?= htmlspecialchars($mascota['descripcion_incapacidad'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label">Comentarios</label>
                        <textarea class="form-control" name="comentarios" rows="2"><?= htmlspecialchars($mascota['comentarios'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Vacunas -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-purple text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Vacunas</h5>
                <button type="button" class="btn btn-sm btn-light" id="add-vacuna">
                    <i class="bi bi-plus"></i> Agregar Vacuna
                </button>
            </div>
            <div class="card-body">
                <div class="vacunas-container">
                    <?php foreach ($vacunas as $index => $vacuna): ?>
                        <div class="vacuna-item mb-3 p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Vacuna <?= $index + 1 ?></h6>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-vacuna">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label small">Nombre*</label>
                                    <input type="text" class="form-control" name="vacunas[nombre][]" value="<?= htmlspecialchars($vacuna['nombre_vacuna']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Fecha*</label>
                                    <input type="date" class="form-control" name="vacunas[fecha][]" value="<?= htmlspecialchars($vacuna['fecha_aplicacion']) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label
                                     <div class="col-md-3">
                                    <label class="form-label small">Comentarios</label>
                                    <input type="text" class="form-control" name="vacunas[comentarios][]" value="<?= htmlspecialchars($vacuna['comentarios'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-end gap-2">
            <a href="<?= BASE_URL ?>/perfil.php?id=<?= $mascota['id_tutor'] ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-purple">Guardar Cambios</button>
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
    const preview = document.getElementById('preview-foto') || this.previousElementSibling;
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                preview.innerHTML = `<img src="${e.target.result}" class="img-fluid" style="max-height: 200px;">`;
            }
        }
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
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
    .img-thumbnail {
        max-height: 200px;
        object-fit: cover;
    }
    .vacuna-item {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 10px;
    }
    @media (max-width: 768px) {
        .img-thumbnail {
            max-height: 150px;
        }
    }
</style>
<style>
    .btn-guardar {
        background-color: #8b0180; /* morado Bootstrap */
        color: #fff;
        border: none;
        padding: 0.5rem 1.2rem;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .btn-guardar:hover {
        background-color: #8b0180;
    }

    .btn-cancelar {
        background-color: #dc3545; /* rojo Bootstrap */
        color: #fff;
        border: none;
        padding: 0.5rem 1.2rem;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-cancelar:hover {
        background-color: #b02a37;
    }
</style>
<?php include(BASE_PATH . '/includes/footer.php'); ?>