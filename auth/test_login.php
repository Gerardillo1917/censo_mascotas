<?php
// Establecer zona horaria de Ciudad de México
date_default_timezone_set('America/Mexico_City');

// Iniciar sesión para verificar sesiones
session_start();

// Incluir dependencias
if (!file_exists(__DIR__ . '/../config.php')) {
    die('Error: No se encontró config.php en ' . __DIR__ . '/../config.php');
}
require_once __DIR__ . '/../config.php';

if (!file_exists(BASE_PATH . '/database/conexion.php')) {
    die('Error: No se encontró conexion.php en ' . BASE_PATH . '/database/conexion.php');
}
require_once BASE_PATH . '/database/conexion.php';

if (!file_exists(BASE_PATH . '/includes/funciones.php')) {
    die('Error: No se encontró funciones.php en ' . BASE_PATH . '/includes/funciones.php');
}
require_once BASE_PATH . '/includes/funciones.php';

// Verificar conexión a la base de datos
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
} else {
    echo "Conexión exitosa a la base de datos.<br>";
    echo "Host: " . mysqli_get_host_info($conn) . "<br>";
    echo "Base de datos: " . mysqli_get_server_info($conn) . " - " . $conn->select_db('censo_mascotas') . "<br>";
}

// Mostrar entradas recibidas
echo "<h3>Entradas recibidas:</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? 'No proporcionado';
    $password = $_POST['password'] ?? 'No proporcionado';
    echo "Username: " . htmlspecialchars($username) . "<br>";
    echo "Password: [Oculta por seguridad]<br>";
} else {
    echo "No se han enviado datos POST.<br>";
}

// Verificar tabla usuarios
echo "<h3>Verificación de tabla usuarios:</h3>";
try {
    $result = $conn->query("SELECT id_usuario, username, rol, activo, password_hash, nombre_completo, campana_lugar FROM usuarios ORDER BY id_usuario LIMIT 10");
    if ($result->num_rows > 0) {
        echo "Usuarios encontrados:<br>";
        while ($row = $result->fetch_assoc()) {
            echo "ID: {$row['id_usuario']}, Username: {$row['username']}, Rol: {$row['rol']}, Activo: {$row['activo']}, Nombre: {$row['nombre_completo']}, Campaña/Lugar: {$row['campana_lugar']}<br>";
        }
    } else {
        echo "No hay usuarios en la tabla usuarios.<br>";
    }
} catch (Exception $e) {
    echo "Error al consultar tabla usuarios: " . $e->getMessage() . "<br>";
}

// Probar autenticación manual
echo "<h3>Prueba de autenticación:</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($username) && $username !== 'No proporcionado') {
    try {
        $stmt = $conn->prepare("SELECT id_usuario, username, password_hash, rol, activo FROM usuarios WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            echo "Usuario encontrado: {$usuario['username']}, Rol: {$usuario['rol']}, Activo: {$usuario['activo']}<br>";
            if (password_verify($password, $usuario['password_hash'])) {
                echo "Contraseña correcta.<br>";
            } else {
                echo "Contraseña incorrecta. Hash almacenado: {$usuario['password_hash']}<br>";
            }
        } else {
            echo "Usuario no encontrado.<br>";
        }
        $stmt->close();
    } catch (Exception $e) {
        echo "Error en consulta: " . $e->getMessage() . "<br>";
    }
} else {
    echo "Proporciona un usuario y contraseña para probar la autenticación.<br>";
}

// Formulario para probar
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Login</title>
</head>
<body>
    <h2>Probar Login</h2>
    <form method="post" action="test_login.php">
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username" required value="admin_panel"><br><br>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required><br><br>
        <button type="submit">Probar</button>
    </form>
</body>
</html>