<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

// Obtener parámetros del reporte|
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$id_localidad = $_GET['id_localidad'] ?? '';
$tipo_procedimiento = $_GET['tipo_procedimiento'] ?? '';

// Validar fechas
if ($fecha_inicio > $fecha_fin) {
    $fecha_inicio = $fecha_fin;
}

// Configurar headers para descarga de Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="reporte_procedimientos_' . date('Ymd') . '.xls"');

// Consulta para obtener los datos
$sql = "SELECT 
        s.fecha, s.hora, s.tipo, s.estado,
        m.nombre as mascota, m.genero, m.edad, m.especie, m.raza,
        CONCAT(t.nombre, ' ', t.apellido_paterno, ' ', COALESCE(t.apellido_materno, '')) as tutor,
        t.telefono, t.email,
        l.nombre as localidad
    FROM salud_mascotas s
    JOIN mascotas m ON s.id_mascota = m.id_mascota
    JOIN tutores t ON m.id_tutor = t.id_tutor
    JOIN localidades l ON s.id_localidad = l.id_localidad
    WHERE s.fecha BETWEEN ? AND ?";

$params = [$fecha_inicio, $fecha_fin];
$types = "ss";

if (!empty($id_localidad)) {
    $sql .= " AND s.id_localidad = ?";
    $params[] = $id_localidad;
    $types .= "i";
}

if (!empty($tipo_procedimiento)) {
    $sql .= " AND s.tipo = ?";
    $params[] = $tipo_procedimiento;
    $types .= "s";
}

$sql .= " ORDER BY s.fecha, s.hora";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Crear contenido del Excel
echo "<table border='1'>";
echo "<tr>
        <th colspan='10' style='background-color: #8b0180; color: white;'>Reporte de Procedimientos</th>
      </tr>
      <tr>
        <th colspan='10'>Fecha: " . date('d/m/Y H:i') . "</th>
      </tr>
      <tr>
        <th colspan='10'>Periodo: " . date('d/m/Y', strtotime($fecha_inicio)) . " al " . date('d/m/Y', strtotime($fecha_fin)) . "</th>
      </tr>";

// Cabeceras de columnas
echo "<tr style='background-color: #e6c7eb;'>
        <th>Fecha</th>
        <th>Hora</th>
        <th>Localidad</th>
        <th>Tipo</th>
        <th>Mascota</th>
        <th>Especie</th>
        <th>Género</th>
        <th>Edad</th>
        <th>Tutor</th>
        <th>Teléfono</th>
      </tr>";

// Datos
foreach ($resultados as $fila) {
    echo "<tr>
            <td>" . date('d/m/Y', strtotime($fila['fecha'])) . "</td>
            <td>" . date('H:i', strtotime($fila['hora'])) . "</td>
            <td>" . htmlspecialchars($fila['localidad']) . "</td>
            <td>" . htmlspecialchars($fila['tipo']) . "</td>
            <td>" . htmlspecialchars($fila['mascota']) . "</td>
            <td>" . htmlspecialchars($fila['especie']) . "</td>
            <td>" . htmlspecialchars($fila['genero']) . "</td>
            <td>" . htmlspecialchars($fila['edad']) . "</td>
            <td>" . htmlspecialchars($fila['tutor']) . "</td>
            <td>" . htmlspecialchars($fila['telefono']) . "</td>
          </tr>";
}

echo "</table>";
exit;