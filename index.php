<?php
session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    // Usamos consulta preparada para evitar inyección SQL
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if (password_verify($clave, $usuario['clave'])) {
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['nombre'] = $usuario['nombre'];

            if ($usuario['rol'] == 'colaborador') {
                header("Location: dashboard_colaborador.php");
            } else {
                header("Location: dashboard_usuario.php");
            }
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Correo o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login | Vititos</title>
  <link rel="stylesheet" href="estilos.css" />
  <style>
    :root {
      --bg-dark: #111;
      --overlay: rgba(0,0,0,0.6);
      --accent: #e69c0f;
      --text-light: #fafafa;
      --footer-bg: #000;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: var(--bg-dark);
      color: var(--text-light);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background: url('https://i.pinimg.com/736x/ef/39/77/ef39774232b1044a185b7b1d76ac8f08.jpg') center/cover no-repeat fixed;
      backdrop-filter: blur(3px);
    }

    header {
      text-align: center;
      padding: 1rem;
    }

    header img {
      height: 120px;
    }

    nav {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 1rem;
      margin: 1rem 0;
    }

    nav a {
      background: var(--accent);
      color: #111;
      padding: 0.6rem 1rem;
      border-radius: 5px;
      font-weight: bold;
      text-decoration: none;
      transition: background 0.3s;
    }

    nav a:hover {
      background: #ffbb33;
    }

    h1 {
      text-align: center;
      color: var(--accent);
      margin-top: 1.5rem;
      text-shadow: 2px 2px 6px rgba(0,0,0,0.7);
    }

    .login-box {
      background: var(--overlay);
      max-width: 400px;
      margin: 2rem auto 1rem;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
    }

    label {
      display: block;
      margin-top: 1rem;
    }

    input {
      width: 100%;
      padding: 0.5rem;
      margin-top: 0.3rem;
      border: none;
      border-radius: 5px;
    }

    button {
      width: 100%;
      padding: 0.7rem;
      margin-top: 1.5rem;
      background-color: var(--accent);
      color: #111;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s;
    }

    button:hover {
      background-color: #ffbb33;
    }

    .login-links {
      text-align: center;
      margin-top: 1rem;
    }

    .login-links a {
      color: #f4c542;
      text-decoration: none;
      display: block;
      margin-top: 0.5rem;
    }

    .note {
      margin-top: 1.5rem;
      text-align: center;
      font-size: 0.95rem;
      color: #ccc;
    }

    .error {
      color: red;
      text-align: center;
      font-weight: bold;
      margin-bottom: 1rem;
    }

    footer {
      background: var(--footer-bg);
      color: #888;
      text-align: center;
      padding: 1rem;
      font-size: 0.85rem;
      margin-top: auto;
    }
  </style>
</head>
<body>

  <header>
    <a href="main.html">
      <img src="vititos.png" alt="Logo Vititos" />
    </a>
  </header>

  <nav>
    <a href="entradas.html">Entradas</a>
    <a href="cervezas.html">Cervezas</a>
    <a href="cocteles.html">Cócteles</a>
    <a href="shots.html">Shots</a>
    <a href="bebidas.html">Bebidas</a>
    <a href="index.html">Reservas</a>
  </nav>

  <h1>Acceso para Reservas</h1>

  <div class="login-box">
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
      <label for="correo">Correo electrónico</label>
      <input type="email" id="correo" name="correo" required>

      <label for="clave">Contraseña</label>
      <input type="password" id="clave" name="clave" required>

      <button type="submit">Ingresar</button>

      <div class="login-links">
        <a href="registro.php">¿No tienes cuenta? Regístrate aquí</a>
      </div>
    </form>

    <div class="note">
      <p>🔒 Inicie sesión para realizar reservaciones y pedidos con antelación.</p>
    </div>
  </div>

  <footer>
    2025 Emilio Polastre. Ingeniería en Informática. Contacto: epolastre1@gmail.com. Todos los derechos reservados.
  </footer>

</body>
</html>
