<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Validar y sanitizar datos
$id_mascota = isset($_POST['id_mascota']) ? intval($_POST['id_mascota']) : 0;
if ($id_mascota <= 0) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Mascota no especificada');
}

// Obtener datos del formulario
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$tipo_procedimiento = sanitizar_input($_POST['tipo_procedimiento']);
if ($tipo_procedimiento === 'otro' && isset($_POST['otro_procedimiento'])) {
    $tipo_procedimiento = sanitizar_input($_POST['otro_procedimiento']);
}
$diagnostico_previo = sanitizar_input($_POST['diagnostico_previo']);
$riesgos_informados = sanitizar_input($_POST['riesgos_informados']);
$responsable = sanitizar_input($_POST['responsable']);
$campana_lugar = sanitizar_input($_POST['campana_lugar']);

// Campos opcionales
$medicacion_previa = isset($_POST['medicacion_previa']) ? sanitizar_input($_POST['medicacion_previa']) : null;
$tipo_anestesia = isset($_POST['tipo_anestesia']) ? sanitizar_input($_POST['tipo_anestesia']) : null;
$cuidados_postoperatorios = isset($_POST['cuidados_postoperatorios']) ? sanitizar_input($_POST['cuidados_postoperatorios']) : null;
$observaciones = isset($_POST['observaciones']) ? sanitizar_input($_POST['observaciones']) : null;

try {
    $stmt = $conn->prepare("INSERT INTO salud_mascotas (
        id_mascota, tipo, fecha, hora, responsable, campana_lugar,
        tipo_procedimiento, diagnostico_previo, riesgos_informados,
        medicacion_previa, tipo_anestesia, cuidados_postoperatorios, observaciones
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param(
        "issssssssssss",
        $id_mascota, 'Procedimiento', $fecha, $hora, $responsable, $campana_lugar,
        $tipo_procedimiento, $diagnostico_previo, $riesgos_informados,
        $medicacion_previa, $tipo_anestesia, $cuidados_postoperatorios, $observaciones
    );
    
    $stmt->execute();
    $stmt->close();
    
    redirigir_con_mensaje(
        BASE_URL . "/salud/salud_animal.php?id=$id_mascota&seccion=historial",
        'success',
        'Procedimiento registrado correctamente'
    );
    
} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje(
        BASE_URL . "/salud/salud_animal.php?id=$id_mascota&seccion=cirugia",
        'danger',
        'Error al registrar el procedimiento'
    );
}
?>