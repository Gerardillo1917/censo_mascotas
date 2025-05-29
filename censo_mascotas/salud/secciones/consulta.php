<form method="post" action="<?= BASE_URL ?>/salud/procesamiento/procesar_consulta.php">
    <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
    
    <div class="mb-3">
        <label class="form-label">Motivo de Consulta*</label>
        <input type="text" class="form-control" name="motivo" required>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Signos Clínicos</label>
            <textarea class="form-control" name="signos_clinicos" rows="3"></textarea>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Estado General*</label>
            <select class="form-select" name="estado_general" required>
                <option value="">Seleccionar...</option>
                <option value="Activo">Activo</option>
                <option value="Letárgico">Letárgico</option>
                <option value="Decaído">Decaído</option>
                <option value="Alerta">Alerta</option>
                <option value="otro">Otro (especificar)</option>
            </select>
            <div id="otro_estado_container" class="mt-2" style="display:none;">
                <input type="text" class="form-control" name="otro_estado" placeholder="Especificar estado general">
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Temperatura (°C)</label>
            <input type="number" step="0.1" class="form-control" name="temperatura">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Frec. Cardíaca</label>
            <input type="text" class="form-control" name="frecuencia_cardiaca" placeholder="Ej: 120 lpm o 120/80">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Frec. Respiratoria</label>
            <input type="text" class="form-control" name="frecuencia_respiratoria" placeholder="Ej: 30 rpm o 30/min">
        </div>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Recomendaciones del Doctor</label>
        <textarea class="form-control" name="recomendaciones" rows="3"></textarea>
    </div>
    
    <div class="card mb-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Medicación Prescrita</h5>
            <button type="button" class="btn btn-sm btn-purple" id="btn-agregar-medicamento">
                <i class="fas fa-plus me-1"></i> Agregar Medicamento
            </button>
        </div>
        <div class="card-body" id="medicamentos-container">
            <!-- Los medicamentos se agregarán aquí dinámicamente -->
            <div class="medicamento-item mb-3 p-3 border rounded">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre del Medicamento</label>
                        <input type="text" class="form-control" name="medicamentos[0][nombre]">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Días</label>
                        <input type="number" class="form-control" name="medicamentos[0][dias]">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Frecuencia</label>
                        <input type="text" class="form-control" name="medicamentos[0][frecuencia]" placeholder="Ej: Cada 8 horas">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Aplicación</label>
                        <select class="form-select" name="medicamentos[0][aplicacion]">
                            <option value="">Seleccionar...</option>
                            <option value="Oral (VO)">Oral (VO)</option>
                            <option value="Intramuscular (IM)">Intramuscular (IM)</option>
                            <option value="Subcutánea (SC o SQ)">Subcutánea (SC o SQ)</option>
                            <option value="Intravenosa (IV)">Intravenosa (IV)</option>
                            <option value="Tópica">Tópica</option>
                            <option value="Oftálmica">Oftálmica</option>
                            <option value="Ótica">Ótica</option>
                            <option value="Rectal">Rectal</option>
                            <option value="Inhalatoria">Inhalatoria</option>
                            <option value="Intraarticular">Intraarticular</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-eliminar-medicamento" style="display: none;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Responsable*</label>
        <input type="text" class="form-control" name="responsable" required>
    </div>
    
    <div class="d-flex justify-content-end gap-2">
        <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-purple">Guardar Consulta</button>
    </div>
</form>

<script>
// Mostrar campo "otro estado" cuando se seleccione "otro"
document.querySelector('[name="estado_general"]').addEventListener('change', function() {
    const container = document.getElementById('otro_estado_container');
    container.style.display = this.value === 'otro' ? 'block' : 'none';
    if (this.value !== 'otro') {
        document.querySelector('[name="otro_estado"]').value = '';
    }
});
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('medicamentos-container');
    const btnAgregar = document.getElementById('btn-agregar-medicamento');
    let contador = 1;
    
    btnAgregar.addEventListener('click', function() {
        const nuevoMedicamento = document.createElement('div');
        nuevoMedicamento.className = 'medicamento-item mb-3 p-3 border rounded';
        nuevoMedicamento.innerHTML = `
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nombre del Medicamento</label>
                    <input type="text" class="form-control" name="medicamentos[${contador}][nombre]" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Días</label>
                    <input type="number" class="form-control" name="medicamentos[${contador}][dias]" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Frecuencia</label>
                    <input type="text" class="form-control" name="medicamentos[${contador}][frecuencia]" placeholder="Ej: Cada 8 horas" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Aplicación</label>
                    <select class="form-select" name="medicamentos[${contador}][aplicacion]" required>
                        <option value="">Seleccionar...</option>
                        <option value="Oral (VO)">Oral (VO)</option>
                        <option value="Intramuscular (IM)">Intramuscular (IM)</option>
                        <option value="Subcutánea (SC o SQ)">Subcutánea (SC o SQ)</option>
                        <option value="Intravenosa (IV)">Intravenosa (IV)</option>
                        <option value="Tópica">Tópica</option>
                        <option value="Oftálmica">Oftálmica</option>
                        <option value="Ótica">Ótica</option>
                        <option value="Rectal">Rectal</option>
                        <option value="Inhalatoria">Inhalatoria</option>
                        <option value="Intraarticular">Intraarticular</option>
                    </select>
                </div>
                <div class="col-md-1 mb-3 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-eliminar-medicamento">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        container.appendChild(nuevoMedicamento);
        contador++;
        
        // Mostrar botones de eliminar en todos los items
        document.querySelectorAll('.btn-eliminar-medicamento').forEach(btn => {
            btn.style.display = 'block';
        });
    });
    
    // Delegación de eventos para los botones eliminar
    container.addEventListener('click', function(e) {
        if (e.target.closest('.btn-eliminar-medicamento')) {
            const item = e.target.closest('.medicamento-item');
            if (document.querySelectorAll('.medicamento-item').length > 1) {
                item.remove();
            }
            
            // Ocultar botones de eliminar si solo queda uno
            if (document.querySelectorAll('.medicamento-item').length === 1) {
                document.querySelector('.btn-eliminar-medicamento').style.display = 'none';
            }
        }
    });
});
</script>