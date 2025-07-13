<?php
include 'conexion.php';

$nombre = trim($_POST['nombre']);
$correo = trim($_POST['correo']);
$clave = trim($_POST['clave']);

if (!$nombre || !$correo || !$clave) {
    header("Location: registro.php?error=registro_fallido");
    exit();
}

$rol = (str_ends_with(strtolower($nombre), 'vitito')) ? 'colaborador' : 'usuario';

$verificar_correo = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
$verificar_correo->bind_param("s", $correo);
$verificar_correo->execute();
$resultado = $verificar_correo->get_result();

if ($resultado->num_rows > 0) {
    header("Location: registro.php?error=correo_repetido");
    exit();
}

$clave_hash = password_hash($clave, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, clave, rol) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nombre, $correo, $clave_hash, $rol);

if ($stmt->execute()) {
    header("Location: registro.php?exito=1");
} else {
    header("Location: registro.php?error=registro_fallido");
}

$stmt->close();
$conn->close();
?>
