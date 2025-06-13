<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/database/conexion.php';

if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "/buscar.php");
    exit();
}

$id_mascota = $_GET['id'];

// Obtener datos de la mascota específica y su tutor
$stmt = $conn->prepare("
    SELECT m.*, t.* 
    FROM mascotas m
    JOIN tutores t ON m.id_tutor = t.id_tutor
    WHERE m.id_mascota = ?
");
$stmt->bind_param("i", $id_mascota);
$stmt->execute();
$mascota_actual = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$mascota_actual) {
    header("Location: " . BASE_URL . "/buscar.php");
    exit();
}

// Obtener TODAS las mascotas del tutor para mostrar en el PDF
$stmt_mascotas = $conn->prepare("SELECT * FROM mascotas WHERE id_tutor = ?");
$stmt_mascotas->bind_param("i", $mascota_actual['id_tutor']);
$stmt_mascotas->execute();
$mascotas = $stmt_mascotas->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_mascotas->close();

// Obtener vacunas para cada mascota
foreach ($mascotas as &$mascota) {
    $stmt_vacunas = $conn->prepare("SELECT * FROM vacunas WHERE id_mascota = ?");
    $stmt_vacunas->bind_param("i", $mascota['id_mascota']);
    $stmt_vacunas->execute();
    $mascota['vacunas'] = $stmt_vacunas->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_vacunas->close();
}
unset($mascota);

