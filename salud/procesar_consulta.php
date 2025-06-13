<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

// Validar ID de mascota
$id_mascota = isset($_POST['id_mascota']) ? intval($_POST['id_mascota']) : 0;
if ($id_mascota <= 0) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Mascota no especificada');
}

// Recoger y sanitizar datos
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$motivo = sanitizar_input($_POST['motivo']);
$signos_clinicos = sanitizar_input($_POST['signos_clinicos']);
$diagnostico = sanitizar_input($_POST['diagnostico']);
$responsable = sanitizar_input($_POST['responsable']);
$campana_lugar = sanitizar_input($_POST['campana_lugar']);

// Campos opcionales
$estado_general = isset($_POST['estado_general']) ? sanitizar_input($_POST['estado_general']) : null;
$estado_hidratacion = isset($_POST['estado_hidratacion']) ? sanitizar_input($_POST['estado_hidratacion']) : null;
$temperatura = isset($_POST['temperatura']) ? floatval($_POST['temperatura']) : null;
$frecuencia_cardiaca = isset($_POST['frecuencia_cardiaca']) ? sanitizar_input($_POST['frecuencia_cardiaca']) : null;
$frecuencia_respiratoria = isset($_POST['frecuencia_respiratoria']) ? sanitizar_input($_POST['frecuencia_respiratoria']) : null;
$medicacion = isset($_POST['medicacion']) ? sanitizar_input($_POST['medicacion']) : null;
$via_administracion = isset($_POST['via_administracion']) ? sanitizar_input($_POST['via_administracion']) : null;
$observaciones = isset($_POST['observaciones']) ? sanitizar_input($_POST['observaciones']) : null;

try {
    // Preparar la consulta
    $stmt = $conn->prepare("INSERT INTO salud_mascotas (
        id_mascota, tipo, fecha, hora, responsable, campana_lugar,
        motivo, signos_clinicos, diagnostico, estado_general, estado_hidratacion,
        temperatura, frecuencia_cardiaca, frecuencia_respiratoria, medicacion,
        via_administracion, observaciones
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Asignar tipo de consulta a variable
    $tipo = 'Consulta';
    
    // Vincular parámetros
    $stmt->bind_param(
        "issssssssssssssss",
        $id_mascota,
        $tipo,
        $fecha,
        $hora,
        $responsable,
        $campana_lugar,
        $motivo,
        $signos_clinicos,
        $diagnostico,
        $estado_general,
        $estado_hidratacion,
        $temperatura,
        $frecuencia_cardiaca,
        $frecuencia_respiratoria,
        $medicacion,
        $via_administracion,
        $observaciones
    );
    
    // Ejecutar
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $stmt->close();
    
redirigir_con_mensaje(
    "/salud/salud_animal.php?id=$id_mascota",
    'success',
    'Consulta registrada correctamente'
);

    
} catch (Exception $e) {
    // Cerrar statement si está abierto
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    
    registrar_error($e->getMessage());
    redirigir_con_mensaje(
    BASE_URL . "/nueva_consulta.php?id=$id_mascota",  // Cambiado
    'danger',
    'Error al registrar la consulta: ' . $e->getMessage()
);
}
?>