<?php
// Evitar salida previa
ob_start();

require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Establecer zona horaria
date_default_timezone_set('America/Mexico_City');

// Forzar autenticación de administrador
requerir_autenticacion_admin();
iniciar_sesion_segura();

$page_title = "Perfil de Usuario";
$errores = [];

// Obtener ID del usuario
$id_usuario = filter_input(INPUT_GET, 'id_usuario', FILTER_SANITIZE_NUMBER_INT);
if (!$id_usuario || !is_numeric($id_usuario)) {
    redirigir_con_mensaje('/admin/gestion_usuarios.php', 'danger', 'ID de usuario inválido');
}

// Obtener datos del usuario
try {
    $stmt = $conn->prepare("SELECT id_usuario, username, nombre_completo, rol, campana_lugar, fecha_registro, ultimo_acceso, activo, comentarios 
                            FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$usuario) {
        redirigir_con_mensaje('/admin/gestion_usuarios.php', 'danger', 'Usuario no encontrado');
    }
} catch (Exception $e) {
    registrar_error('Error al cargar usuario: ' . $e->getMessage());
    redirigir_con_mensaje('/admin/gestion_usuarios.php', 'danger', 'Error al cargar usuario');
}

// Configuración de paginación para registros
$registros_por_pagina = 5;
$pagina_actual_registros = isset($_GET['pagina_registros']) ? max(1, intval($_GET['pagina_registros'])) : 1;
$offset_registros = ($pagina_actual_registros - 1) * $registros_por_pagina;

// Configuración de paginación para mascotas
$mascotas_por_pagina = 5;
$pagina_actual_mascotas = isset($_GET['pagina_mascotas']) ? max(1, intval($_GET['pagina_mascotas'])) : 1;
$offset_mascotas = ($pagina_actual_mascotas - 1) * $mascotas_por_pagina;

// Configuración de paginación para vacunas
$vacunas_por_pagina = 5;
$pagina_actual_vacunas = isset($_GET['pagina_vacunas']) ? max(1, intval($_GET['pagina_vacunas'])) : 1;
$offset_vacunas = ($pagina_actual_vacunas - 1) * $vacunas_por_pagina;

// Obtener conteo total de registros
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total 
                            FROM salud_mascotas sm 
                            JOIN mascotas m ON sm.id_mascota = m.id_mascota 
                            WHERE sm.responsable LIKE ?");
    $responsable = '%' . $usuario['nombre_completo'] . '%';
    $stmt->bind_param("s", $responsable);
    $stmt->execute();
    $total_registros = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    $total_paginas_registros = ceil($total_registros / $registros_por_pagina);
} catch (Exception $e) {
    $total_registros = 0;
    $total_paginas_registros = 1;
    registrar_error('Error al contar registros: ' . $e->getMessage());
}

// Obtener conteo total de mascotas
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total 
                            FROM mascotas m 
                            JOIN salud_mascotas sm ON m.id_mascota = sm.id_mascota 
                            WHERE sm.responsable LIKE ?");
    $stmt->bind_param("s", $responsable);
    $stmt->execute();
    $total_mascotas = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    $total_paginas_mascotas = ceil($total_mascotas / $mascotas_por_pagina);
} catch (Exception $e) {
    $total_mascotas = 0;
    $total_paginas_mascotas = 1;
    registrar_error('Error al contar mascotas: ' . $e->getMessage());
}

// Obtener conteo total de vacunas
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total 
                            FROM vacunas v 
                            JOIN mascotas m ON v.id_mascota = m.id_mascota 
                            JOIN salud_mascotas sm ON m.id_mascota = sm.id_mascota 
                            WHERE sm.responsable LIKE ?");
    $stmt->bind_param("s", $responsable);
    $stmt->execute();
    $total_vacunas = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    $total_paginas_vacunas = ceil($total_vacunas / $vacunas_por_pagina);
} catch (Exception $e) {
    $total_vacunas = 0;
    $total_paginas_vacunas = 1;
    registrar_error('Error al contar vacunas: ' . $e->getMessage());
}

// Obtener registros asociados con paginación
try {
    $stmt = $conn->prepare("SELECT sm.*, m.nombre AS nombre_mascota 
                            FROM salud_mascotas sm 
                            JOIN mascotas m ON sm.id_mascota = m.id_mascota 
                            WHERE sm.responsable LIKE ?
                            ORDER BY sm.fecha DESC, sm.hora DESC
                            LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $responsable, $registros_por_pagina, $offset_registros);
    $stmt->execute();
    $registros = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $registros = [];
    registrar_error('Error al cargar registros: ' . $e->getMessage());
    $errores[] = "Error al cargar registros: " . $e->getMessage();
}

