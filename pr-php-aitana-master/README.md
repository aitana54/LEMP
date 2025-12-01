[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/abb7pIlM)
# Taquilla Online — Practica PHP
## 1. Cómo se conecta la base de datos (Singleton)
La conexión se maneja mediante la clase Database, ubicada en src/db.php.
Usa el patrón Singleton, lo que garantiza que toda la aplicación reutiliza una única conexión mysqli a la base de datos.
```php
<?php

namespace App;

final class Database
{
    private static $instance = null;
    private \mysqli $connection;

    private $host = "db";
    private $user = "root";
    private $pass = "rootpassword";
    private $dbname = "parque";

    // Constructor privado: evita instanciar desde fuera
    private function __construct()
    {
        $this->connection = new \mysqli($this->host, $this->user, $this->pass, $this->dbname);

        if ($this->connection->connect_error) {
            die("Error de conexión: " . $this->connection->connect_error);
        }
    }
    // Evita la clonación del objeto
    private function __clone()
    {
    }

    // Método estático que devuelve la instancia única
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Devuelve la conexión mysqli
    public function getConnection()
    {
        return $this->connection;
    }
}

```
Para usarla desde cualquier archivo (por ejemplo, ```buy.php```, ```preview.php```, ```confirm.php```):
```php
require_once __DIR__ . '/db.php';
$db = App\Database::getInstance();
$conn = $db->getConnection();
```

## 2. Cómo se recupera el pedido pendiente
En ```preview.php```, se obtiene el último pedido pendiente (```status = 'PENDING'```) del usuario logueado (usando el email guardado en la sesión).
```php
$email = $_SESSION['username'];

// Buscar el último pedido PENDING del usuario
$sql = "SELECT * FROM orders WHERE buyer_email = ? AND status = 'PENDING' ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$orderResult = $stmt->get_result();
$order = $orderResult->fetch_assoc();
```
Luego se muestran los productos asociados al pedido con otra consulta:
```php
 $sqlItems = "SELECT oi.quantity, oi.unit_price, tt.label
               FROM order_items oi
               JOIN ticket_types tt ON oi.ticket_type_id = tt.id
               WHERE oi.order_id = ?";
    $stmt2 = $conn->prepare($sqlItems);
    $stmt2->bind_param("i", $orderId);
    $stmt2->execute();
```
## 3. Ejemplo de una consulta con prepared statement
Se utilizan prepared statements para todas las operaciones críticas, evitando inyecciones SQL.
Ejemplo: confirmar un pedido en ```confirm.php```.
```php
$orderId = (int)($_POST['order_id'] ?? 0);
$stmt = $conn->prepare("UPDATE orders SET status = 'COMPLETED' WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
```
## 4. Enlace al video demostración
[Video de demostración](https://drive.google.com/file/d/1KGTGVRhZxn45EtvCevC9xdvBiMuM1mUn/view?usp=sharing)
