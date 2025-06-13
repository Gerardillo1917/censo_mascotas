<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Forzar autenticación
requerir_autenticacion();
$page_title = "Inicio";
require_once BASE_PATH . '/includes/header.php';
?>

<style>
    :root {
        --color-primario: #8b0180;
        --color-secundario: #6a015f;
        --color-texto: #333333;
        --color-fondo: #f8f9fa;
    }

    body {
        background-image: url('<?php echo BASE_URL; ?>/img/logo_ayuntamiento_white.png');
        background-size: 30%;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-color: var(--color-fondo);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    
    .main-content {
        flex: 1;
    }
    
    .card-module {
        transition: all 0.3s ease;
        height: 100%;
        border: none;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .card-module:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }
    
    .card-header-custom {
        background-color: var(--color-primario);
        color: white;
        padding: 1.25rem;
        border-bottom: none;
    }
    
    .card-header-custom h4 {
        font-weight: 600;
        margin-bottom: 0;
    }
    
    .module-icon {
        height: 120px;
        object-fit: contain;
        margin-bottom: 1.5rem;
    }
    
    .btn-custom {
        background-color: var(--color-primario);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
        margin-top: auto;
        width: 100%;
    }
    
    .btn-custom:hover {
        background-color: var(--color-secundario);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .card-text {
        color: var(--color-texto);
        margin-bottom: 1.5rem;
    }
    
    @media (max-width: 768px) {
        body {
            background-size: 50%;
        }
        
        .module-icon {
            height: 80px;
        }
        
        .card-header-custom {
            padding: 1rem;
        }
        
        .card-header-custom h4 {
            font-size: 1.1rem;
        }
    }
</style>

<div class="main-content">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card card-module h-100">
                    <div class="card-header card-header-custom">
                        <h4 class="mb-0">Agregar Tutor</h4>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="text-center mb-3">
                            <img src="<?php echo BASE_URL; ?>/img/registro_tutor.png" alt="Agregar Tutor" class="module-icon">
                        </div>
                        <p class="card-text">Registre un nuevo tutor junto con su(s) mascota(s) en el sistema.</p>
                        <a href="<?php echo BASE_URL; ?>/agregar_tutor.php" class="btn btn-custom mt-auto">Ir a Agregar Tutor</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-module h-100">
                    <div class="card-header card-header-custom">
                        <h4 class="mb-0">Buscar Tutor</h4>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="text-center mb-3">
                            <img src="<?php echo BASE_URL; ?>/img/buscar_tutor.png" alt="Buscar Tutor" class="module-icon">
                        </div>
                        <p class="card-text">Busque un tutor existente para consultar o actualizar su información.</p>
                        <a href="<?php echo BASE_URL; ?>/buscar.php" class="btn btn-custom mt-auto">Ir a Buscar Tutor</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once BASE_PATH . '/includes/footer.php';
?>