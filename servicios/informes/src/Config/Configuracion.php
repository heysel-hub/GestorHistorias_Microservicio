<?php

namespace App\Config;

/**
 * Configuración del microservicio de informes.
 * Solo necesita saber dónde están los otros servicios.
 * Si hace falta cambiarlas, crea src/Config/local.php (no se sube a Git).
 */
class Configuracion
{
    private const PREDETERMINADOS = [
        // Vacías = se calculan solas (carpetas hermanas dentro de "servicios/").
        'url_sprints'   => '',
        'url_historias' => '',
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

    /** Dirección del recurso de otro servicio. */
    public static function urlServicio(string $nombre): string
    {
        $manual = self::obtener('url_' . $nombre);
        if ($manual !== '') {
            return $manual;
        }
        $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        // SCRIPT_NAME = /gestor-microservicios/servicios/informes/public/index.php
        $carpetaServicios = dirname($_SERVER['SCRIPT_NAME'], 3);
        return $esquema . '://' . $_SERVER['HTTP_HOST'] . $carpetaServicios . '/' . $nombre . '/public/' . $nombre;
    }
}
