<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

ob_start();

// Validar parámetros
$id_mascota = isset($_GET['id_mascota']) ? intval($_GET['id_mascota']) : 0;
$redirect = isset($_GET['redirect']) ? sanitizar_input($_GET['redirect']) : 'salud_animal';
$seccion = isset($_GET['seccion']) ? sanitizar_input($_GET['seccion']) : '';

if ($id_mascota <= 0) {
    redirigir_con_mensaje('buscar.php', 'danger', 'Mascota no especificada');
}

// Eliminar registro de vacuna
if (isset($_GET['id_vacuna'])) {
    $id_vacuna = intval($_GET['id_vacuna']);
    try {
        $stmt = $conn->prepare("DELETE FROM vacunas WHERE id_vacuna = ? AND id_mascota = ?");
        $stmt->bind_param("ii", $id_vacuna, $id_mascota);
        $stmt->execute();
        $stmt->close();

        // Verificar si quedan vacunas para actualizar tiene_vacuna
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM vacunas WHERE id_mascota = ?");
        $stmt->bind_param("i", $id_mascota);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $tiene_vacuna = $result['total'] > 0 ? 1 : 0;
        $stmt = $conn->prepare("UPDATE mascotas SET tiene_vacuna = ? WHERE id_mascota = ?");
        $stmt->bind_param("ii", $tiene_vacuna, $id_mascota);
        $stmt->execute();
        $stmt->close();

        $redirect_url = "salud/$redirect.php?id=$id_mascota";
        if ($seccion) {
            $redirect_url .= "&seccion=$seccion";
        }
        redirigir_con_mensaje($redirect_url, 'success', 'Registro de vacuna eliminado correctamente');
    } catch (Exception $e) {
        registrar_error($e->getMessage());
        $redirect_url = "salud/$redirect.php?id=$id_mascota";
        if ($seccion) {
            $redirect_url .= "&seccion=$seccion";
        }
        redirigir_con_mensaje($redirect_url, 'danger', 'Error al eliminar el registro: ' . $e->getMessage());
    }
}

// Eliminar registro de salud_mascotas
if (isset($_GET['id_interaccion'])) {
    $id_interaccion = intval($_GET['id_interaccion']);
    try {
        $stmt = $conn->prepare("DELETE FROM salud_mascotas WHERE id_interaccion = ? AND id_mascota = ?");
        $stmt->bind_param("ii", $id_interaccion, $id_mascota);
        $stmt->execute();
        $stmt->close();
        redirigir_con_mensaje("salud/$redirect.php?id=$id_mascota", 'success', 'Registro eliminado correctamente');
    } catch (Exception $e) {
        registrar_error($e->getMessage());
        redirigir_con_mensaje("salud/$redirect.php?id=$id_mascota", 'danger', 'Error al eliminar el registro: ' . $e->getMessage());
    }
}

redirigir_con_mensaje("salud/$redirect.php?id=$id_mascota", 'danger', 'No se especificó un registro válido para eliminar');
ob_end_flush();
?>