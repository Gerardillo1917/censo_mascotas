<?php
require_once __DIR__ . '/../config.php'; // Para BASE_URL

// Destruir solo las variables de sesión del panel de admin
unset($_SESSION['panel_admin_logged_in']);
unset($_SESSION['panel_admin_id']);
unset($_SESSION['panel_admin_rol']);
unset($_SESSION['panel_admin_nombre']);

// Opcional: destruir la sesión completa si el panel de admin es la única sesión que usa el admin
// session_destroy(); 

// Redirigir a la página de login del panel
header("Location: " . BASE_URL . "/admin/login_panel.php");
exit();
?>