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

// Verifica si la hora está permitida (de 10:00 a 21:30 cada 30 min)
function esHoraValida($hora) {
    $permitidas = [];
    for ($h = 10; $h <= 21; $h++) {
        $permitidas[] = sprintf('%02d:00', $h);
        if ($h < 21) $permitidas[] = sprintf('%02d:30', $h);
    }
    return in_array($hora, $permitidas);
}

// Verifica disponibilidad en la base de datos
function hayDisponibilidad($conexion, $fecha, $hora) {
    $stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM reservas WHERE fecha = ? AND hora = ?");
    $stmt->bind_param("ss", $fecha, $hora);
    $stmt->execute();
    $stmt->bind_result($totalReservas);
    $stmt->fetch();
    $stmt->close();
    return ($totalReservas < 10);
}

// Verificar disponibilidad
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verificar_disponibilidad'])) {
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    if (!esHoraValida($hora)) {
        $verificacion = "⛔ La hora debe estar entre 10:00 y 21:30 en intervalos de 30 minutos.";
    } else {
        if (hayDisponibilidad($conexion, $fecha, $hora)) {
            $verificacion = "✅ Hay disponibilidad para la fecha y hora seleccionadas.";
        } else {
            $verificacion = "⚠️ No hay disponibilidad para esa fecha y hora.";
        }
    }
}

// Nueva reserva
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nueva_reserva'])) {
    $personas = intval($_POST['personas']);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];

    if (!esHoraValida($hora)) {
        $mensaje = "⛔ Solo se permiten reservas entre 10:00 y 21:30 en intervalos de 30 minutos.";
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

// Obtener reservas
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
      min-height: 100vh;
      margin: 0;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      background: url('https://i.pinimg.com/736x/ef/39/77/ef39774232b1044a185b7b1d76ac8f08.jpg') center/cover no-repeat fixed;
      backdrop-filter: blur(3px);
    }
    h2 {
      color: var(--accent);
      margin-bottom: 0.5rem;
      text-shadow: 2px 2px 6px rgba(0,0,0,0.7);
      text-align: center;
    }
    form, table {
      background: var(--overlay);
      padding: 1.5rem 2rem;
      border-radius: 10px;
      max-width: 900px;
      width: 100%;
      box-shadow: 0 0 15px rgba(230, 156, 15, 0.7);
      margin-bottom: 2rem;
    }
    label {
      font-weight: bold;
      margin-top: 1rem;
      display: block;
    }
    input[type="number"],
    input[type="date"],
    input[type="time"],
    select {
      width: 100%;
      padding: 0.5rem;
      border-radius: 6px;
      border: none;
      margin-top: 0.3rem;
      font-size: 1rem;
    }
    button {
      margin-top: 1.5rem;
      width: 100%;
      background-color: var(--accent);
      color: #111;
      font-weight: bold;
      padding: 0.7rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      box-shadow: 0 4px 6px rgba(230, 156, 15, 0.7);
      transition: background-color 0.3s ease;
      font-size: 1.1rem;
    }
    button:hover {
      background-color: #ffbb33;
      box-shadow: 0 6px 12px rgba(255, 187, 51, 0.8);
    }
    table {
      border-collapse: collapse;
      background: var(--overlay);
      border-radius: 10px;
      overflow: hidden;
    }
    th, td {
      padding: 0.9rem 1rem;
      border-bottom: 1px solid #ddd;
      text-align: center;
      color: var(--text-light);
    }
    th {
      background-color: var(--accent);
      color: #111;
      font-weight: bold;
    }
    tr:hover {
      background-color: rgba(230, 156, 15, 0.15);
    }
    a {
      color: var(--accent);
      text-decoration: none;
      font-weight: bold;
    }
    a:hover {
      text-decoration: underline;
    }
    .mensaje {
      background-color: #222;
      color: #fff;
      padding: 0.5rem 1rem;
      border-left: 5px solid var(--accent);
      border-radius: 6px;
      margin-bottom: 1rem;
      max-width: 900px;
    }
    .filtro-fecha {
      max-width: 300px;
      margin-bottom: 1rem;
    }
    .btn-regresar {
      display: inline-block;
      background-color: var(--accent);
      color: #111;
      padding: 0.7rem 1.2rem;
      border-radius: 8px;
      font-weight: bold;
      text-decoration: none;
      box-shadow: 0 4px 6px rgba(230, 156, 15, 0.7);
      transition: background-color 0.3s ease;
      font-size: 1rem;
      cursor: pointer;
      margin-bottom: 1rem;
      text-align: center;
    }
    .btn-regresar:hover {
      background-color: #ffbb33;
      box-shadow: 0 6px 12px rgba(255, 187, 51, 0.8);
    }
  </style>
</head>
<body>

  <h2>Bienvenido al Panel de Reservas</h2>

  <?php if (!empty($mensaje)): ?>
    <div class="mensaje"><?= $mensaje ?></div>
  <?php endif; ?>
  <?php if (!empty($verificacion)): ?>
    <div class="mensaje" style="border-color: #0f0;"><?= $verificacion ?></div>
  <?php endif; ?>

  <h3 style="color: var(--accent);">Reservar una Mesa</h3>
  <form method="POST" id="formReserva">
    <input type="hidden" name="nueva_reserva" value="1" id="inputNuevaReserva">

    <label for="personas">Cantidad de Personas:</label>
    <input type="number" id="personas" name="personas" required min="1" max="20" />

    <label for="fecha">Fecha:</label>
    <input type="date" id="fecha" name="fecha" required />

    <label for="hora">Hora:</label>
    <select name="hora" id="hora" required>
      <option value="">Seleccionar hora</option>
      <?php
        for ($h = 10; $h <= 21; $h++) {
          $hora1 = sprintf('%02d:00', $h);
          $hora2 = sprintf('%02d:30', $h);
          echo "<option value='$hora1'>$hora1</option>";
          if ($h < 21) echo "<option value='$hora2'>$hora2</option>";
        }
      ?>
    </select>

    <button type="submit" onclick="return confirmarReserva()">Reservar</button>
    <button type="button" onclick="verificarDisponibilidad()">Verificar Disponibilidad</button>
  </form>

  <div>
    <a href="entradas.html" class="btn-regresar">← Regresar al Menú</a>
  </div>

  <h3 style="color: var(--accent);">Mis Reservas</h3>
  <?php if (count($reservas) == 0): ?>
    <p>No tienes reservas registradas.</p>
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
    let inputVerificar = document.createElement('input');
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