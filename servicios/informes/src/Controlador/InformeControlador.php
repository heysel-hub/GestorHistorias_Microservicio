<?php

namespace App\Controlador;

use App\Modelo\ClienteHistorias;
use App\Modelo\ClienteSprints;
use App\Modelo\Informe;
use App\Modelo\ServicioNoDisponibleException;
use App\Vista\VistaJson;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

/**
 * CONTROLADOR del microservicio de informes.
 *
 *   GET /informes -> generar()  (general, por responsable y por sprint)
 */
class InformeControlador
{
    private VistaJson $vista;
    private ClienteHistorias $clienteHistorias;
    private ClienteSprints $clienteSprints;

    public function __construct(VistaJson $vista, ClienteHistorias $clienteHistorias, ClienteSprints $clienteSprints)
    {
        $this->vista = $vista;
        $this->clienteHistorias = $clienteHistorias;
        $this->clienteSprints = $clienteSprints;
    }

    public function generar(Request $peticion, Response $respuesta): Response
    {
        try {
            $informe = new Informe(
                $this->clienteHistorias->listar(),
                $this->clienteSprints->listar()
            );
            return $this->vista->exito($respuesta, $informe->aArreglo());
        } catch (ServicioNoDisponibleException $e) {
            return $this->vista->error($respuesta, 503, 'No se pudo generar el informe: ' . $e->getMessage());
        } catch (Throwable $e) {
            return $this->vista->error($respuesta, 500, 'Error interno del servicio de informes.');
        }
    }
}
