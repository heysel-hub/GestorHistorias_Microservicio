<?php
// Punto de entrada del microservicio de SPRINTS (aplicación Slim).
// Aquí solo se arma la aplicación y se declaran las rutas.

use App\Controlador\SprintControlador;
use App\Modelo\Conexion;
use App\Modelo\SprintRepositorio;
use App\Vista\VistaJson;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';   // lo genera Composer

ini_set('display_errors', '0');                // los errores no deben ensuciar el JSON
date_default_timezone_set('America/Bogota');

$app = AppFactory::create();
$app->setBasePath(dirname($_SERVER['SCRIPT_NAME']));   // el servicio vive dentro de una subcarpeta
$app->addBodyParsingMiddleware();                      // convierte el JSON del cuerpo en arreglo
$app->addRoutingMiddleware();
$app->addErrorMiddleware(false, false, false);

$controlador = new SprintControlador(new VistaJson(), new SprintRepositorio(new Conexion()));

// RUTAS:  método + dirección  =>  [objeto, método del controlador]
$app->get('/sprints', [$controlador, 'listar']);
$app->get('/sprints/{id:[0-9]+}', [$controlador, 'obtener']);
$app->post('/sprints', [$controlador, 'crear']);

$app->run();
