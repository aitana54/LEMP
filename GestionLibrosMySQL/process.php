<?php
require_once 'classLibro.php';
require_once 'bookValidator.php';

use biblioteca\Libro;
use biblioteca\BookValidator;

$errores = [];
$libro = null;
$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $validator = new BookValidator($_POST);

    if ($validator->validar()) {
        $old = $validator->old();
        $errores = $validator->errores();
        if (empty($errores)) {
            // Crear libro
            $libro = new Libro();
            $libro->setTitulo($old['titulo']);
            $libro->setAutor($old['autor']);
            $libro->setAnioPublicacion((int)$old['anio_publicacion']);
            $libro->setNumeroPaginas((int)$old['num_paginas']);

            // Asignar variables para bind_param
            $titulo = $libro->getTitulo();
            $autor = $libro->getAutor();
            $anio_publicacion = $libro->getAnioPublicacion();
            $num_paginas = $libro->getNumeroPaginas();

            // Conexión a la base de datos
            $servername = "localhost";
            $username = "admin";
            $password = "Informatica_1";
            $dbname = "biblioteca";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $stmt = $conn->prepare("INSERT INTO libros (titulo, autor,anio_publicacion, num_paginas) VALUES (?, ?, ?, ?) ") ;
            $stmt->bind_param("ssii", $titulo, $autor, $anio_publicacion, $num_paginas);

            if ($stmt->execute()) {
                $mensaje = "Libro guardado correctamente";
            } else {
                $errores[] = "Error al guardar el libro: " . $stmt->error;
            }

            $stmt->close();
            $conn->close();
        }
    } else {
        $errores = $validator->errores();
    }
}
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
        <div style="color: green;">
            <p><?= htmlspecialchars($mensaje) ?></p>
        </div>

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
