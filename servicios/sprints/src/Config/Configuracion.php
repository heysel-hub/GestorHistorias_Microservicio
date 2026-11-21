<?php

namespace App\Config;

/**
 * Datos de configuración del microservicio de sprints.
 * Los valores predeterminados sirven para XAMPP. Si necesitas otros (otra clave
 * de MySQL, por ejemplo) crea el archivo src/Config/local.php, que NO se sube a Git.
 */
class Configuracion
{
    private const PREDETERMINADOS = [
        'bd_servidor' => 'localhost',
        'bd_usuario'  => 'root',
        'bd_clave'    => '',
        'bd_nombre'   => 'ms_sprints_db',
    ];

    public static function obtener(string $clave): string
    {
        $datos = self::PREDETERMINADOS;

        $archivoLocal = __DIR__ . '/local.php';
        if (is_file($archivoLocal)) {
            $datos = array_merge($datos, require $archivoLocal);
        }
        return $datos[$clave];
    }
}