function formatField($value, $default = 'N/A') {
    return !empty($value) ? htmlspecialchars($value) : $default;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Mascotas - <?= htmlspecialchars($mascota_actual['nombre']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 10px;
            color: #333;
        }
        .pdf-container {
            max-width: 100%;
            border: 10px solid #8b0180;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .logo {
            height: 70px;
            margin-bottom: 10px;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            color: #8b0180;
            font-size: 16px;
            font-weight: bold;
            border-bottom: 2px solid #8b0180;
            padding-bottom: 5px;
            margin: 15px 0 10px 0;
        }
        .data-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .data-card {
            flex: 1;
            min-width: 250px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            background: #f9f9f9;
        }
        .data-row {
            display: flex;
            margin-bottom: 8px;
        }
        .data-label {
            font-weight: bold;
            min-width: 120px;
        }
        .photo-container {
            text-align: center;
            margin: 10px 0;
        }
        .photo {
            max-width: 150px;
            max-height: 150px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .vacunas-container {
            margin-top: 10px;
        }
        .vacuna-item {
            background-color: #f0f0f0;
            border-left: 3px solid #8b0180;
            padding: 6px 10px;
            margin-bottom: 6px;
            font-size: 13px;
        }
        .footer {
            text-align: right;
            margin-top: 15px;
            font-size: 12px;
            color: #666;
        }
        @media print {
            body {
                padding: 5px;
            }
            .pdf-container {
                border: none;
                padding: 5px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="pdf-container">
        <!-- Encabezado -->
        <div class="header">
            <img src="<?= BASE_URL ?>/img/logo_ayuntamiento.png" class="logo">
            <h1 style="color: #8b0180; margin: 5px 0; font-size: 20px;">Ayuntamiento de San Andrés Cholula</h1>
            <h2 style="margin: 5px 0; font-size: 16px;">Registro Oficial de Mascotas</h2>
        </div>

        <!-- Datos del Tutor -->
        <div class="section">
            <div class="section-title">DATOS DEL TUTOR</div>
            <div class="data-container">
                <div class="data-card">
                    <?php if (!empty($mascota_actual['foto_ruta'])): ?>
                        <div class="photo-container">
                            <img src="<?= htmlspecialchars($mascota_actual['foto_ruta']) ?>" class="photo">
                        </div>
                    <?php endif; ?>
                    
                    <div class="data-row">
                        <span class="data-label">Nombre completo:</span>
                        <?= htmlspecialchars($mascota_actual['nombre']) ?> 
                        <?= htmlspecialchars($mascota_actual['apellido_paterno']) ?> 
                        <?= formatField($mascota_actual['apellido_materno']) ?>
                    </div>
                    
                    <div class="data-row">
                        <span class="data-label">Contacto:</span>
                        Tel: <?= htmlspecialchars($mascota_actual['telefono']) ?> | 
                        Email: <?= formatField($mascota_actual['email']) ?>
                    </div>
                    
                    <div class="data-row">
                        <span class="data-label">Dirección:</span>
                        <?= htmlspecialchars($mascota_actual['calle']) ?> 
                        <?= htmlspecialchars($mascota_actual['numero_exterior']) ?>
                        <?= !empty($mascota_actual['numero_interior']) ? 'Int. ' . htmlspecialchars($mascota_actual['numero_interior']) : '' ?>,
                        <?= htmlspecialchars($mascota_actual['colonia']) ?>, 
                        C.P. <?= htmlspecialchars($mascota_actual['codigo_postal']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Datos de las Mascotas -->
        <div class="section">
            <div class="section-title">MASCOTAS REGISTRADAS</div>
            <div class="data-container">
                <?php foreach ($mascotas as $mascota): ?>
                    <div class="data-card">
                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <?php if (!empty($mascota['foto_ruta'])): ?>
                                <div class="photo-container">
                                    <img src="<?= htmlspecialchars($mascota['foto_ruta']) ?>" class="photo">
                                </div>
                            <?php endif; ?>
                            
                            <div style="flex: 1;">
                                <div class="data-row">
                                    <span class="data-label">Nombre:</span>
                                    <?= htmlspecialchars($mascota['nombre']) ?>
                                </div>
                                
                                <div class="data-row">
                                    <span class="data-label">Especie/Raza:</span>
                                    <?= htmlspecialchars($mascota['especie']) ?> / <?= formatField($mascota['raza']) ?>
                                </div>
                                
                                <div class="data-row">
                                    <span class="data-label">Edad/Género:</span>
                                    <?= $mascota['edad'] ?> meses / <?= $mascota['genero'] ?>
                                </div>
                                
                                <div class="data-row">
                                    <span class="data-label">Color:</span>
                                    <?= formatField($mascota['color']) ?>
                                </div>
                                
                                <div class="data-row">
                                    <span class="data-label">Estado:</span>
                                    <span style="color: <?= $mascota['estado'] == 'Vivo' ? 'green' : 'red' ?>;">
                                        <?= $mascota['estado'] ?>
                                    </span>
                                </div>
                                
                                <?php if ($mascota['incapacidad']): ?>
                                    <div class="data-row">
                                        <span class="data-label">Incapacidad:</span>
                                        <?= formatField($mascota['descripcion_incapacidad']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($mascota['vacunas'])): ?>
                                    <div class="vacunas-container">
                                        <div style="font-weight: bold; margin-bottom: 5px;">Vacunas:</div>
                                        <?php foreach ($mascota['vacunas'] as $vacuna): ?>
                                            <div class="vacuna-item">
                                                <strong><?= htmlspecialchars($vacuna['nombre_vacuna']) ?></strong>
                                                (<?= date('d/m/Y', strtotime($vacuna['fecha_aplicacion'])) ?>)
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            Documento generado el: <?= date('d/m/Y H:i') ?>
        </div>

        <!-- Botones de acción (no se imprimen) -->
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" style="
                background: #8b0180; 
                color: white; 
                border: none; 
                padding: 8px 15px; 
                margin: 0 5px;
                border-radius: 4px;
                cursor: pointer;
            ">
                Imprimir
            </button>
            <a href="perfil.php?id=<?= $mascota_actual['id_tutor'] ?>" style="
                background: #6c757d; 
                color: white; 
                padding: 8px 15px; 
                text-decoration: none; 
                margin: 0 5px;
                border-radius: 4px;
                display: inline-block;
            ">
                Volver al Perfil
            </a>
        </div>
    </div>
</body>
</html>