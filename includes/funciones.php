<?php
require_once __DIR__ . '/../config.php';
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
function generarPasswordSeguro() {
    $caracteres = '0123456789abcdefghijklmnopqryzABCDEPQRSTUZ';
    $longitud = 8;
    $password = '';
    $max = strlen($caracteres) - 1;
    
    for ($i = 0; $i < $longitud; $i++) {
        $password .= $caracteres[random_int(0, $max)];
    }
    
    return $password;
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
function sanitizar_input($data) {
    if (is_array($data)) {
        return array_map('sanitizar_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function redirigir_con_mensaje($url, $tipo = 'info', $mensaje = '') {
    // Si la URL no comienza con http:// o https://, asumimos que es relativa
    if (!preg_match('/^https?:\/\//', $url)) {
        // Asegurarse de que la URL comience con /
        $url = '/' . ltrim($url, '/');
        $url = BASE_URL . $url;
    }
    
    $_SESSION['mensaje_flash'] = [
        'tipo' => $tipo,
        'contenido' => $mensaje
    ];
    
    header("Location: " . $url);
    exit();
}

function mostrar_mensaje() {
    if (!empty($_SESSION['mensaje_flash'])) {
        $mensaje = $_SESSION['mensaje_flash'];
        echo '<div class="alert alert-' . htmlspecialchars($mensaje['tipo']) . ' alert-dismissible fade show">';
        echo htmlspecialchars($mensaje['contenido']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['mensaje_flash']);
    }
}

function registrar_error($mensaje) {
    $fecha = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $usuario = $_SESSION['username'] ?? 'Anónimo';
    $linea = sprintf("[%s] %s - %s: %s\n", $fecha, $ip, $usuario, $mensaje);
    file_put_contents(BASE_PATH . '/logs/errores_salud.log', $linea, FILE_APPEND);
}
function guardar_firma_digital($firma_data, $id_mascota, $tipo) {
    if (strpos($firma_data, 'data:image/png;base64,') === 0) {
        $firma_data = str_replace('data:image/png;base64,', '', $firma_data);
        $firma_data = str_replace(' ', '+', $firma_data);
        $imagen = base64_decode($firma_data);
        
        if ($imagen === false) {
            return false;
        }
        
        $nombre_archivo = 'firma_' . $tipo . '_' . $id_mascota . '_' . time() . '.png';
        $ruta = BASE_PATH . '/img/firmas/' . $nombre_archivo;
        
        if (file_put_contents($ruta, $imagen)) {
            return BASE_URL . '/img/firmas/' . $nombre_archivo;
        }
    }
    return false;
}

// Función para iniciar sesión segura con timeout
function iniciar_sesion_segura() {
    date_default_timezone_set('America/Mexico_City');
    
    if (session_status() === PHP_SESSION_NONE) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        
        session_start([
            'name' => 'CensoMascotasSesion',
            'cookie_lifetime' => 86400, // 24 horas
            'cookie_path' => '/',
            'cookie_domain' => parse_url(BASE_URL, PHP_URL_HOST), // Usar dominio desde BASE_URL
            'cookie_secure' => $protocol === 'https://',
            'cookie_httponly' => true,
            'use_strict_mode' => true
        ]);
    }

    // Verificar timeout de inactividad (30 minutos = 1800 segundos)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        // Registrar timeout
        registrar_error("Sesión expirada por inactividad: " . ($_SESSION['panel_admin_id'] ?? $_SESSION['user_id'] ?? 'desconocido'));
        cerrar_sesion();
        // Redirigir según el contexto
        $redirect_url = isset($_SESSION['panel_admin_logged_in']) ? '/admin/login_panel.php?error=sesion_expirada' : '/auth/login.php?error=sesion_expirada';
        header("Location: " . BASE_URL . $redirect_url);
        exit();
    }

    // Actualizar última actividad
    $_SESSION['last_activity'] = time();
}

// Función para verificar intentos de login (protección contra fuerza bruta)
function verificar_intentos_login($username, $conn) {
    $max_intentos = 5;
    $bloqueo_segundos = 300; // 5 minutos

    // Sanitizar username
    $username = sanitizar_input($username);

    // Verificar si el usuario está bloqueado
    $stmt = $conn->prepare("SELECT intentos, ultimo_intento, bloqueado_hasta FROM intentos_login WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        $bloqueado_hasta = strtotime($row['bloqueado_hasta']);
        if ($bloqueado_hasta && $bloqueado_hasta > time()) {
            $segundos_restantes = $bloqueado_hasta - time();
            return "Cuenta bloqueada. Intenta de nuevo en $segundos_restantes segundos.";
        }
    }

    return true; // Permitir intento
}

// Función para registrar intento fallido
function registrar_intento_fallido($username, $conn) {
    $max_intentos = 5;
    $bloqueo_segundos = 300; // 5 minutos

    // Sanitizar username
    $username = sanitizar_input($username);

    // Verificar si ya existe registro
    $stmt = $conn->prepare("SELECT intentos, ultimo_intento FROM intentos_login WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        $intentos = $row['intentos'] + 1;
        if ($intentos >= $max_intentos) {
            $bloqueado_hasta = date('Y-m-d H:i:s', time() + $bloqueo_segundos);
            $stmt = $conn->prepare("UPDATE intentos_login SET intentos = ?, ultimo_intento = NOW(), bloqueado_hasta = ? WHERE username = ?");
            $stmt->bind_param("iss", $intentos, $bloqueado_hasta, $username);
        } else {
            $stmt = $conn->prepare("UPDATE intentos_login SET intentos = ?, ultimo_intento = NOW(), bloqueado_hasta = NULL WHERE username = ?");
            $stmt->bind_param("is", $intentos, $username);
        }
    } else {
        $intentos = 1;
        $stmt = $conn->prepare("INSERT INTO intentos_login (username, intentos, ultimo_intento) VALUES (?, ?, NOW())");
        $stmt->bind_param("si", $username, $intentos);
    }

    $stmt->execute();
    $stmt->close();
}

// Función para resetear intentos tras login exitoso
function resetear_intentos_login($username, $conn) {
    $stmt = $conn->prepare("DELETE FROM intentos_login WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
}

// Función para cerrar sesión
function cerrar_sesion() {
    if (session_status() === PHP_SESSION_NONE) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        
        session_start([
            'name' => 'CensoMascotasSesion',
            'cookie_lifetime' => 86400,
            'cookie_path' => '/',
            'cookie_domain' => parse_url(BASE_URL, PHP_URL_HOST),
            'cookie_secure' => $protocol === 'https://',
            'cookie_httponly' => true,
            'use_strict_mode' => true
        ]);
    }

    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}

// Función para forzar autenticación (usuarios regulares)
function requerir_autenticacion() {
    // Iniciar o validar la sesión
    iniciar_sesion_segura();

    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol'])) {
        // Redirigir a login.php sin mensaje
        header("Location: " . BASE_URL . "/auth/login.php");
        exit();
    }
}

