<?php
// Verificar si hay output previo (solo para depuración)
if (headers_sent($filename, $linenum)) {
    die("Error: Output iniciado en $filename, línea $linenum. No debe haber output antes de header.php");
}

// Iniciar buffer de salida
ob_start();

// Configuración necesaria antes de cualquier output
$page_title = $page_title ?? 'Censo de Mascotas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - San Andrés Cholula</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ESTILOS DEL HEADER */
        .header-container {
            --color-primario: #8b0180;
            --color-secundario: #6a015f;
            --color-blanco: #ffffff;
            
            background-color: var(--color-primario);
            padding: 1rem 0;
            position: relative;
            z-index: 1000;
        }

        .header-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path fill="rgba(255,255,255,0.1)" d="M30,10 Q50,5 70,10 Q95,15 90,40 Q85,70 50,90 Q15,70 10,40 Q5,15 30,10"/></svg>');
            background-size: 120px;
            opacity: 0.6;
        }

        .header-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--color-blanco);
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            text-decoration: none;
        }

        .header-logo img {
            height: 45px;
            margin-right: 12px;
        }

        .header-title {
            color: var(--color-primario);
            font-weight: 700;
            margin: 0;
            font-size: 1.4rem;
        }

        .header-nav .nav-link {
            color: var(--color-blanco) !important;
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
            border-radius: 5px;
            transition: all 0.2s ease;
        }

        .header-nav .nav-link:hover {
            background-color: rgba(255,255,255,0.15);
        }

        .btn-back {
            border-color: rgba(255,255,255,0.5);
            color: white;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background-color: rgba(255,255,255,0.1);
            border-color: white;
        }

        @media (max-width: 991.98px) {
            .header-logo {
                margin-bottom: 1rem;
            }
            
            .header-nav .navbar-collapse {
                background-color: var(--color-secundario);
                padding: 1rem;
                border-radius: 8px;
                margin-top: 0.5rem;
            }
            
            .btn-back {
                margin-right: 0;
                margin-bottom: 10px;
                width: 100%;
            }
        }

        @media (max-width: 767.98px) {
            .header-logo {
                flex-direction: column;
                text-align: center;
                padding: 12px;
            }
            
            .header-logo img {
                margin-right: 0;
                margin-bottom: 8px;
            }
            
            .header-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <header class="header-container">
        <div class="container">
            <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between">
                <a href="<?php echo BASE_URL; ?>/index.php" class="header-logo">
                    <img src="<?php echo BASE_URL; ?>/img/logo_ayuntamiento.png" alt="Logo Municipal">
                    <span class="header-title">Censo de Mascotas Administración</span>
                </a>
                
                <nav class="navbar navbar-expand-lg navbar-dark header-nav p-0">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHeader">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarHeader">
                        <ul class="navbar-nav ms-auto">
                            <?php if (basename($_SERVER['PHP_SELF']) != 'index.php'): ?>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php">
                                    <i class="fas fa-home me-1"></i> Inicio
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/logout_panel.php">
                                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mt-4">
<?php
// El buffer continúa, se liberará al incluir el footer