<?php

namespace App\Modelo;

use App\Config\Configuracion;

/**
 * Habla con el microservicio de SPRINTS.
 * Las historias guardan solo el sprint_id; para saber si ese sprint existe
 * hay que preguntárselo al servicio dueño de esa información.
 */
class ClienteSprints
{
    private ClienteHttp $http;

    public function __construct(ClienteHttp $http)
    {
        $this->http = $http;
    }

    public function existe(int $idSprint): bool
    {
        $respuesta = $this->http->obtener(Configuracion::urlServicio('sprints') . '/' . $idSprint);

        if ($respuesta['codigo'] === 200) {
            return true;
        }
        if ($respuesta['codigo'] === 404) {
            return false;
        }
        throw new ServicioNoDisponibleException('El servicio de sprints respondió con un error.');
    }
}
