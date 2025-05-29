<?php
require_once __DIR__ . '/../config.php';

// Verificar autenticación
if (empty($_SESSION['usuario_id'])) {
    // Guardar URL actual para redirección post-login
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    }
    header("Location: " . BASE_URL . "/auth/login.php");
    exit();
}

// Verificar permisos de usuario
function verificarPermiso($rolRequerido) {
    if ($_SESSION['rol'] !== $rolRequerido) {
        $_SESSION['mensaje'] = "Acceso no autorizado";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }
}
?>