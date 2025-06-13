<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

$id_mascota = intval($_POST['id_mascota'] ?? 0);

// Validar campos obligatorios
$campos_requeridos = ['fecha', 'tipo_vacuna'];
foreach ($campos_requeridos as $campo) {
    if (empty($_POST[$campo])) {
        redirigir_con_mensaje(
            "/salud/salud_animal.php?id=$id_mascota&seccion=vacunacion",
            'danger',
            "Falta el campo requerido: $campo"
        );
    }
}

// Determinar nombre de vacuna
$nombre_vacuna = !empty($_POST['otra_vacuna']) 
    ? sanitizar_input($_POST['otra_vacuna'])
    : sanitizar_input($_POST['tipo_vacuna']);

try {
    // Insertar en tabla vacunas
    $stmt_vac = $conn->prepare("INSERT INTO vacunas (
        id_mascota, nombre_vacuna, fecha_aplicacion, comentarios
    ) VALUES (?, ?, ?, ?)");
    
    $fecha = sanitizar_input($_POST['fecha']);
    $comentarios = sanitizar_input($_POST['notas'] ?? '');
    
    $stmt_vac->bind_param("isss",
        $id_mascota,
        $nombre_vacuna,
        $fecha,
        $comentarios
    );
    
    if ($stmt_vac->execute()) {
        // Actualizar estado de vacunación en mascota
        $conn->query("UPDATE mascotas SET tiene_vacuna = 1 WHERE id_mascota = $id_mascota");
        
        redirigir_con_mensaje(
            "/salud/salud_animal.php?id=$id_mascota&seccion=vacunacion",
            'success',
            'Vacuna registrada correctamente'
        );
    } else {
        throw new Exception("Error al registrar vacuna: " . $stmt_vac->error);
    }
} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje(
        "/salud/salud_animal.php?id=$id_mascota&seccion=vacunacion",
        'danger',
        'Error al procesar la vacunación: ' . $e->getMessage()
    );
}