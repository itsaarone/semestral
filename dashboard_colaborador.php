<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'colaborador') {
    header("Location: index.php");
    exit();
}

$mensaje = "";

// Obtener lista de usuarios para asignar reservas
$usuarios = [];
$result = $conexion->query("SELECT id, nombre FROM usuarios ORDER BY nombre");
while ($user = $result->fetch_assoc()) {
    $usuarios[] = $user;
}

// Procesar nueva reserva (por colaborador)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nueva_reserva'])) {
    $usuario_id = intval($_POST['usuario_id']);
    $personas = intval($_POST['personas']);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];

    if ($hora < "10:00" || $hora > "22:00") {
        $mensaje = "⛔ Solo se permiten reservas entre las 10:00 y las 22:00.";
    } else {
        // Contar reservas existentes en esa fecha y hora
        $stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM reservas WHERE fecha = ? AND hora = ?");
        $stmt->bind_param("ss", $fecha, $hora);
        $stmt->execute();
        $stmt->bind_result($totalReservas);
        $stmt->fetch();
        $stmt->close();

        if ($totalReservas >= 10) {
            $mensaje = "⚠️ Ya se han reservado las 10 mesas para esa hora.";
        } else {
            // Insertar la nueva reserva
            $stmt = $conexion->prepare("INSERT INTO reservas (usuario_id, personas, fecha, hora) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $usuario_id, $personas, $fecha, $hora);
            $stmt->execute();
            $stmt->close();
            $mensaje = "✅ Reserva creada con éxito.";
        }
    }
}

// Filtrar reservas por fecha si se envía filtro
$filtro_fecha = $_GET['fecha'] ?? '';

// Obtener todas las reservas (con filtro si hay)
$sql = "SELECT r.*, u.nombre AS nombre_usuario FROM reservas r JOIN usuarios u ON r.usuario_id = u.id";
$params = [];
$types = "";

if ($filtro_fecha) {
    $sql .= " WHERE r.fecha = ?";
    $params[] = $filtro_fecha;
    $types = "s";
}
$sql .= " ORDER BY r.fecha DESC, r.hora DESC";

$stmt = $conexion->prepare($sql);

if ($filtro_fecha) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$reservas = [];
while ($row = $result->fetch_assoc()) {
    $reservas[] = $row;
}
$stmt->close();

// Contador de reservas por fecha y hora para mostrar disponibilidad
$disponibilidad = [];
$stmt2 = $conexion->prepare("SELECT fecha, hora, COUNT(*) as total FROM reservas GROUP BY fecha, hora");
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($row = $res2->fetch_assoc()) {
    $disponibilidad[$row['fecha']][$row['hora']] = $row['total'];
}
$stmt2->close();

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard Colaborador | Vititos</title>
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

  <h2>Panel de Gestión de Reservas - Colaboradores</h2>

  <?php if ($mensaje): ?>
    <div class="mensaje"><?= $mensaje ?></div>
  <?php endif; ?>

  <h3 style="color: var(--accent); margin-bottom: 0.5rem;">Crear Nueva Reserva</h3>
  <form method="POST">
    <input type="hidden" name="nueva_reserva" value="1">
    
    <label for="usuario_id">Usuario:</label>
    <select id="usuario_id" name="usuario_id" required>
      <option value="">Seleccione un usuario</option>
      <?php foreach ($usuarios as $user): ?>
        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
    
    <label for="personas">Cantidad de Personas:</label>
    <input type="number" id="personas" name="personas" min="1" max="20" required>

    <label for="fecha">Fecha:</label>
    <input type="date" id="fecha" name="fecha" required>

    <label for="hora">Hora (10:00 - 22:00):</label>
    <input type="time" id="hora" name="hora" min="10:00" max="22:00" step="3600" required>

    <button type="submit">Crear Reserva</button>
  </form>

  <hr style="width: 100%; margin: 2rem 0; border-color: var(--accent);" />

  <h3 style="color: var(--accent); margin-bottom: 0.5rem;">Reservas Existentes</h3>

  <!-- Formulario filtro -->
  <form method="GET" class="filtro-fecha">
    <input type="date" name="fecha" value="<?= htmlspecialchars($filtro_fecha) ?>">
    <button type="submit">Filtrar</button>
  </form>

  <!-- Botón Mostrar Todo separado y más abajo -->
  <a href="dashboard_colaborador.php" class="btn-regresar">Mostrar Todo</a>

  <table>
    <thead>
      <tr>
        <th>Usuario</th>
        <th>Personas</th>
        <th>Fecha</th>
        <th>Hora</th>
        <th>Disponibilidad</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($reservas) === 0): ?>
        <tr><td colspan="6" style="color: #f0a;">No hay reservas<?= $filtro_fecha ? " para la fecha " . htmlspecialchars($filtro_fecha) : "" ?>.</td></tr>
      <?php else: ?>
        <?php foreach ($reservas as $reserva): ?>
          <?php
            $totalMesas = $disponibilidad[$reserva['fecha']][$reserva['hora']] ?? 0;
            $disponible = 10 - $totalMesas;
            $disponible_text = $disponible > 0 ? "Disponible ($disponible mesas libres)" : "Completo";
            $color_disp = $disponible > 0 ? "#b0ffb0" : "#ff9090";
          ?>
          <tr>
            <td><?= htmlspecialchars($reserva['nombre_usuario']) ?></td>
            <td><?= $reserva['personas'] ?></td>
            <td><?= htmlspecialchars($reserva['fecha']) ?></td>
            <td><?= htmlspecialchars($reserva['hora']) ?></td>
            <td style="color: <?= $color_disp ?>; font-weight: bold;"><?= $disponible_text ?></td>
            <td>
              <a href="editar_reserva.php?id=<?= $reserva['id'] ?>&fecha=<?= urlencode($filtro_fecha) ?>" style="color: #e6a50f;">Editar</a> |
              <a href="eliminar_reserva.php?id=<?= $reserva['id'] ?>&fecha=<?= urlencode($filtro_fecha) ?>" style="color: #ff4444;" onclick="return confirm('¿Seguro que quieres eliminar esta reserva?');">Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

</body>
</html>
