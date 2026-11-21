<?php

namespace App\Modelo;

use App\Config\Configuracion;

/** Pide al microservicio de HISTORIAS la lista de historias. */
class ClienteHistorias
{
    private ClienteHttp $http;

    public function __construct(ClienteHttp $http)
    {
        $this->http = $http;
    }

    /** @return array lista de historias (arreglos) */
    public function listar(): array
    {
        $respuesta = $this->http->obtener(Configuracion::urlServicio('historias'));

        if ($respuesta['codigo'] !== 200 || empty($respuesta['cuerpo']['ok']) || !isset($respuesta['cuerpo']['datos'])) {
            throw new ServicioNoDisponibleException('El servicio de historias no respondió correctamente.');
        }
        return $respuesta['cuerpo']['datos'];
    }
}
