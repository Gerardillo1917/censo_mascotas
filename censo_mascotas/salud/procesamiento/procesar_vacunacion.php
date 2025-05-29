<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
require_once BASE_PATH . '/includes/auth.php'; // Añade esta línea

//verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir_con_mensaje('/salud/salud_animal.php', 'danger', 'Método no permitido');
}

$id_mascota = intval($_POST['id_mascota'] ?? 0);
$tipo_vacuna = !empty($_POST['otra_vacuna']) 
    ? sanitizar_input($_POST['otra_vacuna'])
    : sanitizar_input($_POST['tipo_vacuna'] ?? '');

if (empty($tipo_vacuna) || $id_mascota <= 0) {
    redirigir_con_mensaje(
        "/salud/salud_animal.php?id=$id_mascota&seccion=vacunacion",
        'danger',
        'Datos requeridos faltantes'
    );
}

try {
    // Insertar directamente en tabla vacunas
    $stmt_vac = $conn->prepare("INSERT INTO vacunas (
        id_mascota, nombre_vacuna, fecha_aplicacion, comentarios
    ) VALUES (?, ?, ?, ?)");
    
    $fecha = sanitizar_input($_POST['fecha'] ?? date('Y-m-d'));
    $notas = sanitizar_input($_POST['notas'] ?? '');
    
    $stmt_vac->bind_param("isss",
        $id_mascota,
        $tipo_vacuna,
        $fecha,
        $notas
    );
    
    if ($stmt_vac->execute()) {
        // Actualizar estado de vacunación en la tabla mascotas
        $conn->query("UPDATE mascotas SET tiene_vacuna = 1 WHERE id_mascota = $id_mascota");
        
        redirigir_con_mensaje(
            "/salud/salud_animal.php?id=$id_mascota&seccion=historial",
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
        'Error al procesar la vacunación'
    );
}