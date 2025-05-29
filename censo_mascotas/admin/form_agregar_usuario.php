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

require_once BASE_PATH . '/includes/funciones.php';
$page_title = "Agregar Nuevo Usuario al Sistema";
include(__DIR__ . '/includes_panel/header_panel.php');
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header card-header-custom">
            <h3 class="mb-0"><i class="fas fa-user-plus"></i> Agregar Nuevo Usuario al Sistema</h3>
        </div>
        <div class="card-body">
            <form action="procesar_alta_usuario.php" method="post">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombre_completo" class="form-label">Nombre Completo del Usuario <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required>
                    </div>
                    <div class="col-md-6">
                        <label for="campana_lugar" class="form-label">Campaña/Lugar de Servicio <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="campana_lugar" name="campana_lugar" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="rol" class="form-label">Rol del Usuario <span class="text-danger">*</span></label>
                    <select class="form-select" id="rol" name="rol" required>
                        <option value="">Seleccionar rol...</option>
                        <?php if (defined('ROLES') && is_array(ROLES)): ?>
                            <?php foreach (ROLES as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="registrador">Registrador (Error: ROLES no definido)</option>
                            <option value="veterinario">Veterinario (Error: ROLES no definido)</option>
                            <option value="admin">Administrador (Error: ROLES no definido)</option>
                        <?php endif; ?>
                    </select>
                </div>
                 <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1" checked>
                    <label class="form-check-label" for="activo">Usuario Activo</label>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-3">
    <button type="submit" class="btn btn-purple-primary">Guardar Registro</button>
    <a href="<?php echo BASE_URL; ?>/admin/gestion_usuarios.php" class="btn btn-red-cancel">Cancelar</a>
</div>
            </form>
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
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeeba;
    color: #856404;
}

.badge {
    font-size: 0.85em;
    font-weight: 600;
}

.bg-success {
    background-color: #28a745!important;
}

.bg-secondary {
    background-color: #6c757d!important;
}

.table-purple {
    background-color: var(--purple-primary);
    color: var(--white);
}

.btn-purple-primary {
    background-color: var(--purple-primary);
    border-color: var(--purple-primary);
    color: var(--white);
}

.btn-purple-primary:hover {
    background-color: var(--purple-secondary);
    border-color: var(--purple-secondary);
}
</style>
<?php include(__DIR__ . '/includes_panel/footer_panel.php'); ?>