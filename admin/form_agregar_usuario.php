<?php
// Evitar salida previa
ob_start();

require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Forzar autenticación de administrador
requerir_autenticacion_admin();
iniciar_sesion_segura();

$page_title = "Agregar Nuevo Usuario";
$mensaje_credenciales = null;
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validación y sanitización
    $nombre_completo = filter_input(INPUT_POST, 'nombre_completo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $campana_lugar = filter_input(INPUT_POST, 'campana_lugar', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $rol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validar campos
    if (empty($nombre_completo)) {
        $errores[] = "El nombre completo es requerido.";
    }
    if (empty($campana_lugar)) {
        $errores[] = "El lugar de campaña es requerido.";
    }
    if (empty($rol) || !array_key_exists($rol, ROLES)) {
        $errores[] = "El rol seleccionado no es válido.";
    }

    if (empty($errores)) {
        // Generación de credenciales seguras
        $username = generarUsernameUnico($conn, $nombre_completo);
        $password = generarPasswordSeguro();
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Transacción para mayor seguridad
        $conn->begin_transaction();

        try {
            // Insertar usuario
            $stmt = $conn->prepare("INSERT INTO usuarios (username, password_hash, rol, nombre_completo, campana_lugar, activo) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $conn->error);
            }
            $stmt->bind_param("sssssi", $username, $password_hash, $rol, $nombre_completo, $campana_lugar, $activo);
            if (!$stmt->execute()) {
                throw new Exception("Error al crear el usuario: " . $stmt->error);
            }
            $id_usuario = $stmt->insert_id;
            $stmt->close();

            $conn->commit();

            // Generar mensaje de éxito
            $mensaje_credenciales = '
            <div class="alert alert-success">
                <h5><i class="fas fa-user-check me-2"></i>Usuario Creado - ID: ' . htmlspecialchars($id_usuario) . '</h5>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nombre:</strong> ' . htmlspecialchars($nombre_completo) . '</p>
                        <p><strong>Rol:</strong> ' . htmlspecialchars(ROLES[$rol]) . '</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Usuario:</strong> <code>' . htmlspecialchars($username) . '</code></p>
                        <p><strong>Contraseña:</strong> <code>' . htmlspecialchars($password) . '</code></p>
                    </div>
                </div>
                <div class="mt-2 alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    IMPORTANTE: Anote o guarde estas credenciales en un lugar seguro. No se mostrarán nuevamente.
                </div>
            </div>';

            // Registrar acción
            registrar_acceso($_SESSION['panel_admin_id'], true, "Usuario creado: $username (ID: $id_usuario)");
        } catch (Exception $e) {
            $conn->rollback();
            $errores[] = "Error al crear el usuario: " . $e->getMessage();
            registrar_error("Error en form_agregar_usuario.php: " . $e->getMessage());
        }
    }
}
ob_end_clean();
include(BASE_PATH . '/admin/includes_panel/header_panel.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Censo de Mascotas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --color-primario: #8b0180;
            --color-secundario: #6a015f;
            --color-blanco: #ffffff;
        }
        .btn-primary {
            background-color: var(--color-primario);
            border-color: var(--color-primario);
        }
        .btn-primary:hover {
            background-color: var(--color-secundario);
            border-color: var(--color-secundario);
        }
        .card-header {
            background-color: var(--color-primario);
            color: var(--color-blanco);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Agregar Nuevo Usuario</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['panel_admin_nombre']); ?> (Administrador)</p>

        <!-- Mostrar errores -->
        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Mostrar mensaje de credenciales -->
        <?php if ($mensaje_credenciales): ?>
            <?php echo $mensaje_credenciales; ?>
        <?php endif; ?>

        <!-- Formulario para crear usuario -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Nuevo Usuario</h5>
            </div>
            <div class="card-body">
                <form method="post" action="form_agregar_usuario.php">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nombre_completo" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" value="<?php echo isset($_POST['nombre_completo']) ? htmlspecialchars($_POST['nombre_completo']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="campana_lugar" class="form-label">Lugar de Campaña</label>
                            <input type="text" class="form-control" id="campana_lugar" name="campana_lugar" value="<?php echo isset($_POST['campana_lugar']) ? htmlspecialchars($_POST['campana_lugar']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="">Seleccione un rol</option>
                                <?php foreach (ROLES as $key => $value): ?>
                                    <option value="<?php echo htmlspecialchars($key); ?>" <?php echo isset($_POST['rol']) && $_POST['rol'] === $key ? 'selected' : ''; ?>><?php echo htmlspecialchars($value); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" <?php echo isset($_POST['activo']) && $_POST['activo'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="activo">Activo</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Crear Usuario</button>
                    <a href="<?php echo BASE_URL; ?>/admin/gestion_usuarios.php" class="btn btn-secondary mt-3">Volver a Gestión</a>
                </form>
            </div>
        </div>

        <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="btn btn-danger mt-3">Cerrar Sesión</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include(BASE_PATH . '/admin/includes_panel/footer_panel.php'); ?>