<?php
// Evitar salida previa
ob_start();

// Incluir config.php y otras dependencias
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Iniciar sesión segura
iniciar_sesion_segura();

// Si el admin ya está logueado en el panel, redirigir a gestion_usuarios.php
if (isset($_SESSION['panel_admin_logged_in']) && $_SESSION['panel_admin_logged_in'] === true) {
    header("Location: " . BASE_URL . "/admin/index_panel.php");
    exit();
}

$page_title = "Login Panel Administrativo";
$error_login = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizar_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_login = "Usuario y contraseña son requeridos.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id_usuario, username, password_hash, rol, nombre_completo, activo FROM usuarios WHERE username = ?");
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $conn->error);
            }
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $usuario = $result->fetch_assoc();

                if ($usuario['activo'] == 1 && $usuario['rol'] === 'admin' && password_verify($password, $usuario['password_hash'])) {
                    // Actualizar último acceso
                    $update_stmt = $conn->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?");
                    if (!$update_stmt) {
                        throw new Exception("Error al preparar la actualización de último acceso: " . $conn->error);
                    }
                    $update_stmt->bind_param("i", $usuario['id_usuario']);
                    $update_stmt->execute();
                    $update_stmt->close();

                    // Establecer variables de sesión específicas para el panel de admin
                    $_SESSION['panel_admin_logged_in'] = true;
                    $_SESSION['panel_admin_id'] = $usuario['id_usuario'];
                    $_SESSION['panel_admin_rol'] = $usuario['rol'];
                    $_SESSION['panel_admin_nombre'] = $usuario['nombre_completo'];

                    // Registrar acceso exitoso
                    registrar_acceso($usuario['id_usuario'], true, "Login exitoso en panel de administración");

                    // Limpiar buffer y redirigir
                    ob_end_clean();
                    header("Location: " . BASE_URL . "/admin/index_panel.php");
                    exit();
                } else if ($usuario['rol'] !== 'admin') {
                    $error_login = "Acceso denegado. Solo administradores.";
                    registrar_acceso(null, false, "Intento de login en panel admin con rol no autorizado: $username");
                } else if ($usuario['activo'] == 0) {
                    $error_login = "Usuario inactivo. Contacte al soporte.";
                    registrar_acceso(null, false, "Intento de login en panel admin con usuario inactivo: $username");
                } else {
                    $error_login = "Credenciales incorrectas.";
                    registrar_acceso(null, false, "Credenciales incorrectas en panel admin: $username");
                }
            } else {
                $error_login = "Credenciales incorrectas.";
                registrar_acceso(null, false, "Usuario no encontrado en panel admin: $username");
            }
            $stmt->close();
        } catch (Exception $e) {
            $error_login = "Error de conexión o consulta. Intente más tarde.";
            registrar_error("Error en login_panel.php: " . $e->getMessage());
        }
    }
}

// Limpiar buffer antes de enviar la salida HTML
ob_end_clean();
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
        body { display: flex; flex-direction: column; min-height: 100vh; margin: 0; }
        .login-header {
            background-color: var(--color-primario);
            padding: 1.5rem 0;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path fill="rgba(255,255,255,0.1)" d="M30,10 Q50,5 70,10 Q95,15 90,40 Q85,70 50,90 Q15,70 10,40 Q5,15 30,10"/></svg>');
            background-size: 120px;
        }
        .login-logo-container {
            display: flex; align-items: center; justify-content: center;
            background-color: var(--color-blanco); padding: 10px 20px;
            border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin: 0 auto; max-width: 350px;
        }
        .login-logo { height: 50px; margin-right: 15px; }
        .login-title { color: var(--color-primario); font-weight: 700; margin: 0; font-size: 1.3rem; }
        .login-container {
            flex-grow: 1; display: flex; align-items: center; justify-content: center;
            padding: 2rem 1rem; background-color: var(--color-blanco);
        }
        .login-card {
            width: 100%; max-width: 450px; border: none;
            border-radius: 12px; overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .login-card-header {
            background-color: var(--color-primario); color: var(--color-blanco);
            padding: 1.5rem; text-align: center;
        }
        .login-card-header h2 { font-weight: 600; margin: 0; }
        .login-card-body { padding: 2rem; background-color: var(--color-blanco); }
        .form-control {
            border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            border-color: var(--color-primario);
            box-shadow: 0 0 0 0.2rem rgba(139, 1, 128, 0.15);
        }
        .btn-login {
            background-color: var(--color-primario); color: var(--color-blanco);
            border: none; padding: 0.75rem; border-radius: 8px;
            width: 100%; font-weight: 600; transition: all 0.3s ease;
        }
        .btn-login:hover {
            background-color: var(--color-secundario);
            transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .login-footer {
            background-color: var(--color-footer); color: var(--color-texto);
            padding: 1.5rem 0; text-align: center; border-top: 1px solid #e0e0e0;
        }
        .login-footer img { height: 40px; margin-bottom: 0.5rem; opacity: 0.8; }
        .login-footer p { margin: 0.25rem 0; color: #666; }
    </style>
</head>
<body>
    <header class="login-header">
        <div class="container">
            <div class="login-logo-container">
                <img src="<?php echo BASE_URL; ?>/img/logo_ayuntamiento.png" alt="Logo" class="login-logo">
                <h1 class="login-title">Panel Administrativo</h1>
            </div>
        </div>
    </header>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-card-header">
                <h2><i class="bi bi-shield-lock"></i> Acceso Admin</h2>
            </div>
            <div class="login-card-body">
                <?php if ($error_login): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_login); ?></div>
                <?php endif; ?>
                
                <form method="post" action="login_panel.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" required placeholder="Usuario administrador" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
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
            <img src="<?php echo BASE_URL; ?>/img/logo_ayuntamiento.png" alt="Logo Ayuntamiento">
            <p>Sistema de Censo de Mascotas</p>
            <p>Ayuntamiento de San Andrés Cholula © <?php echo date('Y'); ?></p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>