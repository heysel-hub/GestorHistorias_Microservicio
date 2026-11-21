<?php

namespace App\Modelo;

use mysqli;
use mysqli_result;

/**
 * MODELO: único lugar del servicio con SQL. Trabaja con objetos Historia.
 */
class HistoriaRepositorio
{
    private const COLUMNAS = 'id, titulo, descripcion, responsable, estado, puntos, fecha_creacion, fecha_finalizacion, sprint_id';

    private Conexion $conexion;

    public function __construct(Conexion $conexion)
    {
        $this->conexion = $conexion;
    }

    private function bd(): mysqli
    {
        return $this->conexion->obtener();
    }

    /**
     * @param int|null $sprintId si viene, solo las historias de ese sprint
     * @return Historia[]
     */
    public function listar(?int $sprintId = null): array
    {
        $sql = 'SELECT ' . self::COLUMNAS . ' FROM historias';

        if ($sprintId === null) {
            return $this->convertir($this->bd()->query($sql . ' ORDER BY id ASC'));
        }

        $sentencia = $this->bd()->prepare($sql . ' WHERE sprint_id = ? ORDER BY id ASC');
        $sentencia->bind_param('i', $sprintId);
        $sentencia->execute();
        $lista = $this->convertir($sentencia->get_result());
        $sentencia->close();
        return $lista;
    }

    public function buscarPorId(int $id): ?Historia
    {
        $sentencia = $this->bd()->prepare('SELECT ' . self::COLUMNAS . ' FROM historias WHERE id = ?');
        $sentencia->bind_param('i', $id);
        $sentencia->execute();
        $fila = $sentencia->get_result()->fetch_assoc();
        $sentencia->close();

        return $fila ? Historia::desdeFila($fila) : null;
    }

    /** Guarda la historia y devuelve el id que le asignó MySQL. */
    public function crear(Historia $historia): int
    {
        $titulo = $historia->getTitulo();
        $descripcion = $historia->getDescripcion();
        $responsable = $historia->getResponsable();
        $estado = $historia->getEstado();
        $puntos = $historia->getPuntos();
        $fechaCreacion = $historia->getFechaCreacion();
        $fechaFinalizacion = $historia->getFechaFinalizacion();
        $sprintId = $historia->getSprintId();

        $sentencia = $this->bd()->prepare(
            'INSERT INTO historias
                (titulo, descripcion, responsable, estado, puntos, fecha_creacion, fecha_finalizacion, sprint_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $sentencia->bind_param(
            'ssssissi',
            $titulo, $descripcion, $responsable, $estado, $puntos, $fechaCreacion, $fechaFinalizacion, $sprintId
        );
        $sentencia->execute();
        $nuevoId = $sentencia->insert_id;
        $sentencia->close();

        return $nuevoId;
    }

    public function actualizar(Historia $historia): void
    {
        $titulo = $historia->getTitulo();
        $descripcion = $historia->getDescripcion();
        $responsable = $historia->getResponsable();
        $estado = $historia->getEstado();
        $puntos = $historia->getPuntos();
        $sprintId = $historia->getSprintId();
        $fechaFinalizacion = $historia->getFechaFinalizacion();
        $id = $historia->getId();

        $sentencia = $this->bd()->prepare(
            'UPDATE historias
                SET titulo = ?, descripcion = ?, responsable = ?, estado = ?,
                    puntos = ?, sprint_id = ?, fecha_finalizacion = ?
              WHERE id = ?'
        );
        $sentencia->bind_param(
            'ssssiisi',
            $titulo, $descripcion, $responsable, $estado, $puntos, $sprintId, $fechaFinalizacion, $id
        );
        $sentencia->execute();
        $sentencia->close();
    }

    /** @return bool true si existía y se borró */
    public function eliminar(int $id): bool
    {
        $sentencia = $this->bd()->prepare('DELETE FROM historias WHERE id = ?');
        $sentencia->bind_param('i', $id);
        $sentencia->execute();
        $borradas = $sentencia->affected_rows;
        $sentencia->close();

        return $borradas > 0;
    }

    /** @return Historia[] */
    private function convertir(mysqli_result $resultado): array
    {
        $lista = [];
        while ($fila = $resultado->fetch_assoc()) {
            $lista[] = Historia::desdeFila($fila);
        }
        return $lista;
    }
}
