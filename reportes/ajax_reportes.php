<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

header('Content-Type: text/html');
header("Cache-Control: no-cache, must-revalidate");

$id_tutor = filter_input(INPUT_GET, 'id_tutor', FILTER_VALIDATE_INT) ?? 0;
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?? 1;
$limite = 5;
$offset = ($pagina - 1) * $limite;

// Función para obtener reportes
function obtener_reportes_tutor($id_tutor, $limite, $offset) {
    global $conn;
    $stmt = $conn->prepare("SELECT r.*, 
                           IF(r.tipo = 'tutor', 'Reporte sobre tutor', 
                              CONCAT('Reporte sobre mascota: ', m.nombre)) AS titulo_reporte
                           FROM reportes r
                           LEFT JOIN mascotas m ON r.tipo = 'mascota' AND r.id_referencia = m.id_mascota
                           WHERE (r.tipo = 'tutor' AND r.id_referencia = ?) OR 
                                 (r.tipo = 'mascota' AND m.id_tutor = ?)
                           ORDER BY r.fecha_reporte DESC
                           LIMIT ? OFFSET ?");
    $stmt->bind_param("iiii", $id_tutor, $id_tutor, $limite, $offset);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$reportes = obtener_reportes_tutor($id_tutor, $limite, $offset);
?>

<?php if (empty($reportes)): ?>
    <div class="alert alert-info">No hay más reportes para mostrar.</div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($reportes as $reporte): ?>
            <a href="<?= BASE_URL ?>/reportes/ver_reporte.php?id=<?= $reporte['id_reporte'] ?>" 
               class="list-group-item list-group-item-action">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="mb-1"><?= htmlspecialchars($reporte['titulo_reporte']) ?></h6>
                        <small class="text-muted">
                            <?= date('d/m/Y H:i', strtotime($reporte['fecha_reporte'])) ?>
                            - <?= htmlspecialchars($reporte['tipo_reporte']) ?>
                        </small>
                    </div>
                    <span class="badge bg-<?= 
                        $reporte['estado'] == 'pendiente' ? 'warning' : 
                        ($reporte['estado'] == 'investigando' ? 'info' : 'success') 
                    ?>">
                        <?= ucfirst($reporte['estado']) ?>
                    </span>
                </div>
                <p class="mb-1 mt-2"><?= htmlspecialchars($reporte['descripcion']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>