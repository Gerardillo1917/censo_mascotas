<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
iniciar_sesion_segura();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir_con_mensaje('/admin/gestion_usuarios.php', 'danger', 'Método no permitido');
}

// Validación y sanitización
$nombre_completo = filter_input(INPUT_POST, 'nombre_completo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$campana_lugar = filter_input(INPUT_POST, 'campana_lugar', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$rol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$activo = isset($_POST['activo']) ? 1 : 0;

if (empty($nombre_completo) || empty($campana_lugar) || empty($rol) || !array_key_exists($rol, ROLES)) {
    redirigir_con_mensaje('/admin/form_agregar_usuario.php', 'danger', 'Datos inválidos');
}

// Generación de credenciales seguras
$username = generarUsernameUnico($conn, $nombre_completo);
$password = generarPasswordSeguro();
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Transacción para mayor seguridad
$conn->begin_transaction();

try {
    // Insertar usuario principal
    $stmt = $conn->prepare("INSERT INTO usuarios (username, password_hash, rol, nombre_completo, campana_lugar, activo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $username, $password_hash, $rol, $nombre_completo, $campana_lugar, $activo);
    $stmt->execute();
    $id_usuario = $stmt->insert_id;
    $stmt->close();
    
    $conn->commit();
    

        // Mensaje de éxito con usuario y contraseña
    $_SESSION['mensaje_credenciales'] = '
    <div class="alert alert-success">
        <h5><i class="fas fa-user-check me-2"></i>Usuario Creado - ID: ' . htmlspecialchars($id_usuario) . '</h5>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nombre:</strong> ' . htmlspecialchars($nombre_completo) . '</p>
                <p><strong>Rol:</strong> ' . htmlspecialchars(ROLES[$rol]) . '</p>
            </div>
            <div class="col-md-6">
                <p><strong>Usuario:</strong> <code>' . htmlspecialchars($username) . '</code></p>
                <p><strong>Contraseña:</strong> <code>' . htmlspecialchars($password) . '</code></p>
            </div>
        </div>
        <div class="mt-2 alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            IMPORTANTE: Anote o guarde estas credenciales en un lugar seguro. No se mostrarán nuevamente.
        </div>
    </div>';
    
    
    
    redirigir_con_mensaje('/admin/gestion_usuarios.php', 'success', 'Usuario creado con éxito');
    
} catch (Exception $e) {
    $conn->rollback();
    error_log('Error creación usuario: ' . $e->getMessage());
    redirigir_con_mensaje('/admin/form_agregar_usuario.php', 'danger', 'Error al crear usuario');
}
?>