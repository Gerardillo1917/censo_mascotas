<?php
require_once __DIR__ . '/../config.php'; 
// ---- INICIO BLOQUE DE SEGURIDAD DEL PANEL ----
if (!isset($_SESSION['panel_admin_logged_in']) || $_SESSION['panel_admin_logged_in'] !== true || $_SESSION['panel_admin_rol'] !== 'admin') {
    if (function_exists('redirigir_con_mensaje')) {
         require_once BASE_PATH . '/includes/funciones.php';
         redirigir_con_mensaje('/admin/login_panel.php', 'danger', 'Acceso restringido. Por favor, inicie sesión.');
    } else {
        $_SESSION['mensaje_login_panel'] = 'Acceso restringido. Por favor, inicie sesión.';
        header("Location: " . BASE_URL . "/admin/login_panel.php");
        exit();
    }
}
// ---- FIN BLOQUE DE SEGURIDAD DEL PANEL ----

require_once BASE_PATH . '/database/conexion.php'; 
require_once BASE_PATH . '/includes/funciones.php'; 

$page_title = "Gestión de Usuarios";

// Procesar activación/desactivación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_usuario'])) {
    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_SANITIZE_NUMBER_INT);
    $activo = filter_input(INPUT_POST, 'activo', FILTER_SANITIZE_NUMBER_INT);
    
    try {
        $stmt = $conn->prepare("UPDATE usuarios SET activo = ? WHERE id_usuario = ?");
        $nuevo_estado = ($activo == 1) ? 0 : 1; // Cambiar estado: 1 -> 0, 0 -> 1
        $stmt->bind_param("ii", $nuevo_estado, $id_usuario);
        $stmt->execute();
        $stmt->close();
        
        $accion = $nuevo_estado ? 'activado' : 'desactivado';
        registrar_acceso($id_usuario, true, "Usuario $accion por admin");
        redirigir_con_mensaje('/admin/gestion_usuarios.php', 'success', "Usuario $accion con éxito");
    } catch (Exception $e) {
        registrar_error('Error al cambiar estado de usuario: ' . $e->getMessage());
        redirigir_con_mensaje('/admin/gestion_usuarios.php', 'danger', 'Error al procesar la solicitud');
    }
}

// Obtener todos los usuarios
try {
    $stmt = $conn->prepare("SELECT id_usuario, username, nombre_completo, rol, campana_lugar, fecha_registro, ultimo_acceso, activo FROM usuarios ORDER BY id_usuario DESC");
    $stmt->execute();
    $usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $usuarios = [];
    $_SESSION['mensaje_panel'] = "Error al cargar usuarios: " . $e->getMessage();
    $_SESSION['tipo_mensaje_panel'] = "danger";
}

include(__DIR__ . '/includes_panel/header_panel.php');
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users-cog"></i> Gestión de Usuarios del Sistema</h2>
        <a href="form_agregar_usuario.php" class="btn btn-purple-primary">
            <i class="fas fa-user-plus"></i> Agregar Nuevo Usuario
        </a>
    </div>

    <?php if (isset($_SESSION['mensaje_credenciales'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>¡Usuario Creado!</strong>
            <?php echo $_SESSION['mensaje_credenciales']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensaje_credenciales']); ?>
    <?php endif; ?>

    <div class="card shadow-sm card-purple-border">
        <div class="card-header bg-purple">
            <h5 class="mb-0">Listado de Usuarios</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-purple">
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Nombre Completo</th>
                            <th>Rol</th>
                            <th>Campaña/Lugar</th>
                            <th>Fecha Registro</th>
                            <th>Último Acceso</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($usuarios)): ?>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
                                <td><?= htmlspecialchars($usuario['username']) ?></td>
                                <td><?= htmlspecialchars($usuario['nombre_completo']) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $usuario['rol'] == 'admin' ? 'danger' : 
                                        ($usuario['rol'] == 'veterinario' ? 'info' : 'primary') 
                                    ?>">
                                        <?= isset(ROLES[$usuario['rol']]) ? htmlspecialchars(ROLES[$usuario['rol']]) : htmlspecialchars($usuario['rol']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($usuario['campana_lugar'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($usuario['fecha_registro']))) ?></td>
                                <td><?= $usuario['ultimo_acceso'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($usuario['ultimo_acceso']))) : 'Nunca' ?></td>
                                <td>
                                    <span class="badge bg-<?= $usuario['activo'] ? 'success' : 'secondary' ?>">
                                        <?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">
                                        <input type="hidden" name="activo" value="<?= htmlspecialchars($usuario['activo']) ?>">
                                        <button type="submit" name="toggle_usuario" class="btn btn-sm btn-<?= $usuario['activo'] ? 'warning' : 'success' ?>" 
                                                onclick="return confirm('¿Está seguro de <?= $usuario['activo'] ? 'desactivar' : 'activar' ?> a este usuario?')">
                                            <i class="fas fa-<?= $usuario['activo'] ? 'lock' : 'unlock' ?>"></i>
                                            <?= $usuario['activo'] ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No hay usuarios registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --purple-primary: #8b0180;
    --purple-secondary: #6a015f;
    --white: #ffffff;
    --light-gray: #f8f9fa;
    --border-color: #dee2e6;
    --red-cancel: #dc3545;
}

/* Estilos para la tabla */
.table-purple {
    background-color: var(--purple-primary);
    color: var(--white);
}

.card-purple-border {
    border: 1px solid var(--purple-primary);
}

.card-header.bg-purple {
    background-color: var(--purple-primary) !important;
    color: var(--white) !important;
}

.btn-purple-primary {
    background-color: var(--purple-primary);
    border-color: var(--purple-primary);
    color: var(--white);
}

.btn-purple-primary:hover {
    background-color: var(--purple-secondary);
    border-color: var(--purple-secondary);
    color: var(--white);
}

.btn-red-cancel {
    background-color: var(--red-cancel);
    border-color: var(--red-cancel);
    color: var(--white);
}

.btn-red-cancel:hover {
    background-color: #c82333;
    border-color: #bd2130;
    color: var(--white);
}
</style>

<?php include(BASE_PATH . '/includes/footer.php'); ?>