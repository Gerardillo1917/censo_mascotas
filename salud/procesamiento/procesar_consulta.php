<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Validar ID de mascota
$id_mascota = isset($_POST['id_mascota']) ? intval($_POST['id_mascota']) : 0;
if ($id_mascota <= 0) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Mascota no especificada');
}

// Asignar valores a variables
$tipo = 'Consulta';
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$responsable = sanitizar_input($_POST['responsable']);
$campana_lugar = sanitizar_input($_POST['campana_lugar']);
$motivo = sanitizar_input($_POST['motivo']);
$signos_clinicos = sanitizar_input($_POST['signos_clinicos']);
$diagnostico = sanitizar_input($_POST['diagnostico']);

// Manejar campos opcionales
$opcionales = [
    'estado_general', 'estado_hidratacion', 'temperatura',
    'frecuencia_cardiaca', 'frecuencia_respiratoria', 
    'medicacion', 'via_administracion', 'observaciones'
];

foreach ($opcionales as $campo) {
    $$campo = isset($_POST[$campo]) ? sanitizar_input($_POST[$campo]) : null;
    if ($campo === 'temperatura' && $$campo !== null) {
        $$campo = floatval($$campo);
    }
}

try {
    $stmt = $conn->prepare("INSERT INTO salud_mascotas (
        id_mascota, tipo, fecha, hora, responsable, campana_lugar,
        motivo, signos_clinicos, diagnostico, estado_general, estado_hidratacion,
        temperatura, frecuencia_cardiaca, frecuencia_respiratoria, medicacion,
        via_administracion, observaciones
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param(
        "issssssssssssssss",
        $id_mascota, $tipo, $fecha, $hora, $responsable, $campana_lugar,
        $motivo, $signos_clinicos, $diagnostico, $estado_general, $estado_hidratacion,
        $temperatura, $frecuencia_cardiaca, $frecuencia_respiratoria, $medicacion,
        $via_administracion, $observaciones
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $stmt->close();
    
    redirigir_con_mensaje(
        BASE_URL . "/salud/salud_animal.php?id=$id_mascota&seccion=historial",
        'success',
        'Consulta registrada correctamente'
    );
    
} catch (Exception $e) {
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    
    registrar_error($e->getMessage());
    redirigir_con_mensaje(
        BASE_URL . "/salud/salud_animal.php?id=$id_mascota&seccion=consulta",
        'danger',
        'Error al registrar la consulta: ' . $e->getMessage()
    );
}
?>