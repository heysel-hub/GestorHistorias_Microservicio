<?php

namespace App\Modelo;

use InvalidArgumentException;

/**
 * MODELO: entidad Historia de usuario.
 *
 * ENCAPSULAMIENTO:
 *  - Todos los atributos son privados.
 *  - El constructor es privado: solo se crea con Historia::nueva() o Historia::desdeFila().
 *  - Cada setter valida; una Historia inválida no puede existir.
 *  - La regla "fecha de finalización" vive aquí, dentro de setEstado().
 */
class Historia
{
    public const ESTADOS = ['nueva', 'activa', 'finalizada', 'impedimento'];

    private ?int $id;
    private string $titulo = '';
    private string $descripcion = '';
    private string $responsable = '';
    private string $estado = 'nueva';
    private int $puntos = 1;
    private string $fechaCreacion;
    private ?string $fechaFinalizacion;
    private int $sprintId = 0;

    private function __construct(?int $id, string $fechaCreacion, ?string $fechaFinalizacion)
    {
        $this->id = $id;
        $this->fechaCreacion = $fechaCreacion;
        $this->fechaFinalizacion = $fechaFinalizacion;
    }

    /** Historia nueva a partir de los datos del cliente. Valida todo. */
    public static function nueva(array $datos): Historia
    {
        $historia = new Historia(null, date('Y-m-d'), null);
        $historia->cambiarDatos($datos);
        return $historia;
    }

    /** Historia leída de la base de datos (los datos ya fueron validados al guardarse). */
    public static function desdeFila(array $fila): Historia
    {
        $historia = new Historia((int) $fila['id'], $fila['fecha_creacion'], $fila['fecha_finalizacion']);
        $historia->titulo = $fila['titulo'];
        $historia->descripcion = $fila['descripcion'];
        $historia->responsable = $fila['responsable'];
        $historia->estado = $fila['estado'];
        $historia->puntos = (int) $fila['puntos'];
        $historia->sprintId = (int) $fila['sprint_id'];
        return $historia;
    }

    /** Cambia varios datos a la vez (se usa al crear y al modificar). */
    public function cambiarDatos(array $datos): void
    {
        $this->setTitulo(self::textoDe($datos, 'titulo'));
        $this->setDescripcion(self::textoDe($datos, 'descripcion'));
        $this->setResponsable(self::textoDe($datos, 'responsable'));
        $this->setPuntos($datos['puntos'] ?? null);
        $this->setSprintId($datos['sprint_id'] ?? null);
        // Si no mandan el estado, se conserva el que ya tenía ("nueva" en una historia nueva).
        $this->setEstado(isset($datos['estado']) ? self::textoDe($datos, 'estado') : $this->estado);
    }

    private static function textoDe(array $datos, string $clave): string
    {
        if (isset($datos[$clave]) && is_string($datos[$clave])) {
            return trim($datos[$clave]);
        }
        return '';
    }

    private static function enteroPositivo($valor): ?int
    {
        if (is_bool($valor) || is_array($valor)) {
            return null;
        }
        $numero = filter_var($valor, FILTER_VALIDATE_INT);
        if ($numero === false || $numero < 1) {
            return null;
        }
        return $numero;
    }

    // ---------- getters ----------
    public function getId(): ?int { return $this->id; }
    public function getTitulo(): string { return $this->titulo; }
    public function getDescripcion(): string { return $this->descripcion; }
    public function getResponsable(): string { return $this->responsable; }
    public function getEstado(): string { return $this->estado; }
    public function getPuntos(): int { return $this->puntos; }
    public function getFechaCreacion(): string { return $this->fechaCreacion; }
    public function getFechaFinalizacion(): ?string { return $this->fechaFinalizacion; }
    public function getSprintId(): int { return $this->sprintId; }

    // ---------- setters con validación ----------
    public function setTitulo(string $titulo): void
    {
        if ($titulo === '') {
            throw new InvalidArgumentException('El título es obligatorio.');
        }
        if (mb_strlen($titulo) > 150) {
            throw new InvalidArgumentException('El título no puede pasar de 150 caracteres.');
        }
        $this->titulo = $titulo;
    }

    public function setDescripcion(string $descripcion): void
    {
        if ($descripcion === '') {
            throw new InvalidArgumentException('La descripción es obligatoria.');
        }
        $this->descripcion = $descripcion;
    }

    public function setResponsable(string $responsable): void
    {
        if ($responsable === '') {
            throw new InvalidArgumentException('El responsable es obligatorio.');
        }
        if (mb_strlen($responsable) > 100) {
            throw new InvalidArgumentException('El responsable no puede pasar de 100 caracteres.');
        }
        $this->responsable = $responsable;
    }

    /** @param mixed $puntos puede llegar como número o como texto ("5") */
    public function setPuntos($puntos): void
    {
        $valor = self::enteroPositivo($puntos);
        if ($valor === null) {
            throw new InvalidArgumentException('Los puntos deben ser un número entero mayor o igual a 1.');
        }
        $this->puntos = $valor;
    }

    /** @param mixed $sprintId puede llegar como número o como texto */
    public function setSprintId($sprintId): void
    {
        $valor = self::enteroPositivo($sprintId);
        if ($valor === null) {
            throw new InvalidArgumentException('Debe indicar un sprint válido.');
        }
        $this->sprintId = $valor;
    }

    /**
     * Regla de negocio: al pasar a "finalizada" se guarda la fecha de hoy
     * (si ya tenía una, se conserva); en cualquier otro estado la fecha se borra.
     */
    public function setEstado(string $estado): void
    {
        if (!in_array($estado, self::ESTADOS, true)) {
            throw new InvalidArgumentException('El estado debe ser: nueva, activa, finalizada o impedimento.');
        }
        $this->estado = $estado;

        if ($estado === 'finalizada') {
            if ($this->fechaFinalizacion === null) {
                $this->fechaFinalizacion = date('Y-m-d');
            }
        } else {
            $this->fechaFinalizacion = null;
        }
    }

    public function aArreglo(): array
    {
        return [
            'id'                 => $this->id,
            'titulo'             => $this->titulo,
            'descripcion'        => $this->descripcion,
            'responsable'        => $this->responsable,
            'estado'             => $this->estado,
            'puntos'             => $this->puntos,
            'fecha_creacion'     => $this->fechaCreacion,
            'fecha_finalizacion' => $this->fechaFinalizacion,
            'sprint_id'          => $this->sprintId,
        ];
    }
}
