<?php
// Evitar salida previa
ob_start();

require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/includes/funciones.php';

// Iniciar sesión segura (opcional, ya que cerrar_sesion() ahora maneja esto)
iniciar_sesion_segura();

// Cerrar sesión
cerrar_sesion();

// Limpiar buffer de salida
ob_end_clean();

// Redirigir a login
header("Location: " . BASE_URL . "/auth/login.php");
exit();
?>