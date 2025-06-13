<?php
// Evitar salida previa
ob_start();

// Incluir dependencias
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/includes/funciones.php';

// Iniciar sesión segura
iniciar_sesion_segura();

// Registrar logout
registrar_acceso(
    $_SESSION['panel_admin_id'] ?? null,
    true,
    "Logout exitoso desde el panel de administración"
);

// Destruir solo las variables de sesión del panel de admin
unset($_SESSION['panel_admin_logged_in']);
unset($_SESSION['panel_admin_id']);
unset($_SESSION['panel_admin_rol']);
unset($_SESSION['panel_admin_nombre']);

// Depuración: registrar el estado de la sesión después de unset
error_log("Sesión después de logout admin: " . json_encode($_SESSION));

// Limpiar buffer de salida
ob_end_clean();

// Redirigir a la página de login del panel
header("Location: " . BASE_URL . "/admin/login_panel.php");
exit();
?>