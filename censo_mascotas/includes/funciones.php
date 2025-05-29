<?php
require_once __DIR__ . '/../config.php';

function sanitizar_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function registrar_acceso($usuario_id = null, $exitoso = false, $razon = '') {
    $log_file = BASE_PATH . '/logs/accesos.log';
    $mensaje = sprintf(
        "[%s] %s - Intento de acceso - IP: %s - UserID: %s - Razón: %s\n",
        date('Y-m-d H:i:s'),
        ($exitoso ? 'ÉXITO' : 'FALLO'),
        $_SERVER['REMOTE_ADDR'],
        $usuario_id ?? 'null',
        $razon
    );
    file_put_contents($log_file, $mensaje, FILE_APPEND);
}

function subirFoto($inputName, $directorioDestino, $prefijo) {
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $archivo = $_FILES[$inputName];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $extensionesPermitidas)) {
        throw new Exception("Formato de imagen no válido. Use JPG, PNG o WEBP.");
    }

    if ($archivo['size'] > 2 * 1024 * 1024) {
        throw new Exception("La imagen es demasiado grande (máx. 2MB)");
    }

    $nombreUnico = $prefijo . uniqid() . '.' . $extension;
    $rutaCompleta = BASE_PATH . '/img/' . $directorioDestino . '/' . $nombreUnico;

    if (!is_dir(dirname($rutaCompleta))) {
        mkdir(dirname($rutaCompleta), 0755, true);
    }

    if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        throw new Exception("Error al guardar la imagen");
    }

    return BASE_URL . '/img/' . $directorioDestino . '/' . $nombreUnico;
}

function redirigir_con_mensaje($url, $tipo, $mensaje) {
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
    header("Location: " . BASE_URL . $url);
    exit();
}

function mostrar_mensaje() {
    if (isset($_SESSION['mensaje'])) {
        $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
        $mensaje = $_SESSION['mensaje'];
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
        
        echo '<div class="alert alert-' . htmlspecialchars($tipo) . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($mensaje) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    }
}

function guardar_firma_digital($base64_data, $id_mascota, $tipo) {
    $ruta_base = BASE_PATH . '/img/firmas/';
    if (!is_dir($ruta_base)) {
        mkdir($ruta_base, 0755, true);
    }
    
    $nombre_archivo = "firma_{$tipo}_{$id_mascota}_" . time() . ".png";
    $ruta_completa = $ruta_base . $nombre_archivo;
    
    $image_data = str_replace('data:image/png;base64,', '', $base64_data);
    $image_data = str_replace(' ', '+', $image_data);
    $decoded = base64_decode($image_data);
    
    if (file_put_contents($ruta_completa, $decoded)) {
        return BASE_URL . '/img/firmas/' . $nombre_archivo;
    }
    return false;
}

function registrar_error($error) {
    $log_file = BASE_PATH . '/logs/errores_salud.log';
    $mensaje = "[" . date('Y-m-d H:i:s') . "] " . $error . "\n";
    file_put_contents($log_file, $mensaje, FILE_APPEND);
}

function mostrarPassword($password, $id = 'password') {
    return '
    <div class="input-group">
        <input type="password" class="form-control" id="'.$id.'" value="'.htmlspecialchars($password).'" readonly>
        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="'.$id.'">
            <i class="fas fa-eye"></i>
        </button>
    </div>
    <script>
    document.querySelector(\'[data-target="'.$id.'"]\').addEventListener("click", function() {
        const input = document.getElementById("'.$id.'");
        if (input.type === "password") {
            input.type = "text";
            this.innerHTML = \'<i class="fas fa-eye-slash"></i>\';
        } else {
            input.type = "password";
            this.innerHTML = \'<i class="fas fa-eye"></i>\';
        }
    });
    </script>';
}

function generarUsernameUnico($conn, $nombre_completo) {
    $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nombre_completo));
    $base = substr($base, 0, 10);
    $username = $base;
    $counter = 1;
    
    while (true) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_row()[0];
        $stmt->close();
        
        if ($count == 0) {
            return $username;
        }
        $username = $base . $counter;
        $counter++;
    }
}

function generarPasswordSeguro() {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@#*';
    $longitud = 12;
    $password = '';
    $max = strlen($caracteres) - 1;
    
    for ($i = 0; $i < $longitud; $i++) {
        $password .= $caracteres[random_int(0, $max)];
    }
    
    return $password;
}
?>