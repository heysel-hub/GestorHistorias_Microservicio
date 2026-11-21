<?php

namespace App\Vista;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * VISTA del microservicio. Un servicio no devuelve páginas HTML:
 * su "vista" es la respuesta en formato JSON.
 */
class VistaJson
{
    public function exito(Response $respuesta, $datos, int $codigo = 200): Response
    {
        return $this->enviar($respuesta, $codigo, ['ok' => true, 'datos' => $datos]);
    }

    public function error(Response $respuesta, int $codigo, string $mensaje): Response
    {
        return $this->enviar($respuesta, $codigo, ['ok' => false, 'mensaje' => $mensaje]);
    }

    private function enviar(Response $respuesta, int $codigo, array $contenido): Response
    {
        $respuesta->getBody()->write(json_encode($contenido, JSON_UNESCAPED_UNICODE));
        return $respuesta
            ->withStatus($codigo)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}
