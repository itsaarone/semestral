<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'usuario') {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['id'];
$mensaje = "";
$verificacion = "";
$resumenReservas = [];

function hayDisponibilidad($conexion, $fecha, $hora) {
    $stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM reservas WHERE fecha = ? AND hora = ?");
    $stmt->bind_param("ss", $fecha, $hora);
    $stmt->execute();
    $stmt->bind_result($totalReservas);
    $stmt->fetch();
    $stmt->close();
    return ($totalReservas < 10);
}

// Verificación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verificar_disponibilidad'])) {
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];

    if ($hora < "10:00" || $hora > "21:30") {
        $verificacion = "⛔ La hora debe estar entre 10:00 y 21:30.";
    } else {
        if (hayDisponibilidad($conexion, $fecha, $hora)) {
            $verificacion = "✅ Hay disponibilidad para la fecha y hora seleccionadas.";
        } else {
            $verificacion = "⚠️ No hay disponibilidad para esa fecha y hora.";
        }

        // Generar resumen de reservas por hora
        $stmt = $conexion->prepare("SELECT hora, COUNT(*) AS total FROM reservas WHERE fecha = ? GROUP BY hora ORDER BY hora");
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $resumenReservas[] = $row;
        }
        $stmt->close();
    }
}

// Crear reserva
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nueva_reserva'])) {
    $personas = intval($_POST['personas']);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];

    if ($hora < "10:00" || $hora > "21:30") {
        $mensaje = "⛔ Solo se permiten reservas entre las 10:00 y las 21:30.";
    } else {
        if (!hayDisponibilidad($conexion, $fecha, $hora)) {
            $mensaje = "⚠️ No hay mesas disponibles para esa fecha y hora.";
        } else {
            $stmt = $conexion->prepare("INSERT INTO reservas (usuario_id, personas, fecha, hora) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $usuario_id, $personas, $fecha, $hora);
            $stmt->execute();
            $stmt->close();
            $mensaje = "✅ ¡Reserva realizada con éxito!";
        }
    }
}

// Obtener reservas del usuario
$reservas = [];
$sql = "SELECT * FROM reservas WHERE usuario_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
while ($fila = $result->fetch_assoc()) {
    $reservas[] = $fila;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard Usuario | Vititos</title>
  <link rel="stylesheet" href="estilos.css" />
  <style>
    :root {
      --accent: #e69c0f;
      --bg-dark: #111;
      --text-light: #fafafa;
      --overlay: rgba(0,0,0,0.6);
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--bg-dark);
      color: var(--text-light);
      padding: 1rem;
      background: url('https://i.pinimg.com/736x/ef/39/77/ef39774232b1044a185b7b1d76ac8f08.jpg') center/cover no-repeat fixed;
      backdrop-filter: blur(3px);
    }

    h2, h3 {
      text-align: center;
      color: var(--accent);
      margin: 1rem 0 0.5rem;
    }

    form {
      background: var(--overlay);
      padding: 1.5rem;
      border-radius: 10px;
      max-width: 420px;
      margin: auto;
      margin-bottom: 2rem;
      box-shadow: 0 0 15px rgba(230, 156, 15, 0.7);
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
      padding: 0.5rem;
      border-radius: 6px;
      border: none;
      font-size: 1rem;
      margin-top: 0.3rem;
    }

    button {
      margin-top: 1rem;
      width: 100%;
      background-color: var(--accent);
      color: #111;
      font-weight: bold;
      padding: 0.7rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1.1rem;
      transition: 0.3s;
    }

    button:hover {
      background-color: #ffbb33;
    }

    table {
      width: 90%;
      margin: auto;
      border-collapse: collapse;
      background: var(--overlay);
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(230, 156, 15, 0.7);
      overflow: hidden;
    }

    th, td {
      padding: 0.9rem;
      text-align: center;
      border-bottom: 1px solid #444;
    }

    th {
      background-color: var(--accent);
      color: #111;
    }

    .mensaje {
      background-color: #222;
      color: #fff;
      padding: 0.5rem 1rem;
      border-left: 5px solid var(--accent);
      border-radius: 6px;
      max-width: 600px;
      margin: 1rem auto;
    }

    a.btn-regresar {
      display: block;
      width: max-content;
      margin: auto;
      margin-bottom: 1rem;
      padding: 0.7rem 1.2rem;
      background: var(--accent);
      color: #111;
      border-radius: 8px;
      font-weight: bold;
      text-align: center;
      text-decoration: none;
    }

    a.btn-regresar:hover {
      background-color: #ffbb33;
    }
  </style>
</head>
<body>

  <h2>Bienvenido al Panel de Reservas</h2>

  <?php if (!empty($mensaje)): ?>
    <div class="mensaje"><?= $mensaje ?></div>
  <?php endif; ?>

  <?php if (!empty($verificacion)): ?>
    <div class="mensaje"><?= $verificacion ?></div>
  <?php endif; ?>

  <?php if (count($resumenReservas) > 0): ?>
    <div class="mensaje" style="border-color: #e69c0f;">
      <strong>📊 Reservas por Hora para <?= htmlspecialchars($fecha) ?>:</strong><br />
      <?php foreach ($resumenReservas as $r): ?>
        <?= htmlspecialchars($r['hora']) ?> → <?= $r['total'] ?> reservas<br />
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h3>Reservar una Mesa</h3>
  <form method="POST" id="formReserva">
    <input type="hidden" name="nueva_reserva" value="1" id="inputNuevaReserva">

    <label for="personas">Cantidad de Personas:</label>
    <input type="number" id="personas" name="personas" required min="1" max="20" />

    <label for="fecha">Fecha:</label>
    <input type="date" id="fecha" name="fecha" required />

    <label for="hora">Hora:</label>
    <select id="hora" name="hora" required>
      <?php
        for ($h = 10; $h <= 21; $h++) {
          foreach (["00", "30"] as $min) {
            if ($h == 21 && $min == "30") break;
            $hora_formato = sprintf("%02d:%s", $h, $min);
            echo "<option value='$hora_formato'>$hora_formato</option>";
          }
        }
      ?>
    </select>

    <button type="submit" onclick="return confirmarReserva()">Reservar</button>
    <button type="button" onclick="verificarDisponibilidad()">Verificar Disponibilidad</button>
  </form>

  <a href="entradas.html" class="btn-regresar">← Regresar al Menú</a>

  <h3>Mis Reservas</h3>
  <?php if (count($reservas) == 0): ?>
    <p style="text-align:center;">No tienes reservas registradas.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Personas</th>
          <th>Fecha</th>
          <th>Hora</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reservas as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['personas']) ?></td>
            <td><?= htmlspecialchars($r['fecha']) ?></td>
            <td><?= htmlspecialchars($r['hora']) ?></td>
            <td>
              <a href="editar_reserva.php?id=<?= $r['id'] ?>">Editar</a> |
              <a href="eliminar_reserva.php?id=<?= $r['id'] ?>" onclick="return confirm('¿Eliminar esta reserva?')">Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

<script>
  function verificarDisponibilidad() {
    const form = document.getElementById('formReserva');
    const inputVerificar = document.createElement('input');
    inputVerificar.type = 'hidden';
    inputVerificar.name = 'verificar_disponibilidad';
    inputVerificar.value = '1';
    form.appendChild(inputVerificar);
    document.getElementById('inputNuevaReserva').disabled = true;
    form.submit();
  }

  function confirmarReserva() {
    document.getElementById('inputNuevaReserva').disabled = false;
    return true;
  }
</script>

</body>
</html>
