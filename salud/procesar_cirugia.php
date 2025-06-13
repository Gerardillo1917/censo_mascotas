<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Validar ID de mascota
$id_mascota = isset($_POST['id_mascota']) ? intval($_POST['id_mascota']) : 0;
if ($id_mascota <= 0) {
    redirigir_con_mensaje('buscar.php', 'danger', 'Mascota no especificada');
}

// Recoger y sanitizar datos
$_SESSION['form_data'] = $_POST; // Almacenar datos del formulario para restaurarlos si se vuelve atrás
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
    // Preparar la consulta
    $stmt = $conn->prepare("INSERT INTO salud_mascotas (
        id_mascota, tipo, fecha, hora, responsable, campana_lugar,
        tipo_procedimiento, diagnostico_previo, riesgos_informados,
        medicacion_previa, tipo_anestesia, cuidados_postoperatorios, observaciones, estado
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Asignar tipo de procedimiento y estado
    $tipo = 'Procedimiento';
    $estado = 'pendiente'; // Marcar como pendiente hasta que se firme
    
    // Vincular parámetros
    $stmt->bind_param(
        "isssssssssssss",
        $id_mascota,
        $tipo,
        $fecha,
        $hora,
        $responsable,
        $campana_lugar,
        $tipo_procedimiento,
        $diagnostico_previo,
        $riesgos_informados,
        $medicacion_previa,
        $tipo_anestesia,
        $cuidados_postoperatorios,
        $observaciones,
        $estado
    );
    
    // Ejecutar
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $id_interaccion = $stmt->insert_id;
    $stmt->close();
    
    // Verificar si se debe redirigir a firma digital
    if (isset($_GET['firmar']) && $_GET['firmar'] == 1) {
        // Guardar datos temporales para firma
        $_SESSION['temp_data'] = [
            'tipo' => 'consentimiento_cirugia',
            'id_mascota' => $id_mascota,
            'id_interaccion' => $id_interaccion
        ];
        
        redirigir_con_mensaje(
            'salud/firma_digital.php',
            'success',
            'Procedimiento registrado, ahora puede firmar el consentimiento'
        );
    } else {
        unset($_SESSION['form_data']); // Limpiar datos del formulario si no se necesita firma
        redirigir_con_mensaje(
            "salud/salud_animal.php?id=$id_mascota",
            'success',
            'Procedimiento registrado correctamente'
        );
    }
    
} catch (Exception $e) {
    // Cerrar statement si está abierto
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    
    registrar_error($e->getMessage());
    redirigir_con_mensaje(
        "salud/nueva_cirugia.php?id=$id_mascota",
        'danger',
        'Error al registrar el procedimiento: ' . $e->getMessage()
    );
}
?>