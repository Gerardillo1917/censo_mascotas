<?php
ob_start();
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

requerir_autenticacion_admin();
iniciar_sesion_segura();

// Procesar activación/desactivación de campaña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_campana'])) {
    $id_campana = filter_input(INPUT_POST, 'id_campana', FILTER_SANITIZE_NUMBER_INT);
    $activa = filter_input(INPUT_POST, 'activa', FILTER_SANITIZE_NUMBER_INT);
    
    try {
        $stmt = $conn->prepare("UPDATE campanas SET activa = ? WHERE id_campana = ?");
        $nuevo_estado = ($activa == 1) ? 0 : 1;
        $stmt->bind_param("ii", $nuevo_estado, $id_campana);
        $stmt->execute();
        $stmt->close();
        
        $accion = $nuevo_estado ? 'activada' : 'desactivada';
        registrar_acceso($_SESSION['panel_admin_id'], true, "Campaña $accion (ID: $id_campana)");
        redirigir_con_mensaje('/admin/gestion_campanas.php', 'success', "Campaña $accion con éxito");
    } catch (Exception $e) {
        registrar_error('Error al cambiar estado de campaña: ' . $e->getMessage());
        redirigir_con_mensaje('/admin/gestion_campanas.php', 'danger', 'Error al procesar la solicitud');
    }
}

// Configuración de paginación
$por_pagina = 10;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * $por_pagina;

