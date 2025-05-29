<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
require_once BASE_PATH . '/includes/auth.php';

//verificarAutenticacion();

// Verificar rol de veterinario
if ($_SESSION['rol'] !== 'veterinario') {
    redirigir_con_mensaje('/salud/salud_animal.php', 'danger', 'Solo veterinarios pueden realizar esta acción');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir_con_mensaje('/salud/salud_animal.php', 'danger', 'Método no permitido');
}

if (!isset($_SESSION['temp_data'])) {
    redirigir_con_mensaje(BASE_URL . '/salud/salud_animal.php', 'danger', 'Datos temporales no encontrados');
}

$tipo = sanitizar_input($_POST['tipo']);
$id_mascota = intval($_POST['id_mascota']);
$firma_data = $_POST['firma_data'] ?? '';

if (empty($firma_data) || strpos($firma_data, 'data:image/png;base64,') !== 0) {
    redirigir_con_mensaje(
        "/salud/salud_animal.php?id=$id_mascota&seccion=$tipo",
        'danger',
        'Firma digital inválida'
    );
}

try {
    $firma_ruta = guardar_firma_digital($firma_data, $id_mascota, $tipo);
    
    if (!$firma_ruta) {
        throw new Exception("Error al guardar firma digital");
    }
    
    $temp_data = $_SESSION['temp_data'];
    unset($_SESSION['temp_data']);
    
    if ($tipo === 'urgencia') {
        $stmt = $conn->prepare("INSERT INTO salud_mascotas (
            id_mascota, tipo, fecha, motivo, signos_clinicos, estado_general,
            hidratacion, frecuencia_cardiaca, frecuencia_respiratoria, temperatura,
            primeros_auxilios, medicamentos_sumistrados, observaciones, recomendaciones,
            referido, responsable, firma_ruta, consentimiento_informado
        ) VALUES (?, 'Urgencia', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        
        $stmt->bind_param("isssssssssssisss",
            $id_mascota,
            $temp_data['fecha_completa'],
            $temp_data['motivo'],
            $temp_data['signos_clinicos'],
            $temp_data['estado_general'],
            $temp_data['hidratacion'],
            $temp_data['frecuencia_cardiaca'],
            $temp_data['frecuencia_respiratoria'],
            $temp_data['temperatura'],
            $temp_data['primeros_auxilios'],
            $temp_data['medicacion'],
            $temp_data['observaciones'],
            $temp_data['recomendaciones'],
            $temp_data['referido'],
            $temp_data['responsable'],
            $firma_ruta
        );
    } else {
        $stmt = $conn->prepare("INSERT INTO salud_mascotas (
            id_mascota, tipo, fecha, tipo_procedimiento, diagnostico, 
            medicacion_previa, medicacion_postoperatoria, anestesia,
            cuidados_postoperatorios, riesgos_informados, observaciones,
            responsable, firma_ruta, consentimiento_informado
        ) VALUES (?, 'Procedimiento', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        
        $stmt->bind_param("issssssssssss",
            $id_mascota,
            $temp_data['fecha'],
            $temp_data['tipo_procedimiento'],
            $temp_data['diagnostico'],
            $temp_data['medicacion_previa'],
            $temp_data['medicacion_postoperatoria'],
            $temp_data['anestesia'],
            $temp_data['cuidados_postoperatorios'],
            $temp_data['riesgos'],
            $temp_data['observaciones'],
            $temp_data['responsable'],
            $firma_ruta
        );
    }
    
    if ($stmt->execute()) {
        redirigir_con_mensaje(
            "/salud/salud_animal.php?id=$id_mascota&seccion=historial",
            'success',
            ucfirst($tipo) . ' registrado correctamente'
        );
    } else {
        if ($firma_ruta) {
            $ruta_fisica = str_replace(BASE_URL, BASE_PATH, $firma_ruta);
            @unlink($ruta_fisica);
        }
        throw new Exception("Error al guardar $tipo: " . $stmt->error);
    }
} catch (Exception $e) {
    registrar_error($e->getMessage());
    redirigir_con_mensaje(
        "/salud/salud_animal.php?id=$id_mascota&seccion=$tipo",
        'danger',
        'Error al procesar ' . $tipo
    );
}