<?php

/**
 * Clase Libro
 * @package biblioteca
 */

namespace biblioteca;

class Libro
{
    //Propiedades
    private $titulo;
    private $autor;
    private $anio_publicacion;
    private $num_paginas;

    //Metodos
    //titulo
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    }

    public function getTitulo()
    {
        return $this->titulo;
    }

    //autor
    public function setAutor($autor)
    {
        $this->autor = $autor;
    }

    public function getAutor()
    {
        return $this->autor;
    }

    //anoPublicacion
    public function setAnioPublicacion($anio_publicacion)
    {
        $this->anio_publicacion = $anio_publicacion;
    }

    public function getAnioPublicacion()
    {
        return $this->anio_publicacion;
    }
    //numero de paginas
    public function setNumeroPaginas($num_paginas)
    {
        $this->num_paginas = $num_paginas;
    }

    public function getNumeroPaginas()
    {
        return $this->num_paginas;
    }
}
