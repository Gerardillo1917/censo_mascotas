<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';
requerir_autenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/perfil.php");
    exit();
}

// Validar datos recibidos
$id_mascota = $_POST['id_mascota'] ?? 0;
$motivo_baja = $_POST['motivo_baja'] ?? '';
$comentarios = $_POST['comentarios_baja'] ?? '';

if (empty($id_mascota)) {
    $_SESSION['error'] = "ID de mascota no válido";
    header("Location: " . BASE_URL . "/perfil.php");
    exit();
}

if (!in_array($motivo_baja, ['Muerte', 'Extraviado'])) {
    $_SESSION['error'] = "Motivo de baja no válido";
    header("Location: " . BASE_URL . "/perfil.php?id=" . $_POST['id_tutor'] ?? '');
    exit();
}

// Obtener información de la mascota para redireccionar al tutor correcto
$stmt = $conn->prepare("SELECT id_tutor FROM mascotas WHERE id_mascota = ?");
$stmt->bind_param("i", $id_mascota);
$stmt->execute();
$result = $stmt->get_result();
$mascota = $result->fetch_assoc();
$stmt->close();

if (!$mascota) {
    $_SESSION['error'] = "Mascota no encontrada";
    header("Location: " . BASE_URL . "/buscar.php");
    exit();
}

// Actualizar el estado de la mascota
$stmt = $conn->prepare("UPDATE mascotas SET estado = ?, motivo_baja = ?, comentarios_baja = ?, fecha_baja = NOW() WHERE id_mascota = ?");
$stmt->bind_param("sssi", $motivo_baja, $motivo_baja, $comentarios, $id_mascota);

if ($stmt->execute()) {
    $_SESSION['exito'] = "Mascota dada de baja correctamente (Estado: $motivo_baja)";
} else {
    $_SESSION['error'] = "Error al dar de baja la mascota: " . $conn->error;
}

$stmt->close();

// Redireccionar al perfil del tutor
header("Location: " . BASE_URL . "/perfil.php?id=" . $mascota['id_tutor']);
exit();
?>