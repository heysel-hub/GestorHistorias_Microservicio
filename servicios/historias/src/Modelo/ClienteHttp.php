<?php

namespace App\Modelo;

/**
 * Hace peticiones GET a otros microservicios y entrega la respuesta ya
 * convertida en arreglo. Así la comunicación entre servicios queda en un solo lugar.
 */
class ClienteHttp
{
    /** @return array ['codigo' => int, 'cuerpo' => array] */
    public function obtener(string $url): array
    {
        $contexto = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'timeout'       => 5,
                'ignore_errors' => true,   // también leer el cuerpo cuando el código es 404, 500, etc.
                'header'        => "Accept: application/json\r\n",
            ],
        ]);

        $texto = @file_get_contents($url, false, $contexto);
        if ($texto === false) {
            throw new ServicioNoDisponibleException('No se pudo contactar el servicio: ' . $url);
        }

        $codigo = 0;
        if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0], $coincidencia)) {
            $codigo = (int) $coincidencia[1];
        }

        $cuerpo = json_decode($texto, true);
        if (!is_array($cuerpo)) {
            throw new ServicioNoDisponibleException('El servicio respondió algo que no es JSON.');
        }

        return ['codigo' => $codigo, 'cuerpo' => $cuerpo];
    }
}
