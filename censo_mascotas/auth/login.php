<?php
require_once __DIR__ . '/../config.php';

// Si ya está autenticado, redirigir al inicio
if (!empty($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$page_title = "Iniciar Sesión";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validación simple (remover en producción)
    if ($username == 'admin' && $password == 'admin123') {
        $_SESSION['usuario_id'] = 1;
        $_SESSION['rol'] = 'admin';
        $_SESSION['nombre_usuario'] = 'Administrador';
        
        // Redirigir a la URL guardada o al inicio
        if (isset($_SESSION['redirect_url'])) {
            $redirect = $_SESSION['redirect_url'];
            unset($_SESSION['redirect_url']);
            header("Location: " . $redirect);
        } else {
            header("Location: " . BASE_URL . "/index.php");
        }
        exit();
    } else {
        $error = "Credenciales incorrectas";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - San Andrés Cholula</title>
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
        
        /* Patrón de papel picado para el header */
        .login-header {
            background-color: var(--color-primario);
            padding: 1.5rem 0;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path fill="rgba(255,255,255,0.1)" d="M30,10 Q50,5 70,10 Q95,15 90,40 Q85,70 50,90 Q15,70 10,40 Q5,15 30,10"/></svg>');
            background-size: 120px;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 20px;
            background: var(--color-blanco);
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
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
            max-width: 300px;
        }
        
        .login-logo {
            height: 50px;
            margin-right: 15px;
        }
        
        .login-title {
            color: var(--color-primario);
            font-weight: 700;
            margin: 0;
            font-size: 1.5rem;
        }
        
        /* Contenedor principal */
        .login-container {
            min-height: calc(100vh - 250px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background-color: var(--color-blanco);
        }
        
        /* Tarjeta de login */
        .login-card {
            width: 100%;
            max-width: 450px;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
        }
        
        .login-card-header {
            background-color: var(--color-primario);
            color: var(--color-blanco);
            padding: 1.5rem;
            text-align: center;
            position: relative;
        }
        
        .login-card-header h2 {
            font-weight: 600;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        
        .login-card-body {
            padding: 2rem;
            background-color: var(--color-blanco);
        }
        
        /* Formulario */
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid #ddd;
        }
        
        .form-control:focus {
            border-color: var(--color-primario);
            box-shadow: 0 0 0 0.2rem rgba(139, 1, 128, 0.15);
        }
        
        .btn-login {
            background-color: var(--color-primario);
            color: var(--color-blanco);
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background-color: var(--color-secundario);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Footer */
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-logo-container {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }
            
            .login-logo {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .login-title {
                font-size: 1.3rem;
            }
            
            .login-card {
                margin: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header con diseño de papel picado -->
    <header class="login-header">
        <div class="container">
            <div class="login-logo-container">
                <img src="<?php echo BASE_URL; ?>/img/logo_ayuntamiento.png" alt="Logo" class="login-logo">
                <h1 class="login-title">Censo de Mascotas</h1>
            </div>
        </div>
    </header>
    
    <!-- Contenido principal -->
    <div class="login-container">
        <div class="login-card">
            <div class="login-card-header">
                <h2><i class="bi bi-shield-lock"></i> Iniciar Sesión</h2>
            </div>
            <div class="login-card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" required placeholder="Ingrese su usuario">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Ingrese su contraseña">
                    </div>
                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right"></i> Ingresar
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Footer con fondo gris claro -->
    <footer class="login-footer">
        <div class="container">
            <img src="<?php echo BASE_URL; ?>/img/logo_ayuntamiento.png" alt="Logo Ayuntamiento">
            <p>Sistema de Censo de Mascotas</p>
            <p>Ayuntamiento de San Andrés Cholula &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>