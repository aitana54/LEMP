<?php
require_once 'classLibro.php';
require_once 'bookValidator.php';

use biblioteca\Libro;
use biblioteca\bookValidator;

$validator = new bookValidator($_POST);
$errores = [];
$old = [];
$libro = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($validator->validar()) {
        $old = $validator->old();

        $libro = new Libro();
        $libro->setTitulo($old['titulo']);
        $libro->setAutor($old['autor']);
        $libro->setAnioPublicacion($old['anio_publicacion']);
        $libro->setNumeroPaginas($old['num_paginas']);
    } else {
        $errores = $validator->errores();
        $old = $validator->old();
    }
}

$servername = "db";
$username = "root";
$password = "rootpassword";
$dbname = "biblioteca";
var_dump($servername, $username, $password, $dbname);

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// prepare and bind
$stmt = $conn->prepare("INSERT INTO libros (titulo, autor, anio_publicacion, num_paginas) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssii", $titulo, $autor, $anio_publicacion, $num_paginas);

if ($stmt->execute()) {
    echo "Libro guardado correctamente";
} else {
    echo "Error al guardar el libro: " . $stmt->error;
}


$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado del libro</title>
</head>
<body>
    <h1>Resultado del libro</h1>

    <?php if (!empty($errores)) : ?>
        <div style="color: red;">
            <h3>Errores:</h3>
            <ul>
                <?php foreach ($errores as $error) : ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="form.html">Volver al formulario</a>
        </div>
    <?php elseif ($libro) : ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-top: 20px;">
            <h3>Resumen del libro</h3>
            <p><strong>Título:</strong> <?= htmlspecialchars($libro->getTitulo()) ?></p>
            <p><strong>Autor:</strong> <?= htmlspecialchars($libro->getAutor()) ?></p>
            <p><strong>Año de publicación:</strong> <?= htmlspecialchars($libro->getAnioPublicacion()) ?></p>
            <p><strong>Número de páginas:</strong> <?= htmlspecialchars($libro->getNumeroPaginas()) ?></p>
        </div>

        <a href="form.html">Ingresar otro libro</a>
    <?php endif; ?>
</body>
</html>
