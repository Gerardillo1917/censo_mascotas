<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

requerir_autenticacion();

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Método no permitido');
}

// Validar datos del formulario
$tipo = isset($_POST['tipo']) && in_array($_POST['tipo'], ['tutor', 'mascota']) ? $_POST['tipo'] : null;
$id_referencia = isset($_POST['id_referencia']) ? intval($_POST['id_referencia']) : 0;
$tipo_reporte = isset($_POST['tipo_reporte']) ? sanitizar_input($_POST['tipo_reporte']) : null;
$descripcion = isset($_POST['descripcion']) ? sanitizar_input($_POST['descripcion']) : null;

if (!$tipo || $id_referencia <= 0 || !$tipo_reporte || !$descripcion) {
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', 'Datos incompletos o inválidos');
}

// Verificar que el tutor/mascota exista
if ($tipo === 'tutor') {
    $stmt = $conn->prepare("SELECT id_tutor FROM tutores WHERE id_tutor = ?");
} else {
    $stmt = $conn->prepare("SELECT id_mascota FROM mascotas WHERE id_mascota = ?");
}

$stmt->bind_param("i", $id_referencia);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    redirigir_con_mensaje(BASE_URL . '/buscar.php', 'danger', $tipo === 'tutor' ? 'Tutor no encontrado' : 'Mascota no encontrada');
}
$stmt->close();

// Procesar fotos
$fotos_rutas = [];
$directorio_destino = BASE_PATH . '/public/img/reportes/';

// Crear directorio si no existe
if (!file_exists($directorio_destino)) {
    if (!mkdir($directorio_destino, 0755, true)) {
        error_log("Error al crear directorio: $directorio_destino");
        redirigir_con_mensaje(
            BASE_URL . ($tipo === 'tutor' ? '/reportes/reportar_tutor.php?id=' . $id_referencia : '/reportes/reportar_mascota.php?id=' . $id_referencia),
            'danger',
            'Error al crear directorio para fotos'
        );
    }
}

// Verificar permisos del directorio
if (!is_writable($directorio_destino)) {
    error_log("El directorio $directorio_destino no tiene permisos de escritura");
    redirigir_con_mensaje(
        BASE_URL . ($tipo === 'tutor' ? '/reportes/reportar_tutor.php?id=' . $id_referencia : '/reportes/reportar_mascota.php?id=' . $id_referencia),
        'danger',
        'Error en el servidor al guardar imágenes. Por favor intente nuevamente.'
    );
}

// Procesar fotos subidas por archivo
if (!empty($_FILES['fotos_reporte']['name'][0])) {
    $total_fotos = count($_FILES['fotos_reporte']['name']);
    
    // Limitar a 5 fotos
    if ($total_fotos > 5) {
        redirigir_con_mensaje(
            BASE_URL . ($tipo === 'tutor' ? '/reportes/reportar_tutor.php?id=' . $id_referencia : '/reportes/reportar_mascota.php?id=' . $id_referencia),
            'danger',
            'Solo se permiten un máximo de 5 fotos'
        );
    }
    
    for ($i = 0; $i < $total_fotos; $i++) {
        if ($_FILES['fotos_reporte']['error'][$i] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES['fotos_reporte']['name'][$i], PATHINFO_EXTENSION));
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($extension, $extensiones_permitidas)) {
                $nombre_archivo = 'reporte_' . uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                $ruta_completa = $directorio_destino . $nombre_archivo;
                
                if (move_uploaded_file($_FILES['fotos_reporte']['tmp_name'][$i], $ruta_completa)) {
                    $fotos_rutas[] = '/public/img/reportes/' . $nombre_archivo;
                    error_log("Foto guardada: $ruta_completa");
                } else {
                    error_log("Error al mover archivo: " . $_FILES['fotos_reporte']['tmp_name'][$i] . " a $ruta_completa");
                }
            }
        }
    }
}

// Procesar foto capturada desde cámara (si existe)
if (!empty($_POST['foto_capturada'])) {
    $foto_data = $_POST['foto_capturada'];
    
    // Verificar si es una imagen en base64
    if (strpos($foto_data, 'data:image') === 0) {
        $parts = explode(',', $foto_data);
        $image_data = base64_decode($parts[1]);
        $extension = 'jpg'; // Asumimos jpg para fotos de cámara
        
        $nombre_archivo = 'reporte_cam_' . uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $ruta_completa = $directorio_destino . $nombre_archivo;
        
        if (file_put_contents($ruta_completa, $image_data)) {
            $fotos_rutas[] = '/public/img/reportes/' . $nombre_archivo;
            error_log("Foto de cámara guardada: $ruta_completa");
        } else {
            error_log("Error al guardar foto de cámara en: $ruta_completa");
        }
    }
}

// Convertir array de fotos a JSON para guardar en la base de datos
$fotos_json = !empty($fotos_rutas) ? json_encode($fotos_rutas) : null;
error_log("Datos a guardar en DB: " . print_r($fotos_json, true));

// Obtener datos del denunciante
$nombre_denunciante = isset($_POST['nombre_denunciante']) ? sanitizar_input($_POST['nombre_denunciante']) : null;
$telefono_denunciante = isset($_POST['telefono_denunciante']) ? sanitizar_input($_POST['telefono_denunciante']) : null;
$email_denunciante = isset($_POST['email_denunciante']) ? filter_var($_POST['email_denunciante'], FILTER_SANITIZE_EMAIL) : null;

// Insertar reporte en la base de datos
try {
    $stmt = $conn->prepare("INSERT INTO reportes (
        tipo, id_referencia, tipo_reporte, descripcion, foto_ruta, 
        id_usuario, nombre_denunciante, telefono_denunciante, email_denunciante
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $id_usuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;
    
    $stmt->bind_param(
        "sississss",
        $tipo,
        $id_referencia,
        $tipo_reporte,
        $descripcion,
        $fotos_json,
        $id_usuario,
        $nombre_denunciante,
        $telefono_denunciante,
        $email_denunciante
    );

    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $id_reporte = $stmt->insert_id;
    $stmt->close();

    error_log("Reporte creado con ID: $id_reporte");
    error_log("Rutas de fotos guardadas: " . print_r($fotos_rutas, true));

    // Redirigir a la vista del reporte
    redirigir_con_mensaje(
        BASE_URL . '/reportes/ver_reporte.php?id=' . $id_reporte,
        'success',
        'Reporte enviado correctamente'
    );

} catch (Exception $e) {
    error_log("Error al guardar reporte: " . $e->getMessage());
    // Eliminar fotos subidas si hubo error
    foreach ($fotos_rutas as $foto) {
        $ruta_fisica = BASE_PATH . $foto;
        if (file_exists($ruta_fisica)) {
            unlink($ruta_fisica);
        }
    }
    
    redirigir_con_mensaje(
        BASE_URL . ($tipo === 'tutor' ? '/reportes/reportar_tutor.php?id=' . $id_referencia : '/reportes/reportar_mascota.php?id=' . $id_referencia),
        'danger',
        'Error al guardar el reporte. Por favor intente nuevamente.'
    );
}
?>