<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'usuario') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "ID de reserva no especificado.";
    exit();
}

$reserva_id = $_GET['id'];
$usuario_id = $_SESSION['id'];
$mensaje = "";

$stmt = $conexion->prepare("SELECT * FROM reservas WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $reserva_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$reserva = $result->fetch_assoc();
$stmt->close();

if (!$reserva) {
    echo "Reserva no encontrada o no tienes permiso para editarla.";
    exit();
}

function hayDisponibilidad($conexion, $fecha, $hora, $reserva_id) {
    $stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM reservas WHERE fecha = ? AND hora = ? AND id != ?");
    $stmt->bind_param("ssi", $fecha, $hora, $reserva_id);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    return ($total < 10);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $personas = intval($_POST['personas']);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];

    if ($hora < "10:00" || $hora > "21:30") {
        $mensaje = "⛔ Solo se permiten horarios entre las 10:00 y las 21:30.";
    } elseif (!hayDisponibilidad($conexion, $fecha, $hora, $reserva_id)) {
        $mensaje = "⚠️ No hay disponibilidad para esa fecha y hora.";
    } else {
        $stmt = $conexion->prepare("UPDATE reservas SET personas = ?, fecha = ?, hora = ? WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("issii", $personas, $fecha, $hora, $reserva_id, $usuario_id);
        $stmt->execute();
        $stmt->close();
        $mensaje = "✅ ¡Reserva actualizada con éxito!";
        header("refresh:2;url=dashboard_usuario.php");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Reserva | Vititos</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: url('https://i.pinimg.com/736x/ef/39/77/ef39774232b1044a185b7b1d76ac8f08.jpg') center/cover no-repeat fixed;
      backdrop-filter: blur(3px);
      color: #fff;
      padding: 2rem;
    }

    form {
      background-color: rgba(0,0,0,0.7);
      padding: 2rem;
      max-width: 400px;
      margin: auto;
      border-radius: 12px;
      box-shadow: 0 0 15px #e69c0f;
    }

    h2 {
      text-align: center;
      color: #e69c0f;
    }

    label {
      display: block;
      margin-top: 1rem;
      font-weight: bold;
    }

    input[type="number"],
    input[type="date"],
    select {
      width: 100%;
      padding: 0.6rem;
      margin-top: 0.3rem;
      border-radius: 6px;
      border: none;
      font-size: 1rem;
    }

    button {
      width: 100%;
      margin-top: 1.5rem;
      padding: 0.8rem;
      background-color: #e69c0f;
      border: none;
      border-radius: 8px;
      font-size: 1.1rem;
      font-weight: bold;
      color: #111;
      cursor: pointer;
    }

    button:hover {
      background-color: #ffbb33;
    }

    .mensaje {
      margin-top: 1rem;
      background: #111;
      padding: 0.8rem;
      border-left: 5px solid #e69c0f;
      border-radius: 8px;
    }

    a {
      display: block;
      text-align: center;
      margin-top: 2rem;
      text-decoration: none;
      color: #e69c0f;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <h2>Editar Reserva</h2>

  <?php if ($mensaje): ?>
    <div class="mensaje"><?= $mensaje ?></div>
  <?php endif; ?>

  <form method="POST">
    <label for="personas">Cantidad de Personas:</label>
    <input type="number" id="personas" name="personas" required min="1" max="20" value="<?= htmlspecialchars($reserva['personas']) ?>">

    <label for="fecha">Fecha:</label>
    <input type="date" id="fecha" name="fecha" required value="<?= htmlspecialchars($reserva['fecha']) ?>">

    <label for="hora">Hora:</label>
    <select name="hora" required>
      <?php
        for ($h = 10; $h <= 21; $h++) {
          foreach (["00", "30"] as $min) {
            if ($h == 21 && $min == "30") break;
            $hora_opcion = sprintf("%02d:%s", $h, $min);
            $selected = ($reserva['hora'] == $hora_opcion) ? "selected" : "";
            echo "<option value='$hora_opcion' $selected>$hora_opcion</option>";
          }
        }
      ?>
    </select>

    <button type="submit">Guardar Cambios</button>
  </form>

  <a href="dashboard_usuario.php">← Volver al Panel</a>

</body>
</html>
