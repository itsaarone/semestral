<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro | Vititos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
      background: url('https://i.pinimg.com/736x/ef/39/77/ef39774232b1044a185b7b1d76ac8f08.jpg') center/cover no-repeat fixed;
      color: var(--text-light);
      background-color: var(--bg-dark);
      backdrop-filter: blur(3px);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
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

    h2 {
      text-align: center;
      color: var(--accent);
      margin-top: 1.5rem;
      text-shadow: 2px 2px 6px rgba(0,0,0,0.7);
    }

    .form-box {
      background: var(--overlay);
      max-width: 450px;
      margin: 2rem auto;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
    }

    input, select {
      width: 100%;
      padding: 0.5rem;
      margin-top: 0.7rem;
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

    .form-links {
      text-align: center;
      margin-top: 1rem;
    }

    .form-links a {
      color: #f4c542;
      text-decoration: none;
      display: block;
      margin-top: 0.5rem;
    }

    .mensaje {
      text-align: center;
      font-weight: bold;
      margin-top: 1rem;
    }

    .exito {
      color: #28ff28;
    }

    .error {
      color: #ff5252;
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
    <a href="index.php">Reservas</a>
  </nav>

  <h2>Registrarse</h2>

  <?php if (isset($_GET['exito']) && $_GET['exito'] == 1): ?>
    <div class="mensaje exito">¡Registro exitoso!</div>
  <?php elseif (isset($_GET['error']) && $_GET['error'] == 'correo_repetido'): ?>
    <div class="mensaje error">Error: El correo ya está registrado.</div>
  <?php elseif (isset($_GET['error']) && $_GET['error'] == 'registro_fallido'): ?>
    <div class="mensaje error">Error al registrar. Intenta de nuevo.</div>
  <?php elseif (isset($_GET['error']) && $_GET['error'] == 'nombre_invalido'): ?>
    <div class="mensaje error">Error: El nombre es inválido.</div>
  <?php endif; ?>

  <div class="form-box">
    <form action="procesar_registro.php" method="POST">
      <input type="text" name="nombre" placeholder="Nombre completo" required>
      <input type="email" name="correo" placeholder="Correo electrónico" required>
      <input type="password" name="clave" placeholder="Contraseña" required>
      <button type="submit">Registrarse</button>

      <div class="form-links">
        <a href="index.php">¿Ya tiene cuenta? Inicie sesión</a>
      </div>
    </form>
  </div>

  <footer>
    2025 Emilio Polastre. Ingeniería en Informática. Contacto: epolastre1@gmail.com. Todos los derechos reservados.
  </footer>

</body>
</html>