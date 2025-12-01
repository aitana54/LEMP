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
