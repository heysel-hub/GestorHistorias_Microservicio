<?php

namespace App\Modelo;

/**
 * MODELO: arma el informe de avance a partir de las historias y los sprints.
 * Aquí está la lógica de negocio del servicio (lo que antes hacían los
 * GROUP BY de SQL en el monolito).
 */
class Informe
{
    private ResumenHistorias $general;
    private array $porResponsable = [];   // nombre del responsable => ResumenHistorias
    private array $porSprint = [];        // id del sprint => ResumenHistorias
    private array $sprints;

    public function __construct(array $historias, array $sprints)
    {
        $this->general = new ResumenHistorias();
        $this->sprints = $sprints;

        foreach ($historias as $historia) {
            $this->contar($historia);
        }
    }

    private function contar(array $historia): void
    {
        $estado = (string) $historia['estado'];
        $responsable = (string) $historia['responsable'];
        $idSprint = (int) $historia['sprint_id'];

        $this->general->contar($estado);

        if (!isset($this->porResponsable[$responsable])) {
            $this->porResponsable[$responsable] = new ResumenHistorias();
        }
        $this->porResponsable[$responsable]->contar($estado);

        if (!isset($this->porSprint[$idSprint])) {
            $this->porSprint[$idSprint] = new ResumenHistorias();
        }
        $this->porSprint[$idSprint]->contar($estado);
    }

    public function aArreglo(): array
    {
        // Por responsable, en orden alfabético sin distinguir mayúsculas.
        $nombres = array_keys($this->porResponsable);
        sort($nombres, SORT_STRING | SORT_FLAG_CASE);

        $filasResponsable = [];
        foreach ($nombres as $nombre) {
            $fila = $this->porResponsable[$nombre]->aArreglo();
            $fila['responsable'] = (string) $nombre;
            $filasResponsable[] = $fila;
        }

        // Por sprint: solo los que tienen historias, en el orden en que llegaron (más reciente primero).
        $filasSprint = [];
        foreach ($this->sprints as $sprint) {
            $idSprint = (int) $sprint['id'];
            if (isset($this->porSprint[$idSprint])) {
                $fila = $this->porSprint[$idSprint]->aArreglo();
                $fila['nombre'] = $sprint['nombre'];
                $filasSprint[] = $fila;
            }
        }

        return [
            'general'         => $this->general->aArreglo(),
            'por_responsable' => $filasResponsable,
            'por_sprint'      => $filasSprint,
        ];
    }
}
