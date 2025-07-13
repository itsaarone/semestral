<?php
$host = "localhost";
$usuario = "root";
$contrasena = ""; // o tu contraseña
$base_datos = "restaurante"; // cambia al nombre real de tu base

$conexion = new mysqli($host, $usuario, $contrasena, $base_datos);

// Para login.php que usa $conn
$conn = $conexion;

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>
