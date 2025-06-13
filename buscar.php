<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Forzar autenticación
requerir_autenticacion();

$page_title = "Buscar Tutor";
include(BASE_PATH . '/includes/header.php');
?>

<div class="container my-4 search-container">
    <h2 class="mb-4 search-title">Buscar Tutor</h2>
    
    <div class="card mb-4 search-card shadow-sm">
        <div class="card-header search-card-header">
            <h4>Filtros de Búsqueda</h4>
        </div>
        <div class="card-body">
            <form method="get" action="" class="search-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="nombre" class="form-label">Nombre:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="apellido_paterno" class="form-label">Apellido Paterno:</label>
                        <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" value="<?= htmlspecialchars($_GET['apellido_paterno'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="apellido_materno" class="form-label">Apellido Materno:</label>
                        <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" value="<?= htmlspecialchars($_GET['apellido_materno'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="telefono" class="form-label">Teléfono:</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" value="<?= htmlspecialchars($_GET['telefono'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="calle" class="form-label">Calle:</label>
                        <input type="text" class="form-control" id="calle" name="calle" value="<?= htmlspecialchars($_GET['calle'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="numero_exterior" class="form-label">Número Exterior:</label>
                        <input type="text" class="form-control" id="numero_exterior" name="numero_exterior" value="<?= htmlspecialchars($_GET['numero_exterior'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="colonia" class="form-label">Colonia:</label>
                        <input type="text" class="form-control" id="colonia" name="colonia" value="<?= htmlspecialchars($_GET['colonia'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="codigo_postal" class="form-label">Código Postal:</label>
                        <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" value="<?= htmlspecialchars($_GET['codigo_postal'] ?? '') ?>">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" class="btn btn-primary search-btn">Buscar</button>
                    <a href="<?= BASE_URL ?>/buscar.php" class="btn btn-secondary clear-btn">Limpiar Filtros</a>
                </div>
            </form>
        </div>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && (isset($_GET['nombre']) || isset($_GET['apellido_paterno']) || isset($_GET['apellido_materno']) || isset($_GET['telefono']) || isset($_GET['email']) || isset($_GET['calle']) || isset($_GET['numero_exterior']) || isset($_GET['colonia']) || isset($_GET['codigo_postal']))) {
        $nombre = sanitizar_input($_GET['nombre'] ?? '');
        $apellido_paterno = sanitizar_input($_GET['apellido_paterno'] ?? '');
        $apellido_materno = sanitizar_input($_GET['apellido_materno'] ?? '');
        $telefono = sanitizar_input($_GET['telefono'] ?? '');
        $email = sanitizar_input($_GET['email'] ?? '');
        $calle = sanitizar_input($_GET['calle'] ?? '');
        $numero_exterior = sanitizar_input($_GET['numero_exterior'] ?? '');
        $colonia = sanitizar_input($_GET['colonia'] ?? '');
        $codigo_postal = sanitizar_input($_GET['codigo_postal'] ?? '');

        // Validar conexión a la base de datos
        if (!$conn) {
            echo '<div class="alert alert-danger text-center">Error: No se pudo conectar a la base de datos.</div>';
            include(BASE_PATH . '/includes/footer.php');
            exit;
        }

        $query = "SELECT * FROM tutores WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($nombre)) {
            $query .= " AND nombre LIKE ?";
            $params[] = "%" . $nombre . "%";
            $types .= "s";
        }
        if (!empty($apellido_paterno)) {
            $query .= " AND apellido_paterno LIKE ?";
            $params[] = "%" . $apellido_paterno . "%";
            $types .= "s";
        }
        if (!empty($apellido_materno)) {
            $query .= " AND apellido_materno LIKE ?";
            $params[] = "%" . $apellido_materno . "%";
            $types .= "s";
        }
        if (!empty($telefono)) {
            $query .= " AND telefono LIKE ?";
            $params[] = "%" . $telefono . "%";
            $types .= "s";
        }
        if (!empty($email)) {
            $query .= " AND email LIKE ?";
            $params[] = "%" . $email . "%";
            $types .= "s";
        }
        if (!empty($calle)) {
            $query .= " AND calle LIKE ?";
            $params[] = "%" . $calle . "%";
            $types .= "s";
        }
        if (!empty($numero_exterior)) {
            $query .= " AND numero_exterior LIKE ?";
            $params[] = "%" . $numero_exterior . "%";
            $types .= "s";
        }
        if (!empty($colonia)) {
            $query .= " AND colonia LIKE ?";
            $params[] = "%" . $colonia . "%";
            $types .= "s";
        }
        if (!empty($codigo_postal)) {
            $query .= " AND codigo_postal LIKE ?";
            $params[] = "%" . $codigo_postal . "%";
            $types .= "s";
        }

        $stmt = $conn->prepare($query);

        if ($stmt === false) { // Manejar errores de preparación
            echo '<div class="alert alert-danger text-center">Error al preparar la consulta: ' . htmlspecialchars($conn->error) . '</div>';
            include(BASE_PATH . '/includes/footer.php');
            exit;
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<div class="card search-results-card shadow-sm">
                    <div class="card-header search-card-header">
                        <h4>Resultados de la Búsqueda</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover search-results-table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Apellidos</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Dirección</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<tr>
                        <td>' . htmlspecialchars($row['nombre'] ?? '') . '</td>
                        <td>' . htmlspecialchars($row['apellido_paterno'] ?? '') . ' ' . htmlspecialchars($row['apellido_materno'] ?? '') . '</td>
                        <td>' . htmlspecialchars($row['telefono'] ?? '') . '</td>
                        <td>' . htmlspecialchars($row['email'] ?? '') . '</td>
                        <td>';
                // Construye la dirección de forma segura
                $direccion_parts = [];
                if (!empty($row['calle'])) $direccion_parts[] = htmlspecialchars($row['calle']);
                if (!empty($row['numero_exterior'])) $direccion_parts[] = htmlspecialchars($row['numero_exterior']);
                if (!empty($row['colonia'])) $direccion_parts[] = htmlspecialchars($row['colonia']);
                
                $direccion_display = implode(', ', $direccion_parts);
                
                if (!empty($row['codigo_postal'])) {
                    $direccion_display .= (empty($direccion_display) ? '' : ' C.P. ') . htmlspecialchars($row['codigo_postal']);
                }
                echo $direccion_display;
                echo '</td>
                        <td>
                            <a href="' . BASE_URL . '/perfil.php?id=' . ($row['id_tutor'] ?? '') . '" class="btn btn-sm view-profile-btn">
                                <i class="bi bi-eye-fill"></i> Ver Perfil
                            </a>
                            <a href="' . BASE_URL . '/agregar_mascota.php?tutor=' . ($row['id_tutor'] ?? '') . '" class="btn btn-sm add-pet-btn mt-2 mt-md-0 ms-md-2">
                                <i class="bi bi-plus-circle-fill"></i> Añadir Mascota
                            </a>
                        </td>
                      </tr>';
            }
            
            echo '</tbody>
                            </table>
                        </div>
                    </div>
                  </div>';
        } else {
            echo '<div class="alert alert-warning text-center">No se encontraron resultados para la búsqueda.</div>';
        }
        
        $stmt->close();
    }
    ?>
</div>

<style>
    /* Variables locales para este documento */
    :root {
        --search-primary-color: #8b0180; /* El mismo morado que el header */
        --search-secondary-color: #6c757d; /* Gris de Bootstrap secondary */
        --search-text-color: #343a40; /* Texto oscuro */
        --search-border-color: #dee2e6; /* Borde de tabla y inputs */
        --search-white-text: #ffffff; /* Definir el blanco para el texto */
    }

    /* Estilos para el contenedor principal de la página de búsqueda */
    .search-container {
        padding-top: 2rem;
        padding-bottom: 2rem;
    }

    .search-title {
        color: var(--search-primary-color);
        font-weight: bold;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    /* Estilos para las tarjetas de búsqueda y resultados */
    .search-card {
        border: 1px solid var(--search-border-color);
        border-radius: 0.5rem;
        overflow: hidden; /* Asegura que el border-radius se aplique al card-header */
    }

    .search-card-header {
        background-color: var(--search-primary-color);
        color: var(--search-white-text);
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        font-weight: 600;
        text-align: center;
    }

    .search-card-header h4 {
        margin-bottom: 0;
        color: inherit;
    }

    .search-form .form-label {
        font-weight: 500;
        color: var(--search-text-color);
        margin-bottom: 0.25rem;
    }

    .search-form .form-control {
        border: 1px solid var(--search-border-color);
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .search-form .form-control:focus {
        border-color: rgba(139, 1, 128, 0.5);
        box-shadow: 0 0 0 0.25rem rgba(139, 1, 128, 0.25);
    }

    .search-btn {
        background-color: var(--search-primary-color);
        border-color: var(--search-primary-color);
        color: #fff;
        padding: 0.6rem 1.5rem;
        border-radius: 0.375rem;
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }

    .search-btn:hover {
        background-color: var(--search-secondary-color);
        border-color: var(--search-secondary-color);
        color: #fff;
    }

    .clear-btn {
        background-color: var(--search-secondary-color);
        border-color: var(--search-secondary-color);
        color: #fff;
        padding: 0.6rem 1.5rem;
        border-radius: 0.375rem;
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }

    .clear-btn:hover {
        background-color: #5a6268;
        border-color: #545b62;
        color: #fff;
    }

    /* Estilos para la tabla de resultados */
    .search-results-card {
        margin-top: 2rem;
    }

    .search-results-table th {
        background-color: var(--search-primary-color);
        color: var(--search-white-text);
        font-weight: 600;
        border-bottom: 2px solid var(--search-primary-color);
    }

    .search-results-table td {
        vertical-align: middle;
        border-color: var(--search-border-color);
    }

    /* Botones de acción en la tabla - Versión mejorada */
    .view-profile-btn {
        background-color: #17a2b8;
        border-color: #17a2b8;
        color: #fff;
        border-radius: 20px;
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .view-profile-btn:hover {
        background-color: #138496;
        border-color: #117a8b;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .add-pet-btn {
        background-color: #28a745;
        border-color: #28a745;
        color: #fff;
        border-radius: 20px;
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .add-pet-btn:hover {
        background-color: #218838;
        border-color: #1e7e34;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    /* Efecto para cuando se presiona el botón */
    .view-profile-btn:active, 
    .add-pet-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 3px rgba(0,0,0,0.1);
    }

    /* Estilos para alertas */
    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffeeba;
        color: #856404;
        padding: 1rem;
        border-radius: 0.25rem;
    }

    /* Responsive adjustments for buttons in table */
    @media (max-width: 767.98px) {
        .search-results-table td:last-child {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .view-profile-btn,
        .add-pet-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<?php include(BASE_PATH . '/includes/footer.php'); ?>