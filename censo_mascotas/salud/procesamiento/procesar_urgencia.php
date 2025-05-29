<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
require_once BASE_PATH . '/includes/auth.php';

//verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir_con_mensaje('/salud/salud_animal.php', 'danger', 'Método no permitido');
}

$id_mascota = intval($_POST['id_mascota'] ?? 0);
$responsable = sanitizar_input($_POST['responsable'] ?? '');

if (empty($_POST['motivo']) || empty($_POST['signos_clinicos']) || $id_mascota <= 0) {
    redirigir_con_mensaje(
        "/salud/salud_animal.php?id=$id_mascota&seccion=urgencia",
        'danger',
        'Datos requeridos faltantes'
    );
}

$_SESSION['temp_data'] = [
    'tipo' => 'urgencia',
    'id_mascota' => $id_mascota,
    'fecha_completa' => date('Y-m-d H:i:s', strtotime($_POST['fecha'] . ' ' . $_POST['hora'])),
    'motivo' => sanitizar_input($_POST['motivo']),
    'signos_clinicos' => sanitizar_input($_POST['signos_clinicos']),
    'responsable' => $responsable,
    'estado_general' => sanitizar_input($_POST['estado_general'] ?? ''),
    'hidratacion' => sanitizar_input($_POST['hidratacion'] ?? ''),
    'frecuencia_cardiaca' => sanitizar_input($_POST['frecuencia_cardiaca'] ?? ''),
    'frecuencia_respiratoria' => sanitizar_input($_POST['frecuencia_respiratoria'] ?? ''),
    'temperatura' => sanitizar_input($_POST['temperatura'] ?? ''),
    'primeros_auxilios' => sanitizar_input($_POST['primeros_auxilios'] ?? ''),
    'medicacion' => sanitizar_input($_POST['medicacion'] ?? ''),
    'observaciones' => sanitizar_input($_POST['observaciones'] ?? ''),
    'recomendaciones' => sanitizar_input($_POST['recomendaciones'] ?? ''),
    'referido' => isset($_POST['referido']) ? 1 : 0
];

redirigir_con_mensaje(
    "/salud/firma_digital.php",
    'info',
    'Por favor, complete el proceso con su firma digital'
);