// Obtener tutores asociados
try {
    $stmt = $conn->prepare("SELECT t.id_tutor, t.nombre, t.apellido_paterno, t.apellido_materno 
                            FROM tutores t 
                            JOIN mascotas m ON t.id_tutor = m.id_tutor 
                            JOIN salud_mascotas sm ON m.id_mascota = sm.id_mascota 
                            WHERE sm.responsable LIKE ? 
                            GROUP BY t.id_tutor");
    $stmt->bind_param("s", $responsable);
    $stmt->execute();
    $tutores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $tutores = [];
    registrar_error('Error al cargar tutores: ' . $e->getMessage());
    $errores[] = "Error al cargar tutores: " . $e->getMessage();
}

// Obtener mascotas asociadas con paginación
try {
    $stmt = $conn->prepare("SELECT m.id_mascota, m.nombre 
                            FROM mascotas m 
                            JOIN salud_mascotas sm ON m.id_mascota = sm.id_mascota 
                            WHERE sm.responsable LIKE ?
                            LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $responsable, $mascotas_por_pagina, $offset_mascotas);
    $stmt->execute();
    $mascotas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $mascotas = [];
    registrar_error('Error al cargar mascotas: ' . $e->getMessage());
    $errores[] = "Error al cargar mascotas: " . $e->getMessage();
}

// Obtener resumen de vacunas con paginación
try {
    $stmt = $conn->prepare("SELECT v.nombre_vacuna, COUNT(*) as cantidad, YEAR(v.fecha_aplicacion) as anio 
                            FROM vacunas v 
                            JOIN mascotas m ON v.id_mascota = m.id_mascota 
                            JOIN salud_mascotas sm ON m.id_mascota = sm.id_mascota 
                            WHERE sm.responsable LIKE ? 
                            GROUP BY v.nombre_vacuna, YEAR(v.fecha_aplicacion)
                            LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $responsable, $vacunas_por_pagina, $offset_vacunas);
    $stmt->execute();
    $vacunas_resumen = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $vacunas_resumen = [];
    registrar_error('Error al cargar vacunas: ' . $e->getMessage());
    $errores[] = "Error al cargar vacunas: " . $e->getMessage();
}

include(BASE_PATH . '/admin/includes_panel/header_panel.php');
?>

<div class="container mt-4">
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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-user"></i> Perfil de <?= htmlspecialchars($usuario['nombre_completo']) ?></h2>
        <a href="gestion_usuarios.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm card-purple-border">
                <div class="card-header bg-purple">
                    <h5 class="mb-0">Detalles del Usuario</h5>
                </div>
                <div class="card-body">
                    <p><strong>ID:</strong> <?= htmlspecialchars($usuario['id_usuario']) ?></p>
                    <p><strong>Usuario:</strong> <?= htmlspecialchars($usuario['username']) ?></p>
                    <p><strong>Nombre Completo:</strong> <?= htmlspecialchars($usuario['nombre_completo']) ?></p>
                    <p><strong>Rol:</strong> <?= htmlspecialchars(ROLES[$usuario['rol']] ?? $usuario['rol']) ?></p>
                    <p><strong>Campaña/Lugar:</strong> <?= htmlspecialchars($usuario['campana_lugar'] ?: 'N/A') ?></p>
                    <p><strong>Fecha Registro:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($usuario['fecha_registro']))) ?></p>
                    <p><strong>Último Acceso:</strong> <?= $usuario['ultimo_acceso'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($usuario['ultimo_acceso']))) : 'Nunca' ?></p>
                    <p><strong>Estado:</strong> 
                        <span class="badge bg-<?= $usuario['activo'] ? 'success' : 'secondary' ?>">
                            <?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </p>
                    <p><strong>Comentarios:</strong> <?= htmlspecialchars($usuario['comentarios'] ?: 'No hay comentarios.') ?></p>
                </div>
            </div>

            <!-- Tutores Asociados -->
            <div class="card shadow-sm card-purple-border mt-4">
                <div class="card-header bg-purple">
                    <h5 class="mb-0">Tutores Asociados</h5>
                </div>
                <div class="card-body">
                    <?php if ($tutores): ?>
                        <ul class="list-group">
                            <?php foreach ($tutores as $tutor): ?>
                                <li class="list-group-item">
                                    <?= htmlspecialchars($tutor['nombre'] . ' ' . $tutor['apellido_paterno'] . ' ' . ($tutor['apellido_materno'] ?? '')) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No hay tutores asociados a este usuario.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Resumen de Vacunas -->
            <div class="card shadow-sm card-purple-border mt-4">
                <div class="card-header bg-purple d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vacunas Aplicadas</h5>
                    <span class="badge bg-light text-dark">Total: <?= $total_vacunas ?></span>
                </div>
                <div class="card-body">
                    <?php if ($vacunas_resumen): ?>
                        <ul class="list-group mb-3">
                            <?php foreach ($vacunas_resumen as $vacuna): ?>
                                <li class="list-group-item">
                                    <strong><?= htmlspecialchars($vacuna['nombre_vacuna']) ?>:</strong> 
                                    <?= $vacuna['cantidad'] ?> aplicación(es) (<?= $vacuna['anio'] ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <!-- Paginación de vacunas -->
                        <nav aria-label="Paginación de vacunas">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagina_actual_vacunas > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?id_usuario=<?= $id_usuario ?>&pagina_vacunas=<?= $pagina_actual_vacunas - 1 ?>" aria-label="Anterior">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_paginas_vacunas; $i++): ?>
                                    <li class="page-item <?= $i == $pagina_actual_vacunas ? 'active' : '' ?>">
                                        <a class="page-link" href="?id_usuario=<?= $id_usuario ?>&pagina_vacunas=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagina_actual_vacunas < $total_paginas_vacunas): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?id_usuario=<?= $id_usuario ?>&pagina_vacunas=<?= $pagina_actual_vacunas + 1 ?>" aria-label="Siguiente">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php else: ?>
                        <p>No hay vacunas registradas asociadas a este usuario.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <!-- Mascotas Asociadas -->
            <div class="card shadow-sm card-purple-border mb-4">
                <div class="card-header bg-purple d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mascotas Asociadas</h5>
                    <span class="badge bg-light text-dark">Total: <?= $total_mascotas ?></span>
                </div>
                <div class="card-body">
                    <?php if ($mascotas): ?>
                        <ul class="list-group mb-3">
                            <?php foreach ($mascotas as $mascota): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($mascota['nombre']) ?>
                                    <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= htmlspecialchars($mascota['id_mascota']) ?>" class="btn btn-sm btn-info" title="Ver Salud">
                                        <i class="fas fa-stethoscope"></i>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <!-- Paginación de mascotas -->
                        <nav aria-label="Paginación de mascotas">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagina_actual_mascotas > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?id_usuario=<?= $id_usuario ?>&pagina_mascotas=<?= $pagina_actual_mascotas - 1 ?>" aria-label="Anterior">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_paginas_mascotas; $i++): ?>
                                    <li class="page-item <?= $i == $pagina_actual_mascotas ? 'active' : '' ?>">
                                        <a class="page-link" href="?id_usuario=<?= $id_usuario ?>&pagina_mascotas=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagina_actual_mascotas < $total_paginas_mascotas): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?id_usuario=<?= $id_usuario ?>&pagina_mascotas=<?= $pagina_actual_mascotas + 1 ?>" aria-label="Siguiente">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php else: ?>
                        <p>No hay mascotas asociadas a este usuario.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Registros Asociados -->
            <div class="card shadow-sm card-purple-border">
                <div class="card-header bg-purple d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Registros Asociados</h5>
                    <span class="badge bg-light text-dark">Total: <?= $total_registros ?></span>
                </div>
                <div class="card-body">
                    <?php if ($registros): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Mascota</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th>Detalle</th>
                                        <th>Información</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registros as $registro): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($registro['id_interaccion']) ?></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= htmlspecialchars($registro['id_mascota']) ?>">
                                                    <?= htmlspecialchars($registro['nombre_mascota']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($registro['tipo']) ?></td>
                                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($registro['fecha'] . ' ' . $registro['hora']))) ?></td>
                                            <td>
                                                <?php if ($registro['tipo'] === 'Procedimiento'): ?>
                                                    <?= htmlspecialchars($registro['tipo_procedimiento'] ?: 'N/A') ?>
                                                <?php else: ?>
                                                    <?= $registro['motivo'] ? htmlspecialchars($registro['motivo']) : 'N/A' ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($registro['tipo'] === 'Procedimiento'): ?>
                                                    <?= $registro['diagnostico_previo'] ? htmlspecialchars($registro['diagnostico_previo']) : 'N/A' ?>
                                                <?php else: ?>
                                                    <?= $registro['diagnostico'] ? htmlspecialchars($registro['diagnostico']) : 'N/A' ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación de registros -->
                        <nav aria-label="Paginación de registros">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagina_actual_registros > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?id_usuario=<?= $id_usuario ?>&pagina_registros=<?= $pagina_actual_registros - 1 ?>" aria-label="Anterior">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_paginas_registros; $i++): ?>
                                    <li class="page-item <?= $i == $pagina_actual_registros ? 'active' : '' ?>">
                                        <a class="page-link" href="?id_usuario=<?= $id_usuario ?>&pagina_registros=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagina_actual_registros < $total_paginas_registros): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?id_usuario=<?= $id_usuario ?>&pagina_registros=<?= $pagina_actual_registros + 1 ?>" aria-label="Siguiente">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php else: ?>
                        <p>No hay registros asociados a este usuario.</p>
                    <?php endif; ?>
                </div>
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
}

.card-purple-border {
    border: 1px solid var(--purple-primary);
    border-radius: 8px;
}

.card-header.bg-purple {
    background-color: var(--purple-primary) !important;
    color: var(--white) !important;
    border-radius: 7px 7px 0 0 !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(139, 1, 128, 0.05);
}

.page-item.active .page-link {
    background-color: var(--purple-primary);
    border-color: var(--purple-primary);
}

.page-link {
    color: var(--purple-primary);
}

.badge.bg-light {
    font-size: 0.9rem;
    padding: 0.35rem 0.65rem;
}

.list-group-item {
    transition: background-color 0.2s;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>

<?php
include(BASE_PATH . '/admin/includes_panel/footer_panel.php');
ob_end_flush();
?>