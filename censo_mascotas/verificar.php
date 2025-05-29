<?php
// test_login.php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/database/conexion.php';

// Simulamos datos de formulario
$_POST = [
    'username' => 'admin_panel',
    'password' => 'admin123'
];

echo "<h2>Prueba de autenticación</h2>";

// 1. Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// 2. Sanitizar inputs
$username = trim($_POST['username']);
$password = trim($_POST['password']);

// 3. Buscar usuario en la base de datos
try {
    $stmt = $conn->prepare("SELECT id_usuario, password_hash, rol, activo FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die("Usuario no encontrado");
    }
    
    $usuario = $result->fetch_assoc();
    
    echo "<h3>Datos del usuario encontrado:</h3>";
    echo "<pre>" . print_r($usuario, true) . "</pre>";
    
    // 4. Verificar contraseña
    if (password_verify($password, $usuario['password_hash'])) {
        echo "<p style='color:green;'>¡Contraseña válida!</p>";
        
        // 5. Verificar rol y estado
        if ($usuario['rol'] === 'admin' && $usuario['activo'] == 1) {
            echo "<p style='color:green;'>Usuario tiene rol admin y está activo</p>";
            
            // 6. Simular inicio de sesión exitoso
            $_SESSION['panel_admin_logged_in'] = true;
            $_SESSION['panel_admin_id'] = $usuario['id_usuario'];
            $_SESSION['panel_admin_rol'] = $usuario['rol'];
            $_SESSION['panel_admin_nombre'] = 'Administrador del Sistema';
            
            echo "<h3>Variables de sesión establecidas:</h3>";
            echo "<pre>" . print_r($_SESSION, true) . "</pre>";
            
            echo "<p style='color:green; font-weight:bold;'>¡Autenticación exitosa! Redirigiendo a gestion_usuarios.php...</p>";
            // En un caso real: header("Location: gestion_usuarios.php");
        } else {
            echo "<p style='color:red;'>El usuario no tiene privilegios admin o no está activo</p>";
        }
    } else {
        echo "<p style='color:red;'>Contraseña incorrecta</p>";
        echo "<p>Hash almacenado: " . $usuario['password_hash'] . "</p>";
        echo "<p>Hash generado ahora: " . password_hash($password, PASSWORD_DEFAULT) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>