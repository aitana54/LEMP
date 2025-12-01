<?php
session_start(); // Variables de sesion

// Si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

  // Validación formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, introduce un email válido.";
    } else {
      // Guardar email en la sesión
        $_SESSION['username'] = $email;

      // Redirig a buy.php
        header("Location: buy.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>

<body>

  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite">
    <?php if (isset($error)) {
        echo "<p style='color:red;'>$error</p>";
    } ?>
  </div>

  <header>
    <h1>Identificación</h1>
    <nav>
      <a href="index.php">Volver a Home</a>
    </nav>
  </header>

  <!-- Formulario de login con email -->
  <form id="login-form" action="login.php" method="post" novalidate>
    <div>
      <label for="email-input">Email:</label>
      <input id="email-input" name="email" type="email" required placeholder="nombre@dominio.com" />
    </div>
    <button type="submit">Continuar a compra</button>
  </form>

</body>

</html>
