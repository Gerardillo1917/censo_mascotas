<?php
// Obtener reportes paginados
$pagina = $_GET['pagina'] ?? 1;
$limite = 5;
$offset = ($pagina - 1) * $limite;

$reportes = obtener_reportes_tutor($id_tutor, $limite, $offset);

if (empty($reportes)): ?>
    <div class="alert alert-info">No hay reportes registrados.</div>
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
                <p class="mb-1 mt-2 text-truncate"><?= htmlspecialchars($reporte['descripcion']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>