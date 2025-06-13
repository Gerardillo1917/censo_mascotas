<?php
ob_start();
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Verificar autenticación de admin
requerir_autenticacion_admin();

// Definir colonias predefinidas
$colonias = [
    'San Bernardino Tlaxcalancingo',
    'San Andrés Cholula',
    'San Luis Tehuiloyocan',
    'San Rafael Comac',
    'Buenavista',
    'Santa María Tonantzintla',
    'San Pedro Tonantzintla',
    'Tonantzintla'
];

// Obtener campañas/lugares únicos para el filtro de vacunas
try {
    $stmt = $conn->prepare("SELECT DISTINCT campana_lugar FROM salud_mascotas WHERE campana_lugar IS NOT NULL ORDER BY campana_lugar");
    $stmt->execute();
    $campanas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    registrar_error("Error al obtener campañas en reportes.php: " . $e->getMessage());
    $campanas = [];
}

$page_title = "Generar Reportes - Panel Administrativo";
include(BASE_PATH . '/admin/includes_panel/header_panel.php');
?>

<div class="container mt-4">
    <h2><i class="fas fa-chart-bar"></i> Generar Reportes</h2>


    <div class="card shadow-sm mb-4">
        <div class="card-header bg-purple">
            <h5 class="mb-0">Seleccione un Reporte</h5>
        </div>
        <div class="card-body">
            <form id="form-reportes" method="POST" action="generar_reporte.php">
                <div class="mb-3">
                    <label for="tipo_reporte" class="form-label">Tipo de Reporte</label>
                    <select class="form-control" id="tipo_reporte" name="tipo_reporte" required onchange="mostrarFiltros()">
                        <option value="">Seleccione un reporte</option>
                        <option value="machos_hembras">Conteo de Machos y Hembras por Colonia</option>
                        <option value="esterilizacion">Estado de Esterilización</option>
                        <option value="vacunas">Vacunas Aplicadas</option>
                        <option value="tutores_edad">Distribución de Tutores por Edad</option>
                    </select>
                </div>

                <!-- Filtros para Conteo de Machos y Hembras -->
                <div id="filtros_machos_hembras" class="filtros d-none">
                    <h6>Filtros para Conteo de Machos y Hembras</h6>
                    <div class="mb-3">
                        <label class="form-label">Colonias</label>
                        <?php foreach ($colonias as $colonia): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="colonias[]" value="<?php echo htmlspecialchars($colonia); ?>" id="colonia_<?php echo htmlspecialchars($colonia); ?>">
                                <label class="form-check-label" for="colonia_<?php echo htmlspecialchars($colonia); ?>"><?php echo htmlspecialchars($colonia); ?></label>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="colonia_otra" onchange="toggleOtraColonia()">
                            <label class="form-check-label" for="colonia_otra">Otra</label>
                        </div>
                        <input type="text" class="form-control mt-2 d-none" id="otra_colonia" name="otra_colonia" placeholder="Especifique la localidad">
                    </div>
                </div>

                <!-- Filtros para Estado de Esterilización -->
                <div id="filtros_esterilizacion" class="filtros d-none">
                    <h6>Filtros para Estado de Esterilización</h6>
                    <div class="mb-3">
                        <label for="tipo_mascota" class="form-label">Tipo de Mascota</label>
                        <select class="form-control" id="tipo_mascota" name="tipo_mascota">
                            <option value="todos">Todos</option>
                            <option value="perro">Perro</option>
                            <option value="gato">Gato</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Colonias (opcional)</label>
                        <?php foreach ($colonias as $colonia): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="colonias_esterilizacion[]" value="<?php echo htmlspecialchars($colonia); ?>" id="colonia_est_<?php echo htmlspecialchars($colonia); ?>">
                                <label class="form-check-label" for="colonia_est_<?php echo htmlspecialchars($colonia); ?>"><?php echo htmlspecialchars($colonia); ?></label>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="colonia_otra_est" onchange="toggleOtraColoniaEst()">
                            <label class="form-check-label" for="colonia_otra_est">Otra</label>
                        </div>
                        <input type="text" class="form-control mt-2 d-none" id="otra_colonia_est" name="otra_colonia_est" placeholder="Especifique la localidad">
                    </div>
                </div>

                <!-- Filtros para Vacunas Aplicadas -->
                <div id="filtros_vacunas" class="filtros d-none">
                    <h6>Filtros para Vacunas Aplicadas</h6>
                    <div class="mb-3">
                        <label class="form-label">Tipos de Vacuna</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="vacunas[]" value="Antirrábica" id="vacuna_antirrabica">
                            <label class="form-check-label" for="vacuna_antirrabica">Antirrábica</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="vacunas[]" value="Triple Felina" id="vacuna_triple_felina">
                            <label class="form-check-label" for="vacuna_triple_felina">Triple Felina</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="vacunas[]" value="Parvovirus" id="vacuna_parvovirus">
                            <label class="form-check-label" for="vacuna_parvovirus">Parvovirus</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="vacunas[]" value="Leptospirosis" id="vacuna_leptospirosis">
                            <label class="form-check-label" for="vacuna_leptospirosis">Leptospirosis</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                    </div>
                    <div class="mb-3">
                        <label for="campana_lugar" class="form-label">Campaña/Lugar (opcional)</label>
                        <select class="form-control" id="campana_lugar" name="campana_lugar">
                            <option value="">Ninguna</option>
                            <?php foreach ($campanas as $campana): ?>
                                <option value="<?php echo htmlspecialchars($campana['campana_lugar']); ?>"><?php echo htmlspecialchars($campana['campana_lugar']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Filtros para Distribución de Tutores por Edad -->
                <div id="filtros_tutores_edad" class="filtros d-none">
                    <h6>Filtros para Distribución de Tutores por Edad</h6>
                    <div class="mb-3">
                        <label class="form-label">Colonias</label>
                        <?php foreach ($colonias as $colonia): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="colonias_tutores[]" value="<?php echo htmlspecialchars($colonia); ?>" id="colonia_tut_<?php echo htmlspecialchars($colonia); ?>">
                                <label class="form-check-label" for="colonia_tut_<?php echo htmlspecialchars($colonia); ?>"><?php echo htmlspecialchars($colonia); ?></label>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="colonia_otra_tut" onchange="toggleOtraColoniaTut()">
                            <label class="form-check-label" for="colonia_otra_tut">Otra</label>
                        </div>
                        <input type="text" class="form-control mt-2 d-none" id="otra_colonia_tut" name="otra_colonia_tut" placeholder="Especifique la localidad">
                    </div>
                    <div class="mb-3">
                        <label for="rango_edad" class="form-label">Rango de Edad</label>
                        <select class="form-control" id="rango_edad" name="rango_edad" onchange="toggleRangoPersonalizado()">
                            <option value="18-30">18-30 años</option>
                            <option value="31-45">31-45 años</option>
                            <option value="46-60">46-60 años</option>
                            <option value=">60">Mayor a 60 años</option>
                            <option value="personalizado">Personalizado</option>
                        </select>
                    </div>
                    <div id="rango_personalizado" class="d-none">
                        <div class="mb-3">
                            <label for="edad_min" class="form-label">Edad Mínima</label>
                            <input type="number" class="form-control" id="edad_min" name="edad_min" min="0">
                        </div>
                        <div class="mb-3">
                            <label for="edad_max" class="form-label">Edad Máxima</label>
                            <input type="number" class="form-control" id="edad_max" name="edad_max" min="0">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-purple-primary" id="generar_reporte" disabled>
                    <i class="fas fa-file-excel"></i> Generar Reporte
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function mostrarFiltros() {
    const tipoReporte = document.getElementById('tipo_reporte').value;
    const filtros = document.querySelectorAll('.filtros');
    filtros.forEach(filtro => filtro.classList.add('d-none'));
    if (tipoReporte) {
        document.getElementById(`filtros_${tipoReporte}`).classList.remove('d-none');
        document.getElementById('generar_reporte').disabled = false;
    } else {
        document.getElementById('generar_reporte').disabled = true;
    }
}

function toggleOtraColonia() {
    const otraColonia = document.getElementById('colonia_otra');
    const inputOtra = document.getElementById('otra_colonia');
    if (otraColonia.checked) {
        inputOtra.classList.remove('d-none');
        inputOtra.required = true;
    } else {
        inputOtra.classList.add('d-none');
        inputOtra.required = false;
        inputOtra.value = '';
    }
}

function toggleOtraColoniaEst() {
    const otraColonia = document.getElementById('colonia_otra_est');
    const inputOtra = document.getElementById('otra_colonia_est');
    if (otraColonia.checked) {
        inputOtra.classList.remove('d-none');
        inputOtra.required = true;
    } else {
        inputOtra.classList.add('d-none');
        inputOtra.required = false;
        inputOtra.value = '';
    }
}

function toggleOtraColoniaTut() {
    const otraColonia = document.getElementById('colonia_otra_tut');
    const inputOtra = document.getElementById('otra_colonia_tut');
    if (otraColonia.checked) {
        inputOtra.classList.remove('d-none');
        inputOtra.required = true;
    } else {
        inputOtra.classList.add('d-none');
        inputOtra.required = false;
        inputOtra.value = '';
    }
}

function toggleRangoPersonalizado() {
    const rangoEdad = document.getElementById('rango_edad').value;
    const rangoPersonalizado = document.getElementById('rango_personalizado');
    if (rangoEdad === 'personalizado') {
        rangoPersonalizado.classList.remove('d-none');
        document.getElementById('edad_min').required = true;
        document.getElementById('edad_max').required = true;
    } else {
        rangoPersonalizado.classList.add('d-none');
        document.getElementById('edad_min').required = false;
        document.getElementById('edad_max').required = false;
        document.getElementById('edad_min').value = '';
        document.getElementById('edad_max').value = '';
    }
}
</script>

<style>
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
}
.filtros {
    border: 1px solid #dee2e6;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
}
</style>

<?php
include(BASE_PATH . '/admin/includes_panel/footer_panel.php');
ob_end_flush();
?>