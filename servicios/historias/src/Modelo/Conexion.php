<?php

namespace App\Modelo;

use App\Config\Configuracion;
use mysqli;

/**
 * Se encarga SOLO de abrir la conexión con MySQL.
 * La conexión es privada y se crea la primera vez que alguien la pide.
 */
class Conexion
{
    private ?mysqli $conexion = null;

    public function obtener(): mysqli
    {
        if ($this->conexion === null) {
            // Hace que los errores de MySQL lancen excepciones (se capturan en el controlador).
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $this->conexion = new mysqli(
                Configuracion::obtener('bd_servidor'),
                Configuracion::obtener('bd_usuario'),
                Configuracion::obtener('bd_clave'),
                Configuracion::obtener('bd_nombre')
            );
            $this->conexion->set_charset('utf8mb4');
        }
        return $this->conexion;
    }
}
