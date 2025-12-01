<?php
session_start();
require_once __DIR__ . "/db.php";

// Verificar sesión activa
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$db = App\Database::getInstance();
$conn = $db->getConnection();

$orderNumber = null;
$flashMessage = "";

// Procesar acción enviada desde preview.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($orderId > 0) {
        if ($action === 'confirm') {
          // Confirmar pedido
            $stmt = $conn->prepare("UPDATE orders SET status = 'COMPLETED' WHERE id = ?");
            $stmt->bind_param("i", $orderId);
            if ($stmt->execute()) {
                $orderNumber = $orderId;
                $flashMessage = "Pedido #$orderId confirmado correctamente.";
            } else {
                $flashMessage = "Error al confirmar el pedido.";
            }
        } elseif ($action === 'cancel') {
          // Cancelar pedido
            $stmt = $conn->prepare("UPDATE orders SET status = 'CANCELLED' WHERE id = ?");
            $stmt->bind_param("i", $orderId);
            if ($stmt->execute()) {
                $flashMessage = "Pedido #$orderId cancelado correctamente.";
            } else {
                $flashMessage = "Error al cancelar el pedido.";
            }
        } else {
            $flashMessage = "Acción no válida.";
        }
    } else {
        $flashMessage = "Pedido no válido.";
    }
} else {
    $flashMessage = "Acceso inválido.";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Confirmación</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>

<body>

  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite">
    <?php if (!empty($flashMessage)) {
        echo "<p style='color:green;'>$flashMessage</p>";
    } ?>
  </div>

  <header>
    <h1>Resultado de la operación</h1>
    <nav>
      <a href="index.php">Volver a Home</a>
      <a href="buy.php">Nueva compra</a>
    </nav>
  </header>

  <main>
    <?php if ($orderNumber) : ?>
      <p>Tu número de pedido es: <strong id="order-number"><?= htmlspecialchars($orderNumber) ?></strong></p>
    <?php else : ?>
      <p>No hay número de pedido disponible.</p>
    <?php endif; ?>
  </main>

</body>

</html>
