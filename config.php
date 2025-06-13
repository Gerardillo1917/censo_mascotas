<?php
// Configuración definitiva de rutas
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$project_folder = 'censo_mascotas';

// Roles
define('ROLES', [
    'admin' => 'Administrador',
    'veterinario' => 'Veterinario',
    'registrador' => 'Registrador'
]);

// Definir rutas absolutas
define('BASE_URL', $protocol . $host . '/' . $project_folder . '/');
// Ejemplo resultante: "http://localhost/censo_mascotas/"
define('BASE_PATH', realpath(dirname(__FILE__)));

// Configuración para desarrollo/producción
define('ENVIRONMENT', 'development');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'censo_mascotas');

// Configuración de sesión segura
if (ENVIRONMENT === 'production') {
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
}

// Mostrar errores en desarrollo
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'name' => 'CensoMascotasSesion',
        'cookie_lifetime' => 86400, // 24 horas
        'cookie_path' => '/',
        'cookie_domain' => $host,
        'cookie_secure' => $protocol === 'https://',
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}
?>