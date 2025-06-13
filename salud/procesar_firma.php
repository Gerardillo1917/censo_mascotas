<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir_con_mensaje('salud/salud_animal.php', 'danger', 'Método no permitido');
}

// Validar datos recibidos
$required_fields = ['tipo', 'id_mascota', 'id_interaccion', 'firma_data', 'nombre_firmante', 'dni_firmante', 'acepto_tratamiento'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        redirigir_con_mensaje('salud/salud_animal.php', 'danger', 'Faltan datos obligatorios');
    }
}

// Procesar datos
$id_interaccion = filter_input(INPUT_POST, 'id_interaccion', FILTER_VALIDATE_INT);
$firma_base64 = $_POST['firma_data'];
$nombre_firmante = filter_input(INPUT_POST, 'nombre_firmante', FILTER_SANITIZE_STRING);
$dni_firmante = filter_input(INPUT_POST, 'dni_firmante', FILTER_SANITIZE_STRING);
$acepto_tratamiento = filter_input(INPUT_POST, 'acepto_tratamiento', FILTER_VALIDATE_INT);

// Validar datos
if (!$id_interaccion || $acepto_tratamiento !== 1) {
    redirigir_con_mensaje('salud/salud_animal.php', 'danger', 'Datos inválidos o aceptación no confirmada');
}

// Procesar y guardar la imagen de firma
try {
    // Extraer los datos base64
    $firma_base64 = str_replace('data:image/png;base64,', '', $firma_base64);
    $firma_base64 = str_replace(' ', '+', $firma_base64);
    $firma_binaria = base64_decode($firma_base64);

    if (!$firma_binaria) {
        throw new Exception("La firma no se pudo decodificar correctamente");
    }

    // Crear directorio de firmas si no existe
    $directorio_firmas = BASE_PATH . '/Uploads/firmas/';
    if (!is_dir($directorio_firmas)) {
        mkdir($directorio_firmas, 0755, true);
    }

    // Generar nombre único para el archivo
    $nombre_archivo = 'firma_' . $id_interaccion . '_' . time() . '.png';
    $ruta_archivo = $directorio_firmas . $nombre_archivo;

    // Guardar la imagen
    if (!file_put_contents($ruta_archivo, $firma_binaria)) {
        throw new Exception("No se pudo guardar la imagen de la firma");
    }

    // Guardar en base de datos
    $conn->begin_transaction();
    
    // Actualizar el registro en salud_mascotas
    $stmt = $conn->prepare("UPDATE salud_mascotas 
                          SET firma_imagen = ?, nombre_firmante = ?, dni_firmante = ?, 
                              fecha_firma = NOW(), estado = 'completado'
                          WHERE id_interaccion = ?");
    
    $ruta_relativa = '/Uploads/firmas/' . $nombre_archivo;
    $stmt->bind_param("sssi", $ruta_relativa, $nombre_firmante, $dni_firmante, $id_interaccion);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar el procedimiento: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->commit();
    
    // Limpiar datos temporales
    unset($_SESSION['temp_data']);
    unset($_SESSION['form_data']);
    
    redirigir_con_mensaje(
        "salud/salud_animal.php?id={$_POST['id_mascota']}",
        'success',
        'Firma digital guardada correctamente'
    );

} catch (Exception $e) {
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }
    
    // Eliminar archivo si se creó pero falló la transacción
    if (isset($ruta_archivo) && file_exists($ruta_archivo)) {
        unlink($ruta_archivo);
    }
    
    registrar_error('Error en procesar_firma: ' . $e->getMessage());
    redirigir_con_mensaje(
        "salud/salud_animal.php?id={$_POST['id_mascota']}",
        'danger',
        'Error al procesar la firma: ' . $e->getMessage()
    );
}
?>