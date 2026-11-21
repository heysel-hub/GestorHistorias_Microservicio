<?php

namespace App\Controlador;

use App\Modelo\Sprint;
use App\Modelo\SprintRepositorio;
use App\Vista\VistaJson;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

/**
 * CONTROLADOR: recibe la petición (que Slim ya enrutó), le pide el trabajo al
 * modelo y le entrega el resultado a la vista. No tiene SQL ni validaciones.
 *
 *   GET  /sprints        -> listar()
 *   GET  /sprints/3      -> obtener()
 *   POST /sprints        -> crear()   (JSON en el cuerpo)
 */
class SprintControlador
{
    private VistaJson $vista;
    private SprintRepositorio $repositorio;

    public function __construct(VistaJson $vista, SprintRepositorio $repositorio)
    {
        $this->vista = $vista;
        $this->repositorio = $repositorio;
    }

    public function listar(Request $peticion, Response $respuesta): Response
    {
        return $this->atender($respuesta, function () use ($respuesta) {
            $lista = [];
            foreach ($this->repositorio->listar() as $sprint) {
                $lista[] = $sprint->aArreglo();
            }
            return $this->vista->exito($respuesta, $lista);
        });
    }

    public function obtener(Request $peticion, Response $respuesta, array $args): Response
    {
        return $this->atender($respuesta, function () use ($respuesta, $args) {
            $sprint = $this->repositorio->buscarPorId((int) $args['id']);
            if ($sprint === null) {
                return $this->vista->error($respuesta, 404, 'No existe un sprint con ese id.');
            }
            return $this->vista->exito($respuesta, $sprint->aArreglo());
        });
    }

    public function crear(Request $peticion, Response $respuesta): Response
    {
        return $this->atender($respuesta, function () use ($peticion, $respuesta) {
            $sprint = Sprint::desdeDatos($this->datosDe($peticion));   // valida
            $nuevoId = $this->repositorio->crear($sprint);
            $creado = $this->repositorio->buscarPorId($nuevoId);
            return $this->vista->exito($respuesta, $creado->aArreglo(), 201);
        });
    }

    /** Lee el JSON del cuerpo (Slim ya lo convirtió en arreglo). */
    private function datosDe(Request $peticion): array
    {
        $datos = $peticion->getParsedBody();
        if (!is_array($datos)) {
            throw new InvalidArgumentException('El cuerpo de la petición debe ser un JSON válido.');
        }
        return $datos;
    }

    /** Ejecuta la acción y convierte cualquier error en una respuesta JSON adecuada. */
    private function atender(Response $respuesta, callable $accion): Response
    {
        try {
            return $accion();
        } catch (InvalidArgumentException $e) {
            // Dato inválido: el mensaje lo escribió el modelo y es seguro mostrarlo.
            return $this->vista->error($respuesta, 400, $e->getMessage());
        } catch (Throwable $e) {
            return $this->vista->error($respuesta, 500, 'Error interno del servicio de sprints.');
        }
    }
}