// Nueva función para forzar autenticación de administradores
function requerir_autenticacion_admin() {
    iniciar_sesion_segura();
    if (!isset($_SESSION['panel_admin_logged_in']) || $_SESSION['panel_admin_logged_in'] !== true || $_SESSION['panel_admin_rol'] !== 'admin') {
        registrar_error("Intento de acceso no autorizado al panel de admin: " . ($_SERVER['REQUEST_URI'] ?? 'desconocido') . " - Sesión: " . json_encode($_SESSION));
        redirigir_con_mensaje('/admin/login_panel.php', 'danger', 'Acceso restringido. Por favor, inicie sesión como administrador.');
    }
}
function obtener_total_reportes($id_tutor) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reportes 
                           WHERE (tipo = 'tutor' AND id_referencia = ?) OR 
                                 (tipo = 'mascota' AND id_referencia IN 
                                  (SELECT id_mascota FROM mascotas WHERE id_tutor = ?))");
    $stmt->bind_param("ii", $id_tutor, $id_tutor);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}

function obtener_reportes_tutor($id_tutor, $limite = 5, $offset = 0) {
    global $conn;
    $stmt = $conn->prepare("SELECT r.*, 
                           IF(r.tipo = 'tutor', 'Reporte sobre tutor', 
                              CONCAT('Reporte sobre mascota: ', m.nombre)) AS titulo_reporte
                           FROM reportes r
                           LEFT JOIN mascotas m ON r.tipo = 'mascota' AND r.id_referencia = m.id_mascota
                           WHERE (r.tipo = 'tutor' AND r.id_referencia = ?) OR 
                                 (r.tipo = 'mascota' AND m.id_tutor = ?)
                           ORDER BY r.fecha_reporte DESC
                           LIMIT ? OFFSET ?");
    $stmt->bind_param("iiii", $id_tutor, $id_tutor, $limite, $offset);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>