<?php
// Punto de entrada del microservicio de HISTORIAS (aplicación Slim).

use App\Controlador\HistoriaControlador;
use App\Modelo\ClienteHttp;
use App\Modelo\ClienteSprints;
use App\Modelo\Conexion;
use App\Modelo\HistoriaRepositorio;
use App\Vista\VistaJson;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', '0');
date_default_timezone_set('America/Bogota');

$app = AppFactory::create();
$app->setBasePath(dirname($_SERVER['SCRIPT_NAME']));
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(false, false, false);

$controlador = new HistoriaControlador(
    new VistaJson(),
    new HistoriaRepositorio(new Conexion()),
    new ClienteSprints(new ClienteHttp())
);

// RUTAS
$app->get('/historias', [$controlador, 'listar']);                  // opcional: ?sprint_id=2
$app->get('/historias/{id:[0-9]+}', [$controlador, 'obtener']);
$app->post('/historias', [$controlador, 'crear']);
$app->put('/historias/{id:[0-9]+}', [$controlador, 'modificar']);
$app->delete('/historias/{id:[0-9]+}', [$controlador, 'eliminar']);

$app->run();