// Procesar búsqueda
$busqueda = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['buscar'])) {
    $busqueda['nombre'] = filter_input(INPUT_GET, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: null;
    $busqueda['tipo'] = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: null;
    $busqueda['estado'] = filter_input(INPUT_GET, 'estado', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: null;
}

// Obtener campañas con filtros
try {
    $sql = "SELECT id_campana, nombre, tipo, descripcion, fecha_inicio, fecha_fin, localidades, activa FROM campanas WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($busqueda['nombre'])) {
        $sql .= " AND nombre LIKE ?";
        $params[] = "%" . $busqueda['nombre'] . "%";
        $types .= "s";
    }
    if (!empty($busqueda['tipo'])) {
        $sql .= " AND tipo = ?";
        $params[] = $busqueda['tipo'];
        $types .= "s";
    }
    if (!empty($busqueda['estado'])) {
        if ($busqueda['estado'] === 'activas') {
            $sql .= " AND activa = 1";
        } elseif ($busqueda['estado'] === 'inactivas') {
            $sql .= " AND activa = 0";
        }
    }
    
    $sql .= " ORDER BY fecha_inicio DESC LIMIT ? OFFSET ?";
    $params[] = $por_pagina;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $campanas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Contar total de campañas para paginación
    $sql_count = "SELECT COUNT(*) FROM campanas WHERE 1=1";
    $params_count = [];
    $types_count = "";
    
    if (!empty($busqueda['nombre'])) {
        $sql_count .= " AND nombre LIKE ?";
        $params_count[] = "%" . $busqueda['nombre'] . "%";
        $types_count .= "s";
    }
    if (!empty($busqueda['tipo'])) {
        $sql_count .= " AND tipo = ?";
        $params_count[] = $busqueda['tipo'];
        $types_count .= "s";
    }
    if (!empty($busqueda['estado'])) {
        if ($busqueda['estado'] === 'activas') {
            $sql_count .= " AND activa = 1";
        } elseif ($busqueda['estado'] === 'inactivas') {
            $sql_count .= " AND activa = 0";
        }
    }
    
    $stmt = $conn->prepare($sql_count);
    if ($params_count) {
        $stmt->bind_param($types_count, ...$params_count);
    }
    $stmt->execute();
    $total_campanas = $stmt->get_result()->fetch_row()[0];
    $stmt->close();
    
    $total_paginas = ceil($total_campanas / $por_pagina);
} catch (Exception $e) {
    $campanas = [];
    $_SESSION['mensaje_panel'] = "Error al cargar campañas: " . $e->getMessage();
    $_SESSION['tipo_mensaje_panel'] = "danger";
}

include(BASE_PATH . '/admin/includes_panel/header_panel.php');
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-clipboard-list"></i> Gestión de Campañas</h2>
        <div>
            <a href="reportes.php" class="btn btn-primary me-2">
                <i class="fas fa-chart-bar"></i> Métricas
            </a>
            <a href="form_agregar_campana.php" class="btn btn-purple-primary">
                <i class="fas fa-plus"></i> Nueva Campaña
            </a>
        </div>
    </div>

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
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre de Campaña</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($busqueda['nombre'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Todos</option>
                        <option value="vacunacion" <?= isset($busqueda['tipo']) && $busqueda['tipo'] === 'vacunacion' ? 'selected' : '' ?>>Vacunación</option>
                        <option value="esterilizacion" <?= isset($busqueda['tipo']) && $busqueda['tipo'] === 'esterilizacion' ? 'selected' : '' ?>>Esterilización</option>
                        <option value="consulta" <?= isset($busqueda['tipo']) && $busqueda['tipo'] === 'consulta' ? 'selected' : '' ?>>Consulta</option>
                        <option value="otro" <?= isset($busqueda['tipo']) && $busqueda['tipo'] === 'otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="activas" <?= isset($busqueda['estado']) && $busqueda['estado'] === 'activas' ? 'selected' : '' ?>>Activas</option>
                        <option value="inactivas" <?= isset($busqueda['estado']) && $busqueda['estado'] === 'inactivas' ? 'selected' : '' ?>>Inactivas</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" name="buscar" class="btn btn-purple-primary me-2"><i class="fas fa-search"></i> Buscar</button>
                    <a href="gestion_campanas.php" class="btn btn-secondary"><i class="fas fa-eraser"></i> Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm card-purple-border">
        <div class="card-header bg-purple">
            <h5 class="mb-0">Listado de Campañas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-purple">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Fechas</th>
                            <th>Localidades</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($campanas)): ?>
                            <?php foreach ($campanas as $campana): ?>
                            <tr>
                                <td><?= htmlspecialchars($campana['id_campana']) ?></td>
                                <td><?= htmlspecialchars($campana['nombre']) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $campana['tipo'] == 'vacunacion' ? 'info' : 
                                        ($campana['tipo'] == 'esterilizacion' ? 'primary' : 'secondary') 
                                    ?>">
                                        <?= ucfirst(htmlspecialchars($campana['tipo'])) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(substr($campana['descripcion'], 0, 50)) . (strlen($campana['descripcion']) > 50 ? '...' : '') ?></td>
                                <td>
                                    <?= htmlspecialchars(date('d/m/Y', strtotime($campana['fecha_inicio']))) ?>
                                    <?php if ($campana['fecha_fin']): ?>
                                        - <?= htmlspecialchars(date('d/m/Y', strtotime($campana['fecha_fin']))) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(substr($campana['localidades'], 0, 30)) . (strlen($campana['localidades']) > 30 ? '...' : '') ?></td>
                                <td>
                                    <span class="badge bg-<?= $campana['activa'] ? 'success' : 'secondary' ?>">
                                        <?= $campana['activa'] ? 'Activa' : 'Inactiva' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="detalle_campana.php?id_campana=<?= htmlspecialchars($campana['id_campana']) ?>" class="btn btn-sm btn-info" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="form_editar_campana.php?id_campana=<?= htmlspecialchars($campana['id_campana']) ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id_campana" value="<?= htmlspecialchars($campana['id_campana']) ?>">
                                        <input type="hidden" name="activa" value="<?= htmlspecialchars($campana['activa']) ?>">
                                        <button type="submit" name="toggle_campana" class="btn btn-sm btn-<?= $campana['activa'] ? 'danger' : 'success' ?>" 
                                                onclick="return confirm('¿Está seguro de <?= $campana['activa'] ? 'desactivar' : 'activar' ?> esta campaña?')">
                                            <i class="fas fa-<?= $campana['activa'] ? 'times' : 'check' ?>"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay campañas registradas.</td>
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
</style>

<?php
include(BASE_PATH . '/admin/includes_panel/footer_panel.php');
ob_end_flush();
?>