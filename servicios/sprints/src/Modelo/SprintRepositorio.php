<?php

namespace App\Modelo;

use mysqli;

/**
 * MODELO: único lugar del servicio donde hay SQL.
 * Recibe y devuelve objetos Sprint; el resto del programa no sabe de tablas.
 */
class SprintRepositorio
{
    private Conexion $conexion;

    public function __construct(Conexion $conexion)
    {
        $this->conexion = $conexion;
    }

    private function bd(): mysqli
    {
        return $this->conexion->obtener();
    }

    /** @return Sprint[] del más reciente al más antiguo */
    public function listar(): array
    {
        $resultado = $this->bd()->query(
            'SELECT id, nombre, fecha_inicio, fecha_fin FROM sprints ORDER BY fecha_inicio DESC, id DESC'
        );
        $sprints = [];
        while ($fila = $resultado->fetch_assoc()) {
            $sprints[] = Sprint::desdeFila($fila);
        }
        return $sprints;
    }

    public function buscarPorId(int $id): ?Sprint
    {
        $sentencia = $this->bd()->prepare(
            'SELECT id, nombre, fecha_inicio, fecha_fin FROM sprints WHERE id = ?'
        );
        $sentencia->bind_param('i', $id);
        $sentencia->execute();
        $fila = $sentencia->get_result()->fetch_assoc();
        $sentencia->close();

        return $fila ? Sprint::desdeFila($fila) : null;
    }

    /** Guarda el sprint y devuelve el id que le asignó MySQL. */
    public function crear(Sprint $sprint): int
    {
        $nombre = $sprint->getNombre();
        $inicio = $sprint->getFechaInicio();
        $fin = $sprint->getFechaFin();

        $sentencia = $this->bd()->prepare(
            'INSERT INTO sprints (nombre, fecha_inicio, fecha_fin) VALUES (?, ?, ?)'
        );
        $sentencia->bind_param('sss', $nombre, $inicio, $fin);
        $sentencia->execute();
        $nuevoId = $sentencia->insert_id;
        $sentencia->close();

        return $nuevoId;
    }
}
