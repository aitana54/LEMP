<?php

/**
 * Classe per validar i sanejar formularis.
 */

declare(strict_types=1);

namespace biblioteca;

class BookValidator
{
    private array $data;
    private array $errores = [];
    private array $old = [];

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->sanitize();
    }

    /**
     * Devuelve los errores
     */
    public function errores(): array
    {
        return $this->errores;
    }

    /**
     * Devuelve los valores
     */
    public function old(): array
    {
        return $this->old;
    }

    /**
     * Hace la validación y devuelve true/false
     */
    public function validar(): bool
    {
        $this->validarTitulo();
        $this->validarAutor();
        $this->validarAnioPublicacion();
        $this->validarNumeroPaginas();
        return empty($this->errores);
    }

    private function sanitize(): void
    {
        $this->old['titulo']  = isset($this->data['titulo']) ? $this->clean($this->data['titulo']) : '';
        $this->old['autor']     = isset($this->data['autor']) ? $this->clean($this->data['autor']) : '';
        $this->old['anio_publicacion']    = isset($this->data['anio_publicacion'])
        ? $this->clean($this->data['anio_publicacion'])
        : '';
        $this->old['num_paginas']    = isset($this->data['num_paginas'])
        ? $this->clean($this->data['num_paginas'])
        : '';
    }

    private function clean(string $val): string
    {
        return htmlspecialchars(stripslashes(trim($val)), ENT_QUOTES, 'UTF-8');
    }

    private function validarTitulo(): void
    {
        $titulo = $this->old['titulo'];
        if ($titulo === '') {
            $this->errores['titulo'] = 'El titulo es obligatorio ponerlo';
        }
    }

    private function validarAutor(): void
    {
        $autor = $this->old['autor'];
        if ($autor === '') {
            $this->errores['autor'] = 'El autor es obligatorio ponerlo';
        }
    }

    private function validarAnioPublicacion(): void
    {
        $anio_publicacion = $this->old['anio_publicacion'];
        if ($anio_publicacion === '') {
            $this->errores['anio_publicacion'] = 'El año de publicación es obligatorio ponerlo';
        }
    }

    private function validarNumeroPaginas(): void
    {
        $num_paginas = $this->old['num_paginas'];
        if ($num_paginas === '') {
            $this->errores['num_paginas'] = 'El numero de páginas es obligatorio ponerlo';
        }
        if (!is_numeric($num_paginas)) {
            $this->errores['num_paginas'] = 'El número de páginas debe ser un número';
        }
    }
}
