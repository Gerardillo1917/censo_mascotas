<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/includes/auth.php';
require_once BASE_PATH . '/database/conexion.php';

$fecha_actual = date('Y-m-d');
$hora_actual = date('H:i');
?>

<form method="post" action="<?= BASE_URL ?>/salud/procesamiento/procesar_urgencia.php">
    <input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">
    
    <h4 class="text-danger mb-3">🚨 Formulario de Urgencia Veterinaria</h4>
    
    <div class="card mb-4 border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">🟥 Campos Obligatorios</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Fecha*</label>
                    <input type="date" class="form-control" name="fecha" value="<?= $fecha_actual ?>" readonly required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Hora*</label>
                    <input type="time" class="form-control" name="hora" value="<?= $hora_actual ?>" readonly required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Motivo de urgencia*</label>
                <textarea class="form-control" name="motivo" rows="3" required placeholder="Qué ocurrió, por qué se presenta la emergencia"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Signos clínicos observados*</label>
                <textarea class="form-control" name="signos_clinicos" rows="3" required placeholder="Convulsiones, sangrado, dificultad respiratoria, etc."></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Responsable del procedimiento*</label>
                <input type="text" class="form-control" name="responsable" required placeholder="Nombre del veterinario o técnico que atendió">
            </div>
        </div>
    </div>
    
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">🟨 Campos Opcionales</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estado general</label>
                    <input type="text" class="form-control" name="estado_general" placeholder="Alerta, inconsciente, convulsionando, etc.">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estado de hidratación</label>
                    <input type="text" class="form-control" name="hidratacion" placeholder="Hidratado, deshidratado">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Frecuencia cardiaca</label>
                    <input type="text" class="form-control" name="frecuencia_cardiaca" placeholder="lpm">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Frecuencia respiratoria</label>
                    <input type="text" class="form-control" name="frecuencia_respiratoria" placeholder="rpm">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Temperatura</label>
                    <input type="text" class="form-control" name="temperatura" placeholder="°C">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Primeros auxilios aplicados</label>
                <textarea class="form-control" name="primeros_auxilios" rows="2" placeholder="Si los hubo antes de llegar"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Medicación administrada</label>
                <textarea class="form-control" name="medicacion" rows="2"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Observaciones adicionales</label>
                <textarea class="form-control" name="observaciones" rows="2"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Recomendaciones posteriores</label>
                <textarea class="form-control" name="recomendaciones" rows="2"></textarea>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="referido" id="referido">
                <label class="form-check-label" for="referido">Referido a otro centro</label>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2">
        <a href="<?= BASE_URL ?>/salud/salud_animal.php?id=<?= $id_mascota ?>" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-danger">Continuar a Firma</button>
    </div>
</form>