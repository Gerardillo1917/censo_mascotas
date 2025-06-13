<?php
ob_start();
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

requerir_autenticacion_admin();
iniciar_sesion_segura();

include(BASE_PATH . '/admin/includes_panel/header_panel.php');
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-home"></i> Panel de Administración</h2>
                <a href="<?= BASE_URL ?>/admin/logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>

            <?php if (isset($_SESSION['mensaje_panel'])): ?>
                <div class="alert alert-<?= htmlspecialchars($_SESSION['tipo_mensaje_panel']) ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['mensaje_panel']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['mensaje_panel'], $_SESSION['tipo_mensaje_panel']); ?>
            <?php endif; ?>

            <div class="row">
                <!-- Tarjeta de Gestión de Usuarios -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm card-purple-border h-100">
                        <div class="card-header bg-purple">
                            <h5 class="mb-0"><i class="fas fa-users-cog"></i> Gestión de Usuarios</h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="card-text">Administra los usuarios del sistema, sus permisos y acceso.</p>
                            <div class="mt-auto">
                                <a href="gestion_usuarios.php" class="btn btn-purple-primary w-100">
                                    <i class="fas fa-arrow-right"></i> Acceder
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Gestión de Campañas -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm card-purple-border h-100">
                        <div class="card-header bg-purple">
                            <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Gestión de Campañas</h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="card-text">Crea y administra campañas de vacunación, esterilización y otros procedimientos.</p>
                            <div class="mt-auto">
                                <a href="gestion_campanas.php" class="btn btn-purple-primary w-100">
                                    <i class="fas fa-arrow-right"></i> Acceder
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Reportes -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm card-purple-border h-100">
                        <div class="card-header bg-purple">
                            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Reportes y Métricas</h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="card-text">Visualiza estadísticas y genera reportes de las actividades realizadas.</p>
                            <div class="mt-auto">
                                <a href="reportes.php" class="btn btn-purple-primary w-100">
                                    <i class="fas fa-arrow-right"></i> Acceder
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Configuración -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm card-purple-border h-100">
                        <div class="card-header bg-purple">
                            <h5 class="mb-0"><i class="fas fa-cogs"></i> Configuración del Sistema</h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="card-text">Configura parámetros generales del sistema y preferencias.</p>
                            <div class="mt-auto">
                                <a href="configuracion.php" class="btn btn-purple-primary w-100">
                                    <i class="fas fa-arrow-right"></i> Acceder
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}
</style>

<?php
include(BASE_PATH . '/admin/includes_panel/footer_panel.php');
ob_end_flush();
?>