<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Forzar autenticación
requerir_autenticacion();

// Verificación de parámetro
if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "/buscar.php");
    exit();
}

$id_tutor = $_GET['id'];

// Obtener datos actuales del tutor
$stmt = $conn->prepare("SELECT * FROM tutores WHERE id_tutor = ?");
$stmt->bind_param("i", $id_tutor);
$stmt->execute();
$tutor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tutor) {
    header("Location: " . BASE_URL . "/buscar.php");
    exit();
}

// Procesar actualización - debe estar ANTES de cualquier output
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = sanitizar_input($_POST['nombre']);
    $apellido_paterno = sanitizar_input($_POST['apellido_paterno']);
    $apellido_materno = isset($_POST['apellido_materno']) ? sanitizar_input($_POST['apellido_materno']) : null;
    $edad = isset($_POST['edad']) ? intval($_POST['edad']) : null;
    $telefono = sanitizar_input($_POST['telefono']);
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : null;
    $calle = sanitizar_input($_POST['calle']);
    $numero_exterior = sanitizar_input($_POST['numero_exterior']);
    $numero_interior = isset($_POST['numero_interior']) ? sanitizar_input($_POST['numero_interior']) : null;
    $colonia = sanitizar_input($_POST['colonia']);
    $codigo_postal = sanitizar_input($_POST['codigo_postal']);

    try {
        // Procesar foto
        $foto_ruta = $tutor['foto_ruta'];
        if (!empty($_FILES['foto_tutor']['name'])) {
            if (!empty($foto_ruta)) {
                $ruta_anterior = str_replace(BASE_URL, BASE_PATH, $foto_ruta);
                if (file_exists($ruta_anterior)) {
                    unlink($ruta_anterior);
                }
            }
            $foto_ruta = subirFoto('foto_tutor', 'tutores', 'tutor_');
        }

        // Actualizar tutor
        $stmt = $conn->prepare("UPDATE tutores SET 
            nombre = ?,
            apellido_paterno = ?,
            apellido_materno = ?,
            edad = ?,
            telefono = ?,
            email = ?,
            calle = ?,
            numero_exterior = ?,
            numero_interior = ?,
            colonia = ?,
            codigo_postal = ?,
            foto_ruta = ?
            WHERE id_tutor = ?
        ");

        $stmt->bind_param(
            "sssissssssssi",
            $nombre,
            $apellido_paterno,
            $apellido_materno,
            $edad,
            $telefono,
            $email,
            $calle,
            $numero_exterior,
            $numero_interior,
            $colonia,
            $codigo_postal,
            $foto_ruta,
            $id_tutor
        );

        if ($stmt->execute()) {
            $stmt->close();
            $_SESSION['mensaje'] = "Tutor actualizado correctamente";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: " . BASE_URL . "/perfil.php?id=$id_tutor");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al actualizar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
}

// Configuración después de posibles redirecciones
$page_title = "Editar Tutor";

// Incluir header - después de toda la lógica PHP
include(BASE_PATH . '/includes/header.php');
?>

<div class="container">
    <h2 class="mb-4 text-purple">Editar Tutor</h2>
    
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?id=<?= $id_tutor ?>" method="post" enctype="multipart/form-data">
        <!-- Sección de Datos Personales -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-purple text-white">
                <h5 class="mb-0">Datos Personales</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Foto del tutor -->
                    <div class="col-md-4 text-center">
                        <div class="mb-3">
                            <?php if (!empty($tutor['foto_ruta'])): ?>
                                <img src="<?= htmlspecialchars($tutor['foto_ruta']) ?>" class="img-thumbnail mb-2" style="max-height: 200px;">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <span class="text-muted">Sin foto</span>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="foto_tutor" accept="image/*" class="form-control form-control-sm">
                            <small class="text-muted">Formatos: JPG, PNG (máx. 2MB)</small>
                        </div>
                    </div>
                    
                    <!-- Datos personales -->
                    <div class="col-md-8">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Nombre(s)*</label>
                                <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($tutor['nombre']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Apellido Paterno*</label>
                                <input type="text" class="form-control" name="apellido_paterno" value="<?= htmlspecialchars($tutor['apellido_paterno']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Apellido Materno</label>
                                <input type="text" class="form-control" name="apellido_materno" value="<?= htmlspecialchars($tutor['apellido_materno'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Edad</label>
                                <input type="number" class="form-control" name="edad" min="18" value="<?= htmlspecialchars($tutor['edad'] ?? '') ?>">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Teléfono*</label>
                                <input type="tel" class="form-control" name="telefono" value="<?= htmlspecialchars($tutor['telefono']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($tutor['email'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sección de Dirección -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-purple text-white">
                <h5 class="mb-0">Dirección</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Calle*</label>
                        <input type="text" class="form-control" name="calle" value="<?= htmlspecialchars($tutor['calle']) ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Núm Ext*</label>
                        <input type="text" class="form-control" name="numero_exterior" value="<?= htmlspecialchars($tutor['numero_exterior']) ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Núm Int</label>
                        <input type="text" class="form-control" name="numero_interior" value="<?= htmlspecialchars($tutor['numero_interior'] ?? '') ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Colonia*</label>
                        <input type="text" class="form-control" name="colonia" value="<?= htmlspecialchars($tutor['colonia']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Código Postal*</label>
                        <input type="text" class="form-control" name="codigo_postal" value="<?= htmlspecialchars($tutor['codigo_postal']) ?>" required>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-center gap-2 mt-3">
            <button type="submit" class="btn btn-guardar">Guardar Registro</button>
            <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-cancelar">Cancelar</a>
        </div>
    </form>
</div>
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