<?php
ob_start();
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Verificar autenticación de admin
requerir_autenticacion_admin();

// Validar tipo de reporte
$tipo_reporte = filter_input(INPUT_POST, 'tipo_reporte', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$validos = ['machos_hembras', 'esterilizacion', 'vacunas', 'tutores_edad'];
if (!in_array($tipo_reporte, $validos)) {
    registrar_error("Tipo de reporte inválido: $tipo_reporte");
    redirigir_con_mensaje('/admin/reportes.php', 'danger', 'Seleccione un tipo de reporte válido.');
}

// Función para generar archivo CSV
function generarCSV($data, $filename, $title, $headers) {
    ob_end_clean();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF"); // BOM para UTF-8
    fputcsv($output, [$title]);
    fputcsv($output, $headers);
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Función para generar archivo Excel usando ZipArchive
function generarExcel($data, $filename, $title, $headers) {
    if (!class_exists('ZipArchive')) {
        // Fallback a CSV si ZipArchive no está disponible
        return generarCSV($data, $filename, $title, $headers);
    }

    $temp_dir = sys_get_temp_dir();
    $temp_zip = tempnam($temp_dir, 'xlsx_');
    $zip = new ZipArchive();
    if ($zip->open($temp_zip, ZipArchive::CREATE) !== true) {
        throw new Exception("No se pudo crear el archivo temporal.");
    }

    // [Content_Types].xml
    $content_types = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>';
    $zip->addFromString('[Content_Types].xml', $content_types);

    // _rels/.rels
    $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    $zip->addFromString('_rels/.rels', $rels);

    // xl/_rels/workbook.xml.rels
    $workbook_rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>';
    $zip->addFromString('xl/_rels/workbook.xml.rels', $workbook_rels);

    // xl/workbook.xml
    $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets></workbook>';
    $zip->addFromString('xl/workbook.xml', $workbook);

    // xl/worksheets/sheet1.xml
    $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
    $sheet .= '<row><c t="inlineStr"><is><t>' . htmlspecialchars($title) . '</t></is></c></row>';
    $sheet .= '<row>';
    foreach ($headers as $header) {
        $sheet .= '<c t="inlineStr"><is><t>' . htmlspecialchars($header) . '</t></is></c>';
    }
    $sheet .= '</row>';
    foreach ($data as $row) {
        $sheet .= '<row>';
        foreach ($row as $cell) {
            $sheet .= '<c t="inlineStr"><is><t>' . htmlspecialchars($cell) . '</t></is></c>';
        }
        $sheet .= '</row>';
    }
    $sheet .= '</sheetData></worksheet>';
    $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);

    $zip->close();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($temp_zip);
    unlink($temp_zip);
    exit;
}

try {
    // Procesar según el tipo de reporte
    if ($tipo_reporte === 'machos_hembras') {
        // Validar colonias
        $colonias = filter_input_array(INPUT_POST, ['colonias' => ['filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS, 'flags' => FILTER_REQUIRE_ARRAY]])['colonias'] ?: [];
        $otra_colonia = sanitizar_input($_POST['otra_colonia'] ?? '');
        if (empty($colonias) && empty($otra_colonia)) {
            throw new Exception("Debe seleccionar al menos una colonia o especificar otra localidad.");
        }

        $sql = "SELECT t.colonia, 
                       COUNT(DISTINCT t.id_tutor) as total_tutores, 
                       COUNT(m.id_mascota) as total_mascotas, 
                       COUNT(CASE WHEN m.sexo = 'macho' THEN 1 END) as machos, 
                       COUNT(CASE WHEN m.sexo = 'hembra' THEN 1 END) as hembras 
                FROM tutores t 
                LEFT JOIN mascotas m ON t.id_tutor = m.id_tutor 
                WHERE ";
        $params = [];
        $types = "";
        $conditions = [];

        if (!empty($colonias)) {
            $placeholders = implode(',', array_fill(0, count($colonias), '?'));
            $conditions[] = "t.colonia IN ($placeholders)";
            $params = array_merge($params, $colonias);
            $types .= str_repeat('s', count($colonias));
        }
        if (!empty($otra_colonia)) {
            $conditions[] = "t.colonia LIKE ?";
            $params[] = "%$otra_colonia%";
            $types .= "s";
        }

        $sql .= implode(' OR ', $conditions);
        $sql .= " GROUP BY t.colonia";

        $stmt = $conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $data = [];
        $total_tutores = 0;
        $total_mascotas = 0;
        $total_machos = 0;
        $total_hembras = 0;
        foreach ($result as $row) {
            $data[] = [
                $row['colonia'],
                $row['total_tutores'],
                $row['total_mascotas'],
                $row['machos'],
                $row['hembras']
            ];
            $total_tutores += $row['total_tutores'];
            $total_mascotas += $row['total_mascotas'];
            $total_machos += $row['machos'];
            $total_hembras += $row['hembras'];
        }
        if (count($result) > 1) {
            $data[] = ['Total', $total_tutores, $total_mascotas, $total_machos, $total_hembras];
        }

        generarExcel($data, 'reporte_machos_hembras_' . date('Ymd_His'), 'Conteo de Machos y Hembras por Colonia', ['Colonia', 'Total Tutores', 'Total Mascotas', 'Machos', 'Hembras']);
    } elseif ($tipo_reporte === 'esterilizacion') {
        $tipo_mascota = filter_input(INPUT_POST, 'tipo_mascota', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $colonias = filter_input_array(INPUT_POST, ['colonias_esterilizacion' => ['filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS, 'flags' => FILTER_REQUIRE_ARRAY]])['colonias_esterilizacion'] ?: [];
        $otra_colonia = sanitizar_input($_POST['otra_colonia_est'] ?? '');

        $sql = "SELECT m.especie, t.colonia, 
                       COUNT(m.id_mascota) as total_mascotas, 
                       COUNT(CASE WHEN m.esterilizado = 'SÍ' THEN 1 END) as esterilizadas, 
                       COUNT(CASE WHEN m.esterilizado = 'NO' THEN 1 END) as no_esterilizadas 
                FROM mascotas m 
                LEFT JOIN tutores t ON m.id_tutor = t.id_tutor 
                WHERE 1=1";
        $params = [];
        $types = "";
        $conditions = [];

        if ($tipo_mascota !== 'todos') {
            $conditions[] = "m.especie = ?";
            $params[] = $tipo_mascota;
            $types .= "s";
        }
        if (!empty($colonias)) {
            $placeholders = implode(',', array_fill(0, count($colonias), '?'));
            $conditions[] = "t.colonia IN ($placeholders)";
            $params = array_merge($params, $colonias);
            $types .= str_repeat('s', count($colonias));
        }
        if (!empty($otra_colonia)) {
            $conditions[] = "t.colonia LIKE ?";
            $params[] = "%$otra_colonia%";
            $types .= "s";
        }

        if ($conditions) {
            $sql .= " AND (" . implode(' OR ', $conditions) . ")";
        }
        $sql .= empty($colonias) && empty($otra_colonia) ? " GROUP BY m.especie" : " GROUP BY m.especie, t.colonia";

        $stmt = $conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $data = [];
        foreach ($result as $row) {
            $data[] = [
                ucfirst($row['especie']),
                empty($colonias) && empty($otra_colonia) ? '' : $row['colonia'],
                $row['total_mascotas'],
                $row['esterilizadas'],
                $row['no_esterilizadas']
            ];
        }

        $headers = empty($colonias) && empty($otra_colonia) ? ['Tipo de Mascota', 'Total Mascotas', 'Esterilizadas', 'No Esterilizadas'] : ['Tipo de Mascota', 'Colonia', 'Total Mascotas', 'Esterilizadas', 'No Esterilizadas'];
        generarExcel($data, 'reporte_esterilizacion_' . date('Ymd_His'), 'Estado de Esterilización', $headers);
    } elseif ($tipo_reporte === 'vacunas') {
        $vacunas = filter_input_array(INPUT_POST, ['vacunas' => ['filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS, 'flags' => FILTER_REQUIRE_ARRAY]])['vacunas'] ?: [];
        $fecha_inicio = filter_input(INPUT_POST, 'fecha_inicio', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $fecha_fin = filter_input(INPUT_POST, 'fecha_fin', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $campana_lugar = filter_input(INPUT_POST, 'campana_lugar', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (empty($vacunas)) {
            throw new Exception("Debe seleccionar al menos una vacuna.");
        }
        if (!$fecha_inicio || !$fecha_fin || strtotime($fecha_inicio) > strtotime($fecha_fin)) {
            throw new Exception("Rango de fechas inválido.");
        }

        $sql = "SELECT v.nombre_vacuna, sm.campana_lugar, DATE_FORMAT(v.fecha_aplicacion, '%Y-%m') as mes_anio, 
                       COUNT(v.id_vacuna) as total_aplicaciones, m.especie 
                FROM vacunas v 
                JOIN mascotas m ON v.id_mascota = m.id_mascota 
                LEFT JOIN salud_mascotas sm ON v.id_mascota = sm.id_mascota 
                WHERE v.nombre_vacuna IN (" . implode(',', array_fill(0, count($vacunas), '?')) . ") 
                AND v.fecha_aplicacion BETWEEN ? AND ?";
        $params = $vacunas;
        $types = str_repeat('s', count($vacunas));
        $params[] = $fecha_inicio;
        $params[] = $fecha_fin;
        $types .= "ss";

        if (!empty($campana_lugar)) {
            $sql .= " AND sm.campana_lugar = ?";
            $params[] = $campana_lugar;
            $types .= "s";
        }

        $sql .= " GROUP BY v.nombre_vacuna, sm.campana_lugar, DATE_FORMAT(v.fecha_aplicacion, '%Y-%m'), m.especie";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $data = [];
        foreach ($result as $row) {
            $data[] = [
                $row['nombre_vacuna'],
                $row['campana_lugar'] ?: 'N/A',
                $row['mes_anio'],
                $row['total_aplicaciones'],
                ucfirst($row['especie'])
            ];
        }

        generarExcel($data, 'reporte_vacunas_' . date('Ymd_His'), 'Vacunas Aplicadas', ['Tipo de Vacuna', 'Campaña/Lugar', 'Mes/Año', 'Total Aplicaciones', 'Especie']);
    } elseif ($tipo_reporte === 'tutores_edad') {
        $colonias = filter_input_array(INPUT_POST, ['colonias_tutores' => ['filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS, 'flags' => FILTER_REQUIRE_ARRAY]])['colonias_tutores'] ?: [];
        $otra_colonia = sanitizar_input($_POST['otra_colonia_tut'] ?? '');
        $rango_edad = filter_input(INPUT_POST, 'rango_edad', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $edad_min = filter_input(INPUT_POST, 'edad_min', FILTER_SANITIZE_NUMBER_INT);
        $edad_max = filter_input(INPUT_POST, 'edad_max', FILTER_SANITIZE_NUMBER_INT);

        if (empty($colonias) && empty($otra_colonia)) {
            throw new Exception("Debe seleccionar al menos una colonia o especificar otra localidad.");
        }
        if ($rango_edad === 'personalizado' && ($edad_min === '' || $edad_max === '' || $edad_min > $edad_max)) {
            throw new Exception("Rango de edad personalizado inválido.");
        }

        $sql = "SELECT t.colonia, 
                       CASE 
                           WHEN TIMESTAMPDIFF(YEAR, t.fecha_nacimiento, CURDATE()) BETWEEN ? AND ? THEN ?
                           ELSE ? 
                       END as rango_edad,
                       COUNT(DISTINCT t.id_tutor) as total_tutores,
                       COUNT(m.id_mascota) as total_mascotas
                FROM tutores t 
                LEFT JOIN mascotas m ON t.id_tutor = m.id_tutor 
                WHERE ";
        $params = [];
        $types = "";
        $conditions = [];

        if ($rango_edad !== 'personalizado') {
            if ($rango_edad === '>60') {
                $min = 60;
                $max = 120; // Límite razonable
                $rango_label = '>60';
            } else {
                [$min, $max] = explode('-', $rango_edad);
                $rango_label = $rango_edad;
            }
            $params[] = (int)$min;
            $params[] = (int)$max;
            $params[] = $rango_label;
            $params[] = $rango_label;
            $types .= "iiss";
        } else {
            $params[] = (int)$edad_min;
            $params[] = (int)$edad_max;
            $params[] = "$edad_min-$edad_max";
            $params[] = "$edad_min-$edad_max";
            $types .= "iiss";
        }

        if (!empty($colonias)) {
            $placeholders = implode(',', array_fill(0, count($colonias), '?'));
            $conditions[] = "t.colonia IN ($placeholders)";
            $params = array_merge($params, $colonias);
            $types .= str_repeat('s', count($colonias));
        }
        if (!empty($otra_colonia)) {
            $conditions[] = "t.colonia LIKE ?";
            $params[] = "%$otra_colonia%";
            $types .= "s";
        }

        $sql .= implode(' OR ', $conditions);
        $sql .= " GROUP BY t.colonia, rango_edad";

        $stmt = $conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $data = [];
        $total_tutores = 0;
        $total_mascotas = 0;
        foreach ($result as $row) {
            $data[] = [
                $row['colonia'],
                $row['rango_edad'],
                $row['total_tutores'],
                $row['total_mascotas']
            ];
            $total_tutores += $row['total_tutores'];
            $total_mascotas += $row['total_mascotas'];
        }
        if (count($result) > 1) {
            $data[] = ['Total', '', $total_tutores, $total_mascotas];
        }

        generarExcel($data, 'reporte_tutores_edad_' . date('Ymd_His'), 'Distribución de Tutores por Edad', ['Colonia', 'Rango de Edad', 'Total Tutores', 'Total Mascotas']);
    }

    registrar_acceso($_SESSION['panel_admin_id'], true, "Generó reporte: $tipo_reporte");
} catch (Exception $e) {
    registrar_error("Error al generar reporte: " . $e->getMessage());
    redirigir_con_mensaje('/admin/reportes.php', 'danger', 'Error al generar el reporte: ' . $e->getMessage());
}
?>