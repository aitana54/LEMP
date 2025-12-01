<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>

<body>
  <?php

  require_once __DIR__ . '/db.php';

  $db = App\Database::getInstance();
  $conn = $db->getConnection();
  // Filtro recibido por GET
  $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

  // Consulta según el filtro
  if ($filter === 'maintenance') {
      $sql = "SELECT name, description, maintenance FROM attractions WHERE maintenance = '1'";
  } elseif ($filter === 'available') {
      $sql = "SELECT name, description, maintenance FROM attractions WHERE maintenance = '0'";
  } else {
      $sql = "SELECT name, description, maintenance FROM attractions";
  }
  $resultado = $conn->query($sql);
    ?>
  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite"></div>

  <header>
    <h1>Parque Temático</h1>
    <!-- Enlace a login -->
    <nav>
      <a href="login.php">Iniciar compra</a>
    </nav>
  </header>

  <!-- Imagen temática (el alumno la coloca directamente en el HTML) -->
  <figure>
    <img id="theme-image" src="./public/dinosaurio.jpg" alt="Imagen temática del parque" width="200" height="200" />
    <figcaption></figcaption>
  </figure>

  <!-- Filtro tipo desplegable (select) -->
  <form method="GET" action="index.php">
    <section aria-labelledby="filtro-title">
      <h2 id="filtro-title">Filtrar atracciones</h2>
      <label for="filter-maintenance">Estado:</label>
      <select id="filter-maintenance" name="filter" onchange="this.form.submit()">
        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Todas</option>
        <option value="maintenance" <?= $filter === 'maintenance' ? 'selected' : '' ?>>En mantenimiento</option>
        <option value="available" <?= $filter === 'available' ? 'selected' : '' ?>>Disponibles</option>
      </select>
      <span>Mostrando: <strong id="attraction-count"><?= $resultado ? $resultado->num_rows : 0 ?></strong></span>
    </section>
  </form>

  <!-- Lista de atracciones -->
  <section aria-labelledby="lista-title">
    <h2 id="lista-title">Atracciones</h2>
    <div id="attraction-list">
      <!-- Rellenar con tarjetas/filas de atracciones desde la BBDD -->
      <?php

        if ($resultado && $resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $nombre = htmlspecialchars($fila['name']);
                $descripcion = htmlspecialchars($fila['description']);
                $estado = htmlspecialchars($fila['maintenance']);


              // Clase CSS según estado
                if ($estado === '1') {
                    $estado = "Mantenimiento";
                } else {
                    $estado = "Disponible";
                }

                echo "
          <article class='attraction'>
            <h3>$nombre</h3>
            <p>$descripcion</p>
            <span class='badge'>$estado</span>
          </article>
          ";
            }
        } else {
            echo "<p>No hay atracciones registradas.</p>";
        }

        $conn->close();
        ?>
    </div>
  </section>

</body>

</html>
