<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

requerir_autenticacion();

// Verificar permisos
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'veterinario'])) {
    redirigir_con_mensaje(BASE_URL . '/index.php', 'danger', 'No tienes permisos para esta acción');
}

// Validar ID
$id_reporte = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_reporte <= 0) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Reporte no especificado');
}

// Obtener información del reporte para redirección
$stmt = $conn->prepare("SELECT tipo, id_referencia FROM reportes WHERE id_reporte = ?");
$stmt->bind_param("i", $id_reporte);
$stmt->execute();
$result = $stmt->get_result();
$reporte = $result->fetch_assoc();
$stmt->close();

if (!$reporte) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Reporte no encontrado');
}

// Eliminar reporte
try {
    $stmt = $conn->prepare("DELETE FROM reportes WHERE id_reporte = ?");
    $stmt->bind_param("i", $id_reporte);
    $stmt->execute();
    $stmt->close();

    // Redirigir al perfil correspondiente
    if ($reporte['tipo'] === 'tutor') {
        $id_tutor = $reporte['id_referencia'];
    } else {
        // Obtener ID del tutor asociado a la mascota
        $stmt = $conn->prepare("SELECT id_tutor FROM mascotas WHERE id_mascota = ?");
        $stmt->bind_param("i", $reporte['id_referencia']);
        $stmt->execute();
        $result = $stmt->get_result();
        $mascota = $result->fetch_assoc();
        $stmt->close();
        
        if (!$mascota) {
            redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'No se pudo encontrar el tutor asociado a la mascota');
        }
        
        $id_tutor = $mascota['id_tutor'];
    }
    
    redirigir_con_mensaje(
        BASE_URL . '/perfil.php?id=' . $id_tutor,
        'success',
        'Reporte eliminado correctamente'
    );

} catch (Exception $e) {
    error_log("Error al eliminar reporte: " . $e->getMessage());
    redirigir_con_mensaje(
        BASE_URL . '/ver_reporte.php?id=' . $id_reporte,
        'danger',
        'Error al eliminar el reporte'
    );
}