<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/includes/auth.php';
require_once BASE_PATH . '/database/conexion.php';
?>

<form method="post" action="<?= BASE_URL ?>/salud/procesamiento/procesar_procedimiento.php">
    <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
    
    <h4 class="text-purple mb-3">🔪 Formulario de Procedimiento Quirúrgico</h4>
    
    <div class="card mb-4 border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">🟥 Campos Obligatorios</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Fecha del procedimiento*</label>
                <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" readonly required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Tipo de procedimiento realizado*</label>
                <select class="form-select" name="tipo_procedimiento" required>
                    <option value="">Seleccionar...</option>
                    <option value="Esterilización">Esterilización</option>
                    <option value="Castración">Castración</option>
                    <option value="Extracción dental">Extracción dental</option>
                    <option value="Sutura">Sutura</option>
                    <option value="otro">Otro (especificar)</option>
                </select>
            </div>
            
            <div id="otro_procedimiento_container" class="mb-3" style="display:none;">
                <label class="form-label">Especificar procedimiento*</label>
                <input type="text" class="form-control" name="otro_procedimiento">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Responsable del procedimiento*</label>
                <input type="text" class="form-control" name="responsable" required>
            </div>
        </div>
    </div>
    
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">🟨 Campos Opcionales</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Diagnóstico previo</label>
                <textarea class="form-control" name="diagnostico" rows="2" placeholder="Breve descripción de por qué se realiza la cirugía"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Medicación preoperatoria</label>
                <textarea class="form-control" name="medicacion_previa" rows="2"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Medicación postoperatoria</label>
                <textarea class="form-control" name="medicacion_postoperatoria" rows="2"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Tipo de anestesia utilizada</label>
                <input type="text" class="form-control" name="anestesia">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Cuidados postoperatorios recomendados</label>
                <textarea class="form-control" name="cuidados_postoperatorios" rows="2"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Riesgos informados</label>
                <textarea class="form-control" name="riesgos" rows="2" placeholder="Complicaciones posibles"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Observaciones generales</label>
                <textarea class="form-control" name="observaciones" rows="2"></textarea>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2">
        <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-purple">Continuar a Firma</button>
    </div>
</form>

<script>
document.querySelector('[name="tipo_procedimiento"]').addEventListener('change', function() {
    const container = document.getElementById('otro_procedimiento_container');
    container.style.display = this.value === 'otro' ? 'block' : 'none';
    if (this.value !== 'otro') {
        document.querySelector('[name="otro_procedimiento"]').value = '';
    }
});
</script>