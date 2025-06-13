<?php
// Habilitar depuración temporalmente
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Establecer zona horaria
date_default_timezone_set('America/Mexico_City');

// Verificar inclusiones
if (!defined('BASE_URL') || !defined('BASE_PATH')) {
    die('Error: Configuración no cargada correctamente.');
}

// ---- INICIO BLOQUE DE SEGURIDAD DEL PANEL ----
requerir_autenticacion_admin();
// ---- FIN BLOQUE DE SEGURIDAD DEL PANEL ----

// Procesar comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_comentario'])) {
    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_SANITIZE_NUMBER_INT);
    $comentario = filter_input(INPUT_POST, 'comentario', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validar
    if (empty($id_usuario) || !is_numeric($id_usuario)) {
        $_SESSION['mensaje_panel'] = "ID de usuario inválido.";
        $_SESSION['tipo_mensaje_panel'] = "danger";
    } elseif (empty($comentario)) {
        $_SESSION['mensaje_panel'] = "El comentario no puede estar vacío.";
        $_SESSION['tipo_mensaje_panel'] = "danger";
    } else {
        try {
            $id_admin = $_SESSION['panel_admin_id'];
            $stmt = $conn->prepare("UPDATE usuarios SET comentarios = ? WHERE id_usuario = ?");
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $conn->error);
            }
            $stmt->bind_param("si", $comentario, $id_usuario);
            if (!$stmt->execute()) {
                throw new Exception("Error al guardar el comentario: " . $stmt->error);
            }
            $stmt->close();

            $_SESSION['mensaje_panel'] = "Comentario guardado con éxito para el usuario ID: $id_usuario.";
            $_SESSION['tipo_mensaje_panel'] = "success";
            registrar_acceso($id_admin, true, "Comentario añadido para usuario ID: $id_usuario");
        } catch (Exception $e) {
            $_SESSION['mensaje_panel'] = "Error al guardar el comentario: " . $e->getMessage();
            $_SESSION['tipo_mensaje_panel'] = "danger";
            registrar_error("Error en gestion_usuarios.php al guardar comentario: " . $e->getMessage());
        }
    }
    header("Location: gestion_usuarios.php");
    exit;
}

