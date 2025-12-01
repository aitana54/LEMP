<?php
session_start();

require_once __DIR__ . '/db.php';

// Si el usuario no está logueado → redirige al login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['username'];

// Conexión a la base de datos
$db = App\Database::getInstance();
$conn = $db->getConnection();

// Buscar el último pedido PENDING del usuario
$sql = "SELECT * FROM orders WHERE buyer_email = ? AND status = 'PENDING' ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$orderResult = $stmt->get_result();
$order = $orderResult->fetch_assoc();

$items = [];
$total = 0;

if ($order) {
    $orderId = $order['id'];

  // Buscar líneas del pedido
    $sqlItems = "SELECT oi.quantity, oi.unit_price, tt.label
               FROM order_items oi
               JOIN ticket_types tt ON oi.ticket_type_id = tt.id
               WHERE oi.order_id = ?";
    $stmt2 = $conn->prepare($sqlItems);
    $stmt2->bind_param("i", $orderId);
    $stmt2->execute();
    $itemsResult = $stmt2->get_result();

    while ($row = $itemsResult->fetch_assoc()) {
        $items[] = $row;
        $total += $row['unit_price'] * $row['quantity'];
    }
}

// Mensajes flash
$flashMessage = '';
if (isset($_SESSION['flash'])) {
    $flashMessage = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Vista previa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>

<body>

  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite"></div>

  <header>
    <h1>Vista previa del pedido</h1>
    <nav>
      <a href="index.php">Home</a>
      <a href="buy.php">Editar compra</a>
    </nav>
  </header>

  <!-- Contenido del carrito/pedido -->
  <section aria-labelledby="cart-title">
    <h2 id="cart-title">Resumen</h2>
    <div id="cart-preview">
      <?php if ($order && count($items) > 0) : ?>
            <?php foreach ($items as $item) : ?>
          <div class="cart-item">
            <span><?= htmlspecialchars($item['label']) ?> x<?= (int)$item['quantity'] ?> </span>
            <span><?= number_format($item['unit_price'] * $item['quantity'], 2) ?> €</span>
          </div>
            <?php endforeach; ?>
        <div class="cart-total"><strong>Total: <?= number_format($total, 2) ?> €</strong></div>
      <?php else : ?>
        <p>No hay pedidos pendientes.</p>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($order) : ?>
    <!-- Acciones: confirmar o cancelar -->
    <form action="confirm.php" method="post" style="display:inline">
      <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
      <button id="finalize-button" type="submit" name="action" value="confirm">Confirmar compra</button>
    </form>

    <form action="confirm.php" method="post" style="display:inline">
      <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
      <button id="cancel-button" type="submit" name="action" value="cancel">Cancelar pedido</button>
    </form>
  <?php endif; ?>

</body>

</html>
