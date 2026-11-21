<?php

namespace App\Modelo;

use DateTime;
use InvalidArgumentException;

/**
 * MODELO: entidad Sprint.
 * Todos los atributos son privados (encapsulamiento). La única forma de
 * cambiarlos es con los setters, y los setters VALIDAN: un Sprint inválido
 * nunca puede existir.
 */
class Sprint
{
    private ?int $id;
    private string $nombre = '';
    private string $fechaInicio = '';
    private string $fechaFin = '';

    public function __construct(?int $id, string $nombre, string $fechaInicio, string $fechaFin)
    {
        $this->id = $id;
        $this->setNombre($nombre);
        $this->setFechas($fechaInicio, $fechaFin);
    }

    /** Crea un sprint nuevo a partir de los datos que llegan del cliente (JSON). */
    public static function desdeDatos(array $datos): Sprint
    {
        return new Sprint(
            null,
            self::textoDe($datos, 'nombre'),
            self::textoDe($datos, 'fecha_inicio'),
            self::textoDe($datos, 'fecha_fin')
        );
    }

    /** Crea un sprint a partir de una fila de la base de datos. */
    public static function desdeFila(array $fila): Sprint
    {
        return new Sprint((int) $fila['id'], $fila['nombre'], $fila['fecha_inicio'], $fila['fecha_fin']);
    }

    private static function textoDe(array $datos, string $clave): string
    {
        if (isset($datos[$clave]) && is_string($datos[$clave])) {
            return trim($datos[$clave]);
        }
        return '';
    }

    // ---------- getters ----------
    public function getId(): ?int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getFechaInicio(): string { return $this->fechaInicio; }
    public function getFechaFin(): string { return $this->fechaFin; }

    // ---------- setters con validación (reglas de negocio) ----------
    public function setNombre(string $nombre): void
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre del sprint es obligatorio.');
        }
        if (mb_strlen($nombre) > 100) {
            throw new InvalidArgumentException('El nombre del sprint no puede pasar de 100 caracteres.');
        }
        $this->nombre = $nombre;
    }

    public function setFechas(string $inicio, string $fin): void
    {
        if (!self::fechaValida($inicio)) {
            throw new InvalidArgumentException('La fecha de inicio no es válida (formato AAAA-MM-DD).');
        }
        if (!self::fechaValida($fin)) {
            throw new InvalidArgumentException('La fecha de fin no es válida (formato AAAA-MM-DD).');
        }
        // Con el formato AAAA-MM-DD, comparar como texto equivale a comparar fechas.
        if ($fin < $inicio) {
            throw new InvalidArgumentException('La fecha de fin no puede ser anterior a la de inicio.');
        }
        $this->fechaInicio = $inicio;
        $this->fechaFin = $fin;
    }

    private static function fechaValida(string $fecha): bool
    {
        $objeto = DateTime::createFromFormat('Y-m-d', $fecha);
        return $objeto !== false && $objeto->format('Y-m-d') === $fecha;
    }

    /** Forma controlada de "mostrar" el objeto hacia afuera (lo que viaja en el JSON). */
    public function aArreglo(): array
    {
        return [
            'id'           => $this->id,
            'nombre'       => $this->nombre,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin'    => $this->fechaFin,
        ];
    }
}
