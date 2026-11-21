<?php

namespace App\Modelo;

use RuntimeException;

/** Se lanza cuando otro microservicio no responde o responde con un error. */
class ServicioNoDisponibleException extends RuntimeException
{
}
