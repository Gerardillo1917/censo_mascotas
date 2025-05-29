<form method="post" action="<?= BASE_URL ?>/salud/procesamiento/procesar_vacunacion.php">
    <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
    
    <div class="mb-3">
        <label class="form-label">Tipo de Vacuna*</label>
        <select class="form-select" name="tipo_vacuna" required>
            <option value="">Seleccionar...</option>
            <option value="Rabia">Rabia</option>
            <option value="Moquillo">Moquillo</option>
            <option value="Parvovirus">Parvovirus</option>
            <option value="Leptospirosis">Leptospirosis</option>
            <option value="otro">Otra (especificar)</option>
        </select>
    </div>
    
    <div id="otra_vacuna_container" class="mb-3" style="display:none;">
        <label class="form-label">Especificar vacuna*</label>
        <input type="text" class="form-control" name="otra_vacuna">
    </div>
    
<input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" readonly required>

    
    <div class="mb-3">
        <label class="form-label">Observaciones</label>
        <textarea class="form-control" name="notas" rows="2"></textarea>
    </div>
    
    <!-- Mostrar vacunas existentes -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Vacunas Registradas</h5>
        </div>
        <div class="card-body">
            <?php
            $stmt_vac = $conn->prepare("SELECT * FROM vacunas WHERE id_mascota = ? ORDER BY fecha_aplicacion DESC");
            $stmt_vac->bind_param("i", $id_mascota);
            $stmt_vac->execute();
            $vacunas = $stmt_vac->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt_vac->close();
            
            if (!empty($vacunas)) {
                echo '<ul class="list-group">';
                foreach ($vacunas as $vacuna) {
                    echo '<li class="list-group-item">';
                    echo '<div class="d-flex justify-content-between">';
                    echo '<div>';
                    echo '<strong>' . htmlspecialchars($vacuna['nombre_vacuna']) . '</strong>';
                    echo '<div class="small text-muted">' . date('d/m/Y', strtotime($vacuna['fecha_aplicacion'])) . '</div>';
                    echo '</div>';
                    if (!empty($vacuna['comentarios'])) {
                        echo '<span class="badge bg-info">' . htmlspecialchars($vacuna['comentarios']) . '</span>';
                    }
                    echo '</div>';
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<div class="alert alert-info mb-0">No hay vacunas registradas</div>';
            }
            ?>
        </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2">
        <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-purple">Registrar Vacuna</button>
    </div>
</form>

<script>
document.querySelector('[name="tipo_vacuna"]').addEventListener('change', function() {
    const container = document.getElementById('otra_vacuna_container');
    container.style.display = this.value === 'otro' ? 'block' : 'none';
    if (this.value !== 'otro') {
        document.querySelector('[name="otra_vacuna"]').value = '';
    }
});
</script>