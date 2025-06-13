<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

// Validar ID de mascota
$id_mascota = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_mascota <= 0) {
    redirigir_con_mensaje('buscar.php', 'danger', 'Mascota no especificada');
}

// Obtener datos básicos de la mascota
ob_start();
try {
    $stmt = $conn->prepare("SELECT nombre FROM mascotas WHERE id_mascota = ?");
    $stmt->bind_param("i", $id_mascota);
    $stmt->execute();
    $result = $stmt->get_result();
    $mascota = $result->fetch_assoc();
    $stmt->close();

    if (!$mascota) {
        redirigir_con_mensaje('buscar.php', 'danger', 'Mascota no encontrada');
    }
} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje("salud/nueva_vacuna.php?id=$id_mascota", 'danger', 'Error al cargar datos de mascota');
}

// Obtener historial de vacunas
try {
    $stmt = $conn->prepare("SELECT id_vaca, nombre_vacuna, fecha_aplicacion, comentarios 
                            FROM vacunas 
                            WHERE id_mascota = ? 
                            ORDER BY fecha_aplicacion DESC");
    $stmt->bind_param("i", $id_mascota);
    $stmt->execute();
    $vacunas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje("salud/nueva_vacuna.php?id=$id_mascota", 'danger', 'Error al cargar historial de vacunas');
}

$page_title = "Vacunación - " . htmlspecialchars($mascota['nombre']);
include(BASE_PATH . '/includes/header.php');
?>

<div class="container py-4">
    <h2 class="mb-4 text-purple">Agregar Vacuna</h2>
    <form method="POST" action="<?= BASE_URL ?>/salud/procesar_vacuna.php" id="form-vacunas">
        <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
        <div class="card mb-3 shadow-sm card-purple-border">
            <div class="card-header bg-purple text-white">
                <h5 class="mb-0">Datos de la Vacuna</h5>
            </div>
            <div class="card-body">
                <div class="vacunas-container">
                    <!-- Campos dinámicos de vacunas se agregan aquí vía JavaScript -->
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-vacuna">
                    <i class="bi bi-plus"></i> Agregar Nueva Vacuna
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="submit" class="btn btn-purple-primary">Enviar</button>
            <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>" class="btn btn-sm btn-cancelar">
                <i class="fas fa-times me-1"></i> Cancelar
            </a>
        </div>
    </form>

    <!-- Historial de vacunas -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-purple text-white">
            <h5 class="mb-0">Historial de Vacunas</h5>
        </div>
        <div class="card-body">
            <?php if (empty($vacunas)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No hay vacunas registradas para esta mascota.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Vacuna</th>
                                <th>Comentarios</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vacunas as $vacuna): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($vacuna['fecha_aplicacion'])) ?></td>
                                    <td><?= htmlspecialchars($vacuna['nombre_vacuna']) ?></td>
                                    <td><?= !empty($vacuna['comentarios']) ? htmlspecialchars($vacuna['comentarios']) : '-' ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/salud/eliminar_registro.php?id_vaca=<?= $vacuna['id_vaca'] ?>&id_mascota=<?= $id_mascota ?>&redirect=nueva_vacuna" 
                                           class="btn btn-sm btn-eliminar" 
                                           onclick="return confirm('¿Está seguro de que desea eliminar este registro?');">
                                            <i class="fas fa-trash me-1"></i> Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    :root {
        --purple-primary: #8b0180;
        --red-cancel: #dc3545;
        --white-text: #ffffff;
        --border-color: #dee2e6;
    }
    .text-purple { color: var(--purple-primary); }
    .card-purple-border { border: 1px solid var(--purple-primary); }
    .card-header.bg-purple { 
        background-color: var(--purple-primary) !important; 
        color: var(--white-text) !important; 
    }
    .card-header.bg-purple h5 { color: var(--white-text) !important; }
    .btn-purple-primary {
        background-color: var(--purple-primary);
        border-color: var(--purple-primary);
        color: var(--white-text);
    }
    .btn-purple-primary:hover {
        background-color: #6a0160;
        border-color: #6a0160;
        color: var(--white-text);
    }
    .btn-eliminar {
        background-color: var(--red-cancel);
        border-color: var(--red-cancel);
        color: var(--white-text);
    }
    .btn-eliminar:hover {
        background-color: #c82333;
        border-color: #bd2130;
        color: var(--white-text);
    }
    .btn-cancelar {
        padding: 0.2rem 0.5rem;
        font-size: 0.8rem;
    }
    .vacuna-item { background-color: #f8f9fa; }
    .table thead { background-color: #f8f1f8; }
</style>

<script>
    // Plantilla para vacunas
    const vacunaTemplate = (index = '') => `
        <div class="vacuna-item mb-3 p-3 border rounded">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Vacuna ${index}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-vacuna">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small">Nombre vacuna*</label>
                    <input type="text" class="form-control" name="vacunas[nombre][]" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Fecha aplicación*</label>
                    <input type="date" class="form-control" name="vacunas[fecha][]" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label small">Comentarios</label>
                    <input type="text" class="form-control" name="vacunas[comentarios][]">
                </div>
            </div>
        </div>
    `;

    // Agregar primera vacuna por defecto
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.vacunas-container');
        const newVacuna = document.createElement('div');
        newVacuna.innerHTML = vacunaTemplate(1);
        container.appendChild(newVacuna);
        
        // Agregar evento al botón de eliminar
        newVacuna.querySelector('.remove-vacuna').addEventListener('click', function() {
            this.closest('.vacuna-item').remove();
        });
    });

    // Agregar más vacunas
    document.getElementById('add-vacuna').addEventListener('click', function() {
        const container = document.querySelector('.vacunas-container');
        const count = container.children.length + 1;
        const newVacuna = document.createElement('div');
        newVacuna.innerHTML = vacunaTemplate(count);
        container.appendChild(newVacuna);
        
        // Agregar evento al botón de eliminar
        newVacuna.querySelector('.remove-vacuna').addEventListener('click', function() {
            this.closest('.vacuna-item').remove();
        });
    });

    // Eliminar vacunas
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-vacuna')) {
            e.target.closest('.vacuna-item').remove();
        }
    });
</script>

<?php 
include(BASE_PATH . '/includes/footer.php');
ob_end_flush();
?>