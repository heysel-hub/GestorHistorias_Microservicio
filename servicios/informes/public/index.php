<?php
// Punto de entrada del microservicio de INFORMES (aplicación Slim).
// Este servicio NO tiene base de datos: pide los datos a los otros servicios.

use App\Controlador\InformeControlador;
use App\Modelo\ClienteHistorias;
use App\Modelo\ClienteHttp;
use App\Modelo\ClienteSprints;
use App\Vista\VistaJson;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', '0');
date_default_timezone_set('America/Bogota');

$app = AppFactory::create();
$app->setBasePath(dirname($_SERVER['SCRIPT_NAME']));
$app->addRoutingMiddleware();
$app->addErrorMiddleware(false, false, false);

$http = new ClienteHttp();
$controlador = new InformeControlador(
    new VistaJson(),
    new ClienteHistorias($http),
    new ClienteSprints($http)
);

// RUTAS
$app->get('/informes', [$controlador, 'generar']);

$app->run();
