<?php

namespace App\Modelo;

/**
 * MODELO: un "contador" de historias por estado.
 * Los contadores son privados: solo se modifican con contar().
 * "Pendientes" = nuevas + activas.
 */
class ResumenHistorias
{
    private int $total = 0;
    private int $finalizadas = 0;
    private int $pendientes = 0;
    private int $impedimentos = 0;

    public function contar(string $estado): void
    {
        $this->total++;

        if ($estado === 'finalizada') {
            $this->finalizadas++;
        } elseif ($estado === 'nueva' || $estado === 'activa') {
            $this->pendientes++;
        } elseif ($estado === 'impedimento') {
            $this->impedimentos++;
        }
    }

    public function aArreglo(): array
    {
        return [
            'total'        => $this->total,
            'finalizadas'  => $this->finalizadas,
            'pendientes'   => $this->pendientes,
            'impedimentos' => $this->impedimentos,
        ];
    }
}
