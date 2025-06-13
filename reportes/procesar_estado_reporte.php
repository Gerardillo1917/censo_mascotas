<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

requerir_autenticacion();

// Verificar permisos
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'veterinario'])) {
    redirigir_con_mensaje(BASE_URL . '/index.php', 'danger', 'No tienes permisos para esta acción');
}

// Validar datos
$id_reporte = isset($_POST['id_reporte']) ? intval($_POST['id_reporte']) : 0;
$nuevo_estado = isset($_POST['nuevo_estado']) && in_array($_POST['nuevo_estado'], ['pendiente', 'investigando', 'resuelto']) 
    ? $_POST['nuevo_estado'] 
    : null;
$comentarios = isset($_POST['comentarios_resolucion']) ? sanitizar_input($_POST['comentarios_resolucion']) : null;

if ($id_reporte <= 0 || !$nuevo_estado) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Datos inválidos');
}

// Actualizar estado del reporte
try {
    $stmt = $conn->prepare("UPDATE reportes 
                           SET estado = ?, comentarios_resolucion = ?
                           WHERE id_reporte = ?");
    $stmt->bind_param("ssi", $nuevo_estado, $comentarios, $id_reporte);
    $stmt->execute();
    $stmt->close();

    redirigir_con_mensaje(
        BASE_URL . '/reportes/ver_reporte.php?id=' . $id_reporte,
        'success',
        'Estado del reporte actualizado correctamente'
    );

} catch (Exception $e) {
    error_log("Error al actualizar estado del reporte: " . $e->getMessage());
    redirigir_con_mensaje(
        BASE_URL . '/reportes/ver_reporte.php?id=' . $id_reporte,
        'danger',
        'Error al actualizar el estado del reporte'
    );
}
?>