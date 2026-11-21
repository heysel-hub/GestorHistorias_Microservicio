<?php

namespace App\Modelo;

use App\Config\Configuracion;

/** Pide al microservicio de SPRINTS la lista de sprints. */
class ClienteSprints
{
    private ClienteHttp $http;

    public function __construct(ClienteHttp $http)
    {
        $this->http = $http;
    }

    /** @return array lista de sprints (arreglos con id, nombre, fecha_inicio, fecha_fin) */
    public function listar(): array
    {
        $respuesta = $this->http->obtener(Configuracion::urlServicio('sprints'));

        if ($respuesta['codigo'] !== 200 || empty($respuesta['cuerpo']['ok']) || !isset($respuesta['cuerpo']['datos'])) {
            throw new ServicioNoDisponibleException('El servicio de sprints no respondió correctamente.');
        }
        return $respuesta['cuerpo']['datos'];
    }
}
