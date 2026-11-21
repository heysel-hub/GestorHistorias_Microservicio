<?php

namespace App\Controlador;

use App\Modelo\ClienteSprints;
use App\Modelo\Historia;
use App\Modelo\HistoriaRepositorio;
use App\Modelo\ServicioNoDisponibleException;
use App\Vista\VistaJson;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

/**
 * CONTROLADOR del microservicio de historias.
 *
 *   GET    /historias        -> listar()      (opcional ?sprint_id=2)
 *   GET    /historias/5      -> obtener()
 *   POST   /historias        -> crear()       (JSON)
 *   PUT    /historias/5      -> modificar()   (JSON)
 *   DELETE /historias/5      -> eliminar()
 */
class HistoriaControlador
{
    private VistaJson $vista;
    private HistoriaRepositorio $repositorio;
    private ClienteSprints $clienteSprints;

    public function __construct(VistaJson $vista, HistoriaRepositorio $repositorio, ClienteSprints $clienteSprints)
    {
        $this->vista = $vista;
        $this->repositorio = $repositorio;
        $this->clienteSprints = $clienteSprints;
    }

    public function listar(Request $peticion, Response $respuesta): Response
    {
        return $this->atender($respuesta, function () use ($peticion, $respuesta) {
            $lista = [];
            foreach ($this->repositorio->listar($this->sprintIdDeLaUrl($peticion)) as $historia) {
                $lista[] = $historia->aArreglo();
            }
            return $this->vista->exito($respuesta, $lista);
        });
    }

    public function obtener(Request $peticion, Response $respuesta, array $args): Response
    {
        return $this->atender($respuesta, function () use ($respuesta, $args) {
            $historia = $this->repositorio->buscarPorId((int) $args['id']);
            if ($historia === null) {
                return $this->vista->error($respuesta, 404, 'No existe una historia con ese id.');
            }
            return $this->vista->exito($respuesta, $historia->aArreglo());
        });
    }

    public function crear(Request $peticion, Response $respuesta): Response
    {
        return $this->atender($respuesta, function () use ($peticion, $respuesta) {
            $historia = Historia::nueva($this->datosDe($peticion));   // valida los datos
            $this->verificarSprint($historia->getSprintId());

            $nuevoId = $this->repositorio->crear($historia);
            return $this->vista->exito($respuesta, $this->repositorio->buscarPorId($nuevoId)->aArreglo(), 201);
        });
    }

    public function modificar(Request $peticion, Response $respuesta, array $args): Response
    {
        return $this->atender($respuesta, function () use ($peticion, $respuesta, $args) {
            $id = (int) $args['id'];

            $historia = $this->repositorio->buscarPorId($id);
            if ($historia === null) {
                return $this->vista->error($respuesta, 404, 'No existe una historia con ese id.');
            }

            $historia->cambiarDatos($this->datosDe($peticion));       // valida los datos
            $this->verificarSprint($historia->getSprintId());

            $this->repositorio->actualizar($historia);
            return $this->vista->exito($respuesta, $this->repositorio->buscarPorId($id)->aArreglo());
        });
    }

    public function eliminar(Request $peticion, Response $respuesta, array $args): Response
    {
        return $this->atender($respuesta, function () use ($respuesta, $args) {
            $id = (int) $args['id'];

            if (!$this->repositorio->eliminar($id)) {
                return $this->vista->error($respuesta, 404, 'No existe una historia con ese id.');
            }
            return $this->vista->exito($respuesta, ['id' => $id]);
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

    private function sprintIdDeLaUrl(Request $peticion): ?int
    {
        $parametros = $peticion->getQueryParams();
        if (!isset($parametros['sprint_id'])) {
            return null;
        }
        $valor = filter_var($parametros['sprint_id'], FILTER_VALIDATE_INT);
        if ($valor === false || $valor < 1) {
            throw new InvalidArgumentException('El parámetro sprint_id no es válido.');
        }
        return $valor;
    }

    /** Pregunta al microservicio de sprints si el sprint existe. */
    private function verificarSprint(int $idSprint): void
    {
        if (!$this->clienteSprints->existe($idSprint)) {
            throw new InvalidArgumentException('El sprint indicado no existe.');
        }
    }

    /** Ejecuta la acción y convierte cualquier error en una respuesta JSON adecuada. */
    private function atender(Response $respuesta, callable $accion): Response
    {
        try {
            return $accion();
        } catch (InvalidArgumentException $e) {
            return $this->vista->error($respuesta, 400, $e->getMessage());
        } catch (ServicioNoDisponibleException $e) {
            return $this->vista->error(
                $respuesta,
                503,
                'No se pudo verificar el sprint: el servicio de sprints no está disponible.'
            );
        } catch (Throwable $e) {
            return $this->vista->error($respuesta, 500, 'Error interno del servicio de historias.');
        }
    }
}
