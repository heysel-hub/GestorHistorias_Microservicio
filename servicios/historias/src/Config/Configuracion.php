<?php

namespace App\Config;

/**
 * Configuración del microservicio de historias.
 * Si necesitas otros valores, crea src/Config/local.php (no se sube a Git).
 */
class Configuracion
{
    private const PREDETERMINADOS = [
        'bd_servidor' => 'localhost',
        'bd_usuario'  => 'root',
        'bd_clave'    => '',
        'bd_nombre'   => 'ms_historias_db',
        // Dirección del servicio de sprints. Vacía = se calcula sola.
        // Ejemplo: 'http://localhost/gestor-microservicios/servicios/sprints/public/sprints'
        'url_sprints' => '',
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

    /** Dirección del recurso de otro servicio (están en carpetas hermanas dentro de "servicios/"). */
    public static function urlServicio(string $nombre): string
    {
        $manual = self::obtener('url_' . $nombre);
        if ($manual !== '') {
            return $manual;
        }
        $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        // SCRIPT_NAME = /gestor-microservicios/servicios/historias/public/index.php
        $carpetaServicios = dirname($_SERVER['SCRIPT_NAME'], 3);
        return $esquema . '://' . $_SERVER['HTTP_HOST'] . $carpetaServicios . '/' . $nombre . '/public/' . $nombre;
    }
}
