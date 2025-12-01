<?php
require_once __DIR__ . '/db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
// Conexión a la base de datos
$db = App\Database::getInstance();
$conn = $db->getConnection();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['username'];
    $quantities = $_POST['quantity'] ?? [];

  // Validación 2: Al menos una cantidad > 0
    $hasQuantity = false;
    foreach ($quantities as $qty) {
        if ((int)$qty > 0) {
            $hasQuantity = true;
            break;
        }
    }

    if (!$hasQuantity) {
        $errors[] = "Debes seleccionar al menos una entrada.";
    }

  // Validación 3: Cantidades enteras 0–100
    foreach ($quantities as $id => $qty) {
        if (!is_numeric($qty) || (int)$qty < 0 || (int)$qty > 100) {
            $errors[] = "Las cantidades deben estar entre 0 y 100.";
            break;
        }
    }

  // Validación 4: Ticket IDs válidos
    $ticketIds = array_keys($quantities);
    if (empty($errors) && !empty($ticketIds)) {
        $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));
        $stmt = $conn->prepare("SELECT id, price FROM ticket_types WHERE id IN ($placeholders)");
        $types = str_repeat('i', count($ticketIds));
        $stmt->bind_param($types, ...$ticketIds);
        $stmt->execute();
        $result = $stmt->get_result();

        $validTickets = [];
        while ($row = $result->fetch_assoc()) {
            $validTickets[$row['id']] = $row['price'];
        }

        foreach ($ticketIds as $id) {
            if (!isset($validTickets[$id])) {
                $errors[] = "Tipo de ticket inválido: $id";
            }
        }
    }

  // Si no hay errores, procesar pedido
    if (empty($errors)) {
        $conn->begin_transaction();

        try {
          // Buscar si ya hay un pedido pendiente
            $stmtFind = $conn->prepare("SELECT id FROM orders WHERE buyer_email = ? AND status = 'PENDING' LIMIT 1");
            $stmtFind->bind_param("s", $email);
            $stmtFind->execute();
            $resultFind = $stmtFind->get_result();
            $existingOrder = $resultFind->fetch_assoc();

          // Calcular total con precios reales
            $total = 0;
            foreach ($quantities as $id => $qty) {
                if ($qty > 0) {
                    $total += $validTickets[$id] * $qty;
                }
            }

            if ($existingOrder) {
              // Actualiza el pedido existente
                $orderId = $existingOrder['id'];

              // Borrar los items anteriores
                $stmtDel = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
                $stmtDel->bind_param("i", $orderId);
                $stmtDel->execute();

              // Actualizar el total
                $stmtUpd = $conn->prepare("UPDATE orders SET total = ? WHERE id = ?");
                $stmtUpd->bind_param("di", $total, $orderId);
                $stmtUpd->execute();
            } else {
              // Crear nuevo pedido si no hay ninguno pendiente
                $stmtOrder = $conn->prepare("INSERT INTO orders (buyer_email, total, status) VALUES (?, ?, 'PENDING')");
                $stmtOrder->bind_param("sd", $email, $total);
                $stmtOrder->execute();
                $orderId = $stmtOrder->insert_id;
            }

          // Insertar items actualizados
            $stmtItem = $conn->prepare("
        INSERT INTO order_items (order_id, ticket_type_id, quantity, unit_price)
        VALUES (?, ?, ?, ?)
      ");
            foreach ($quantities as $id => $qty) {
                if ($qty > 0) {
                    $price = $validTickets[$id];
                    $stmtItem->bind_param("iiid", $orderId, $id, $qty, $price);
                    $stmtItem->execute();
                }
            }

            $conn->commit();
            $_SESSION['last_order_id'] = $orderId;
            header("Location: preview.php");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Error al crear o actualizar el pedido: " . $e->getMessage();
        }
    }
}

// Obtener tipos de entradas
$result = $conn->query("SELECT id, label, price FROM ticket_types");
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Compra</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>

<body>

  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite">
    <h1>Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?></h1>
    <p>Ya puedes continuar con tu compra.</p>
  </div>

  <header>
    <h1>Compra de entradas</h1>
    <nav>
      <a href="index.php">Home</a>
      <a href="login.php">Cambiar de usuario</a>
    </nav>
  </header>

  <?php
    if (!empty($errors)) {
        echo "<div style='color:red;'><ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul></div>";
    }
    ?>

  <!-- Formulario de compra -->
  <form id="buy-form" action="buy.php" method="post" novalidate>
    <p>Selecciona cantidades (0–100). El precio se muestra junto al tipo:</p>

    <!-- BLOQUE DINÁMICO: repite esto por cada tipo de ticket leído de la BBDD -->
    <!-- Sustituye "1" por el ID real del ticket type -->
    <fieldset>
      <legend>Tipos de entrada</legend>

      <?php
        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $id = htmlspecialchars($fila['id']);
                $label = htmlspecialchars($fila['label']);
                $price = number_format($fila['price'], 2);

                echo "
          <div class='ticket-row'>
            <label for='quantity-$id'>$label — <span class='ticket-price'>$price €</span></label>
            <input
              id='quantity-$id'
              name='quantity[$id]'
              type='number'
              min='0'
              max='100'
              step='1'
              value='0'
              inputmode='numeric' />
          </div>
          ";
            }
        } else {
            echo "<p>No hay tipos de entradas disponibles.</p>";
        }
        ?>
    </fieldset>

    <button type="submit">Ir a vista previa</button>
  </form>

</body>

</html>
