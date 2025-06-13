<?php
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

// Iniciar sesión segura
iniciar_sesion_segura();

// Depuración: Verificar conexión
if (!$conn) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}
error_log("Conexión a DB en login.php: Host: " . mysqli_get_host_info($conn));

// Si el usuario ya está logueado, redirigir a index.php
if (isset($_SESSION['user_id']) && isset($_SESSION['rol'])) {
    error_log("Sesión existente - User ID: {$_SESSION['user_id']}, Rol: {$_SESSION['rol']}");
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$page_title = "Iniciar Sesión - Censo de Mascotas";
$error_login = null;

// Verificar si hay error de sesión expirada
if (isset($_GET['error']) && $_GET['error'] === 'sesion_expirada') {
    $error_login = "La sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $username = sanitizar_input($username);

    if (empty($username) || empty($password)) {
        $error_login = "Usuario y contraseña son requeridos.";
        registrar_intento_fallido($username, $conn);
    } else {
        // Verificar intentos de login (protección contra fuerza bruta)
        $resultado = verificar_intentos_login($username, $conn);
        if ($resultado !== true) {
            $error_login = $resultado;
        } else {
            try {
                // Depuración: Registrar entradas
                error_log("Intento de login - Username: $username");

                $stmt = $conn->prepare("SELECT id_usuario, username, password_hash, rol, nombre_completo, activo FROM usuarios WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $usuario = $result->fetch_assoc();
                    
                    // Depuración: Registrar datos del usuario
                    error_log("Usuario encontrado - ID: {$usuario['id_usuario']}, Rol: {$usuario['rol']}, Activo: {$usuario['activo']}");

                    if ($usuario['activo'] == 1 && in_array($usuario['rol'], ['admin', 'veterinario', 'registrador']) && password_verify($password, $usuario['password_hash'])) {
                        // Regenerar ID de sesión
                        session_regenerate_id(true);

                        // Actualizar último acceso
                        $update_stmt = $conn->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?");
                        $update_stmt->bind_param("i", $usuario['id_usuario']);
                        $update_stmt->execute();
                        $update_stmt->close();

                        // Establecer variables de sesión
                        $_SESSION['user_id'] = $usuario['id_usuario'];
                        $_SESSION['username'] = $usuario['username'];
                        $_SESSION['rol'] = $usuario['rol'];
                        $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
                        $_SESSION['last_activity'] = time();

                        // Para admin, compatibilidad con login_panel.php
                        if ($usuario['rol'] === 'admin') {
                            $_SESSION['panel_admin_logged_in'] = true;
                            $_SESSION['panel_admin_id'] = $usuario['id_usuario'];
                            $_SESSION['panel_admin_rol'] = $usuario['rol'];
                            $_SESSION['panel_admin_nombre'] = $usuario['nombre_completo'];
                        }

                        // Resetear intentos fallidos
                        resetear_intentos_login($username, $conn);

                        // Depuración: Confirmar sesión
                        error_log("Login exitoso - User ID: {$usuario['id_usuario']}, Rol: {$usuario['rol']}, Sesión: " . json_encode($_SESSION));

                        // Redirigir a index.php para todos los roles
                        header("Location: " . BASE_URL . "/index.php");
                        exit();
                    } else if ($usuario['activo'] == 0) {
                        $error_login = "Usuario inactivo. Contacte al soporte.";
                        registrar_intento_fallido($username, $conn);
                    } else if (!in_array($usuario['rol'], ['admin', 'veterinario', 'registrador'])) {
                        $error_login = "Rol no autorizado.";
                        registrar_intento_fallido($username, $conn);
                    } else {
                        $error_login = "Credenciales incorrectas.";
                        registrar_intento_fallido($username, $conn);
                        error_log("Fallo de contraseña para $username. Hash: {$usuario['password_hash']}");
                    }
                } else {
                    $error_login = "Credenciales incorrectas.";
                    registrar_intento_fallido($username, $conn);
                    error_log("Usuario no encontrado: $username");
                }
                $stmt->close();
            } catch (Exception $e) {
                $error_login = "Error de conexión o consulta. Intente más tarde.";
                registrar_intento_fallido($username, $conn);
                error_log("Error en auth/login.php: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Censo de Mascotas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --color-primario: #8b0180;
            --color-secundario: #6a015f;
            --color-blanco: #ffffff;
            --color-texto: #333333;
            --color-footer: #f5f5f5;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: var(--color-blanco);
        }
        .login-header {
            background-color: var(--color-primario);
            padding: 1.5rem 0;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path fill="rgba(255,255,255,0.1)" d="M30,10 Q50,5 70,10 Q95,15 90,40 Q85,70 50,90 Q15,70 10,40 Q5,15 30,10"/></svg>');
            background-size: 120px;
        }
        .login-logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--color-blanco);
            padding: 10px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin: 0 auto;
            max-width: 350px;
        }
        .login-logo {
            height: 50px;
            margin-right: 15px;
        }
        .login-title {
            color: var(--color-primario);
            font-weight: 700;
            margin: 0;
            font-size: 1.3rem;
        }
        .login-container {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .login-card {
            width: 100%;
            max-width: 450px;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .login-card-header {
            background-color: var(--color-primario);
            color: var(--color-blanco);
            padding: 1.5rem;
            text-align: center;
        }
        .login-card-header h2 {
            font-weight: 600;
            margin: 0;
        }
        .login-card-body {
            padding: 2rem;
            background-color: var(--color-blanco);
        }
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid #ddd;
        }
                .btn-login {
            background-color: var(--color-primario); color: var(--color-blanco);
            border: none; padding: 0.75rem; border-radius: 8px;
            width: 100%; font-weight: 600; transition: all 0.3s ease;
        }
        .btn-login:hover {
            background-color: var(--color-secundario);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .login-footer {
            background-color: var(--color-footer);
            color: var(--color-texto);
            padding: 1.5rem 0;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .login-footer img {
            height: 40px;
            margin-bottom: 0.5rem;
            opacity: 0.8;
        }
        .login-footer p {
            margin: 0.25rem 0;
            color: #666;
        }
        .aviso-privacidad {
            font-size: 0.85rem;
            color: #555;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <header class="login-header">
        <div class="container">
            <div class="login-logo-container">
                <img src="<?php echo htmlspecialchars(BASE_URL); ?>/img/logo_ayuntamiento.png" alt="Logo" class="login-logo">
                <h1 class="login-title">Censo de Mascotas</h1>
            </div>
        </div>
    </header>

    <div class="login-container">
        <div class="login-card">
            <div class="login-card-header">
                <h2><i class="bi bi-person-circle"></i> Iniciar Sesión</h2>
            </div>
            <div class="login-card-body">
                <?php if ($error_login): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_login); ?></div>
                <?php endif; ?>

                <form method="post" action="login.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" required placeholder="Nombre de usuario" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Contraseña">
                    </div>
                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right"></i> Ingresar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <footer class="login-footer">
        <div class="container">
            <img src="<?php echo htmlspecialchars(BASE_URL); ?>/img/logo_ayuntamiento.png" alt="Logo Ayuntamiento">
            <p>Sistema de Censo de Mascotas</p>
            <p>Ayuntamiento de San Andrés Cholula, Administración 2024-2027</p>
            <div class="aviso-privacidad">
                <p><strong>Aviso de Privacidad:</strong> Los datos personales recabados serán protegidos y tratados conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP) y se utilizarán únicamente para los fines del Censo de Mascotas del Ayuntamiento de San Andrés Cholula.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>