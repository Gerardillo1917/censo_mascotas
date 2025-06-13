<?php
require_once __DIR__ . '/../config.php';

session_start();

if (!isset($_SESSION['usuario_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    redirigir_con_mensaje('/auth/login.php', 'warning', 'Debe iniciar sesión para acceder');
}

function verificar_rol($roles_permitidos) {
    if (!in_array($_SESSION['rol'], $roles_permitidos)) {
        redirigir_con_mensaje('/index.php', 'danger', 'No tiene permisos para esta acción');
    }
}