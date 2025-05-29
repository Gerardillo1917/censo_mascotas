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

$tipo_procedimiento = !empty($_POST['otro_procedimiento']) 
    ? sanitizar_input($_POST['otro_procedimiento'])
    : sanitizar_input($_POST['tipo_procedimiento'] ?? '');

if (empty($tipo_procedimiento) || $id_mascota <= 0) {
    redirigir_con_mensaje(
        "/salud/salud_animal.php?id=$id_mascota&seccion=procedimiento",
        'danger',
        'Datos requeridos faltantes'
    );
}

$_SESSION['temp_data'] = [
    'tipo' => 'procedimiento',
    'id_mascota' => $id_mascota,
    'fecha' => sanitizar_input($_POST['fecha']),
    'tipo_procedimiento' => $tipo_procedimiento,
    'responsable' => $responsable,
    'diagnostico' => sanitizar_input($_POST['diagnostico'] ?? ''),
    'medicacion_previa' => sanitizar_input($_POST['medicacion_previa'] ?? ''),
    'medicacion_postoperatoria' => sanitizar_input($_POST['medicacion_postoperatoria'] ?? ''),
    'anestesia' => sanitizar_input($_POST['anestesia'] ?? ''),
    'cuidados_postoperatorios' => sanitizar_input($_POST['cuidados_postoperatorios'] ?? ''),
    'riesgos' => sanitizar_input($_POST['riesgos'] ?? ''),
    'observaciones' => sanitizar_input($_POST['observaciones'] ?? '')
];

redirigir_con_mensaje(
    "/salud/firma_digital.php",
    'info',
    'Por favor, complete el proceso con su firma digital'
);