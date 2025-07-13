<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$filtro_fecha = $_GET['fecha'] ?? '';

$usuario_id = $_SESSION['id'];
$rol = $_SESSION['rol'];

// Solo puede eliminar si es colaborador o dueño de la reserva
if ($rol === 'colaborador') {
    // Colaborador puede borrar cualquier reserva
    $stmt = $conexion->prepare("DELETE FROM reservas WHERE id = ?");
    $stmt->bind_param("i", $id);
} else {
    // Usuario solo puede borrar sus reservas
    $stmt = $conexion->prepare("DELETE FROM reservas WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id, $usuario_id);
}

$stmt->execute();
$stmt->close();

// Redirigir según rol, manteniendo filtro fecha si existe
if ($rol === 'colaborador') {
    $location = "dashboard_colaborador.php";
} else {
    $location = "dashboard_usuario.php";
}
if ($filtro_fecha) {
    $location .= "?fecha=" . urlencode($filtro_fecha);
}

header("Location: " . $location);
exit();
?>
