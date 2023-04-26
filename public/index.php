<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../utils/hash.php';

$app = AppFactory::create();

$app->setBasePath('/api');
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response
    ->withHeader('Access-Control-Allow-Origin', '*')
    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/', function (Request $request, Response $response) {
  $params = $request->getQueryParams();
  $response->getBody()->write($params["id"]);
  return $response->withHeader("Content-Type", "application/json")->withStatus(200);
});

require __DIR__ . '/../routes/test.php';
//
require __DIR__ . '/../routes/admin.php';
require __DIR__ . '/../routes/company.php';
require __DIR__ . '/../routes/circular.php';
require __DIR__ . '/../routes/field.php';

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
  throw new HttpNotFoundException($request);
});

$app->run();
