<?php
require_once __DIR__ . '/../config.php';

// Destruir sesión completamente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Eliminar todas las variables de sesión
$_SESSION = array();

// Borrar cookie de sesión
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

// Destruir sesión
session_destroy();

// Redirigir a login
header("Location: " . BASE_URL . "/auth/login.php");
exit();