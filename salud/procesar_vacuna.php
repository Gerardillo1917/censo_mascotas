<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

ob_start();

// Validar ID de mascota
$id_mascota = isset($_POST['id_mascota']) ? intval($_POST['id_mascota']) : 0;
if ($id_mascota <= 0) {
    redirigir_con_mensaje('buscar.php', 'danger', 'Mascota no especificada');
}

try {
    // Procesar vacunas
    if (!empty($_POST['vacunas']['nombre'])) {
        $stmt_vacuna = $conn->prepare("INSERT INTO vacunas (
            id_mascota, nombre_vacuna, fecha_aplicacion, comentarios
        ) VALUES (?, ?, ?, ?)");

        foreach ($_POST['vacunas']['nombre'] as $index => $nombre_vacuna) {
            if (!empty($nombre_vacuna) && !empty($_POST['vacunas']['fecha'][$index])) {
                $nombre_vacuna = sanitizar_input($nombre_vacuna);
                $fecha = sanitizar_input($_POST['vacunas']['fecha'][$index]);
                $comentario_vacuna = isset($_POST['vacunas']['comentarios'][$index]) ? sanitizar_input($_POST['vacunas']['comentarios'][$index]) : null;
                
                $stmt_vacuna->bind_param("isss", $id_mascota, $nombre_vacuna, $fecha, $comentario_vacuna);
                $stmt_vacuna->execute();
            }
        }
        $stmt_vacuna->close();

        // Actualizar campo tiene_vacuna en mascotas
        $stmt = $conn->prepare("UPDATE mascotas SET tiene_vacuna = 1 WHERE id_mascota = ?");
        $stmt->bind_param("i", $id_mascota);
        $stmt->execute();
        $stmt->close();
    } else {
        redirigir_con_mensaje("salud/salud_animal.php?id=$id_mascota&seccion=AltaVacuna", 'danger', 'No se proporcionaron datos de vacunas válidos');
    }

    redirigir_con_mensaje("salud/salud_animal.php?id=$id_mascota&seccion=AltaVacuna", 'success', 'Vacuna(s) registrada(s) correctamente');

} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje("salud/salud_animal.php?id=$id_mascota&seccion=AltaVacuna", 'danger', 'Error al registrar la(s) vacuna(s): ' . $e->getMessage());
}

ob_end_flush();
?>