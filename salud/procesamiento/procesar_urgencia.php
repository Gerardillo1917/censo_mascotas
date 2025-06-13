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
$motivo = sanitizar_input($_POST['motivo']);
$signos_clinicos = sanitizar_input($_POST['signos_clinicos']);
$primeros_auxilios = sanitizar_input($_POST['primeros_auxilios']);
$responsable = sanitizar_input($_POST['responsable']);
$campana_lugar = sanitizar_input($_POST['campana_lugar']);

// Campos opcionales
$estado_general = isset($_POST['estado_general']) ? sanitizar_input($_POST['estado_general']) : null;
$estado_hidratacion = isset($_POST['estado_hidratacion']) ? sanitizar_input($_POST['estado_hidratacion']) : null;
$temperatura = isset($_POST['temperatura']) ? floatval($_POST['temperatura']) : null;
$frecuencia_cardiaca = isset($_POST['frecuencia_cardiaca']) ? sanitizar_input($_POST['frecuencia_cardiaca']) : null;
$frecuencia_respiratoria = isset($_POST['frecuencia_respiratoria']) ? sanitizar_input($_POST['frecuencia_respiratoria']) : null;
$medicacion = isset($_POST['medicacion']) ? sanitizar_input($_POST['medicacion']) : null;
$referido_otro_centro = isset($_POST['referido_otro_centro']) ? 1 : 0;
$observaciones = isset($_POST['observaciones']) ? sanitizar_input($_POST['observaciones']) : null;

try {
    $stmt = $conn->prepare("INSERT INTO salud_mascotas (
        id_mascota, tipo, fecha, hora, responsable, campana_lugar,
        motivo, signos_clinicos, primeros_auxilios, estado_general, estado_hidratacion,
        temperatura, frecuencia_cardiaca, frecuencia_respiratoria, medicacion,
        referido_otro_centro, observaciones
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param(
        "issssssssssssssiss",
        $id_mascota, 'Urgencia', $fecha, $hora, $responsable, $campana_lugar,
        $motivo, $signos_clinicos, $primeros_auxilios, $estado_general, $estado_hidratacion,
        $temperatura, $frecuencia_cardiaca, $frecuencia_respiratoria, $medicacion,
        $referido_otro_centro, $observaciones
    );
    
    $stmt->execute();
    $stmt->close();
    
    redirigir_con_mensaje(
        BASE_URL . "/salud/salud_animal.php?id=$id_mascota&seccion=historial",
        'success',
        'Urgencia registrada correctamente'
    );
    
} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje(
        BASE_URL . "/salud/salud_animal.php?id=$id_mascota&seccion=urgencia",
        'danger',
        'Error al registrar la urgencia'
    );
}
?>