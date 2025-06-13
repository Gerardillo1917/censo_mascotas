<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Método no permitido');
}

// Validar datos recibidos
$required_fields = ['id_mascota', 'fecha', 'hora', 'motivo', 'signos_clinicos', 'primeros_auxilios', 'responsable', 'campana_lugar'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        redirigir_con_mensaje(BASE_URL . '/salud/salud_animal.php?id=' . (int)$_POST['id_mascota'], 'danger', 'Faltan datos obligatorios');
    }
}

// Recoger datos del formulario
$id_mascota = (int)$_POST['id_mascota'];
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$motivo = $_POST['motivo'];
$signos_clinicos = $_POST['signos_clinicos'];
$primeros_auxilios = $_POST['primeros_auxilios'];
$responsable = $_POST['responsable'];
$campana_lugar = $_POST['campana_lugar'];
$estado_general = $_POST['estado_general'] ?? null;
$estado_hidratacion = $_POST['estado_hidratacion'] ?? null;
$temperatura = $_POST['temperatura'] ? (float)$_POST['temperatura'] : null;
$frecuencia_cardiaca = $_POST['frecuencia_cardiaca'] ?? null;
$frecuencia_respiratoria = $_POST['frecuencia_respiratoria'] ?? null;
$medicacion = $_POST['medicacion'] ?? null;
$referido_otro_centro = isset($_POST['referido_otro_centro']) ? 1 : 0;
$observaciones = $_POST['observaciones'] ?? null;

try {
    $conn->begin_transaction();

    // Insertar la urgencia en salud_mascotas
    $stmt = $conn->prepare("
        INSERT INTO salud_mascotas (
            id_mascota, tipo, fecha, hora, motivo, signos_clinicos, primeros_auxilios,
            estado_general, estado_hidratacion, temperatura, frecuencia_cardiaca,
            frecuencia_respiratoria, medicacion, referido_otro_centro, observaciones,
            responsable, campana_lugar, estado
        ) VALUES (?, 'Urgencia', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $estado = isset($_POST['guardar_y_firmar']) ? 'pendiente_firma' : 'completado';

    $stmt->bind_param(
        "issssssssssssssss",
        $id_mascota, $fecha, $hora, $motivo, $signos_clinicos, $primeros_auxilios,
        $estado_general, $estado_hidratacion, $temperatura, $frecuencia_cardiaca,
        $frecuencia_respiratoria, $medicacion, $referido_otro_centro, $observaciones,
        $responsable, $campana_lugar, $estado
    );

    $stmt->execute();
    $id_interaccion = $stmt->insert_id;
    $stmt->close();

    // Verificar si se debe redirigir a firma digital
    if (isset($_POST['guardar_y_firmar'])) {
        // Guardar datos temporales para la firma
        $_SESSION['temp_data'] = [
            'tipo' => 'consentimiento_urgencia',
            'id_mascota' => $id_mascota,
            'id_interaccion' => $id_interaccion
        ];

        $conn->commit();
        redirigir_con_mensaje('/salud/firma_digital.php', 'success', 'Urgencia registrada, proceda a firmar');
    } else {
        $conn->commit();
        redirigir_con_mensaje('/salud/salud_animal.php?id=' . $id_mascota, 'success', 'Urgencia registrada correctamente');
    }
} catch (Exception $e) {
    $conn->rollback();
    registrar_error($e->getMessage());
    redirigir_con_mensaje('/salud/salud_animal.php?id=' . $id_mascota, 'danger', 'Error al registrar la urgencia');
}
?>