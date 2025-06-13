<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Forzar autenticación
requerir_autenticacion();

// Sanitizar y validar datos del tutor
$nombre = sanitizar_input($_POST['nombre']);
$apellido_paterno = sanitizar_input($_POST['apellido_paterno']);
$apellido_materno = isset($_POST['apellido_materno']) ? sanitizar_input($_POST['apellido_materno']) : null;
$edad = isset($_POST['edad']) ? intval($_POST['edad']) : null;
$telefono = sanitizar_input($_POST['telefono']);
$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : null;
$calle = sanitizar_input($_POST['calle']);
$numero_exterior = sanitizar_input($_POST['numero_exterior']);
$numero_interior = isset($_POST['numero_interior']) ? sanitizar_input($_POST['numero_interior']) : null;
$colonia = sanitizar_input($_POST['colonia']);
if ($colonia === 'Otro' && !empty($_POST['otra_colonia'])) {
    $colonia = sanitizar_input($_POST['otra_colonia']);
}
$codigo_postal = sanitizar_input($_POST['codigo_postal']);

// Sanitizar y validar datos de la mascota
$nombre_mascota = sanitizar_input($_POST['nombre_mascota']);
$especie = sanitizar_input($_POST['especie']);
if ($especie === 'Otro' && !empty($_POST['otra_especie'])) {
    $especie = sanitizar_input($_POST['otra_especie']);
}
$raza = isset($_POST['raza']) ? sanitizar_input($_POST['raza']) : null;
$color = isset($_POST['color']) ? sanitizar_input($_POST['color']) : null;
$edad_mascota = intval($_POST['edad_mascota']);
$genero = sanitizar_input($_POST['genero']);
$tiene_vacuna = isset($_POST['tiene_vacuna']) ? 1 : 0;
$esterilizado = isset($_POST['esterilizado']) ? 1 : 0;
$incapacidad = isset($_POST['incapacidad']) ? 1 : 0;
$descripcion_incapacidad = isset($_POST['descripcion_incapacidad']) ? sanitizar_input($_POST['descripcion_incapacidad']) : null;
$comentarios_mascota = isset($_POST['comentarios_mascota']) ? sanitizar_input($_POST['comentarios_mascota']) : null;

// Procesar fotos
try {
    $foto_tutor_ruta = null;
    if (!empty($_FILES['foto_tutor']['name'])) {
        $foto_tutor_ruta = subirFoto('foto_tutor', 'tutores', 'tutor_');
    }

    $foto_mascota_ruta = null;
    if (!empty($_FILES['foto_mascota']['name'])) {
        $foto_mascota_ruta = subirFoto('foto_mascota', 'mascotas', 'mascota_');
    }

    // Iniciar transacción
    $conn->begin_transaction();

    // Insertar tutor
    $stmt_tutor = $conn->prepare("INSERT INTO tutores (
        nombre, apellido_paterno, apellido_materno, edad, telefono, email, 
        calle, numero_exterior, numero_interior, colonia, codigo_postal, foto_ruta
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt_tutor->bind_param(
        "sssissssssss",
        $nombre, $apellido_paterno, $apellido_materno, $edad, $telefono, $email,
        $calle, $numero_exterior, $numero_interior, $colonia, $codigo_postal, $foto_tutor_ruta
    );
    $stmt_tutor->execute();
    $id_tutor = $stmt_tutor->insert_id;
    $stmt_tutor->close();

    // Insertar mascota
    $stmt_mascota = $conn->prepare("INSERT INTO mascotas (
        id_tutor, nombre, especie, raza, color, edad, genero, tiene_vacuna, esterilizado,
        incapacidad, descripcion_incapacidad, comentarios, foto_ruta
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt_mascota->bind_param(
        "issssisiiisss",
        $id_tutor, $nombre_mascota, $especie, $raza, $color, $edad_mascota, $genero,
        $tiene_vacuna, $esterilizado, $incapacidad, $descripcion_incapacidad, $comentarios_mascota, $foto_mascota_ruta
    );
    $stmt_mascota->execute();
    $id_mascota = $stmt_mascota->insert_id;
    $stmt_mascota->close();

    // Procesar vacunas si existen
    if ($tiene_vacuna && isset($_POST['vacunas']['nombre'])) {
        $stmt_vacuna = $conn->prepare("INSERT INTO vacunas (
            id_mascota, nombre_vacuna, fecha_aplicacion, comentarios
        ) VALUES (?, ?, ?, ?)");

        foreach ($_POST['vacunas']['nombre'] as $index => $nombre_vacuna) {
            if (!empty($nombre_vacuna) && !empty($_POST['vacunas']['fecha'][$index])) {
                $nombre_vacuna = sanitizar_input($nombre_vacuna);
                $fecha_vacuna = sanitizar_input($_POST['vacunas']['fecha'][$index]);
                $comentarios_vacuna = isset($_POST['vacunas']['comentarios'][$index]) ? sanitizar_input($_POST['vacunas']['comentarios'][$index]) : null;
                
                $stmt_vacuna->bind_param("isss", $id_mascota, $nombre_vacuna, $fecha_vacuna, $comentarios_vacuna);
                $stmt_vacuna->execute();
            }
        }
        $stmt_vacuna->close();
    }

    // Confirmar transacción
    $conn->commit();

    // Redirigir al perfil del tutor
    header("Location: " . BASE_URL . "/perfil.php?id=$id_tutor");
    exit();

} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    
    // Registrar el error y redirigir con mensaje
    error_log("Error al guardar tutor: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error al guardar los datos. Por favor intente nuevamente.";
    header("Location: " . BASE_URL . "/agregar_tutor.php");
    exit();
}
?>