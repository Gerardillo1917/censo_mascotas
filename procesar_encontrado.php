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
$comentarios = $_POST['comentarios_encontrado'] ?? '';

if (empty($id_mascota)) {
    $_SESSION['error'] = "ID de mascota no válido";
    header("Location: " . BASE_URL . "/perfil.php");
    exit();
}

// Obtener información de la mascota para redireccionar al tutor correcto
$stmt = $conn->prepare("SELECT id_tutor FROM mascotas WHERE id_mascota = ? AND estado = 'Extraviado'");
$stmt->bind_param("i", $id_mascota);
$stmt->execute();
$result = $stmt->get_result();
$mascota = $result->fetch_assoc();
$stmt->close();

if (!$mascota) {
    $_SESSION['error'] = "Mascota no encontrada o no está en estado 'Extraviado'";
    header("Location: " . BASE_URL . "/buscar.php");
    exit();
}

// Actualizar el estado de la mascota a "Vivo"
$stmt = $conn->prepare("UPDATE mascotas SET estado = 'Vivo', comentarios_encontrado = ?, fecha_encontrado = NOW() WHERE id_mascota = ?");
$stmt->bind_param("si", $comentarios, $id_mascota);

if ($stmt->execute()) {
    $_SESSION['exito'] = "Mascota marcada como encontrada correctamente (Estado: Vivo)";
} else {
    $_SESSION['error'] = "Error al actualizar el estado de la mascota: " . $conn->error;
}

$stmt->close();

// Redireccionar al perfil del tutor
header("Location: " . BASE_URL . "/perfil.php?id=" . $mascota['id_tutor']);
exit();
?>