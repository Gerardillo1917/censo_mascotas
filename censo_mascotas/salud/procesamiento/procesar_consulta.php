<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

//verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir_con_mensaje('/salud/salud_animal.php', 'danger', 'Método no permitido');
}

$id_mascota = intval($_POST['id_mascota'] ?? 0);
$responsable = sanitizar_input($_POST['responsable'] ?? '');

// Validación básica
if (empty($_POST['motivo'])) {
    redirigir_con_mensaje(
        "/salud/salud_animal.php?id=$id_mascota&seccion=consulta",
        'danger',
        'El motivo de la consulta es requerido'
    );
}

try {
    // Preparar los valores para la consulta
    $motivo = sanitizar_input($_POST['motivo']);
    $signos_clinicos = sanitizar_input($_POST['signos_clinicos'] ?? '');
    $estado_general = ($_POST['estado_general'] === 'otro') 
        ? sanitizar_input($_POST['otro_estado'] ?? '')
        : sanitizar_input($_POST['estado_general'] ?? '');
    
    $temperatura = !empty($_POST['temperatura']) ? floatval($_POST['temperatura']) : null;
    $frecuencia_cardiaca = sanitizar_input($_POST['frecuencia_cardiaca'] ?? '');
    $frecuencia_respiratoria = sanitizar_input($_POST['frecuencia_respiratoria'] ?? '');
    $recomendaciones = sanitizar_input($_POST['recomendaciones'] ?? '');
    $notas = sanitizar_input($_POST['notas'] ?? '');

    // Insertar la consulta principal
    $stmt = $conn->prepare("INSERT INTO salud_mascotas (
        id_mascota, tipo, fecha, motivo, signos_clinicos, estado_general,
        temperatura, frecuencia_cardiaca, frecuencia_respiratoria,
        recomendaciones, responsable, notas
    ) VALUES (?, 'Consulta', NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Pasar variables, no valores directos
    $stmt->bind_param("isssdsssss",
        $id_mascota,
        $motivo,
        $signos_clinicos,
        $estado_general,
        $temperatura,
        $frecuencia_cardiaca,
        $frecuencia_respiratoria,
        $recomendaciones,
        $responsable,
        $notas
    );
    
    if ($stmt->execute()) {
        $id_consulta = $stmt->insert_id;
        
        // Procesar medicamentos si existen
        if (!empty($_POST['medicamentos'])) {
            foreach ($_POST['medicamentos'] as $med) {
                if (!empty($med['nombre'])) {
                    $nombre_med = sanitizar_input($med['nombre']);
                    $dias_med = intval($med['dias'] ?? 0);
                    $frecuencia_med = sanitizar_input($med['frecuencia'] ?? '');
                    $aplicacion_med = sanitizar_input($med['aplicacion'] ?? '');
                    
                    $stmt_med = $conn->prepare("UPDATE salud_mascotas SET 
                        medicamento_nombre = ?,
                        medicamento_dias = ?,
                        medicamento_frecuencia = ?,
                        medicamento_aplicacion = ?
                        WHERE id_interaccion = ?");
                    
                    $stmt_med->bind_param("sissi",
                        $nombre_med,
                        $dias_med,
                        $frecuencia_med,
                        $aplicacion_med,
                        $id_consulta
                    );
                    $stmt_med->execute();
                    $stmt_med->close();
                }
            }
        }
        
        redirigir_con_mensaje(
            "/salud/salud_animal.php?id=$id_mascota&seccion=historial",
            'success',
            'Consulta registrada correctamente'
        );
    } else {
        throw new Exception("Error al guardar consulta: " . $stmt->error);
    }
} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje(
        "/salud/salud_animal.php?id=$id_mascota&seccion=consulta",
        'danger',
        'Error al procesar la consulta'
    );
}