// Procesar activación/desactivación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_usuario'])) {
    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_SANITIZE_NUMBER_INT);
    $activo = filter_input(INPUT_POST, 'activo', FILTER_SANITIZE_NUMBER_INT);
    
    try {
        $stmt = $conn->prepare("UPDATE usuarios SET activo = ? WHERE id_usuario = ?");
        $nuevo_estado = ($activo == 1) ? 0 : 1;
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

// Configuración de paginación
$por_pagina = 10;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * $por_pagina;

// Procesar búsqueda
$busqueda = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['buscar'])) {
    $busqueda['id_usuario'] = filter_input(INPUT_GET, 'id_usuario', FILTER_SANITIZE_NUMBER_INT) ?: null;
    $busqueda['nombre_completo'] = filter_input(INPUT_GET, 'nombre_completo', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: null;
    $busqueda['rol'] = filter_input(INPUT_GET, 'rol', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: null;
}

// Obtener usuarios con filtros
try {
    $sql = "SELECT id_usuario, username, nombre_completo, rol, fecha_registro, ultimo_acceso, activo, comentarios FROM usuarios WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($busqueda['id_usuario'])) {
        $sql .= " AND id_usuario = ?";
        $params[] = $busqueda['id_usuario'];
        $types .= "i";
    }
    if (!empty($busqueda['nombre_completo'])) {
        $sql .= " AND nombre_completo LIKE ?";
        $params[] = "%" . $busqueda['nombre_completo'] . "%";
        $types .= "s";
    }
    if (!empty($busqueda['rol'])) {
        $sql .= " AND rol = ?";
        $params[] = $busqueda['rol'];
        $types .= "s";
    }
    
    $sql .= " ORDER BY id_usuario ASC LIMIT ? OFFSET ?";
    $params[] = $por_pagina;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Contar total de usuarios para paginación
    $sql_count = "SELECT COUNT(*) FROM usuarios WHERE 1=1";
    $params_count = [];
    $types_count = "";
    
    if (!empty($busqueda['id_usuario'])) {
        $sql_count .= " AND id_usuario = ?";
        $params_count[] = $busqueda['id_usuario'];
        $types_count .= "i";
    }
    if (!empty($busqueda['nombre_completo'])) {
        $sql_count .= " AND nombre_completo LIKE ?";
        $params_count[] = "%" . $busqueda['nombre_completo'] . "%";
        $types_count .= "s";
    }
    if (!empty($busqueda['rol'])) {
        $sql_count .= " AND rol = ?";
        $params_count[] = $busqueda['rol'];
        $types_count .= "s";
    }
    
    $stmt = $conn->prepare($sql_count);
    if ($params_count) {
        $stmt->bind_param($types_count, ...$params_count);
    }
    $stmt->execute();
    $total_usuarios = $stmt->get_result()->fetch_row()[0];
    $stmt->close();
    
    $total_paginas = ceil($total_usuarios / $por_pagina);
} catch (Exception $e) {
    $usuarios = [];
    $_SESSION['mensaje_panel'] = "Error al cargar usuarios: " . $e->getMessage();
    $_SESSION['tipo_mensaje_panel'] = "danger";
}

include(BASE_PATH . '/admin/includes_panel/header_panel.php');
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users-cog"></i> Gestión de Usuarios del Sistema</h2>
        <div>
            <a href="reportes.php" class="btn btn-primary me-2">
                <i class="fas fa-chart-bar"></i> Métricas
            </a>
            <a href="form_agregar_usuario.php" class="btn btn-purple-primary">
                <i class="fas fa-user-plus"></i> Agregar Nuevo Usuario
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['mensaje_credenciales'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>¡Usuario Creado!</strong>
            <?php echo htmlspecialchars($_SESSION['mensaje_credenciales']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensaje_credenciales']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje_panel'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['tipo_mensaje_panel']) ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['mensaje_panel']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensaje_panel'], $_SESSION['tipo_mensaje_panel']); ?>
    <?php endif; ?>

    <!-- Formulario de búsqueda -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="id_usuario" class="form-label">ID Usuario</label>
                    <input type="number" class="form-control" id="id_usuario" name="id_usuario" value="<?= htmlspecialchars($busqueda['id_usuario'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label for="nombre_completo" class="form-label">Nombre Completo</label>
                    <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" value="<?= htmlspecialchars($busqueda['nombre_completo'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label for="rol" class="form-label">Rol</label>
                    <select class="form-select" id="rol" name="rol">
                        <option value="">Todos</option>
                        <?php foreach (ROLES as $key => $value): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= isset($busqueda['rol']) && $busqueda['rol'] === $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($value) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" name="buscar" class="btn btn-purple-primary me-2"><i class="fas fa-search"></i> Buscar</button>
                    <a href="gestion_usuarios.php" class="btn btn-secondary"><i class="fas fa-eraser"></i> Limpiar</a>
                </div>
            </form>
        </div>
    </div>

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
                            <th>Fecha Registro</th>
                            <th>Último Acceso</th>
                            <th>Estado</th>
                            <th>Acciones</th>
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
                                        <?= htmlspecialchars(ROLES[$usuario['rol']] ?? $usuario['rol']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($usuario['fecha_registro']))) ?></td>
                                <td><?= $usuario['ultimo_acceso'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($usuario['ultimo_acceso']))) : 'Nunca' ?></td>
                                <td>
                                    <span class="badge bg-<?= $usuario['activo'] ? 'success' : 'secondary' ?>">
                                        <?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="perfil.php?id_usuario=<?= htmlspecialchars($usuario['id_usuario']) ?>" class="btn btn-sm btn-info" title="Ver Perfil">
                                        <i class="fas fa-user"></i>
                                    </a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">
                                        <input type="hidden" name="activo" value="<?= htmlspecialchars($usuario['activo']) ?>">
                                        <button type="submit" name="toggle_usuario" class="btn btn-sm btn-<?= $usuario['activo'] ? 'warning' : 'success' ?>" 
                                                onclick="return confirm('¿Está seguro de <?= $usuario['activo'] ? 'desactivar' : 'activar' ?> a este usuario?')">
                                            <i class="fas fa-<?= $usuario['activo'] ? 'lock' : 'unlock' ?>"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-completo-cyan" data-bs-toggle="modal" data-bs-target="#comentarioModal<?= htmlspecialchars($usuario['id_usuario']) ?>" title="Añadir Comentario">
                                        <i class="fas fa-comment"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Modal para agregar comentario -->
                            <div class="modal fade" id="comentarioModal<?= htmlspecialchars($usuario['id_usuario']) ?>" tabindex="-1" aria-labelledby="comentarioModalLabel<?= htmlspecialchars($usuario['id_usuario']) ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-purple">
                                            <h5 class="modal-title" id="comentarioModalLabel<?= htmlspecialchars($usuario['id_usuario']) ?>">Añadir Comentario para <?= htmlspecialchars($usuario['nombre_completo']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="">
                                                <input type="hidden" name="guardar_comentario" value="1">
                                                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">
                                                <div class="mb-3">
                                                    <label for="comentario<?= htmlspecialchars($usuario['id_usuario']) ?>" class="form-label">Comentario</label>
                                                    <textarea class="form-control" id="comentario<?= htmlspecialchars($usuario['id_usuario']) ?>" name="comentario" rows="4" required maxlength="500"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-purple-primary">Guardar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay usuarios registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <nav aria-label="Paginación">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link page-link-purple" href="?pagina=<?= $pagina - 1 ?>&<?= http_build_query($busqueda) ?>">Anterior</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                <a class="page-link page-link-purple <?= $i == $pagina ? 'active-purple' : '' ?>" href="?pagina=<?= $i ?>&<?= http_build_query($busqueda) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $pagina >= $total_paginas ? 'disabled' : '' ?>">
                            <a class="page-link page-link-purple" href="?pagina=<?= $pagina + 1 ?>&<?= http_build_query($busqueda) ?>">Siguiente</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
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

.page-link-purple {
    color: var(--purple-primary);
    border-color: var(--purple-primary);
}

.page-link-purple:hover {
    background-color: var(--purple-secondary);
    color: var(--white);
    border-color: var(--purple-secondary);
}

.page-item.active .page-link-purple.active-purple {
    background-color: var(--purple-primary);
    border-color: var(--purple-primary);
    color: var(--white);
}

.page-item.disabled .page-link-purple {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
}

.btn-completo-cyan {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: #ffffff;
}

.btn-completo-cyan:hover {
    background-color: #138496;
    border-color: #117a8b;
    color: #ffffff;
}
</style>

<?php
include(BASE_PATH . '/admin/includes_panel/footer_panel.php');
ob_end_flush();
?>